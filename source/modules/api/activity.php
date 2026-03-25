<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0);

class activity extends api_base {

    private $db_act, $db_log, $db_user, $db_account, $db_order;

    public function __construct() {
        parent::__construct();
        $this->db_act     = base::load_model('activity_model');
        $this->db_log     = base::load_model('activity_log_model');
        $this->db_user    = base::load_model('user_model');
        $this->db_account = base::load_model('account_model');
        $this->db_order   = base::load_model('order_model');
    }

    /**
     * GET /api/activity/get_list
     * 获取活动列表（含用户完成状态与进度）
     * 注意：PHP5.6 中 list 是保留关键字，不能用作方法名，改为 get_list
     */
    public function get_list() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $now = SYS_TIME;

        // 获取当前登录用户（不强制要求，未登录时 uid=0）
        $user = $this->check_user();
        $uid  = ($user && isset($user['uid'])) ? intval($user['uid']) : 0;

        // 取出所有启用的活动，按 sort asc
        $where = "status = 1 AND (start_time = 0 OR start_time <= '$now') AND (end_time = 0 OR end_time >= '$now')";
        $activities = $this->db_act->select($where, '*', '', 'sort ASC, id DESC');

        if (!$activities) {
            $this->return_json(200, '获取成功', array('tasks' => array(), 'banners' => array()));
        }

        // 今日0点时间戳
        $day_start = strtotime(date('Y-m-d 00:00:00'));
        // 今日充值/投注（登录时才有意义）
        $today_recharge = $uid ? $this->_get_today_recharge($uid, $day_start) : 0;
        $today_bet      = $uid ? $this->_get_today_bet($uid, $day_start)      : 0;

        $tasks   = array(); // 每日任务（type 1/2/3）
        $banners = array(); // 展示型活动（type 4）

        foreach ($activities as $act) {
            $claimed   = false;
            $completed = false;
            $progress  = 0;

            if ($uid) {
                $cycle_key = $this->_get_cycle_key($act['cycle']);
                // 检查本周期是否已领取
                $log = $this->db_log->get_one(
                    "uid = '$uid' AND activity_id = '{$act['id']}' AND cycle_key = '$cycle_key'"
                );
                $claimed = !empty($log);

                if (!$claimed) {
                    $act_type = intval($act['type']);
                    if ($act_type === 1) {
                        $completed = true;
                        $progress  = 100;
                    } elseif ($act_type === 2) {
                        $target    = floatval($act['target']);
                        $progress  = ($target > 0) ? min(100, round($today_recharge / $target * 100, 1)) : 100;
                        $completed = ($today_recharge >= $target);
                    } elseif ($act_type === 3) {
                        $target    = floatval($act['target']);
                        $progress  = ($target > 0) ? min(100, round($today_bet / $target * 100, 1)) : 100;
                        $completed = ($today_bet >= $target);
                    }
                }
            }

            $item = array(
                'id'        => intval($act['id']),
                'title'     => $act['title'],
                'desc'      => $act['desc'],
                'tag'       => $act['tag'],
                'type'      => intval($act['type']),
                'reward'    => floatval($act['reward']),
                'target'    => floatval($act['target']),
                'cycle'     => intval($act['cycle']),
                'gradient'  => $act['gradient'],
                'progress'  => $progress,
                'completed' => $completed,
                'claimed'   => $claimed,
                'time_label'=> $this->_get_time_label($act),
            );

            if (intval($act['type']) === 4) {
                $banners[] = $item;
            } else {
                $tasks[] = $item;
            }
        }

        // 统计今日完成数/总数
        $done = 0;
        foreach ($tasks as $t) {
            if ($t['claimed']) $done++;
        }

