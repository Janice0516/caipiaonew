<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header');
?>
<div class="subnav">
	<h2 class="title-1">活动管理</h2>
	<a href="javascript:location.reload();" class="reload">刷新</a>
	<div class="content-menu">
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=init" class="on"><em>活动列表</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=add"><em>新增活动</em></a><span>|</span>
		<a href="<?php echo ADMIN_PATH?>&c=activity&a=logs"><em>领奖记录</em></a>
	</div>
</div>
<div class="content-t">
	<div class="table-list">
		<table width="100%" cellspacing="0">
			<thead>
				<tr>
					<th align="left" width="30">ID</th>
					<th align="left" width="150">活动标题</th>
					<th align="center" width="70">类型</th>
					<th align="center" width="70">周期</th>
					<th align="center" width="80">奖励(元)</th>
					<th align="center" width="80">目标值</th>
					<th align="center" width="60">排序</th>
					<th align="center" width="60">状态</th>
					<th align="left">时间</th>
					<th align="center" width="150">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($list): foreach ($list as $v): ?>
				<tr id="list_<?php echo $v['id']?>">
					<td><?php echo $v['id']?></td>
					<td><?php echo htmlspecialchars($v['title'])?><br><small style="color:#999"><?php echo htmlspecialchars($v['desc'])?></small></td>
					<td align="center"><?php echo isset($type_names[$v['type']]) ? $type_names[$v['type']] : '-'?></td>
					<td align="center"><?php echo isset($cycle_names[$v['cycle']]) ? $cycle_names[$v['cycle']] : '-'?></td>
					<td align="center"><?php echo $v['reward'] > 0 ? $v['reward'] : '-'?></td>
					<td align="center"><?php echo $v['target'] > 0 ? $v['target'] : '-'?></td>
					<td align="center"><?php echo $v['sort']?></td>
					<td align="center" id="status_<?php echo $v['id']?>">
						<?php if($v['status']): ?>
						<span style="color:#00B520">启用</span>
						<?php else: ?>
						<span style="color:#F00">禁用</span>
						<?php endif; ?>
					</td>
					<td><?php echo date('Y-m-d', $v['addtime'])?></td>
					<td align="center">
						<a href="<?php echo ADMIN_PATH?>&c=activity&a=edit&id=<?php echo $v['id']?>">[编辑]</a>
						<a href="javascript:;" onclick="showwindow('<?php echo ADMIN_PATH?>&c=activity&a=toggle&id=<?php echo $v['id']?>&status=<?php echo $v['status']?>', '确定<?php echo $v['status'] ? '禁用' : '启用'?>该活动吗？', 1)">[<?php echo $v['status'] ? '禁用' : '启用'?>]</a>
						<a href="<?php echo ADMIN_PATH?>&c=activity&a=logs&activity_id=<?php echo $v['id']?>">[记录]</a>
						<a href="javascript:;" onclick="showwindow('<?php echo ADMIN_PATH?>&c=activity&a=del&id=<?php echo $v['id']?>', '确定删除该活动吗？')">[删除]</a>
					</td>
				</tr>
				<?php endforeach; else: ?>
				<tr><td colspan="10" align="center">暂无活动，请先<a href="<?php echo ADMIN_PATH?>&c=activity&a=add">新增活动</a></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
		<div id="pages"><?php echo $pages?></div>
	</div>
</div>
</body>
</html>
