<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0); 

class agent extends api_base {

    private $db_account;

    public function __construct() {
        parent::__construct();
        $this->db_account = base::load_model('account_model');
    }

    /**
     * GET /api/agent/summary
     * 获取代理团队概况 (人数、报表统计)
     */
    public function summary() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date("Y-m-d");
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date("Y-m-d");
        
        $term = "";
        if(!empty($start_date)){
            $term .= " AND addtime >=" . strtotime($start_date . " 00:00:00");
        }
        if(!empty($end_date)){
            $term .= " AND addtime <=" . strtotime($end_date . " 23:59:59");
        }

        $data = array(
            'team_size'  => 0,
            'recharge'   => '0.00',
            'withdraw'   => '0.00',
            'order'      => '0.00',
            'commission' => '0.00'
        );

        // 使用基类继承下来的递归查询下级ID的方法
        if (method_exists($this, 'get_user_children_id')) {
            $childs = $this->get_user_children_id($uid, false);
            $child = array();
            foreach ($childs as $val){
                if($val != $uid) {
                    $child[] = $val;
                }
            }
            
            $data['team_size'] = count($child);

            if(!empty($child)){
                $child_str = implode(',', $child);
                $user_model = base::load_model('user_model');
                
                // 团队充值
                $ret = $user_model->querys("SELECT SUM(`money`) as money FROM bc_pay WHERE state IN(1,2) AND `uid` IN({$child_str}) {$term}", 1);
                $data['recharge'] = empty($ret[0]['money']) ? '0.00' : $ret[0]['money'];

                // 团队提现
                $ret = $user_model->querys("SELECT SUM(`money`) as money FROM bc_cash WHERE state=2 AND `uid` IN({$child_str}) {$term}", 1);
                $data['withdraw'] = empty($ret[0]['money']) ? '0.00' : $ret[0]['money'];

                // 团队投注
                $ret = $user_model->querys("SELECT SUM(`money`) as money FROM bc_order WHERE `uid` IN({$child_str}) {$term}", 1);
                $data['order'] = empty($ret[0]['money']) ? '0.00' : $ret[0]['money'];

                // 个人佣金收益 (type=5)
                $ret = $this->db_account->querys("SELECT SUM(`money`) as money FROM bc_account WHERE type=5 AND `uid`={$uid} {$term}", 1);
                $data['commission'] = empty($ret[0]['money']) ? '0.00' : $ret[0]['money'];
            }
        }
        
        // 适配前端 Key: team_total, commission_month
        $result = array(
            'team_total'       => $data['team_size'],
            'commission_month' => $data['commission'],
            'invite_code'      => $user['username'], // 明确返回邀请码
            'my_rebate'        => isset($user['rebate']) && $user['rebate'] > 0 ? floatval($user['rebate']) : 1996
        );
        $result = array_merge($result, $data); // 保留原有的也行

