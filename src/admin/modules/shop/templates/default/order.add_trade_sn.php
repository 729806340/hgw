<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i
                        class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3><?php echo $lang['order_manage']; ?></h3>
                <h5><?php echo $lang['order_manage_subhead']; ?></h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span']; ?>"></span></div>
        <ul>
            <li>提交后，订单将变为支付状态，商家后台可以操作发货</li>
        </ul>
    </div>
    <form method="post" name="form1" id="form1"
          action="index.php?act=<?php echo $_GET['act']; ?>&op=add_trade_sn&order_id=<?php echo intval($_GET['order_id']); ?>">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" value="index.php?act=order&op=show_order&order_id=<?php echo intval($_GET['order_id']); ?>"
               name="ref_url">
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">订单编号</label>
                </dt>
                <dd class="opt"><?php echo $output['order_info']['order_sn']; ?>
                </dd>
            </dl>
            <?php if ($_GET['act'] == 'order') { ?>
                <dl class="row">
                    <dt class="tit">
                        <label for="site_name">支付单号</label>
                    </dt>
                    <dd class="opt"><?php echo $output['order_info']['pay_sn']; ?>
                    </dd>
                </dl>
            <?php } ?>

            <dl class="row">
                <dt class="tit">
                    <label for="site_name">流水号</label>
                </dt>
                <dd class="opt">
                    <input class="" name="trade_no" value="<?php echo $output['order_info']['trade_no']; ?>" type="text"/>
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>

            <dl class="row">
                <dt class="tit">
                    <label for="site_name">订单总金额 </label>
                </dt>
                <dd class="opt"><?php echo ncPriceFormat($output['order_info']['order_amount']); ?>
                </dd>
            </dl>

            <dl class="row">
                <dt class="tit">
                    <label for="site_name">付款时间</label>
                </dt>
                <dd class="opt">
                    <?php echo date('Y-m-d H:i:s'); ?>
                    <span class="err"></span>
                    <p class="notic"></p>
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
            $('#form1').submit();
        });
        $("#form1").validate({
            errorPlacement: function (error, element) {
                var error_td = element.parent('dd').children('span.err');
                error_td.append(error);
            },
            rules: {
                payment_time: {
                    required: true
                },
                <?php foreach($output['goods_list'] as $goods){ ?>
                jicai_price<?php echo $goods['goods_id'];?>    : {
                    required: true
                },
                <?php }?>
            },
            messages: {
                payment_time: {
                    required: '<i class="fa fa-exclamation-circle"></i>请填写付款准确时间'
                },
            }
        });
    });
</script> 