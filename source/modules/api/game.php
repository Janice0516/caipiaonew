<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); 

class game extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * GET /api/game/lobby_list
     * 获取游戏大厅列表及状态
     */
    public function lobby_list() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();

        $game_open = array();
        $agent = $user['agent'];
        if ($agent) {
            $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '$agent'", 'aid,agent,agentconfig');
            if ($agent_db['aid'] == 3) {
                $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '$agent_db[agent]'", 'aid,agent,agentconfig');
            }
            if ($agent_db) {
                $config = unserialize($agent_db['agentconfig']);
                $game_open = isset($config['gameid']) ? $config['gameid'] : array();
            }
        }

        $db = base::load_model('game_model');
        $where = "state = 1";
        if ($game_open) {
            $where .= " AND id IN (" . implode(",", $game_open) . ")";
        }

        // 默认过滤番摊模板，如果前端需要可以通过参数获取
        $type = isset($_GET['type']) ? trim($_GET['type']) : 'index';
        if ($type == 'index') {
            $where .= " AND template != 'fantan'";
        } elseif ($type == 'fantan') {
            $where .= " AND template = 'fantan'";
        }

        $gamedb = $db->select($where, 'id, name, template, state, fptime', '', 'sort ASC, id DESC');
        
        $games_out = array();
        if (is_array($gamedb)) {
            $haoma_db = base::load_model('haoma_model');
            foreach ($gamedb as $g) {
                $g['gameid'] = $g['id'];
                // 附加最近一期开奖号码
                $last = $haoma_db->get_one("gameid = '{$g['id']}' AND haoma != '' AND is_lottery = 1", 'qishu, haoma, sendtime', 'id DESC');
                $g['last_issue'] = $last ? $last['qishu'] : '';
                $g['last_haoma'] = $last ? $last['haoma'] : '';
                $g['last_time']  = $last ? $last['sendtime'] : 0;
                $games_out[] = $g;
            }
        }
        
        $this->return_json(200, '获取成功', array(
            'games' => $games_out,
            'announcement' => $this->get_settings('ann'),
            'banners' => $this->get_settings('frontend_banners')
        ));
    }

    /**
     * GET /api/game/room_config
     * 获取指定游戏的房间列表、限定额度
     */
    public function room_config() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $this->require_login();
        
        $gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
        if (!$gameid) {
            $this->return_json(400, '参数错误：未指定gameid');
        }

        $room = base::load_config('room/room_' . $gameid);
        
        if (empty($room)) {
            $this->return_json(400, '游戏房间配置不存在');
        }

        $rooms_out = array();
        foreach ($room as $idx => $r) {
            $rooms_out[] = array(
                'roomid'  => $idx + 1,
                'name'    => isset($r['name']) ? $r['name'] : '房间',
                'minimum' => isset($r['minimum']) ? $r['minimum'] : 0,
                'maximum' => isset($r['maximum']) ? $r['maximum'] : 0
            );
        }

        $this->return_json(200, '获取成功', array(
            'gameid' => $gameid,
            'rooms'  => $rooms_out
        ));
    }

    /**
     * GET /api/game/odds_data
     * 获取指定游戏的原始赔率及玩法字典
     */
    public function odds_data() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
        if (!$gameid) {
            $this->return_json(400, '参数错误：未指定gameid');
        }

        // 优先从不同房间加载定制的玩法，如果没有传roomid，使用第一组数据作为默认玩法
        $roomid = isset($_GET['roomid']) ? intval($_GET['roomid']) : 1;
        $roomid_index = $roomid - 1;

        $db = base::load_model('game_model');
        $gamedata = $db->get_one(array('id' => $gameid, 'state' => 1), 'data');
        if (!$gamedata) {
            $this->return_json(400, '获取游戏数据失败');
        }

        // 处理从房间配置中读取数据
        $room_cfg = base::load_config('room/room_' . $gameid);
        $final_data_str = '';

        if (!empty($room_cfg) && isset($room_cfg[$roomid_index])) {
            $roomConf = $room_cfg[$roomid_index];
            if (empty($roomConf['show_data'])) {
                $final_data_str = serialize(array($roomConf['data']));
            } else {
                $final_data_str = serialize(array($roomConf['show_data']));
            }
        } else {
            $final_data_str = $gamedata['data'];
        }

        $dataarr = unserialize($final_data_str);
        $datalist = array();
        if ($dataarr && is_array($dataarr)) {
            foreach($dataarr as $k => $data) {
                // 每种玩法按换行分割，这部分和原版逻辑严格一致
                $datalist[($k+1)] = explode("\n", str_replace(array("\r\n", "\r"), "\n", $data));
            }
        }

        // 计算当前用户的动态奖金组加成
        $user_rebate = isset($user['rebate']) ? intval($user['rebate']) : 1996;
        $rebate_ratio = $user_rebate / 1996;

        $this->return_json(200, '获取成功', array(
            'gameid' => $gameid,
            'roomid' => $roomid,
            'rebate' => $user_rebate,     // 下发奖金组
            'ratio'  => $rebate_ratio,    // 下发计算比例
            'wanfa'  => $datalist         // 前端拿到后，自己结合比例重新渲染赔率
        ));
    }
}
?>
