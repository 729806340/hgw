<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<form method="post" name="receiver_add" id="receiver_add" class="ncap-form-dialog" action="<?php echo urlAdminShop('receiver', 'add_receiver');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output['common_info']['goods_commonid'];?>" name="commonid">
    <div id="warning"></div>
    <div class="ncap-form-default">
        <dl class="row">
            <dt class="tit">领卡人标识码</dt>
            <dd class="opt"><input type="text" class="text w100" name="sn" value=""/></dd>
        </dl>
        <dl class="row">
            <dt class="tit">领卡人名称</dt>
            <dd class="opt"><input type="text" class="text w100" name="receiver" value=""/></dd>
        </dl>

        <div class="bot">
<!--            <input type="submit" class="submit" value="提交">-->
            <a href="javascript:void(0);" class="ncap-btn-big ncap-btn-green" nctype="btn_submit"><?php echo $lang['nc_submit'];?></a>
        </div>
    </div>
</form>
<script>
    $(function(){
        $('a[nctype="btn_submit"]').click(function(){
            if($("#receiver_add").valid()){
                $("#receiver_add").submit();
            } else {
                console.log('error');
            }
        });

        $('#receiver_add').validate({
                submitHandler:function(form){
                    ajaxpost('receiver_add', '', '', 'onerror');
                },
            errorLabelContainer: $('#warning'),
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if(errors)
                {
                    $('#warning').show();
                }
                else
                {
                    $('#warning').hide();
                }
            },
                rules : {
                    sn : {
                        required : true
                    },
                    receiver : {
                        required : true
                    }
                },
                messages : {
                    sn : {
                        required : '需要领卡人标识码;'
                    },
                    receiver : {
                        required : '需要领卡人名称;'
                    }
                }
            });


    });
</script>