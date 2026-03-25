<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('admin', 'admin', 0);

class bot extends admin {
    public function __construct() {
        parent::__construct(1);
    }

    public function init() {
        $db = base::load_model('game_model'); // 借用一下模型来获取通用 DB 连接

        if (isset($_POST['dosubmit'])) {
            $setting_arr = $_POST['setting'];
            foreach($setting_arr as $k => $v) {
                $v = safe_replace(trim($v));
                $k = safe_replace(trim($k));
                $db->query("INSERT INTO bc_bot_settings (k, v) VALUES ('{$k}', '{$v}') ON DUPLICATE KEY UPDATE v='{$v}'");
            }
            showmessage('风控参数更新成功！', HTTP_REFERER);
        }

        // 默认参数兜底
        $risk_enabled = '0';
        $risk_window_size = '10';
        $risk_kill_profit_threshold = '500';
        $risk_save_loss_threshold = '-1000';

        $settings_raw = $db->query("SELECT k, v FROM bc_bot_settings");
        $settings = array();
        while($r = $db->fetch_array($settings_raw)) {
            $settings[] = $r;
        }
        if($settings) {
            foreach($settings as $row) {
                $k = $row['k'];
                $$k = $row['v'];
            }
        }

        include $this->admin_tpl('bot_settings');
    }
}
?>
