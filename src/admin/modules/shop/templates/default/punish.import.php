<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style>
    .ncm-goods-gift {
        text-align: left;
    }

    .ncm-goods-gift ul {
        display: inline-block;
        font-size: 0;
        vertical-align: middle;
    }

    .ncm-goods-gift li {
        display: inline-block;
        letter-spacing: normal;
        margin-right: 4px;
        vertical-align: top;
        word-spacing: normal;
    }

    .ncm-goods-gift li a {
        background-color: #fff;
        display: table-cell;
        height: 30px;
        line-height: 0;
        overflow: hidden;
        text-align: center;
        vertical-align: middle;
        width: 30px;
    }

    .ncm-goods-gift li a img {
        max-height: 30px;
        max-width: 30px;
    }

    input.editable, input[type="text"], input[type="number"], input[type="password"] {
        width: 48px;
    }
</style>
<div class="page" style="padding: 10px;">
    <div class="ncap-order-style">

        <form id="punish_form" method="post" action="index.php?act=store&op=add_punish"  enctype="multipart/form-data">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="store_id" id="store_id" value="<?php echo $output['store_array']['store_id'] ; ?>" />
            <div class="ncap-form-default">
                <dl class="row">
                    <dt class="tit">下载模板</dt>
                    <dd class="opt">
                        下载地址
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit"><em>*</em>上传数据</dt>
                    <dd class="opt">
                        <input type="file" id="data-file">
                    </dd>
                </dl>
                <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
            </div>
        </form>

    </div>

</div>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script type="text/javascript">
    $(function () {
        var file = $('#data-file');

        file.fileupload({
            dataType: 'json',
            url: 'index.php?act=refund&op=import',
            done: function (e,data) {
                mask.fadeOut();loading.fadeOut();
                uploaded=true;
                var param = data.result;
                if(param.state==false){
                    showError(param.msg);
                    return false;
                }
                var tips = '<div class="title"><i class="fa fa-lightbulb-o"></i> <h4>文件上传成功</h4> </div>';
                tips += "<ul class=\"mt5\">";
                tips +="<li>批量退款的订单有<font color=\"red\">"+param.result.total+"</font>个；</li>";
                tips +="<li>退款成功的订单有<font color=\"red\">"+param.result.success+"</font>个；</li>";
                tips +="<li>退款失败的订单有<font color=\"red\">"+param.result.fail.length+"</font>个；</li>";
                if(param.result.fail.length>0){
                    tips +="<li>退款的失败的订单编号有：</li>";
                    for(var i = 0 ; i<param.result.fail.length; i++){
                        tips +=param.result.fail[i]+"、";
                    }
                    tips +="<li>失败原因：</li>";
                    for(var i = 0 ; i<param.result.errorMsg.length; i++){
                        tips +=	"<li>"+param.result.errorMsg[i]+"</li>";
                    }
                }
                tips +="</ul>";
                resultPanel.html(tips).fadeIn('fast');
            },
            fail : function () {
                mask.fadeOut();loading.fadeOut();
                uploaded=true;
                showError('上传失败');
            }
        });
        $('#submitBtn').click(function (e) {
            $.ajax({url: 'index.php?act=store&op=add_punish',
                type: 'POST',
                dataType: 'json',
                data: {channel_id:channel_id,store_id:store_id,cost_price:cost_price,cost_remark:cost_remark,form_submit:'ok'}}).done(function (res) {
                console.log(res);
                if(!res.state){
                    return alert(res.msg)
                }
                alert('罚款成功');
                console.log(res);
                $('.dialog_close_button').click();
            }).fail(function (xhr,error) {
                alert('请求失败');
                console.log(xhr);
            });
            //$('#punish_form').submit();
        });
    });
</script>