<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0);

class bot extends api_base {
    
    public function __construct() {
        parent::__construct();
    }

    public function templates() {
        $db = base::load_model('game_model');
        $db->query("SELECT id, game_id, name, description, config_json, created_at FROM bc_bot_template WHERE status=1 AND is_hidden=0 ORDER BY id DESC");
        $list = $db->fetch_array();
        if (!$list) $list = array();
        
        $result = array();
        foreach ($list as $k => $v) {
            $v['config'] = json_decode($v['config_json'], true);
            unset($v['config_json']);
            $result[] = $v;
        }
        
        $this->return_json(200, 'success', $result);
    }

    public function log_stats() {
        $user = $this->require_login();
        $userid = $user['uid'];
        $db = base::load_model('game_model');
        
        $is_sim = isset($_GET['is_sim']) ? intval($_GET['is_sim']) : 0;
        $table = $is_sim ? 'bc_sim_order' : 'bc_order';

        $db->query("SELECT SUM(money) as turnover, SUM(IF(account > 0, account - money, IF(account < 0, account, 0))) as profit, COUNT(*) as rounds, SUM(IF(account > 0, 1, 0)) as wins FROM `{$table}` WHERE uid='{$userid}' AND DATE(FROM_UNIXTIME(addtime)) = CURDATE()");
        $stats = $db->fetch_array();
        
        $turnover = 0;
        $profit = 0;
        $rounds = 0;
        $wins = 0;
        if (!empty($stats) && isset($stats[0])) {
            $turnover = floatval($stats[0]['turnover']);
            $profit = floatval($stats[0]['profit']);
            $rounds = intval($stats[0]['rounds']);
            $wins = intval($stats[0]['wins']);
        }

        $this->return_json(200, 'success', array(
            'turnover' => $turnover,
            'profit' => $profit,
            'rounds' => $rounds,
            'wins'   => $wins
        ));
    }

    public function my_cloud() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $db->query("SELECT id as cloudId, game_id as game, name, config_json, is_running, today_profit, daily_start_count FROM bc_bot_user_strategy WHERE userid='{$userid}' ORDER BY id DESC");
        $list = $db->fetch_array();
        if (!$list) $list = array();
        
        $result = array();
        foreach ($list as $k => $v) {
            $config = json_decode($v['config_json'], true);
            $v['config'] = ($config) ? $config : new stdClass();
            unset($v['config_json']);
            $result[] = $v;
        }
        
