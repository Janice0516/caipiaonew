<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('admin', 'admin', 0);

class activity extends admin {

    private $db, $db_log, $db_user, $db_account;

    private $type_names = [
        1 => '每日签到',
        2 => '充值任务',
        3 => '投注任务',
        4 => '展示型活动',
    ];

    private $cycle_names = [
        1 => '每日',
        2 => '每周',
        3 => '一次性',
    ];

    public function __construct() {
        parent::__construct();
        $this->db         = base::load_model('activity_model');
        $this->db_log     = base::load_model('activity_log_model');
        $this->db_user    = base::load_model('user_model');
        $this->db_account = base::load_model('account_model');
    }

    /* ===== 活动列表 ===== */
    public function init() {
        $page  = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $list  = $this->db->listinfo('', 'sort ASC, id DESC', $page, 20);
        $pages = $this->db->pages;
        $type_names  = $this->type_names;
        $cycle_names = $this->cycle_names;
        include $this->admin_tpl('activity_list');
    }

    /* ===== 新增活动 ===== */
    public function add() {
        if (isset($_POST['dosubmit'])) {
            $data = $this->_build_data_from_post();
            if (is_string($data)) showmessage($data, HTTP_REFERER);
            $data['addtime'] = SYS_TIME;
            if ($this->db->insert($data)) {
                showmessage('添加成功！', 'c=activity&a=init');
            } else {
                showmessage('添加失败！', HTTP_REFERER);
            }
        }
        $type_names  = $this->type_names;
        $cycle_names = $this->cycle_names;
        include $this->admin_tpl('activity_add');
    }

    /* ===== 编辑活动 ===== */
    public function edit() {
        $id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $info = $id ? $this->db->get_one(['id' => $id]) : null;
        if (!$info) showmessage('未找到活动！', 'c=activity&a=init');

        if (isset($_POST['dosubmit'])) {
            $data = $this->_build_data_from_post();
            if (is_string($data)) showmessage($data, HTTP_REFERER);
            if ($this->db->update($data, ['id' => $id])) {
                showmessage('修改成功！', 'c=activity&a=init');
            } else {
                showmessage('修改失败！', HTTP_REFERER);
            }
        }
        $type_names  = $this->type_names;
        $cycle_names = $this->cycle_names;
        include $this->admin_tpl('activity_edit');
    }

    /* ===== 删除活动 (Ajax) ===== */
    public function del() {
        $id = intval($_GET['id']);
        if (!$id) {
            echo json_encode(['run' => 'no', 'msg' => '参数错误']);
            exit;
        }
        if ($this->db->delete(['id' => $id])) {
            // 同步删除领奖记录
            $this->db_log->delete(['activity_id' => $id]);
            echo json_encode(['run' => 'yes', 'msg' => '删除成功', 'id' => 'list_' . $id]);
        } else {
            echo json_encode(['run' => 'no', 'msg' => '删除失败']);
        }
        exit;
    }

    /* ===== 启用/禁用 (Ajax) ===== */
    public function toggle() {
        $id     = intval($_GET['id']);
        $status = intval($_GET['status']);
        if (!$id) {
            echo json_encode(['run' => 'no', 'msg' => '参数错误']);
            exit;
        }
        $new_status = $status ? 0 : 1;
        if ($this->db->update(['status' => $new_status], ['id' => $id])) {
            echo json_encode(['run' => 'yes', 'msg' => $new_status ? '已启用' : '已禁用', 'new_status' => $new_status]);
        } else {
            echo json_encode(['run' => 'no', 'msg' => '操作失败']);
        }
        exit;
    }

    /* ===== 领奖记录 ===== */
    public function logs() {
        $activity_id = intval($_GET['activity_id']);
        $page        = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
        $info        = $activity_id ? $this->db->get_one(['id' => $activity_id]) : null;

        $where = $activity_id ? "activity_id = '$activity_id'" : '';
        $list  = $this->db_log->listinfo($where, 'id DESC', $page, 20);
        $pages = $this->db_log->pages;

        // 补充用户名
        foreach ($list as &$row) {
            $u = $this->db_user->get_one(['uid' => $row['uid']], 'username,nickname');
            $row['username'] = $u ? $u['username'] : '(已删除)';
            $row['nickname'] = $u ? $u['nickname'] : '';
        }
        unset($row);

        include $this->admin_tpl('activity_logs');
    }

    /* ===== 私有辅助方法：从 POST 构造数据数组 ===== */
    private function _build_data_from_post() {
        $title    = safe_replace(trim(isset($_POST['title']) ? $_POST['title'] : ''));
        $desc     = safe_replace(trim(isset($_POST['desc']) ? $_POST['desc'] : ''));
        $tag      = safe_replace(trim(isset($_POST['tag']) ? $_POST['tag'] : '活动'));
        $type     = intval(isset($_POST['type']) ? $_POST['type'] : 1);
        $reward   = round(floatval(isset($_POST['reward']) ? $_POST['reward'] : 0), 3);
        $target   = round(floatval(isset($_POST['target']) ? $_POST['target'] : 0), 3);
        $cycle    = intval(isset($_POST['cycle']) ? $_POST['cycle'] : 1);
        $gradient = safe_replace(trim(isset($_POST['gradient']) ? $_POST['gradient'] : 'from-blue-600 to-purple-600'));
        $sort     = intval(isset($_POST['sort']) ? $_POST['sort'] : 0);
        $status   = intval(isset($_POST['status']) ? $_POST['status'] : 1);
        
        $start_time_val = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
        $end_time_val   = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
        $start_time = $start_time_val ? strtotime($start_time_val) : 0;
        $end_time   = $end_time_val ? strtotime($end_time_val) : 0;

        if (!$title) return '请填写活动标题！';
        if (!in_array($type, [1,2,3,4])) return '活动类型不合法！';
        if (!in_array($cycle, [1,2,3])) return '活动周期不合法！';
        if ($type != 4 && $reward <= 0) return '请填写奖励金额！';
        if (in_array($type,[2,3]) && $target <= 0) return '充值/投注任务需填写目标金额！';

        return compact('title','desc','tag','type','reward','target','cycle','gradient','sort','status','start_time','end_time');
    }
}
?>
