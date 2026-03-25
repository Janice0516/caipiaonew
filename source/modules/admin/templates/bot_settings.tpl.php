<?php defined('IN_ADMIN') or exit('No permission resources.');?>
<?php include $this->admin_tpl('header');?>
<div class="pad-10">
<form action="" method="post" id="myform">
<fieldset>
	<legend>底层发号风控设置 (Bottom-Level Risk Control)</legend>
	<table width="100%" class="table_form">
        <tr>
            <th width="200">风控总开关:</th>
            <td>
                <label><input type="radio" name="setting[risk_enabled]" value="1" <?php if($risk_enabled=='1') echo 'checked';?>> 开启极速杀率控制</label>
                &nbsp;&nbsp;
                <label><input type="radio" name="setting[risk_enabled]" value="0" <?php if($risk_enabled=='0' || empty($risk_enabled)) echo 'checked';?>> 关闭</label>
                <div class="onShow">开启后，自营彩种(分分彩)在后台发号前将截取高盈玩家注单进行预先必杀计算。</div>
            </td>
        </tr>
		<tr>
			<th width="200">监控周期期数 (N):</th>
			<td class="y-bg">
                <input type="text" class="input-text" name="setting[risk_window_size]" id="risk_window_size" size="10" value="<?php echo $risk_window_size;?>"/>
                <div class="onShow">例如填写 10，代表追溯单个注单玩家过去 10 期的盈亏情况。</div>
            </td>
		</tr>
		<tr>
			<th width="200">杀猪阈值 (盈利上限限额):</th>
			<td class="y-bg">
                <input type="text" class="input-text" name="setting[risk_kill_profit_threshold]" id="risk_kill_profit_threshold" size="10" value="<?php echo $risk_kill_profit_threshold;?>"/> 
                <div class="onShow">例如填写 500。如果提取到下大注的玩家近N期累计盈利超过 500 元，发号引擎将强行放弃让他继续赢的号码，重新摇杀号。</div>
            </td>
		</tr>
        <tr>
			<th width="200">放水救济阈值 (亏损下限限额):</th>
			<td class="y-bg">
                <input type="text" class="input-text" name="setting[risk_save_loss_threshold]" id="risk_save_loss_threshold" size="10" value="<?php echo $risk_save_loss_threshold;?>"/> 
                <div class="onShow">例如填写 -1000。如果玩家近N期累计亏损大于 1000 元，系统可能会过滤掉必杀号，放一次水以免流失客户。</div>
            </td>
		</tr>
	</table>
</fieldset>

<div class="bk15"></div>
<input type="submit" class="button" name="dosubmit" value="保存全局风控配置" />
</form>
</div>
</body>
</html>
