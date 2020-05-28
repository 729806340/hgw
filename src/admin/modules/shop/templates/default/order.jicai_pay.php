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
          action="index.php?act=<?php echo $_GET['act']; ?>&op=change_state&state_type=jicai_pay&order_id=<?php echo intval($_GET['order_id']); ?>">
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
                <dd class="opt"><?php echo $output['order_info']['trade_no']; ?>
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
                    <input class="" name="payment_time" value="<?php echo date('Y-m-d H:i:s'); ?>" type="text"/>
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">商品信息</label>
                </dt>
                <dd class="opt">
                    <table>
                        <thead>
                        <tr>
                            <th colspan="2">商品</th>
                            <th>单价</th>
                            <th>数量</th>
                            <th>集采销售单价</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($output['goods_list'] as $goods) { ?>
                            <tr>
                                <td class="w30">
                                    <div class="goods-thumb"><img src="<?php echo thumb($goods, 60); ?>" alt=""></div>
                                </td>
                                <td style="text-align: left;"><a target="_blank"
                                                                 href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"><?php echo $goods['goods_name']; ?></a>
                                </td>
                                <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_price']); ?></td>
                                <td class="w60"><?php echo $goods['goods_num']; ?></td>
                                <td class="w80"><input type="text" size="10"
                                                       name="jicai_price[<?php echo $goods['goods_id']; ?>]"
                                                       value="<?php echo ncPriceFormat($goods['goods_pay_price'] / $goods['goods_num']); ?>">
                                </td>

                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
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
            if ($("#form1").valid()) {

                var arr = $('#form1').serializeArray();
                var str = $.param(arr);

                $.ajax({
                    type: "post",
                    url: "index.php?act=order&op=jicai_total&order_id=<?php echo $_GET['order_id'];?>&" + str,
                    dataType: "json",
                    data: [],
                    success: function (data) {
                        if (data.status == '1') {
                            if (confirm("操作提醒：请核实新的订单价格为：<?php echo $lang['currency']; ?>" + data.msg + "\n\n\n继续操作吗?")) {
                            } else {
                                return false;
                            }
                            $('#form1').submit();
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert('请求出错..');
                    }
                });
            }
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