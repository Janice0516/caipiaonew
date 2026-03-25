<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header');
?>
<?php
	$unit = isset($data['unit']) ? $data['unit'] : 'yuan';
	$unit_label = money_unit_label($unit);
?>
<div class="subnav">
	<h2 class="title-1">账户管理</h2>
	<a href="javascript:location.reload();" class="reload">刷新</a>
	<div class="content-menu">
		<a href="<?php echo ADMIN_PATH?>&c=user&a=init"><em>账户列表</em></a><span>|</span>
		<a href="javascript:;" class="on"><em>账户报表</em></a>
	</div>
</div>
<div class="content-t">
	<div id="searchshow">
		<form name="searchform" action="<?php echo ADMIN_PATH?>&c=user&a=info" method="get" >
			<input type="hidden" name="m" value="admin">
			<input type="hidden" name="c" value="user">
			<input type="hidden" name="a" value="info">
			<table width="100%" cellspacing="0" class="search-form">
			  <tbody>
					<tr>
						<td>
							<div class="explain-col">
								账户UID <input class="input-text" type="text" id="uid" name="uid" style="width:50px;" value="<?php echo $uid?>">
								游戏 <?php echo form::select($gamearr, $gameid, 'name="gameid"', '全部', 0)?>
								自定义时间 <?php echo form::date('start_time',$start_time,'1')?>
								到 <?php echo form::date('end_time',$end_time,'1')?>
								<input type="submit" value="查阅报表" class="button" name="dosubmit">
								<?php if($custom){?><input type="submit" value="返回" class="button" name="backsubmit"><?php }?>
							</div>
						</td>
					</tr>
			  </tbody>
			</table>
		</form>
	</div>
	<div class="uname">
    <em>用户名</em>
    <span><?php echo $data['username']?></span>
    <em>上级代理人(UID)</em>
    <span><?php echo $this -> go_user($data['agent'])?></span>
    <em>报告时间</em>
    <span><?php echo format::date(SYS_TIME, 1)?></span>
	</div>
	<?php if($custom){?>
	<table width="100%" class="table_form info custom">
	  <tr>
	    <th class="title">类型</th>
	    <th><?php echo $start_time?> ~ <?php echo $end_time?></th>
	  </tr>
	  <tr>
	    <td class="title">注单数<span title="用户投注的总单数，但不包含退单。">？</span></td>
	    <td ><?php echo intval($custom_count['num'])?></td>
	  </tr>
	  <tr>
	    <td class="title">注单总额<span title="用户未开奖结算与已结算注单的所有金额，但不包含已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($custom_count['money'], $unit)).$unit_label?></td>
	  </tr>
    <tr>
      <td class="title">注单总额(输赢)<span title="用户输赢注单金额，不包含和局、未结算和已经退单的金额。">？</span></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($custom_count['moneys'], $unit)).$unit_label?></td>
    </tr>
	  <tr>
	    <td class="title">盈利总额<span title="用户有效注单的结算统计，正数代表用户盈利，负数代表用户亏损。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($custom_count['account'], $unit)).$unit_label?></td>
	  </tr>
	  <tr>
	  	<td class="title">本金总额(赢单)<span title="用户有效赢单本金总额，不包含和局、输单、未结算和已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($custom_count['take'], $unit)).$unit_label?></td>
	  </tr>
	</table>
	<?php if($data['aid']){?>
	<div class="mt20"></div>
	<div class="uname">
    <em>代理人</em>
    <span><?php echo $data['username']?></span>
    <em>旗下账户总数</em>
    <span><?php echo $daili_user_count?> 人 <a href="<?php echo ADMIN_PATH?>&c=user&a=search&search[agent]=<?php echo $data['uid']?>">立即管理</a></span>
    <em>旗下注单总数</em>
    <span><?php echo $daili_order_count?> 条 <a href="<?php echo ADMIN_PATH?>&c=order&a=search&search[agent]=<?php echo $data['uid']?>">立即管理</a></span>
    <em>报告时间</em>
    <span><?php echo format::date(SYS_TIME, 1)?></span>
	</div>
	<table width="100%" class="table_form info custom">
	  <tr>
	    <th class="title">类型</th>
	    <th><?php echo $start_time?> ~ <?php echo $end_time?></th>
	  </tr>
	  <tr>
	    <td class="title">注单数<span title="该代理人旗下用户投注的总单数，但不包含退单。">？</span></td>
	    <td><?php echo intval($daili_custom_count['num'])?></td>
	  </tr>
	  <tr>
	    <td class="title">注单总额<span title="该代理人旗下用户未开奖结算与已结算注单的所有金额，但不包含已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_custom_count['money'], $unit)).$unit_label?></td>
	  </tr>
    <tr>
      <td class="title">注单总额(输赢)<span title="该代理人旗下用户输赢注单金额，不包含和局、未结算和已经退单的金额。">？</span></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_custom_count['moneys'], $unit)).$unit_label?></td>
    </tr>
	  <tr>
	    <td class="title">盈利总额<span title="该代理人旗下用户有效注单的结算统计，正数代表用户盈利，负数代表用户亏损。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_custom_count['account'], $unit)).$unit_label?></td>
	  </tr>
	  <tr>
	    <td class="title">本金总额(赢单)<span title="该代理人旗下用户有效赢单本金总额，不包含和局、输单、未结算和已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($custom_count['take'], $unit)).$unit_label?></td>
	  </tr>
	</table>
	<?php }?>
	<?php }else{?>
	<table width="100%" class="table_form info">
	  <tr>
	    <th class="title">类型</th>
	    <th>今日</th>
	    <th>昨日</th>
	    <th>本周</th>
	    <th>本月</th>
	    <th>上月</th>
	    <th>本季度</th>
	  </tr>
	  <tr>
	    <td class="title">注单数<span title="用户投注的总单数，但不包含退单。">？</span></td>
	    <td><?php echo intval($today_count['num'])?></td>
	    <td><?php echo intval($yesterday_count['num'])?></td>
	    <td><?php echo intval($tswk_count['num'])?></td>
	    <td><?php echo intval($thismonth_count['num'])?></td>
	    <td><?php echo intval($lastmonth_count['num'])?></td>
	    <td><?php echo intval($quarter_count['num'])?></td>
	  </tr>
	  <tr>
	    <td class="title">注单总额<span title="用户未开奖结算与已结算注单的所有金额，但不包含已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($today_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($yesterday_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($tswk_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($thismonth_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($lastmonth_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($quarter_count['money'], $unit)).$unit_label?></td>
	  </tr>
    <tr>
      <td class="title">注单总额(输赢)<span title="用户输赢注单金额，不包含和局、未结算和已经退单的金额。">？</span></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($today_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($yesterday_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($tswk_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($thismonth_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($lastmonth_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($quarter_count['moneys'], $unit)).$unit_label?></td>
    </tr>
	  <tr>
	    <td class="title">盈利总额<span title="用户有效注单的结算统计，正数代表用户盈利，负数代表用户亏损。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($today_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($yesterday_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($tswk_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($thismonth_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($lastmonth_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($quarter_count['account'], $unit)).$unit_label?></td>
	  </tr>
	  <tr>
	    <td class="title">本金总额(赢单)<span title="用户有效赢单本金总额，不包含和局、输单、未结算和已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($today_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($yesterday_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($tswk_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($thismonth_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($lastmonth_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($quarter_count['take'], $unit)).$unit_label?></td>
	  </tr>
	</table>
	<?php if($data['aid']){?>
	<div class="mt20"></div>
	<div class="uname">
    <em>代理人</em>
    <span><?php echo $data['username']?></span>
    <em>旗下账户总数</em>
    <span><?php echo $daili_user_count?> 人 <a href="<?php echo ADMIN_PATH?>&c=user&a=search&search[agent]=<?php echo $data['uid']?>">立即管理</a></span>
    <em>旗下注单总数</em>
    <span><?php echo $daili_order_count?> 条 <a href="<?php echo ADMIN_PATH?>&c=order&a=search&search[agent]=<?php echo $data['uid']?>">立即管理</a></span>
    <em>报告时间</em>
    <span><?php echo format::date(SYS_TIME, 1)?></span>
	</div>
	<table width="100%" class="table_form info">
	  <tr>
	    <th class="title">类型</th>
	    <th>今日</th>
	    <th>昨日</th>
	    <th>本周</th>
	    <th>本月</th>
	    <th>上月</th>
	    <th>本季度</th>
	  </tr>
	  <tr>
	    <td class="title">注单数<span title="该代理人旗下用户投注的总单数，但不包含退单。">？</span></td>
	    <td><?php echo intval($daili_today_count['num'])?></td>
	    <td><?php echo intval($daili_yesterday_count['num'])?></td>
	    <td><?php echo intval($daili_tswk_count['num'])?></td>
	    <td><?php echo intval($daili_thismonth_count['num'])?></td>
	    <td><?php echo intval($daili_lastmonth_count['num'])?></td>
	    <td><?php echo intval($daili_quarter_count['num'])?></td>
	  </tr>
	  <tr>
	    <td class="title">注单总额<span title="该代理人旗下用户未开奖结算与已结算注单的所有金额，但不包含已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_today_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_yesterday_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_tswk_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_thismonth_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_lastmonth_count['money'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_quarter_count['money'], $unit)).$unit_label?></td>
	  </tr>
    <tr>
      <td class="title">注单总额(输赢)<span title="该代理人旗下用户输赢注单金额，不包含和局、未结算和已经退单的金额。">？</span></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_today_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_yesterday_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_tswk_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_thismonth_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_lastmonth_count['moneys'], $unit)).$unit_label?></td>
      <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_quarter_count['moneys'], $unit)).$unit_label?></td>
    </tr>
	  <tr>
	    <td class="title">盈利总额<span title="该代理人旗下用户有效注单的结算统计，正数代表用户盈利，负数代表用户亏损。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_today_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_yesterday_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_tswk_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_thismonth_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_lastmonth_count['account'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_quarter_count['account'], $unit)).$unit_label?></td>
	  </tr>
	  <tr>
	    <td class="title">本金总额(赢单)<span title="该代理人旗下用户有效赢单本金总额，不包含和局、输单、未结算和已经退单的金额。">？</span></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_today_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_yesterday_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_tswk_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_thismonth_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_lastmonth_count['take'], $unit)).$unit_label?></td>
	    <td><?php echo $settingarr['stamp'].money_fmt(money_from_yuan($daili_quarter_count['take'], $unit)).$unit_label?></td>
	  </tr>
	</table>
	<?php }}?>
</div>
<script type="text/javascript">
	$('.table_form .title span').click(function(){
		layer.tips($(this).attr('title'), $(this), {tips: [1, '#333C59'], time: 4000});
	});
</script>
</body>
</html>
