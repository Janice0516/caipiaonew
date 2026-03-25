<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0);

class bet extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * POST /api/bet/submit
     * 提交游戏注单
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if($input) {
            $_POST = array_merge($_POST, $input);
        }

        if ($this->get_settings('stop')) {
            $this->return_json(400, '抱歉，系统维护，暂停投注，请关注游戏公告');
        }

        $gameid = isset($_POST['gameid']) ? intval($_POST['gameid']) : 0;
        $gamename = isset($_POST['gamename']) ? safe_replace(trim($_POST['gamename'])) : '';
        $qishu = isset($_POST['qishu']) ? safe_replace(trim($_POST['qishu'])) : '';
        $wanfa = isset($_POST['wanfa']) ? safe_replace(trim($_POST['wanfa'])) : ''; // 格式 例： 100@大@1.98 或者 多个用 | 隔开
        
        $unit = isset($_POST['unit']) ? safe_replace(trim($_POST['unit'])) : '';
        if (!$unit) {
            $unit = isset($user['unit']) ? $user['unit'] : 'yuan';
        }
        if (!in_array($unit, array('yuan', 'jiao', 'fen', 'li'))) {
            $unit = 'yuan';
        }

        $money_input = isset($_POST['money']) ? round($_POST['money'], 3) : 0;
        if (!function_exists('money_to_yuan')) {
            base::load_sys_func('global');
        }
        $money = money_to_yuan($money_input, $unit);
        $sum = isset($_POST['sum']) ? safe_replace(trim($_POST['sum'])) : '';
        $is_sim = isset($_POST['is_sim']) ? intval($_POST['is_sim']) : 0;

        if (!$gameid || !$gamename || !$qishu || !$wanfa) {
            $this->return_json(400, '参数异常');
        }

        // 基础金额限制
        $send_money = $sum ? $sum : (empty($user['send_money']) ? $this->get_settings('send_money') : $user['send_money']);
        if ($send_money == '10-1000000' && $this->get_settings('send_money') != '10-1000000') {
            $send_money = $this->get_settings('send_money');
        }
        $send_arr = explode('-', $send_money);
        if ($money < 0.001 || $money > $send_arr[1]) {
            $this->return_json(400, '单笔下注不能低于 1 厘');
        }

        $uid = $user['uid'];
        $agent = $user['agent'];
        $agents = 0;
        if ($agent) {
            $agent_db = base::load_model('user_model')->get_one("aid > 0 AND uid = '$agent'");
            if ($agent_db && $agent_db['aid'] == 3) {
                $agents = $agent_db['agent'];
            }
        }

        $db = base::load_model('game_model');
        $gamedb = $db->get_one("id = '$gameid' AND state = 1", '*', 'id DESC');
        if (!$gamedb) {
            $this->return_json(400, '游戏不存在或已关闭');
        }

        $db1 = base::load_model('haoma_model');
        
        // 特殊彩种期数获取 (依赖于时间推算)
        if(in_array($gameid, array(13, 14, 25, 26))) {
            $second = array(13 => 150, 14 => 90, 25 => 180, 26 => 300);
            $fixno = array(13 => '188579', 14 => '238579', 25 => '290579', 26 => '290579');
            $StartTime = array(13 => '2018-06-20', 14 => '2018-06-20', 25 => '2019-08-18', 26 => '2019-08-18');
            $dayQ = array(13 => '576', 14 => '960', 25 => '480', 26 => '288');

            $time = SYS_TIME;
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $curtime = time()- $beginToday;
            $curqishu =  intval($curtime / $second[$gameid]);
            $daynum = floor(($time-strtotime($StartTime[$gameid]." 00:00:00"))/3600/24);
            $lastno = ($daynum-1)*$dayQ[$gameid] + $fixno[$gameid];
            
            $nextqishu = $lastno + $curqishu + 1;
            $nextdb = $db1->get_one("gameid = '$gameid' AND qishu = '$nextqishu'", 'qishu, sendtime', 'id DESC');
        } else {
            $nextdb = $db1->get_one("gameid = '$gameid' AND haoma = ''", 'qishu, sendtime', 'id DESC');
        }

        // 封盘时间校验
        if (SYS_TIME > ($nextdb['sendtime'] - $gamedb['fptime']) || $nextdb['qishu'] != $qishu) {
            $this->return_json(400, '已封盘或期号已过期');
        }

        $db2 = $is_sim ? base::load_model('sim_order_model') : base::load_model('order_model');
        $time = date('YmdHis', SYS_TIME);
        $ban = 0;

        // 自动使用第一个房间配置的赔率数据（取消房间选择要求）
        $room = base::load_config('room/room_' . $gameid);
        if (!empty($room)) {
            $roomConf = $room[0]; // 默认使用第一个房间
            // 使用房间内的赔率数据
            $gamedb['data'] = serialize(array($roomConf['data']));
        }

        $dataarr = unserialize($gamedb['data']);
        $sql = '';
        $dbmoney = 0;
        $orderids = array();
        
        // 动态奖金组：读取下注玩家的返点赔率以折算实际赔率
        $user_rebate = isset($user['rebate']) ? intval($user['rebate']) : 1996;
        $ratio = $user_rebate / 1996;

        // 针对特殊上庄玩法过滤
        if ($gameid == 11) {
            // 省略牌九复杂的庄家逻辑，简化为 API 返回报错（如需支持，照抄源码即可）
            $this->return_json(400, '当前API暂不支持该游戏上庄玩法');
        } else {
            // 这里解析由前端传来的多组以 | 分隔的注单
            // 前端传参格式: wanfa = "220@大@1.98 | 221@单@1.98"  (具体跟随以前的格式)
            $peilv = explode("\n", str_replace(array("\r\n", "\r"), "\n", $dataarr[0]));
            if(!$peilv[count($peilv)-1]) unset($peilv[count($peilv)-1]);

            $wanfa_arr = explode('|', $wanfa);
            foreach($wanfa_arr as $wf) {
                $wf = trim($wf);
                $arr = explode('@', $wf);
                if (count($arr) < 3) continue;
                
                $show_wanfa = $wf_final = $arr[1].'@'.$arr[2].(isset($arr[3]) && $arr[3] ? '@'.$arr[3] : '');
                
                $pl = '';
                foreach ($peilv as $peilval) {
                    $peilval = trim($peilval);
                    $peiln = explode('@', $peilval);
                    if($arr[0] == $peiln[0]){
                        // 用数据库的最高赔率验证合法性
                        $pl = $sum ? $arr[0].'@'.$peiln[1].'@'.$sum : $arr[0].'@'.$peiln[1];
                        
                        // 动态计算该玩家的实际当单赔率 = db满赔率 * (用户奖金组 / 1996)
                        $expected_odds = round(floatval($peiln[1]) * $ratio, 3);
                        
                        // 将真实的玩家折扣赔率写进入库流水
                        if (isset($arr[3]) && $arr[3]) {
                            $wf_final = $arr[1].'@'.$expected_odds.'@'.$arr[3];
                        } else {
                            $wf_final = $arr[1].'@'.$expected_odds;
                        }
                        break;
                    }
                }
                
                if(empty($pl)){
                    $pl = $sum ? $arr[0].'@'.$arr[2].'@'.$sum : $arr[0].'@'.$arr[2];
                }
                
                if (in_array($pl, $peilv)) {
                    $orderid = $time . random(6, '1234567890');
                    $sql .= "('$uid', '$agent', '$agents', '$orderid', '$gameid', '$qishu', '$money', '$show_wanfa', '$wf_final', ".SYS_TIME.", '$ban'),";
                    $dbmoney = bcadd($dbmoney, $money, 3);
                    $orderids[] = $orderid;
                } else {
                    $this->return_json(400, '存在非法赔率或玩法被篡改，提交失败');
                }
            }
            $sql = rtrim($sql, ',');
        }

        if (empty($sql)) {
            $this->return_json(400, '没有可提交的合法注单');
        }

        // 强一致性扣款
        $user_money = $is_sim ? floatval($user['sim_money']) : floatval($user['money']);
        if ($user_money < $dbmoney) {
            $this->return_json(400, '下注失败，当前余额不足');
        }

        if ($db2->insert($sql, false, false, '(uid, agent, agents, orderid, gameid, qishu, money, show_wanfa, wanfa, addtime, ban)')) {
            $db3 = $is_sim ? base::load_model('sim_account_model') : base::load_model('account_model');
            $user_model = base::load_model('user_model');
            
            // 扣余额
            if ($is_sim) {
                $user_model->update(array('sim_money' => '-='.$dbmoney), array('uid' => $uid));
            } else {
                $user_model->update(array('money' => '-='.$dbmoney, 'dama' => '+='.$dbmoney), array('uid' => $uid));
            }
            
            // 写入流水日志
            $moneydb = round(0 - floatval($dbmoney), 3);
            $countmoney = round($user_money - floatval($dbmoney), 3);
            $comment = ($is_sim ? '[模拟] ' : '') . $gamename.' '.$qishu.'期 注单'.$time.'...投注';
            $db3->insert(array('uid'=>$uid, 'money'=>$moneydb, 'countmoney'=>$countmoney, 'type'=>2, 'addtime'=>SYS_TIME, 'comment'=>$comment));

            // ================= 代理级差返点 (Agency Rebate) 逻辑 =================
            if (!$is_sim) {
                $current_agent_uid = intval($user['agent']);
            $current_rebate = $user_rebate;
            $depth = 0;
            
            while ($current_agent_uid > 0 && $depth < 15) {
                $agent_info = $user_model->get_one(array('uid' => $current_agent_uid), 'uid, rebate, agent, money');
                if (!$agent_info) break;
                
                $agent_rebate = intval($agent_info['rebate']);
                if ($agent_rebate > $current_rebate) {
                    $diff_ratio = ($agent_rebate - $current_rebate) / 1996;
                    $commission = round($dbmoney * $diff_ratio, 3);
                    
                    if ($commission > 0) {
                        $user_model->update(array('money' => '+='.$commission), array('uid' => $current_agent_uid));
                        $agent_after_money = floatval($agent_info['money']) + $commission;
                        $rebate_comment = "下级投注返点(单号:".$orderids[0]."等)";
                        $db3->insert(array(
                            'uid' => $current_agent_uid,
                            'money' => $commission,
                            'countmoney' => $agent_after_money,
                            'type' => 4, // 佣金返水类型
                            'addtime' => SYS_TIME,
                            'comment' => $rebate_comment
                        ));
                    }
                    $current_rebate = $agent_rebate; // 向上移交层级基准
                }
                
                $current_agent_uid = intval($agent_info['agent']);
                $depth++;
            }
            // ================= 代理级差返点逻辑 结束 =================
            } // end if !$is_sim

            $this->return_json(200, '下注成功', array(
                'deduct' => $dbmoney,
                'orders' => $orderids
            ));
        } else {
            $this->return_json(500, '下注失败，数据库写入异常');
        }
    }

    /**
     * GET /api/bet/history
     * 查询历史注单
     */
    public function history() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $user = $this->require_login();
        
        $gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
        $qishu = isset($_GET['qishu']) ? safe_replace(trim($_GET['qishu'])) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $is_sim = isset($_GET['is_sim']) ? intval($_GET['is_sim']) : 0;

        $where = array('uid' => $user['uid']);
        if ($gameid) {
            $where['gameid'] = $gameid;
        }
        if ($qishu) {
            $where['qishu'] = $qishu;
        }

        $db = $is_sim ? base::load_model('sim_order_model') : base::load_model('order_model');
        
        // 分页获取
        $start = ($page - 1) * $limit;
        
        // 获取记录
        $orderlist = $db->select($where, '*', "$start, $limit", 'id DESC');
        
        // 获取总数
        $total = $db->count($where);
        
        // 补充获取游戏名称
        $game_db = base::load_model('game_model');
        $games = $game_db->select('', 'id, name');
        $game_map = array();
        foreach($games as $g) {
            $game_map[$g['id']] = $g['name'];
        }
        
        if (is_array($orderlist)) {
            foreach ($orderlist as &$order) {
                if (isset($game_map[$order['gameid']])) {
                    $order['gamename'] = str_replace(array("\r", "\n", "\t", " "), '', $game_map[$order['gameid']]);
                } else {
                    $order['gamename'] = '游戏 ' . $order['gameid'];
                }
            }
            unset($order);
        }
        
        $this->return_json(200, '获取成功', array(
            'list'  => $orderlist,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ));
    }
}
?>