        $this->return_json(200, '获取成功', array(
            'tasks'          => $tasks,
            'banners'        => $banners,
            'today_recharge' => $today_recharge,
            'today_bet'      => $today_bet,
            'done_count'     => $done,
            'total_count'    => count($tasks),
            'is_logged_in'   => $uid > 0,
        ));
    }

    /**
     * POST /api/activity/claim
     * 领取活动奖励
     */
    public function claim() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        $uid  = $user['uid'];

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) $_POST = array_merge($_POST, $input);

        $activity_id = intval(isset($_POST['activity_id']) ? $_POST['activity_id'] : 0);
        if (!$activity_id) {
            $this->return_json(400, '参数错误');
        }

        $now = SYS_TIME;

        // 取活动配置
        $act = $this->db_act->get_one(
            "id = '$activity_id' AND status = 1 AND (start_time = 0 OR start_time <= '$now') AND (end_time = 0 OR end_time >= '$now')"
        );
        if (!$act) {
            $this->return_json(404, '活动不存在或已结束');
        }
        if (intval($act['type']) === 4) {
            $this->return_json(400, '该活动不支持领取奖励');
        }

        $cycle_key = $this->_get_cycle_key($act['cycle']);

        // 检查完成条件
        $day_start = strtotime(date('Y-m-d 00:00:00'));
        $act_type  = intval($act['type']);
        if ($act_type === 2) {
            $today_recharge = $this->_get_today_recharge($uid, $day_start);
            if ($today_recharge < floatval($act['target'])) {
                $need = floatval($act['target']) - $today_recharge;
                $this->return_json(400, sprintf('还需充值 %.2f 元才能领取', $need));
            }
        } elseif ($act_type === 3) {
            $today_bet = $this->_get_today_bet($uid, $day_start);
            if ($today_bet < floatval($act['target'])) {
                $need = floatval($act['target']) - $today_bet;
                $this->return_json(400, sprintf('还需投注 %.2f 元才能领取', $need));
            }
        }

        $reward = round(floatval($act['reward']), 3);

        // 防重复领取（唯一索引 uid+activity_id+cycle_key）
        $inserted = $this->db_log->insert(array(
            'uid'         => $uid,
            'activity_id' => $activity_id,
            'reward'      => $reward,
            'cycle_key'   => $cycle_key,
            'addtime'     => $now,
        ));

        if (!$inserted) {
            // 唯一索引冲突 = 已领取
            $this->return_json(400, '本周期已领取，请勿重复操作');
        }

        // 发放奖励到用户余额
        if (!$this->db_user->update(array('money' => '+=' . $reward), array('uid' => $uid))) {
            // 回滚领奖记录
            $this->db_log->delete(array('uid' => $uid, 'activity_id' => $activity_id, 'cycle_key' => $cycle_key));
            $this->return_json(500, '奖励发放失败，请稍后重试');
        }

        // 写账单流水（type=5 平台赠送）
        $fresh_user  = $this->db_user->get_one(array('uid' => $uid), 'money');
        $count_money = round(floatval($fresh_user['money']), 3);
        $this->db_account->insert(array(
            'uid'        => $uid,
            'money'      => $reward,
            'countmoney' => $count_money,
            'type'       => 5,
            'addtime'    => $now,
            'comment'    => '活动奖励：' . $act['title'],
        ));

        $this->return_json(200, '领取成功！奖励已发放到您的账户', array(
            'reward'      => $reward,
            'new_balance' => $count_money,
        ));
    }

    /* ===== 私有辅助方法 ===== */

    /** 计算今日充值到账金额 */
    private function _get_today_recharge($uid, $day_start) {
        $res = $this->db_account->query(
            "SELECT SUM(money) AS total FROM bc_account WHERE uid='$uid' AND type=0 AND addtime >= '$day_start'",
            true
        );
        return round(floatval(isset($res['total']) ? $res['total'] : 0), 3);
    }

    /** 计算今日有效投注金额 */
    private function _get_today_bet($uid, $day_start) {
        $res = $this->db_order->query(
            "SELECT SUM(money) AS total FROM bc_order WHERE uid='$uid' AND tui=0 AND addtime >= '$day_start'",
            true
        );
        return round(floatval(isset($res['total']) ? $res['total'] : 0), 3);
    }

    /** 根据周期返回当前周期标识字符串 */
    private function _get_cycle_key($cycle) {
        $c = intval($cycle);
        if ($c === 2) return date('Y') . '-W' . date('W'); // 每周
        if ($c === 3) return 'once';                        // 一次性
        return date('Y-m-d');                               // 每日（默认）
    }

    /** 生成活动时间标签文字 */
    private function _get_time_label($act) {
        if (!$act['start_time'] && !$act['end_time']) {
            return '长期有效';
        }
        $s = $act['start_time'] ? date('Y.m.d', $act['start_time']) : '';
        $e = $act['end_time']   ? date('Y.m.d', $act['end_time'])   : '';
        if ($s && $e) return $s . ' - ' . $e;
        if ($e)       return '截止 ' . $e;
        return '从 ' . $s . ' 起';
    }
}
?>
