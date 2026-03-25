<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<div class="pad-lr-10">
    <div class="table-list">
        <form name="myform" action="?m=admin&c=bot_template&a=listorder" method="post">
            <table width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">所属游戏</th>
                        <th width="200">模板名称</th>
                        <th width="300">描述摘要</th>
                        <th width="100">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($list)) { foreach ($list as $info) { ?>
                        <tr>
                            <td align="center"><?php echo $info['id']; ?></td>
                            <td align="center"><span style="color:#2a5e8c;font-weight:bold;"><?php echo $info['gamename']; ?></span></td>
                            <td align="center"><?php echo $info['name']; ?></td>
                            <td align="center"><?php echo $info['description']; ?></td>
                            <td align="center">
                                <a href="?m=admin&c=bot_template&a=edit&id=<?php echo $info['id']; ?>&menuid=<?php echo $_GET['menuid'];?>">修改</a> | 
                                <a href="javascript:confirmurl('?m=admin&c=bot_template&a=del&id=<?php echo $info['id']; ?>&menuid=<?php echo $_GET['menuid'];?>', '是否确认删除该模板？')">删除</a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
            <div class="btn">
                <a href="?m=admin&c=bot_template&a=add&menuid=<?php echo $_GET['menuid'];?>" class="button" style="padding:4px 15px; background: #0099cc; color:#fff;">添加新模板</a>
            </div>
            <div id="pages"><?php echo $pages; ?></div>
        </form>
    </div>
</div>
</body>
</html>
