<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); 

class user extends api_base {

    private $db_account;

    public function __construct() {
        parent::__construct();
        $this->db_account = base::load_model('account_model');
    }

    /**
     * GET /api/user/profile
     * 获取用户基础资料
     */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        // 先判断是否设置了资金密码，再删除敏感字段
        $user['has_fund_pwd'] = !empty($user['money_password']);
        $user['unit'] = isset($user['unit']) ? $user['unit'] : 'yuan';

        // 过滤掉敏感信息
        unset($user['password']);
        unset($user['encrypt']);
        unset($user['money_password']);
        unset($user['money_encrypt']);

        $this->return_json(200, '获取成功', $user);
    }

    /**
     * GET /api/user/balance
     * 高频刷新用户可用余额
     */
    public function balance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();

        $unit = isset($user['unit']) ? $user['unit'] : 'yuan';
        if (!in_array($unit, array('yuan', 'jiao', 'fen', 'li'))) {
            $unit = 'yuan';
        }
        
        // 原始代码使用了 money_fmt 和 money_from_yuan，为保证精确性，API 默认返回元，或者根据设置转换
        // 如果我们统一用元做交易，最好返回实际的 double，$user['money'] 本身就是元
        if (!function_exists('money_fmt')) {
            base::load_sys_func('global');
        }
        
        $balance_formatted = function_exists('money_from_yuan') ? money_fmt(money_from_yuan($user['money'], $unit)) : $user['money'];
        $sim_balance_formatted = function_exists('money_from_yuan') ? money_fmt(money_from_yuan($user['sim_money'], $unit)) : $user['sim_money'];

        $this->return_json(200, '获取成功', array(
            'balance' => $user['money'], // 真实数字供程序处理
            'balance_display' => $balance_formatted, // 格式化后供显示使用
            'sim_balance' => $user['sim_money'],
            'sim_balance_display' => $sim_balance_formatted,
            'unit' => $unit
        ));
    }

    /**
     * POST /api/user/update_info
     * 修改基础资料
     */
    public function update_info() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $nickname = isset($_POST['nickname']) ? safe_replace(trim($_POST['nickname'])) : '';
        $oldnickname = isset($_POST['oldnickname']) ? safe_replace(trim($_POST['oldnickname'])) : '';
        
        if (strlen($nickname) > 30) {
            $this->return_json(400, '昵称长度为30字符内!');
        } elseif ($nickname && str_allexists($nickname, $this->get_settings('userfilter'))) {
            $this->return_json(400, '昵称包含系统禁用的字符!');
        } elseif ($nickname && $oldnickname != $nickname && base::load_model('user_model')->get_one(array('nickname' => $nickname))) {
            $this->return_json(400, '昵称已被使用!');
        }

        $update = array();
        $update['nickname'] = $nickname;
        
        // 不允许随意修改，只有当原有字段为空时才允许设置（依据原先安全规则）
        if (empty($user['name']) && isset($_POST['name'])) {
            $update['name'] = safe_replace(trim($_POST['name']));
        }
        if (empty($user['qq']) && isset($_POST['qq'])) {
            $update['qq'] = safe_replace(trim($_POST['qq']));
        }
        if (empty($user['mobile']) && isset($_POST['mobile'])) {
            $update['mobile'] = safe_replace(trim($_POST['mobile']));
        }
        if (empty($user['bank']) && isset($_POST['bank'])) {
            $update['bank'] = safe_replace(trim($_POST['bank']));
        }
        if (empty($user['card']) && isset($_POST['card'])) {
            $update['card'] = safe_replace(trim($_POST['card']));
        }
        if (isset($_POST['alipay'])) {
            $new_alipay = safe_replace(trim($_POST['alipay']));
            if (!empty($new_alipay)) {
                if (empty($user['alipay'])) {
                    $update['alipay'] = $new_alipay;
                } else {
                    $existing = explode(',', $user['alipay']);
                    if (!in_array($new_alipay, $existing)) {
                        if (count($existing) >= 5) {
                            $this->return_json(400, '最多只能绑定5个USDT地址');
                        }
                        $update['alipay'] = $user['alipay'] . ',' . $new_alipay;
                    } else {
                        // 避免因返回操作失败抛出500影响Vue前端
                        $this->return_json(400, '该USDT地址已被绑定');
                    }
                }
            }
        }
        
        if (isset($_POST['unit'])) {
            $unit = safe_replace(trim($_POST['unit']));
            if (!in_array($unit, array('yuan', 'jiao', 'fen', 'li'))) {
                $unit = 'yuan';
            }
            $update['unit'] = $unit;
        }

        if (base::load_model('user_model')->update($update, array('uid' => $user['uid']))) {
            $this->return_json(200, '保存成功');
        } else {
            $this->return_json(500, '保存失败，请稍后重试');
        }
    }

    /**
     * POST /api/user/password
     * 修改登录密码
     */
    public function password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $oldpassword = safe_replace(trim($_POST['oldpassword']));
        $newpassword = safe_replace(trim($_POST['newpassword']));

        if (strlen($newpassword) > 20 || strlen($newpassword) < 6) {
            $this->return_json(400, '密码长度为6-20字符');
        } elseif (md5(md5($oldpassword) . $user['encrypt']) != $user['password']) {
            $this->return_json(400, '旧密码验证错误');
        }

        list($password, $encrypt) = creat_password($newpassword);
        if (base::load_model('user_model')->update(array('password' => $password, 'encrypt' => $encrypt), array('uid' => $user['uid']))) {
            
            // 可选：修改完密码清空登录态
            set_cookie('password', '', 0);
            
            $this->return_json(200, '密码修改成功，请重新登录');
        } else {
            $this->return_json(500, '修改失败，请稍后重试');
        }
    }

    /**
     * POST /api/user/fund_password
     * 修改资金密码
     */
    public function fund_password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $oldpassword = safe_replace(trim($_POST['oldpassword']));
        $newpassword = safe_replace(trim($_POST['newpassword']));

        if (strlen($newpassword) > 20 || strlen($newpassword) < 6) {
            $this->return_json(400, '密码长度为6-20字符');
        } elseif (!empty($user['money_password']) && md5(md5($oldpassword) . $user['money_encrypt']) != $user['money_password']) {
            $this->return_json(400, '旧密码验证错误');
        }

        list($password, $encrypt) = creat_password($newpassword);
        if (base::load_model('user_model')->update(array('money_password' => $password, 'money_encrypt' => $encrypt), array('uid' => $user['uid']))) {
            $this->return_json(200, '资金密码修改成功');
        } else {
            $this->return_json(500, '修改失败，请稍后重试');
        }
    }
}
?>
