<?php
defined('IN_MYWEB') or exit('No permission resources.');
base::load_app_class('api_base', 'api', 0);

class lottery extends api_base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * GET /api/lottery/current_state
     * 获取当前开奖状态（含倒计时、上一期开奖号码）
     */
    public function current_state() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
        if (!$gameid) {
            $this->return_json(400, '缺少游戏ID');
        }

        // 可以选择是否要求登录（大部分平台开奖数据是公开的，但原版没有做强制拦截）
        // 这里为了安全和统一，可保留 require_login() 或是直接放行 
        // 考虑到很多平台游客也可以看开奖倒计时，这里先不强制 require_login，或者根据需求开启
        // $this->require_login(); 

        $db = base::load_model('haoma_model');
        $haomadata = array();

        // 获取游戏名称、模板和赔率配置用于前端显示
        $game_db = base::load_model('game_model')->get_one(array('id' => $gameid), 'name, template, data');
        $gameName = $game_db ? $game_db['name'] : '游戏';
        $gameTemplate = $game_db ? $game_db['template'] : 'pk10';
        $haomadata['template'] = $gameTemplate;
        
        // 解析赔率数据
        $odds_list = array();
        if ($game_db && !empty($game_db['data'])) {
            $dataarr = unserialize($game_db['data']);
            $peilv_raw = explode("\n", str_replace(array("\r\n", "\r"), "\n", $dataarr[0]));
            foreach ($peilv_raw as $pl_item) {
                $pl_item = trim($pl_item);
                if (empty($pl_item)) continue;
                $p_parts = explode('@', $pl_item);
                if (count($p_parts) >= 2) {
                    $odds_list[$p_parts[0]] = $p_parts[1];
                }
            }
        }
        $haomadata['odds'] = $odds_list;

        // 针对特殊彩种的本地时间计算逻辑（如 13, 14, 25, 26 等）
        if (in_array($gameid, array(13, 14, 25, 26))) {
            $second = array(
                13 => 150,
                14 => 90,
                25 => 180,
                26 => 300
            );
            $fixno = array(
                13 => '188579',
                14 => '238579',
                25 => '290579',
                26 => '290579'
            );
            $StartTime = array(
                13 => '2018-06-20',
                14 => '2018-06-20',
                25 => '2019-08-18',
                26 => '2019-08-18'
            );
            $dayQ = array(
                13 => '576',
                14 => '960',
                25 => '480',
                26 => '288'
            );

            $time = SYS_TIME;
            $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $curtime = time() - $beginToday;
            $curqishu = intval($curtime / $second[$gameid]);
            $nexqishu = $curqishu + 1;
            $curqishutime = $curqishu * $second[$gameid];
            $nexqishutime = $curqishutime + $second[$gameid];
            $fixn = $fixno[$gameid];
            
            $daynum = floor(($time - strtotime($StartTime[$gameid]." 00:00:00")) / 3600 / 24);
            $lastno = ($daynum - 1) * $dayQ[$gameid] + $fixn;

            $openqishu = $lastno + $curqishu;

            // 获取刚开奖的号码
            $haoma_db = $db->select(array('gameid' => $gameid, 'qishu' => $openqishu), '*', 1, 'id DESC');

            $haomadata['time'] = SYS_TIME;
            $haomadata['gameName'] = $gameName;
            $haomadata['currentIssue'] = (string)($openqishu + 1);
            $haomadata['endTime'] = $beginToday + $nexqishutime;
            
            $haomadata['lastIssue'] = isset($haoma_db[0]['qishu']) ? $haoma_db[0]['qishu'] : '';
            $haoma_str = isset($haoma_db[0]['haoma']) ? $haoma_db[0]['haoma'] : '';
            $haomadata['lastNumbers'] = $haoma_str ? explode(',', str_replace(' ', ',', $haoma_str)) : array();
            
            $haomadata['awartime'] = $haomadata['endTime'] - $haomadata['time'];
            $haomadata['awartime'] = $haomadata['awartime'] < 0 ? 0 : $haomadata['awartime'];
            
            $this->return_json(200, '获取成功', $haomadata);

        } else {
            // 普通彩种：寻找下期未开和上期已开
            // 1. 获取下一期 (未开奖且时间最靠近现在的)
            $next_issue = $db->get_one("gameid = '$gameid' AND haoma = '' AND sendtime > " . SYS_TIME, '*', 'sendtime ASC');
            if (!$next_issue) {
                // 如果没有未来的期数，尝试取最后一条记录
                $next_issue = $db->get_one("gameid = '$gameid'", '*', 'id DESC');
            }

            // 2. 获取上一期记录 (已有开奖号码的最新一期)
            $last_draw = $db->get_one("gameid = '$gameid' AND haoma != ''", '*', 'id DESC');
            
            if (!$next_issue) {
                 $this->return_json(400, '该彩种暂无数据');
            }

            $haomadata['time'] = SYS_TIME;
            $haomadata['gameName'] = $gameName;
            $haomadata['currentIssue'] = $next_issue['qishu']; // 下期期号
            $haomadata['endTime'] = $next_issue['sendtime'];   // 下期开奖时间
            
            $haomadata['lastIssue'] = $last_draw ? $last_draw['qishu'] : ''; 
            $haoma_str = $last_draw ? $last_draw['haoma'] : '';
            $haomadata['lastNumbers'] = $haoma_str ? explode(',', str_replace(' ', ',', $haoma_str)) : array();
            
            $haomadata['awartime'] = $haomadata['endTime'] - $haomadata['time'];
            $haomadata['awartime'] = $haomadata['awartime'] < 0 ? 0 : $haomadata['awartime'];

            $this->return_json(200, '获取成功', $haomadata);
        }
    }

    /**
     * GET /api/lottery/history_results
     * 获取历史开奖结果
     */
    public function history_results() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->return_json(400, 'Invalid request method');
        }

        $gameid = isset($_GET['gameid']) ? intval($_GET['gameid']) : 0;
        if (!$gameid) {
            $this->return_json(400, '缺少游戏ID');
        }

        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

        $db = base::load_model('haoma_model');
        // 获取有开奖号码(且 is_lottery = 1 )的历史数据
        $gamelist = $db->select("gameid = '$gameid' AND haoma != '' AND is_lottery = 1", 'qishu, haoma, sendtime', $limit, 'id DESC');

        $this->return_json(200, '获取成功', array(
            'list' => $gamelist
        ));
    }
}
?>
