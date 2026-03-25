<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header');
?>
<div class="subnav">
	<h2 class="title-1">活动管理</h2>
	<div class="content-menu">
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=init"><em>活动列表</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=add" class="on"><em>新增活动</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=logs"><em>领奖记录</em></a>
	</div>
</div>
<div class="content-t">
<form name="editform" action="" method="post">
<table width="100%" cellspacing="0" class="edit-table">
<tbody>
<tr><th width="120">活动标题 *</th>
<td><input class="input-text" type="text" name="title" value="<?php echo htmlspecialchars(isset($info['title'])?$info['title']:'')?>" style="width:250px;" placeholder="例：每日签到"></td></tr>

<tr><th>活动描述</th>
<td><input class="input-text" type="text" name="desc" value="<?php echo htmlspecialchars(isset($info['desc'])?$info['desc']:'')?>" style="width:350px;" placeholder="例：每天签到领取彩金奖励"></td></tr>

<tr><th>标签文字</th>
<td><input class="input-text" type="text" name="tag" value="<?php echo htmlspecialchars(isset($info['tag'])?$info['tag']:'活动')?>" style="width:100px;" placeholder="如：热门、限时、每日"></td></tr>

<tr><th>活动类型 *</th>
<td>
<select name="type">
<?php foreach ($type_names as $k => $v): ?>
<option value="<?php echo $k?>" <?php echo (isset($info['type'])&&$info['type']==$k)?'selected':''?>><?php echo $v?></option>
<?php endforeach; ?>
</select>
<small style="color:#999">（充值/投注任务需填写目标值；展示型活动仅展示不发奖）</small>
</td></tr>

<tr><th>奖励金额(元)</th>
<td><input class="input-text" type="text" name="reward" value="<?php echo isset($info['reward'])?$info['reward']:'0'?>" style="width:100px;"> 元 <small style="color:#999">展示型活动填0</small></td></tr>

<tr><th>目标值</th>
<td><input class="input-text" type="text" name="target" value="<?php echo isset($info['target'])?$info['target']:'0'?>" style="width:100px;"> 元 <small style="color:#999">充值/投注任务的完成门槛</small></td></tr>

<tr><th>活动周期</th>
<td>
<select name="cycle">
<?php foreach ($cycle_names as $k => $v): ?>
<option value="<?php echo $k?>" <?php echo (isset($info['cycle'])&&$info['cycle']==$k)?'selected':''?>><?php echo $v?></option>
<?php endforeach; ?>
</select>
</td></tr>

<tr><th>渐变色CSS</th>
<td><input class="input-text" type="text" name="gradient" value="<?php echo htmlspecialchars(isset($info['gradient'])?$info['gradient']:'from-blue-600 to-purple-600')?>" style="width:300px;">
<small style="color:#999">例：from-blue-600 to-purple-600 / from-red-600 to-orange-600 / from-green-500 to-teal-500</small></td></tr>

<tr><th>排序</th>
<td><input class="input-text" type="text" name="sort" value="<?php echo isset($info['sort'])?$info['sort']:'0'?>" style="width:60px;"> <small style="color:#999">数字越小越靠前</small></td></tr>

<tr><th>状态</th>
<td>
<label><input type="radio" name="status" value="1" <?php echo (!isset($info['status'])||$info['status'])?' checked':''?>> 启用</label> &nbsp;
<label><input type="radio" name="status" value="0" <?php echo (isset($info['status'])&&!$info['status'])?' checked':''?>> 禁用</label>
</td></tr>

<tr><th>开始时间</th>
<td><input class="input-text" type="text" name="start_time" value="<?php echo isset($info['start_time'])&&$info['start_time']?date('Y-m-d',$info['start_time']):''?>" style="width:150px;" placeholder="留空=长期有效">
到
<input class="input-text" type="text" name="end_time" value="<?php echo isset($info['end_time'])&&$info['end_time']?date('Y-m-d',$info['end_time']):''?>" style="width:150px;" placeholder="留空=长期有效"></td></tr>

<tr><td>&nbsp;</td>
<td><input class="button" type="submit" name="dosubmit" value="提交保存"></td>
</tr>
</tbody>
</table>
</form>
</div>
</body>
</html>
