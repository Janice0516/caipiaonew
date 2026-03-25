<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); 

class finance extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * GET /api/finance/recharge_channels
     * 获取可用充值通道及收款信息
     */
    public function recharge_channels() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $agent_db = null;
        
        $config = array();
        if ($user['agent']) {
            $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '{$user['agent']}'");
            if ($agent_db) {
                $config = unserialize($agent_db['agentconfig']);
            }
        }

        if (!$agent_db || empty($config) || !is_array($config)) {
            $config = array();
            $config['wxewm'] = $this->get_settings('wxewm');
            $config['aliewm'] = $this->get_settings('aliewm');
            $config['card'] = $this->get_settings('card');
        }

        // 返回通道数据，前端根据返回的数据决定显示哪些充值通道
        $channels = array();
        
        // 假设 wxewm, aliewm 为二维码图片名，card 为银行卡富文本信息
        if (!empty($config['wxewm'])) {
            $channels[] = array('type' => 'wx', 'name' => '微信支付', 'info' => 'uppic/ewm/' . $config['wxewm']);
        }
        if (!empty($config['aliewm'])) {
            $channels[] = array('type' => 'ali', 'name' => '数字货币', 'info' => 'uppic/ewm/' . $config['aliewm']);
        }
        if (!empty($config['card'])) {
            $channels[] = array('type' => 'bank', 'name' => '银行卡转账', 'info' => $config['card']);
        }

        $this->return_json(200, '获取成功', array(
            'min_pay'            => $this->get_settings('pay'),
            'usdt_rate'          => floatval($this->get_settings('hblv1') ?: 7.2), // USDT充值汇率
            'usdt_withdraw_rate' => floatval($this->get_settings('hblv2') ?: 7.2), // USDT提现汇率
            'usdt_address'       => $this->get_settings('hbname2') ?: '',          // 平台 USDT 收款地址
            'channels'           => $channels
        ));
    }

    /**
     * POST /api/finance/recharge_submit
     * 提交充值单
     */
    public function recharge_submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $money = isset($_POST['money']) ? round(trim($_POST['money']), 3) : 0;
        $comment = isset($_POST['comment']) ? safe_replace(trim($_POST['comment'])) : '';
        $pay_type = isset($_POST['pay_type']) ? safe_replace(trim($_POST['pay_type'])) : '';

        if ($money < $this->get_settings('pay')) {
            $this->return_json(400, '充值金额低于最低限制');
        }

        $agents = 0;
        if ($user['agent']) {
            $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '{$user['agent']}'");
            if ($agent_db && $agent_db['aid'] == 3) {
                $agents = $agent_db['agent'];
                // 向上追溯到顶级代理
                while (true) {
                    $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '$agents'");
                    if (!$agent_db) break;
                    $agents = $agent_db['agent'];
                    if ($agent_db['aid'] == 1) break;
                }
            }
        }

        $payid = date('YmdHis', SYS_TIME) . random(6, '1234567890');
        $db = base::load_model('pay_model');
        
        $paydb = array(
            'uid'     => $user['uid'],
            'agent'   => $user['agent'],
            'agents'  => $agents,
            'payid'   => $payid,
            'money'   => $money,
            'state'   => 0,
            'addtime' => SYS_TIME,
            'comment' => $comment,
            'paytype' => $pay_type
        );
        
        if ($db->insert($paydb)) {
            $this->return_json(200, '订单创建成功，请等待审核', array(
                'payid' => $payid,
                'money' => $money
            ));
        } else {
            $this->return_json(500, '订单创建失败');
        }
    }

    /**
     * POST /api/finance/withdraw_submit
     * 提交提现单
     */
    public function withdraw_submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        if(empty($user['money_password'])){
            $this->return_json(400, '请先设置资金密码');
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $money = isset($_POST['money']) ? round(trim($_POST['money']), 3) : 0;
        $account_type = isset($_POST['account_type']) ? intval($_POST['account_type']) : 1; // 1:银联 2:数字货币
        $comment = isset($_POST['comment']) ? safe_replace(trim($_POST['comment'])) : '';
        $money_password = isset($_POST['money_password']) ? $_POST['money_password'] : '';

        if ($money < $this->get_settings('tixian')) {
            $this->return_json(400, '提现金额低于最低限制');
        }
        if ($money > $user['money']) {
            $this->return_json(400, '余额不足');
        }
        if ($money > $this->get_settings('maxcash')) {
            $this->return_json(400, '超过单笔最高提现限制');
        }

        if ($account_type == 1) {
            if (empty($user['name']) || empty($user['bank']) || empty($user['card'])) {
                $this->return_json(400, '未绑定银行卡信息，请先完善资料');
            }
            $from = $user['name'] . ' ' . $user['bank'] . '：' . $user['card'];
        } else {
            if (empty($user['name']) || empty($user['alipay'])) {
                $this->return_json(400, '未绑定数字货币地址，请先完善资料');
            }
            $target_address = isset($_POST['target_address']) ? safe_replace(trim($_POST['target_address'])) : '';
            $addresses = explode(',', $user['alipay']);
            if ($target_address && in_array($target_address, $addresses)) {
                $addr = $target_address;
            } else {
                $addr = $addresses[0]; // 默认使用第一个
            }
            $from = $user['name'] . ' 数字货币：' . $addr;
        }

        if (md5(md5($money_password) . $user['money_encrypt']) != $user['money_password']) {
            $this->return_json(400, '资金密码错误');
        }

        // 提现时间限制
        $start_time = strtotime(date("Y-m-d " . $this->get_settings('cash_limit_start_time')));
        $end_time = strtotime(date("Y-m-d " . $this->get_settings('cash_limit_end_time')));
        if(time() > $end_time || time() < $start_time){
            $this->return_json(400, $this->get_settings('pay_error_remind'));
        }

        // 打码量限制
        if($user['dama'] < $user['aims_dama']){
            $this->return_json(400, '流水打码量未完成');
        }

        $uid = $user['uid'];
        $user_model = base::load_model('user_model');
        
        if ($user_model->update(array('money' => '-=' . $money), array('uid' => $uid))) {
            
            // 计算提现手续费
            $cash_fee_setting = $this->get_settings('cash');
            if (str_exists($cash_fee_setting, '%')) {
                $service = round($money * rtrim($cash_fee_setting / 100, '%'), 3);
            } else {
                $service = round($cash_fee_setting, 3);
            }

            $agents = 0;
            if ($user['agent']) {
                $agent_db = $user_model->get_one("aid > 0 AND uid = '{$user['agent']}'");
                if ($agent_db && $agent_db['aid'] == 3) {
                    $agents = $agent_db['agent'];
                }
            }

            base::load_model('cash_model')->insert(array(
                'uid' => $uid,
                'agent' => $user['agent'],
                'agents' => $agents,
                'money' => $money,
                'service' => $service,
                'from' => $from,
                'state' => 0,
                'addtime' => SYS_TIME,
                'comment' => $comment
            ));

            // 流水日志
            $moneydb = round(0 - floatval($money), 3);
            $countmoney = round(floatval($user['money']) - floatval($money), 3);
            base::load_model('account_model')->insert(array(
                'uid' => $uid,
                'money' => $moneydb,
                'countmoney' => $countmoney,
                'type' => 1,
                'addtime' => SYS_TIME,
                'comment' => '提现申请'
            ));

            $this->return_json(200, '提现申请提交成功，请等待审核');
        } else {
            $this->return_json(500, '提现操作失败，请重试');
        }
    }

    /**
     * GET /api/finance/record_list
     * 获取账单流水记录 (结合了 pay_list, cash_list, account_list)
     */
    public function record_list() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $type = isset($_GET['type']) ? trim($_GET['type']) : 'all'; // all, recharge, withdraw
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        
        $start = ($page - 1) * $limit;
        $uid = $user['uid'];
        
        if ($type == 'recharge') {
            // from account_model where comment contains '充值' (usually type 5 or others depending on admin/user)
            $db = base::load_model('account_model');
            $list = $db->select("uid = '$uid' AND comment LIKE '%充值%'", '*', "$start, $limit", 'id DESC');
            $total = $db->count("uid = '$uid' AND comment LIKE '%充值%'");
        } elseif ($type == 'withdraw') {
            // Withdraws are type 1 or comment contains 提现
            $db = base::load_model('account_model');
            $list = $db->select("uid = '$uid' AND (type = 1 OR comment LIKE '%提现%')", '*', "$start, $limit", 'id DESC');
            $total = $db->count("uid = '$uid' AND (type = 1 OR comment LIKE '%提现%')");
        } elseif ($type == 'bet') {
            // Bets are type 2
            $db = base::load_model('account_model');
            $list = $db->select("uid = '$uid' AND type = 2", '*', "$start, $limit", 'id DESC');
            $total = $db->count("uid = '$uid' AND type = 2");
        } else {
            // all 余额流水
            $db = base::load_model('account_model');
            $list = $db->select(array('uid' => $uid), '*', "$start, $limit", 'id DESC');
            $total = $db->count(array('uid' => $uid));
        }

        $this->return_json(200, '获取成功', array(
            'type'  => $type,
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ));
    }
}
?>
