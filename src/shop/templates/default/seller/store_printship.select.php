<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="eject_con">
    <div id="warning"></div>
    <!--<form method="post" id="order_print_ship_form" onsubmit="ajaxpost('order_print_ship_form', '', '', 'onerror');return false;" action="index.php?act=store_printship&op=pushOne">-->
        <input type="hidden" name="form_submit" value="ok" />
        <input type="hidden" name="order_sn" id="order_sn" value="<?php echo $_GET['order_sn']?>">
        <dl>
            <dt>订单编号：</dt>
            <dd><span class="num"><?php echo trim($_GET['order_sn']); ?></span></dd>
        </dl>

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
            DialogManager.close('seller_order_print_ship;');
        });
        $('#createPrintShip').click(function(){
            window.open("index.php?act=store_printship&op=printship_add");
            DialogManager.close('seller_order_print_ship');
        });

        $('#confirm_button').click(function () {
            var order_sn = '<?php echo $_GET['order_sn']?>';
            var template_id = $('#template_id').val();
            if(!template_id) {
                showError('请选择模板');
                return false;
            }
            $.post(
                'index.php?act=store_printship&op=pushOne',
                {order_sn:order_sn,template_id:template_id},
                function(data){
                if(data.error==1001){
                    showError(data.msg);
                }else{
                    showSucc('操作成功');
                    window.location.reload();
                }
            })
        })
    });
</script>