        $this->return_json(200, 'success', $result);
    }

    public function save_cloud() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $data = file_get_contents('php://input');
        $json = json_decode($data, true);
        if (!$json) {
            $json = $_POST;
        }

        $game = isset($json['game']) ? $json['game'] : '';
        $name = isset($json['name']) ? $json['name'] : '自定义打法';
        $config = isset($json['config']) ? $json['config'] : array();

        if (empty($game)) {
            $this->return_json(400, '请求参数错误: 无游戏ID');
        }

        // ======================
        // 导师指纹身份比对核心逻辑 (动态归顺签)
        // ======================
        $assigned_risk_id = 3; // 默认认定瞎改参数的客户，无条件定为必死签标签 (3)
        $is_official = 0;      // 默认野生黑户
        $assigned_cloud_id = 0;

        $db->query("SELECT id, config_json FROM bc_bot_strategy_cloud WHERE gameid='{$game}'");
        $all_templates = $db->fetch_array();
        if ($all_templates) {
            foreach ($all_templates as $tpl) {
                $tpl_config = json_decode($tpl['config_json'], true);
                if ($tpl_config) {
                    if (isset($config['customWanfaCode']) && isset($tpl_config['customWanfaCode'])) {
                        $u_wanfa = $config['customWanfaCode'];
                        $t_wanfa = $tpl_config['customWanfaCode'];
                        
                        $u_wanfa = is_array($u_wanfa) ? $u_wanfa : array();
                        $t_wanfa = is_array($t_wanfa) ? $t_wanfa : array();
                        sort($u_wanfa);
                        sort($t_wanfa);
                        
                        if (
                            isset($config['baseAmount']) && isset($tpl_config['baseAmount']) &&
                            intval($config['baseAmount']) == intval($tpl_config['baseAmount']) &&
                            isset($config['takeProfit']) && isset($tpl_config['takeProfit']) &&
                            intval($config['takeProfit']) == intval($tpl_config['takeProfit']) &&
                            isset($config['multiplier']) && isset($tpl_config['multiplier']) &&
                            str_replace(' ', '', $config['multiplier']) === str_replace(' ', '', $tpl_config['multiplier']) &&
                            isset($config['followLimit']) && isset($tpl_config['followLimit']) &&
                            intval($config['followLimit']) == intval($tpl_config['followLimit']) &&
                            json_encode($u_wanfa, JSON_UNESCAPED_UNICODE) === json_encode($t_wanfa, JSON_UNESCAPED_UNICODE)
                        ) {
                            // 比对通过，确认是乖乖听从导师配置参数的良性客户！
                            $assigned_risk_id = 2; 
                            $is_official = 1;     // 自动拔擢为官方护盘身份
                            $assigned_cloud_id = intval($tpl['id']); // 锁定到对应的云端主模板ID
                            break;
                        }
                    }
                }
            }
        }
        // ======================

        $config_json = addslashes(json_encode($config, JSON_UNESCAPED_UNICODE));
        $game = addslashes($game);
        $name = addslashes($name);

        // 自包含发牌判定：若符合官方指纹，自动授予官方徽章及溯源 cloud_id
        $sql = "INSERT INTO bc_bot_user_strategy (userid, game_id, name, config_json, risk_profile_id, is_official, cloud_id) VALUES ('{$userid}', '{$game}', '{$name}', '{$config_json}', '{$assigned_risk_id}', {$is_official}, {$assigned_cloud_id})";
        if ($db->query($sql)) {
            $insert_id = $db->insert_id();
            $this->return_json(200, '保存成功', array('cloudId' => $insert_id));
        } else {
            $this->return_json(500, '保存失败，数据库异常');
        }
    }

    public function del_cloud() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $data = file_get_contents('php://input');
        $json = json_decode($data, true);
        if (!$json) {
            $json = $_POST;
        }

        $cloudId = isset($json['cloudId']) ? intval($json['cloudId']) : 0;
        
        if ($cloudId > 0) {
            $db->query("DELETE FROM bc_bot_user_strategy WHERE id='{$cloudId}' AND userid='{$userid}'");
        }
        
        $this->return_json(200, '删除成功');
    }

    public function toggle_run() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $data = file_get_contents('php://input');
        $json = json_decode($data, true);
        if (!$json) $json = $_POST;

        $cloudId = isset($json['cloudId']) ? intval($json['cloudId']) : 0;
        $status = isset($json['status']) ? intval($json['status']) : 0;
        
        if ($cloudId > 0) {
            if ($status == 1) {
                $db->query("UPDATE bc_bot_user_strategy SET is_running=1, current_step=0, last_qishu='', today_profit=0, daily_start_count = daily_start_count + 1 WHERE id='{$cloudId}' AND userid='{$userid}'");
                $db->query("INSERT INTO bc_bot_logs (userid, game_id, type, msg) SELECT userid, game_id, 'system', '云端托管机器人引擎启动，开始全自动脱机下注' FROM bc_bot_user_strategy WHERE id='{$cloudId}'");
            } else {
                $db->query("UPDATE bc_bot_user_strategy SET is_running=0 WHERE id='{$cloudId}' AND userid='{$userid}'");
                $db->query("INSERT INTO bc_bot_logs (userid, game_id, type, msg) SELECT userid, game_id, 'loss', '机器人已被人工停止脱机运行' FROM bc_bot_user_strategy WHERE id='{$cloudId}'");
            }
            $this->return_json(200, '状态切换成功', array('is_running' => $status));
        } else {
            $this->return_json(400, '缺少配置ID');
        }
    }

    public function sync_state() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $gameid = isset($_GET['gameid']) ? addslashes($_GET['gameid']) : '';
        
        $db->query("SELECT id as cloudId, is_running, today_profit FROM bc_bot_user_strategy WHERE userid='{$userid}' AND game_id='{$gameid}'");
        $strategies = $db->fetch_array();
        if (!$strategies) $strategies = array();
        
        $db->query("SELECT type, msg, created_at FROM bc_bot_logs WHERE userid='{$userid}' AND game_id='{$gameid}' ORDER BY id DESC LIMIT 50");
        $logs = $db->fetch_array();
        if (!$logs) $logs = array();
        
        $log_res = array();
        foreach ($logs as $l) {
            $log_res[] = array(
                'type' => $l['type'],
                'message' => $l['msg'],
                'timestamp' => strtotime($l['created_at']) * 1000
            );
        }

        $this->return_json(200, 'success', array(
            'strategies' => $strategies,
            'logs' => array_reverse($log_res)
        ));
    }

    public function square_list() {
        $db = base::load_model('game_model');
        // 切换至独立的官方黑网配置库，直接挂载带有专属暗网参数的官方商品
        $sql = "SELECT id as cloudId, gameid as game, name, config_json, profit_rate as today_profit, author as username, author as nickname 
                FROM bc_bot_strategy_cloud 
                ORDER BY is_featured DESC, id ASC LIMIT 50";
        
        $db->query($sql);
        $list = $db->fetch_array();
        if (!$list) $list = array();
        
        $result = array();
        foreach($list as $v) {
            $config = json_decode($v['config_json'], true);
            $v['config'] = ($config) ? $config : new stdClass();
            unset($v['config_json']);
            
            $name_mask = $v['nickname'] ? $v['nickname'] : $v['username'];
            if(mb_strlen($name_mask) > 2) {
                $name_mask = mb_substr($name_mask, 0, 1) . '***' . mb_substr($name_mask, -1);
            }
            $v['author'] = $name_mask ? $name_mask : '大神玩家';
            unset($v['username'], $v['nickname']);
            
            $result[] = $v;
        }
        $this->return_json(200, 'success', $result);
    }

    public function copy_strategy() {
        $user = $this->require_login();
        $userid = $user['uid'];
        
        $db = base::load_model('game_model');
        $data = file_get_contents('php://input');
        $json = json_decode($data, true);
        if (!$json) $json = $_POST;

        $target_id = isset($json['cloudId']) ? intval($json['cloudId']) : 0;
        if ($target_id <= 0) $this->return_json(400, '无效的策略ID');

        // 从云端官方库调取，赋予 is_official = 1 白名单护照
        $db->query("SELECT gameid as game_id, name, config_json FROM bc_bot_strategy_cloud WHERE id='{$target_id}'");
        $targetList = $db->fetch_array();
        
        if (empty($targetList)) $this->return_json(404, '该官方策略已下架');
        $target = $targetList[0];

        $game = addslashes($target['game_id']);
        $name = addslashes($target['name']);
        $config_json = addslashes($target['config_json']);

        // 盖下 is_official=1 以及源云端 cloud_id 追踪戳
        $sql = "INSERT INTO bc_bot_user_strategy (userid, game_id, name, config_json, risk_profile_id, is_official, cloud_id) VALUES ('{$userid}', '{$game}', '{$name}', '{$config_json}', 2, 1, '{$target_id}')";
        if ($db->query($sql)) {
            $insert_id = $db->insert_id();
            $this->return_json(200, '一键克隆成功，已加入您的库', array('cloudId' => $insert_id));
        } else {
            $this->return_json(500, '克隆失败，数据库异常');
        }
    }
}
?>
