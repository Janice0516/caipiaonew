<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header');
?>
<div class="subnav">
	<h2 class="title-1">活动管理</h2>
	<div class="content-menu">
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=init"><em>活动列表</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=add"><em>新增活动</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=logs" class="on"><em>领奖记录</em></a>
	</div>
</div>
<div class="content-t">
<?php if ($info): ?>
<p style="padding:5px 10px;background:#f5f5f5;margin-bottom:10px;">当前活动：<strong><?php echo htmlspecialchars($info['title'])?></strong></p>
<?php endif; ?>
<div class="table-list">
	<table width="100%" cellspacing="0">
		<thead>
			<tr>
				<th width="60">ID</th>
				<th>用户(UID)</th>
				<th align="center" width="80">获得奖励</th>
				<th align="center" width="100">领取周期</th>
				<th align="left" width="160">领取时间</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($list): foreach ($list as $v): ?>
			<tr>
				<td><?php echo $v['id']?></td>
				<td><?php echo htmlspecialchars($v['username'])?>(UID:<?php echo $v['uid']?>)
				<?php if($v['nickname']): ?><small style="color:#999"> / <?php echo htmlspecialchars($v['nickname'])?></small><?php endif;?></td>
				<td align="center">+<?php echo $v['reward']?> 元</td>
				<td align="center"><?php echo $v['cycle_key'] ?: '-'?></td>
				<td><?php echo date('Y-m-d H:i:s', $v['addtime'])?></td>
			</tr>
			<?php endforeach; else: ?>
			<tr><td colspan="5" align="center">暂无记录</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
	<div id="pages"><?php echo $pages?></div>
</div>
</div>
</body>
</html>
