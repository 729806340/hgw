<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="modal">
    <div class="explanation" id="explanation" style="width: auto;">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
        </div>
        <ul>
            <li>提交后，商品状态变为已发货状态</li>
        </ul>
    </div>
    <form method="post" name="form1" id="form1"
          action="index.php?act=order&op=address_send&address_id=<?php echo intval($_GET['address_id']); ?>">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" value="<?php echo getReferer(); ?>" name="ref_url">

        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">订单编号</label>
                </dt>
                <dd class="opt"><?php echo $output['order_info']['order_sn']; ?></dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">收货信息</label>
                </dt>
                <dd class="opt"><?php echo $output['address_info']['buyer_name'] . ' , ' . $output['address_info']['address'] . '(' . $output['address_info']['buyer_phone'] . ')' ?></dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">商品名称 </label>
                </dt>
                <dd class="opt"><?php echo $output['goods_info']['goods_name']; ?>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">商品金额 </label>
                </dt>
                <dd class="opt">
                    <?php echo ncPriceFormat($output['address_info']['rec_price']); ?> X
                    <?php echo $output['address_info']['rec_num']; ?> =
                    <?php echo ncPriceFormat($output['address_info']['rec_price'] * $output['address_info']['rec_num']); ?>

                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="closed_reason">物流公司</label>
                </dt>
                <dd class="opt">
                    <input type="text" class="txt2" name="logi_name" id="logi_name" maxlength="40">
                    <span class="err"></span>
                    <p class="notic"><span class="vatop rowform">物流公司名称</span></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="closed_reason">物流单号</label>
                </dt>
                <dd class="opt">
                    <input type="text" class="txt2" name="logi_code" id="logi_code" maxlength="40">
                    <span class="err"></span>
                    <p class="notic"><span class="vatop rowform">物流单号</span></p>
                </dd>
            </dl>
            <div class="bot"><a href="JavaScript:void(0);" id="ncsubmit"
                                class="ncap-btn-big ncap-btn-green"><?php echo $lang['nc_submit']; ?></a></div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function () {
        $('#ncsubmit').click(function () {
            if($('#logi_name').val()===''||$('#logi_code').val()==='')
                return alert('物流公司和物流单号不得为空');
            if (confirm("操作提醒：确认要标记发货吗?")) {
            } else {
                return false;
            }
            $('#form1').submit();
        });
    });
</script> 