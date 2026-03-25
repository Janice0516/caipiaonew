<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('admin', 'admin', 0);

class bot_template extends admin {
    public function __construct() {
        parent::__construct(1);
    }

    public function init() {
        $db = base::load_model('game_model');
        $page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $pagesize = 20;
        $offset = ($page - 1) * $pagesize;
        
        // 获取带游戏名称的模板列表
        $list_raw = $db->query("SELECT t.*, g.name as gamename FROM bc_bot_template t LEFT JOIN bc_game g ON t.game_id = g.id ORDER BY t.id DESC LIMIT $offset, $pagesize");
        $list = array();
        while($r = $db->fetch_array($list_raw)) { $list[] = $r; }
        
        $total_res = $db->query("SELECT COUNT(*) as num FROM bc_bot_template");
        $total_row = $db->fetch_array($total_res);
        $total = $total_row['num'];
        
        $pages = pages($total, $page, $pagesize);
        
        include $this->admin_tpl('bot_template_list');
    }

    public function add() {
        $db = base::load_model('game_model');
        if (isset($_POST['dosubmit'])) {
            $game_id = intval($_POST['info']['game_id']);
            $name = safe_replace(trim($_POST['info']['name']));
            $description = safe_replace(trim($_POST['info']['description']));
            $config_json = trim($_POST['info']['config_json']);
            
            if (!$name || !$game_id) showmessage('参数错误', HTTP_REFERER);
            
            $c = $_POST['config'];
            $targets = array_map('trim', explode(',', $c['customTarget']));
            $targets = array_filter($targets);
            $config_arr = array(
                'baseAmount' => floatval($c['baseAmount']) ? floatval($c['baseAmount']) : 10,
                'unit' => intval($c['unit']) ? intval($c['unit']) : 1,
                'multiplier' => trim($c['multiplier']) ? trim($c['multiplier']) : '1,2,4,8',
                'takeProfit' => floatval($c['takeProfit']),
                'stopLoss' => floatval($c['stopLoss']),
                'customTarget' => empty($targets) ? array('冠军大') : array_values($targets)
            );
            $config_json = json_encode($config_arr, JSON_UNESCAPED_UNICODE);
            $config_json_safe = addslashes($config_json);

            $db->query("INSERT INTO bc_bot_template (game_id, name, description, config_json) VALUES ('{$game_id}', '{$name}', '{$description}', '{$config_json_safe}')");
            showmessage('添加模板成功', '?m=admin&c=bot_template&a=init');
        }
        $config = array(); // Default empty config for addition
        $games_raw = $db->query("SELECT id, name, template FROM bc_game WHERE state=1 ORDER BY sort ASC");
        $games = array();
        while($r = $db->fetch_array($games_raw)) { $games[] = $r; }
        
        include $this->admin_tpl('bot_template_edit');
    }

    public function edit() {
        $db = base::load_model('game_model');
        $id = intval($_GET['id']);
        if (!$id) showmessage('参数错误', HTTP_REFERER);
        
        if (isset($_POST['dosubmit'])) {
            $game_id = intval($_POST['info']['game_id']);
            $name = safe_replace(trim($_POST['info']['name']));
            $description = safe_replace(trim($_POST['info']['description']));
            $config_json = trim($_POST['info']['config_json']);
            
            if (!$name || !$game_id) showmessage('参数错误', HTTP_REFERER);
            
            // 解析图形化表单
            $c = $_POST['config'];
            $targets = array_map('trim', explode(',', $c['customTarget']));
            $targets = array_filter($targets);
            $config_arr = array(
                'baseAmount' => floatval($c['baseAmount']) ? floatval($c['baseAmount']) : 10,
                'unit' => intval($c['unit']) ? intval($c['unit']) : 1,
                'multiplier' => trim($c['multiplier']) ? trim($c['multiplier']) : '1,2,4,8',
                'takeProfit' => floatval($c['takeProfit']),
                'stopLoss' => floatval($c['stopLoss']),
                'customTarget' => empty($targets) ? array('冠军大') : array_values($targets)
            );
            $config_json = json_encode($config_arr, JSON_UNESCAPED_UNICODE);
            $config_json_safe = addslashes($config_json);

            $db->query("UPDATE bc_bot_template SET game_id='{$game_id}', name='{$name}', description='{$description}', config_json='{$config_json_safe}' WHERE id='{$id}'");
            showmessage('修改模板成功', '?m=admin&c=bot_template&a=init');
        }
        
        $info_raw = $db->query("SELECT * FROM bc_bot_template WHERE id='{$id}'");
        $info = $db->fetch_array($info_raw);
        $config = json_decode($info['config_json'], true);

        $games_raw = $db->query("SELECT id, name, template FROM bc_game WHERE state=1 ORDER BY sort ASC");
        $games = array();
        while($r = $db->fetch_array($games_raw)) { $games[] = $r; }
        
        include $this->admin_tpl('bot_template_edit');
    }

    public function del() {
        $db = base::load_model('game_model');
        $id = intval($_GET['id']);
        if ($id) {
            $db->query("DELETE FROM bc_bot_template WHERE id='{$id}'");
            showmessage('删除模板成功', HTTP_REFERER);
        } else {
            showmessage('参数错误', HTTP_REFERER);
        }
    }
}
?>
