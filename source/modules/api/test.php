<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); // 加载我们刚刚创建的 api_base 类

class test extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 测试 API 是否连通: GET /index.php?m=api&c=test&a=ping
     */
    public function ping() {
        $this->return_json(200, 'pong', array(
            'time' => date('Y-m-d H:i:s'),
            'm' => ROUTE_M,
            'c' => ROUTE_C,
            'a' => ROUTE_A
        ));
    }

    /**
     * 测试鉴权中间件: GET /index.php?m=api&c=test&a=auth_test
     */
    public function auth_test() {
        // 如果没有登录，这行代码内部会中断程序并返回 {"code":401, ...}
        $user = $this->require_login();
        
        $this->return_json(200, 'auth success', array(
            'uid' => $user['uid'],
            'username' => $user['username']
        ));
    }
}
?>
