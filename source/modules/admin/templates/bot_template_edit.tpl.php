<?php
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>
<script type="text/javascript">
  $(function(){
    $.formValidator.initConfig({formid:"myform",autotip:true,onerror:function(msg,obj){window.top.art.dialog({content:msg,lock:true,width:'200',height:'50'}, function(){this.close();$(obj).focus();})}});
    $("#name").formValidator({onshow:"请输入模板名称",onfocus:"模板名称不能为空"}).inputValidator({min:1,max:99,onerror:"模板名称不能为空"});
  })
</script>
<div class="pad-lr-10">
    <div class="common-form">
        <form name="myform" id="myform" action="?m=admin&c=bot_template&a=<?php echo $_GET['a']; ?>&id=<?php echo $_GET['id']; ?>" method="post">
            <table width="100%" class="table_form">
                <tr>
                    <td width="100">所属游戏</td>
                    <td>
                        <select name="info[game_id]" onchange="if(typeof renderTargets=='function') renderTargets();">
                            <?php foreach($games as $game) { ?>
                            <option value="<?php echo $game['id']; ?>" <?php if(isset($info['game_id']) && $info['game_id'] == $game['id']) echo 'selected'; ?>><?php echo $game['name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>模板名称</td>
                    <td><input type="text" name="info[name]" id="name" class="input-text" size="40" value="<?php echo isset($info['name']) ? $info['name'] : ''; ?>"></td>
                </tr>
                <tr>
                    <td>描述摘要</td>
                    <td><input type="text" name="info[description]" class="input-text" size="60" value="<?php echo isset($info['description']) ? $info['description'] : ''; ?>"></td>
                </tr>
                <tr>
                    <td>策略详细配置</td>
                    <td>
                        <table width="100%" class="table_form" style="border:1px solid #ddd; background:#f9f9f9; padding:10px;">
                            <tr>
                                <td width="100">基底金额:</td>
                                <td><input type="text" name="config[baseAmount]" class="input-text" size="10" value="<?php echo isset($config['baseAmount']) ? $config['baseAmount'] : '10'; ?>">
                                 <select name="config[unit]">
                                    <option value="1" <?php if(isset($config['unit']) && $config['unit']==1) echo 'selected'; ?>>元</option>
                                    <option value="2" <?php if(isset($config['unit']) && $config['unit']==2) echo 'selected'; ?>>角</option>
                                    <option value="3" <?php if(isset($config['unit']) && $config['unit']==3) echo 'selected'; ?>>分</option>
                                 </select>
                                </td>
                            </tr>
                            <tr>
                                <td>翻倍数列:</td>
                                <td><input type="text" name="config[multiplier]" class="input-text" size="40" value="<?php echo isset($config['multiplier']) ? $config['multiplier'] : '1,2,4,8,16'; ?>"> <span style="color:#888;">用半角逗号分隔，如 1,2,4,8</span></td>
                            </tr>
                            <tr>
                                <td>自动止盈:</td>
                                <td><input type="text" name="config[takeProfit]" class="input-text" size="10" value="<?php echo isset($config['takeProfit']) ? $config['takeProfit'] : '1000'; ?>"> <span style="color:#888;">达到设定的正收益后自动停止挂机</span></td>
                            </tr>
                            <tr>
                                <td>自动止损:</td>
                                <td><input type="text" name="config[stopLoss]" class="input-text" size="10" value="<?php echo isset($config['stopLoss']) ? $config['stopLoss'] : '2000'; ?>"> <span style="color:#888;">输掉设定的金额后自动熔断停止</span></td>
                            </tr>
                            <tr>
                                <td>目标追号:</td>
                                <td>
                                    <input type="text" name="config[customTarget]" id="customTargetRealInput" class="input-text" size="60" value="<?php echo isset($config['customTarget']) ? implode(',', $config['customTarget']) : ''; ?>" readonly style="background:#e8e8e8; color:#666;">
                                    <div style="color:#888; margin-top:5px; margin-bottom:10px;">您可以直接在下方勾选【官方玩法组】，系统会自动拼接目标注单代码。</div>
                                    <div id="targetWrapper" style="background:#fff; border:1px solid #ccc; padding:10px; border-radius:4px; max-height:220px; overflow-y:auto; font-size:12px;">
                                        <!-- JS rendered checkboxes -->
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <script type="text/javascript">
            var gamesData = <?php echo json_encode($games); ?>;
            var wanfaDict = {
              'pk10': [
                { title: '冠亚和值', targets: ['冠亚大','冠亚小','冠亚单','冠亚双'] },
                { title: '一到五名', targets: ['冠军大','冠军小','冠军单','冠军双','亚军大','亚军小','亚军单','亚军双','第三名大','第三名小','第三名单','第三名双','第四名大','第四名小','第四名单','第四名双','第五名大','第五名小','第五名单','第五名双'] },
                { title: '六到十名', targets: ['第六名大','第六名小','第六名单','第六名双','第七名大','第七名小','第七名单','第七名双','第八名大','第八名小','第八名单','第八名双','第九名大','第九名小','第九名单','第九名双','第十名大','第十名小','第十名单','第十名双'] },
                { title: '龙虎决', targets: ['冠军龙','冠军虎','亚军龙','亚军虎','第三名龙','第三名虎','第四名龙','第四名虎','第五名龙','第五名虎'] }
              ],
              'cqssc': [
                { title: '总和大小', targets: ['总和大','总和小','总和单','总和双'] },
                { title: '万千百十个', targets: ['万位大','万位小','万位单','万位双','千位大','千位小','千位单','千位双','百位大','百位小','百位单','百位双','十位大','十位小','十位单','十位双','个位大','个位小','个位单','个位双'] },
                { title: '前中后三', targets: ['前三豹子','前三顺子','前三对子','前三半顺','前三杂六','中三豹子','中三顺子','中三对子','中三半顺','中三杂六','后三豹子','后三顺子','后三对子','后三半顺','后三杂六'] }
              ],
              'k3': [
                { title: '快三基础', targets: ['总和大','总和小','总和单','总和双'] }
              ],
              'pc28': [
                { title: 'PC28综合', targets: ['大','小','单','双','极大','极小','大单','小单','大双','小双'] }
              ],
              'other': [
                { title: '默认基础', targets: ['大','小','单','双','龙','虎','和大','和小','和单','和双'] }
              ]
            };

            var currentTargetStr = "<?php echo isset($config['customTarget']) ? implode(',', $config['customTarget']) : ''; ?>";
            var currentSelectedTargets = currentTargetStr ? currentTargetStr.split(',') : [];

            function renderTargets() {
                var gameId = $('select[name="info[game_id]"]').val();
                var gameObj = gamesData.find(function(g) { return g.id == gameId });
                var template = gameObj ? gameObj.template : 'other';
                
                if (!wanfaDict[template]) {
                    if(template.indexOf('pk10')>-1 || template.indexOf('xyft')>-1) template = 'pk10';
                    else if(template.indexOf('ssc')>-1) template = 'cqssc';
                    else if(template.indexOf('k3')>-1) template = 'k3';
                    else if(template.indexOf('pc28')>-1) template = 'pc28';
                    else template = 'other';
                }
                var groups = wanfaDict[template] || wanfaDict['other'];
                
                var html = '';
                for (var i=0; i<groups.length; i++) {
                    var g = groups[i];
                    html += '<div style="margin-bottom:8px; border-bottom:1px dashed #eee; padding-bottom:5px;">';
                    html += '<strong style="display:inline-block; width:80px; color:#c00;">'+g.title+'</strong>';
                    for(var j=0; j<g.targets.length; j++) {
                        var t = g.targets[j];
                        var checked = currentSelectedTargets.indexOf(t) > -1 ? 'checked' : '';
                        html += '<label style="margin-right:12px; cursor:pointer; display:inline-block;"><input type="checkbox" value="'+t+'" onclick="updateTargetInput()" '+checked+'> '+t+'</label>';
                    }
                    html += '</div>';
                }
                $('#targetWrapper').html(html);
                updateTargetInput();
            }

            function updateTargetInput() {
                var selected = [];
                $('#targetWrapper input:checked').each(function(){
                    selected.push($(this).val());
                });
                $('#customTargetRealInput').val(selected.join(','));
                currentSelectedTargets = selected;
            }

            $(function(){
                renderTargets();
            });
            </script>
            <div class="bk15"></div>
            <input type="submit" name="dosubmit" value="保存策略模板" class="button">
        </form>
    </div>
</div>
</body>
</html>