        $this->return_json(200, '获取成功', $result);
    }

    /**
     * GET /api/agent/members
     * 获取下级直属团队成员列表
     */
    public function members() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
        $start = ($page - 1) * $limit;

        $user_model = base::load_model('user_model');
        // 获取直属及二级代理下线
        $where = "agent = '{$uid}' OR agents = '{$uid}'";
        
        $list = $user_model->select($where, 'uid, username, nickname, regtime, money, logintime', "$start, $limit", 'uid DESC');
        $total = $user_model->count($where);
        
        // 获取每个成员的贡献 (佣金)
        if($list) {
            $db_acc = base::load_model('account_model');
            foreach($list as &$m) {
                $u_name = addslashes($m['username']);
                // 汇总来自该下级的真实返点流水 (type=5)
                $ret = $db_acc->querys("SELECT SUM(`money`) as sum_money FROM `bc_account` WHERE `uid`='{$uid}' AND `type`=5 AND `comment` LIKE '%下级[{$u_name}]%'", 1);
                $m['commission'] = empty($ret[0]['sum_money']) ? '0.00' : number_format($ret[0]['sum_money'], 2, '.', '');
            }
        }

        $this->return_json(200, '获取成功', array(
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ));
    }

    /**
     * GET /api/agent/member_detail?uid=xxx
     * 获取某个下级成员的资金和奖金组信息（仅能查看自己的下级）
     */
    public function member_detail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $agent_uid = $user['uid'];
        $target_uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

        if (!$target_uid) {
            $this->return_json(400, '缺少uid参数');
        }

        $user_model = base::load_model('user_model');

        // 安全校验：目标用户必须是当前代理的直属或二级下级
        $member = $user_model->get_one(
            "uid = '{$target_uid}' AND (agent = '{$agent_uid}' OR agents = '{$agent_uid}')",
            'uid, username, nickname, money, rebate, regtime, logintime'
        );

        if (!$member) {
            $this->return_json(403, '无权限查看该用户信息');
        }

        $this->return_json(200, '获取成功', array(
            'uid'      => $member['uid'],
            'username' => $member['username'],
            'nickname' => $member['nickname'],
            'money'    => number_format(floatval($member['money']), 2, '.', ''),
            'rebate'   => intval($member['rebate']),  // 奖金组（如1960、1980、1996等）
            'regtime'  => $member['regtime'],
            'logintime'=> $member['logintime'],
        ));
    }

    /**
     * POST /api/agent/update_rebate
     * 修改下级成员的奖金组（返点）
     */
    public function update_rebate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $agent_uid = $user['uid'];
        
        // vue前端在POST时可能按JSON格式传，也可能x-www-form-urlencoded
        $postData = json_decode(file_get_contents('php://input'), true);
        if (!$postData) {
            $postData = $_POST;
        }

        $target_uid = isset($postData['uid']) ? intval($postData['uid']) : 0;
        $new_rebate = isset($postData['rebate']) ? floatval($postData['rebate']) : 0;

        if (!$target_uid) {
            $this->return_json(400, '缺少uid参数');
        }
        if ($new_rebate <= 0) {
            $this->return_json(400, '奖金组设置错误');
        }

        $user_model = base::load_model('user_model');

        // 安全校验：目标用户必须是当前代理的直属或二级下级
        $member = $user_model->get_one(
            "uid = '{$target_uid}' AND (agent = '{$agent_uid}' OR agents = '{$agent_uid}')",
            'uid, rebate'
        );

        if (!$member) {
            $this->return_json(403, '无权操作该用户信息 (不是您的直属或二级下级)');
        }

        // 当前代理的奖金组
        $my_info = $user_model->get_one(array('uid' => $agent_uid), 'rebate');
        $my_rebate = isset($my_info['rebate']) && $my_info['rebate'] > 0 ? floatval($my_info['rebate']) : 1996;

        if ($new_rebate > $my_rebate) {
            $this->return_json(400, "返点不能高于上级代理 (您的奖金组为: {$my_rebate}, 尝试设置: {$new_rebate})");
        }

        // 更新数据库
        $update_res = $user_model->update(array('rebate' => $new_rebate), array('uid' => $target_uid));

        if ($update_res !== false) {
            $this->return_json(200, '修改成功');
        } else {
            $this->return_json(500, '修改失败 (数据库返回false)');
        }
    }

    /**
     * GET /api/agent/commission
     * 获取代理佣金收益明细
     */
    public function commission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $start = ($page - 1) * $limit;

        // type 5 代表返点或佣金相关的入账单
        $where = array('uid' => $uid, 'type' => 5);

        $list = $this->db_account->select($where, 'id, money, countmoney, comment, addtime', "$start, $limit", 'id DESC');
        $total = $this->db_account->count($where);

        $this->return_json(200, '获取成功', array(
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ));
    }

    /**
     * GET /api/agent/invite_links
     * 获取当前代理创建的所有专属邀请链接
     */
    public function invite_links() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $db = base::load_model('invite_link_model');
        $list = $db->select(array('uid' => $uid, 'status' => 1), '*', '', 'id DESC');

        $this->return_json(200, '获取成功', array(
            'list' => $list ? $list : array()
        ));
    }

    /**
     * POST /api/agent/create_invite_link
     * 创建一个特定奖金组的专属邀请链接
     */
    public function create_invite_link() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $postData = json_decode(file_get_contents('php://input'), true);
        if (!$postData) $postData = $_POST;

        $rebate = isset($postData['rebate']) ? intval($postData['rebate']) : 0;
        
        // Validation
        if ($rebate < 1800) {
            $this->return_json(400, '奖金组设置不能低于1800');
        }

        $user_model = base::load_model('user_model');
        $my_info = $user_model->get_one(array('uid' => $uid), 'rebate');
        $my_rebate = isset($my_info['rebate']) && $my_info['rebate'] > 0 ? intval($my_info['rebate']) : 1996;

        if ($rebate > $my_rebate) {
            $this->return_json(400, "设置的奖金组不能高于您自身的奖金组 ({$my_rebate})");
        }

        // Generate unique code
        $invite_code = substr(md5($uid . time() . mt_rand(1000, 9999)), 8, 12); // 12-char code
        
        $insertData = array(
            'uid' => $uid,
            'invite_code' => $invite_code,
            'rebate' => $rebate,
            'status' => 1,
            'addtime' => SYS_TIME
        );

        $db = base::load_model('invite_link_model');
        $insert_id = $db->insert($insertData, true);
        
        if ($insert_id) {
            $insertData['id'] = $insert_id;
            $this->return_json(200, '创建成功', $insertData);
        } else {
            $this->return_json(500, '创建失败');
        }
    }

    /**
     * POST /api/agent/delete_invite_link
     * 作废一个邀请链接
     */
    public function delete_invite_link() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid = $user['uid'];

        $postData = json_decode(file_get_contents('php://input'), true);
        if (!$postData) $postData = $_POST;

        $id = isset($postData['id']) ? intval($postData['id']) : 0;
        if (!$id) {
            $this->return_json(400, '缺少参数id');
        }

        $db = base::load_model('invite_link_model');
        $link = $db->get_one(array('id' => $id, 'status' => 1));
        
        if (!$link) {
            $this->return_json(404, '链接不存在或已作废');
        }
        if ($link['uid'] != $uid) {
            $this->return_json(403, '无权限操作该链接');
        }

        $res = $db->update(array('status' => 0), array('id' => $id));
        if ($res !== false) {
            $this->return_json(200, '作废成功');
        } else {
            $this->return_json(500, '操作失败');
        }
    }
}
?>
