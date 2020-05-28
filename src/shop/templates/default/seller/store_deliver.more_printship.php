<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="eject_con">
    <div id="warning"></div>
    <!--<form method="post" id="order_print_ship_form" onsubmit="ajaxpost('order_print_ship_form', '', '', 'onerror');return false;" action="index.php?act=store_printship&op=pushOne">-->
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="dir_name" id="dir_name" >

    <dl>
        <?php
        if(count($output['template_list']) > 0){
            ?>
            <dt>请选择模板：</dt>
            <dd>
                <ul class="checked">
                    <select name="template_id" id="template_id">
                        <option value="">请选择</option>
                        <?php foreach($output['template_list'] as $item=>$value){?>
                            <option value="<?php echo $value['id']?>"><?php echo $value['template_name']?>(<?php echo $value['express_name']?>)</option>
                        <?php }?>
                    </select>
                </ul>
            </dd>
            <dt></dt>
            <dd><div class="upload-con-div" style="border: none; margin:0px;">
                    <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                    <input type="file" id="batch_print" hidefocus="true" size="1" class="input-file" name="file"/>
                    </span>
                            <p><i class="icon-upload-alt"></i>导入文件</p>
                        </a>

                    </div></div></dd>
            <dt></dt>
            <dd id="file_name"></dd>
            <dt>温馨提示：</dt>
            <dd>请先下载<a href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=downBatchTemplate&file_type=dzmd" style="color: blue;">模板文件</a></dd>


            <?php
        }else{
            ?>
            <dd style="text-align: center;" >您还没有创建电子面单模板：<a href="#" id="createPrintShip">点击创建</a></dd>
        <?php }?>
    </dl>
    <?php if(count($output['template_list']) > 0){ ?>
        <dl class="bottom">
            <dt>&nbsp;</dt>
            <dd>
                <input type="submit" class="submit" id="confirm_button" value="确认" />
            </dd>
        </dl>
    <?php }?>
    <!--</form>-->
</div>
<script type="text/javascript">
    $(function(){
        $('#cancel_button').click(function(){
            DialogManager.close('seller_order_print_ship');
        });
        $('#createPrintShip').click(function(){
            window.open("index.php?act=store_printship&op=printship_add");
            DialogManager.close('seller_order_print_ship');
        });

        //ajax上传文件
        $('#batch_print').fileupload({
            dataType: 'json',
            url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload_printship',
            done: function (e,data) {
                var param = data.result;
                if(param.state==false){
                    showError(param.msg);
                    return false;
                }else{
                    $("#file_name").html(param.file_name);
                    $("#dir_name").val(param.dir_name);
                }
            }
        });

        //批量申请电子面单
        $("#confirm_button").click(function () {
             var template_id = $("#template_id").val();
             var dir_name = $("#dir_name").val();
             if(!template_id){
                 showError('请选择电子面单模板');
                 return false;
             }

             if(dir_name==''){
                 showError('请上传文件');
                 return false;
             }
            $.ajax({
                type: 'POST',
                url: 'index.php?act=store_deliver&op=upload_morepringship',
                data: {
                    template_id:template_id,
                    dir_name:dir_name
                },
                dataType: 'json',
                success: function(data){
                    if(data.state==false){
                        showError(data.msg);
                        return false;
                    }
                    var tips = "<ul class=\"mt5\"><li>文件上传成功!</li>";
                    tips +="<li>批量申请电子面单的订单有<font color=\"red\">"+data.totals+"</font>个；</li>";
                    tips +="<li>发货成功的订单有<font color=\"red\">"+data.succNum+"</font>个；</li>";
                    tips +="<li>发货失败的订单有<font color=\"red\">"+data.failNum+"</font>个；</li>";
                    if(parseInt(data.failNum)>0){
                        tips +="<li>发送的失败的订单编号有：</li>";
                        for(var i = 0 ; i<data.failOrderids.length; i++){
                            tips +=data.failOrderids[i]+"、";
                        }
                    }
                    if(parseInt(data.failNum)>0){
                        tips +="<li>失败原因：</li>";
                        for(var i = 0 ; i<data.errorMsg.length; i++){
                            tips +=	"<li>"+data.errorMsg[i]+"</li>";
                        }
                    }
                    tips +="</ul>";
                    $("#showBatchResult").html(tips);
                    $("#showBatchResult").show('fast');
                },
            });
            DialogManager.close('seller_order_print_ship');
        })
    });
</script>