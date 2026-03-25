<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('go', 'go', 0); // 加载原系统的基础类，为了复用 check_user 等方法

class api_base extends go {

    public function __construct() {
        parent::__construct();
        $this->set_cors_headers();
    }

    /**
     * 设置跨域 CORS 头，允许 Vue3 环境访问并携带 Cookie
     */
    private function set_cors_headers() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        
        // 当 Access-Control-Allow-Credentials 为 true 时，Origin 不可为 *
        if ($origin !== '*') {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
        } else {
            header('Access-Control-Allow-Origin: *');
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Content-Type: application/json; charset=utf-8');

        // 如果是 OPTIONS 请求预检，直接返回 200
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit();
        }
    }

    /**
     * 统一的 JSON 响应格式输出
     * @param int $code 状态码 (200=成功，400=客户端参数错误，401=未登录)
     * @param string $msg 提示消息
     * @param array/object $data 返回的业务数据
     */
    protected function return_json($code = 200, $msg = 'success', $data = array()) {
        $response = array(
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        );
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API 专属登录检查中间件
     * 调用原版 check_user()，如果未登录直接断言返回 401 JSON，无需像原版一样跳转。
     * 可选择性传入 JWT 解析逻辑（目前向下兼容原 Cookie/Session 登录态）
     */
    protected function require_login() {
        $user = $this->check_user();
        if (!$user) {
            $this->return_json(401, '未登录或登录已过期');
        }
        return $user;
    }

    /**
     * 递归获取所有下级 UID 数组
     * @param int $uid 目标用户UID
     * @param bool $self 是否包含目标用户本身
     * @return array
     */
    protected function get_user_children_id($uid, $self = true) {
        $user_model = base::load_model('user_model');
        $uids = $self ? array($uid) : array();
        
        $childs = $user_model->select("agent = '$uid' OR agents = '$uid'", 'uid');
        if($childs){
            foreach($childs as $val){
                $child_ids = $this->get_user_children_id($val['uid'], true);
                if($child_ids){
                    $uids = array_merge($uids, $child_ids);
                }
            }
        }
        return array_unique($uids);
    }
}
?>
