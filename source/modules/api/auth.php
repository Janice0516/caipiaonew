<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); 

class auth extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * POST /api/auth/login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }
        
        // 兼容前端可能传 JSON 的情况
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        if (!$this->check_code($code)) {
            $this->return_json(400, '验证码错误或已过期');
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if(empty($username) || empty($password)) {
            $this->return_json(400, '账号或密码不能为空');
        }

        $user = $this->check_user($username, $password);
        if ($user) {
            $_SESSION['userid'] = get_cookie('uid');
            
            // 为了安全，不返回密码等敏感信息
            unset($user['password']);
            unset($user['encrypt']);
            unset($user['money_password']);
            unset($user['money_encrypt']);
            
            // 下发一个假的 JWT Token，后续可深度改造为真正的 JWT。
            // 目前通过 check_user 设置了 cookie，支持后端校验。
            $pseudo_token = base64_encode($user['uid'] . ':' . time());
            
            $this->return_json(200, '登录成功', array(
                'token' => $pseudo_token,
                'user'  => $user
            ));
        } else {
            $this->return_json(400, '账户或密码错误或已被锁定');
        }
    }

    /**
     * POST /api/auth/register
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        if (!$this->check_code($code)) {
            $this->return_json(400, '验证码错误或已过期');
        }

        $username = safe_replace(trim($_POST['username']));
        $name = safe_replace(trim($_POST['name']));
        $password = safe_replace(trim($_POST['password']));
        $agent = safe_replace(trim($_POST['agent'])); // 邀请码

        $namelen = @iconv_strlen($name, 'UTF-8');
        if ($namelen > 5 || $namelen < 2) {
            $this->return_json(400, '姓名应该为2-5个字');
        } elseif (strlen($password) > 20 || strlen($password) < 6) {
            $this->return_json(400, '密码长度为6-20字符');
        } elseif (str_allexists($username, $this->get_settings('userfilter'))) {
            $this->return_json(400, '用户名包含系统禁用的字符');
        } elseif (base::load_model('user_model')->get_one(array('username' => $username))) {
            $this->return_json(400, '用户名已被注册');
        }

        // 检查代理邀请码
        $agent_uid = 0;
        $agent_db = null;
        $invite_link_rebate = 0;
        if(empty($agent)){
            /* 若必须填邀请码可取消注释
            $this->return_json(400, '请填写邀请码');
            */
        } else {
            // 首先检查是否是专属邀请链接码
            $invite_link = base::load_model('invite_link_model')->get_one("invite_code = '$agent' AND status = 1");
            if ($invite_link) {
                $agent_uid = $invite_link['uid'];
                $invite_link_rebate = $invite_link['rebate'];
                $agent_db = base::load_model('user_model')->get_one(array('uid' => $agent_uid));
            } else {
                // 回退到 支持 UID 和用户名作为邀请码
                $agent_db = base::load_model('user_model')->get_one("uid = '$agent' OR username = '$agent'");
                if (!$agent_db) {
                    $this->return_json(400, '请填写正确的邀请码');
                }
                $agent_uid = $agent_db['uid'];
            }
            
            // 原逻辑要求 aid > 0，这里根据用户需求放宽，或者只要用户存在即认为有效
            // 如果需要严格限制只有代理能邀请，可以保留 aid > 0
            // 但用户反馈 8848global（aid=0）也应该是对的，所以这里不做 aid > 0 的强制限制
            if ($agent_db && $agent_db['aid'] == 3) {
                $send['agents'] = $agent_db['agent'];
            }
        }

        // 注册赠金
        $gift = unserialize(urldecode($this->get_settings('gift')));
        $register_gift = 0;
        if(!empty($gift['registerMoneyMin']) && !empty($gift['registerMoneyMax'])){
            $register_gift = intval(mt_rand($gift['registerMoneyMin'], $gift['registerMoneyMax']));
        }

        list($newpassword, $encrypt) = creat_password($password);
        $money = round($this->get_settings('money') + $register_gift, 3);
        
        $send['money'] = $money;
        $send['username'] = $username;
        $send['name'] = $name;
        $send['password'] = $newpassword;
        $send['encrypt'] = $encrypt;
        $send['agent'] = $agent_uid;
        
        // 自动设置奖金组/返点
        $send['rebate'] = 1996;
        if ($invite_link_rebate > 0) {
            $send['rebate'] = $invite_link_rebate;
        } elseif ($agent_uid > 0 && isset($agent_db['rebate'])) {
            $parent_rebate = intval($agent_db['rebate']);
            $child_rebate = $parent_rebate - 6; 
            if ($child_rebate < 1800) $child_rebate = 1800;
            $send['rebate'] = $child_rebate;
        }
        
        $send['regtime'] = SYS_TIME;
        $send['free_dama'] = $this->get_settings('init_dama');
        $send['dama'] = $this->get_settings('init_dama');
        $send['aims_dama'] = $money;
        
        if (base::load_model('user_model')->insert($send)) {
            $uid = base::load_model('user_model')->insert_id();
            
            if($register_gift > 0){
                base::load_model('account_model')->insert(array(
                    'uid'=>$uid,
                    'money'=>$register_gift,
                    'countmoney'=>$money,
                    'type'=>5,
                    'addtime'=>SYS_TIME,
                    'comment'=>'新用户注册送金额'
                ));
            }

            // 注册成功后自动执行登录？通常可以要求用户手动登录，或直接下发 Token
            $this->return_json(200, '注册成功', array('uid' => $uid));
        } else {
            $this->return_json(500, '注册失败，请稍后重试');
        }
    }

    /**
     * GET /api/auth/captcha
     * 直接输出图片二进制数据，用于 <img src="...">
     */
    public function captcha() {
        $checkcode = base::load_sys_class('checkcode');
        $checkcode->code_len = isset($_GET['code_len']) && $_GET['code_len'] ? intval($_GET['code_len']) : 4;
        $checkcode->font_size = isset($_GET['font_size']) && $_GET['font_size'] ? intval($_GET['font_size']) : 14;
        $checkcode->width = isset($_GET['width']) && $_GET['width'] ? intval($_GET['width']) : 84;
        $checkcode->height = isset($_GET['height']) && $_GET['height'] ? intval($_GET['height']) : 24;
        $checkcode->font_color = isset($_GET['font_color']) && $_GET['font_color'] ? trim(urldecode($_GET['font_color'])) : '#000000';
        $checkcode->background = isset($_GET['background']) && $_GET['background'] ? trim(urldecode($_GET['background'])) : '#F7F7F7';
        $checkcode->charset = isset($_GET['charset']) && $_GET['charset'] ? trim($_GET['charset']) : '196152064981065096874984203547865015647';
        $checkcode->doimage();

        set_cookie('code', $checkcode->get_code(), 60 * 5);
        exit;
    }

    /**
     * POST /api/auth/logout
     */
    public function logout() {
        set_cookie('uid', '', 0);
        set_cookie('password', '', 0);
        $this->return_json(200, '退出登录成功');
    }
}
?>
