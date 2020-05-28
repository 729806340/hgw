<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="eject_con">
    <div class="adds" style=" min-height:240px;">
        <form id="changeform" method="post" action="index.php?act=store_order&op=bill_remark&order_id=<?php echo $output['order_info']['order_id']; ?>">
            <input type="hidden" name="form_submit" value="ok" />
            <dl>
                <dt style="width: 100px;"><?php echo '账单备注'.$lang['nc_colon'];?></dt>
                <dd>
                    <textarea title="账单备注" style="width: 480px;height: 160px;" class="text" name="bill_remark" id="bill_remark" rows="6"><?php echo $output['order_info']['extend_order_common']['bill_remark']; ?></textarea>
                </dd>
            </dl>
            <input type="submit" class="submit" id="confirm_button" style="margin: 20px auto;" value="<?php echo $lang['nc_ok'];?>" />

        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        return;
        $('#confirm_button1').on('click',function(){
            var bill_remark = $("#bill_remark").val();
            if( bill_remark=='' ){
                showError('不能为空');
            }

            $.post(
                "index.php?act=store_order&op=edit_deliver&order_id=<?php echo $output['order_id'];?>",
                {express_id: express_id, shipping_code: shipping_code},
                function(data){
                    if(data.status == 'true') {
                        $('.e_name').html(data.e_name);
                        $('.s_code').html(shipping_code);
                        DialogManager.close('edit_deliver');
                    } else {
                        showError(data.msg);
                    }
                }, "json");
        });
    });
</script>
