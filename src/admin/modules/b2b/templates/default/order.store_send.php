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
            <li>提交后，订单状态变为待收货状态</li>
        </ul>
    </div>
    <form method="post" name="form1" id="form1"
          action="index.php?act=<?php echo $_GET['act']; ?>&op=change_state&state_type=order_send&order_id=<?php echo intval($_GET['order_id']); ?>">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" value="<?php echo getReferer(); ?>" name="ref_url">

        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">订单编号</label>
                </dt>
                <dd class="opt"><?php echo $output['order_info']['order_sn']; ?>
                    <?php if ($output['order_info']['order_type'] == 2) echo '[预定]'; ?>
                    <?php if ($output['order_info']['order_type'] == 3) echo '[门店自提]'; ?>
                </dd>
            </dl>
            <?php if ($_GET['act'] == 'order') { ?>
                <dl class="row">
                    <dt class="tit">
                        <label for="site_name">收货信息</label>
                    </dt>
                    <dd class="opt">
                        <span class="r_name"><?php echo $output['order_info']['extend_order_common']['reciver_name']; ?></span>&nbsp;&nbsp;,&nbsp;<span
                                class="r_info"><?php echo @$output['order_info']['extend_order_common']['reciver_info']['address']; ?></span>
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit">
                        <label for="site_name">联系方式</label>
                    </dt>
                    <dd class="opt"><span
                                class="r_mobile"><?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone']; ?></span>
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
                                <th><?php echo $lang['product_num']; ?></th>
                                <th>退款进度</th>
                                <th>优惠活动</th>
                                <th>佣金比例</th>
                                <th>收取佣金</th>
                                <th>更多字段</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; ?>
                            <?php foreach ($output['order_info']['goods_list'] as $goods) { ?>
                                <?php $i++; ?>
                                <?php
                                $refund_id = isset($goods['extend_refund']) && !empty($goods['extend_refund']) ? $goods['extend_refund']['refund_id'] : $output['order_info']['refund_all']['refund_id'];
                                ?>
                                <tr>
                                    <td class="w30">
                                        <div class="goods-thumb"><a
                                                    href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                                    target="_blank"><img alt="<?php echo $lang['product_pic']; ?>"
                                                                         src="<?php echo thumb($goods, 60); ?>"/> </a>
                                        </div>
                                    </td>
                                    <td style="text-align: left;"><a
                                                href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                                target="_blank"><?php echo $goods['goods_name']; ?></a><span
                                                class="rec"><a target="_blank"
                                                               href="<?php echo urlShop('snapshot', 'index', array('rec_id' => $goods['rec_id'])); ?>">[交易快照]</a></span><br/><?php echo $goods['goods_spec']; ?>
                                    </td>
                                    <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_price']); ?></td>
                                    <td class="w60"><?php echo $goods['goods_num']; ?></td>
                                    <td class="w60"><a class="nyroModal"
                                                       href="index.php?act=refund&op=view&refund_id=<?php echo $refund_id; ?>"><?php echo $goods['step']; ?></a>
                                    </td>
                                    <td class="w100"><?php echo orderGoodsType($goods['goods_type']); ?></td>
                                    <td class="w60"><?php echo $goods['commis_rate'] == 200 ? '' : $goods['commis_rate'] . '%'; ?></td>
                                    <td class="w80"><?php echo $goods['commis_rate'] == 200 ? '' : ncPriceFormat($goods['goods_pay_price'] * $goods['commis_rate'] / 100); ?></td>
                                    <td class="w60"><a class="nyroModal"
                                                       href="index.php?act=order&op=show_goods_column&order_id=<?php echo $output['order_info']['order_id']; ?>&goods_id=<?php echo $goods['goods_id']; ?>">更多</a>
                                    </td>
                                </tr>
                                <!-- S 赠品列表 -->
                                <?php if (!empty($output['order_info']['zengpin_list']) && $i == count($output['order_info']['goods_list'])) { ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td colspan="6">
                                            <div class="ncm-goods-gift">赠品：
                                                <ul><?php foreach ($output['order_info']['zengpin_list'] as $zengpin_info) { ?>
                                                        <li>
                                                            <a title="赠品：<?php echo $zengpin_info['goods_name']; ?> * <?php echo $zengpin_info['goods_num']; ?>"
                                                               target="_blank"
                                                               href="<?php echo $zengpin_info['goods_url']; ?>"><img
                                                                        src="<?php echo $zengpin_info['image_60_url']; ?>"/></a>
                                                        </li>
                                                    <?php } ?></ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <!-- E 赠品列表 -->
                            <?php } ?>
                            </tbody>
                            <!-- S 促销信息 -->
                            <?php $pinfo = $output['order_info']['extend_order_common']['promotion_info']; ?>
                            <?php if (!empty($pinfo)) { ?>
                                <?php $pinfo = unserialize($pinfo); ?>
                                <tfoot>
                                <tr>
                                    <th colspan="10">其它信息</th>
                                </tr>
                                <tr>
                                    <td colspan="10">
                                        <?php if ($pinfo == false) { ?>
                                            <?php echo $output['order_info']['extend_order_common']['promotion_info']; ?>
                                        <?php } elseif (is_array($pinfo)) { ?>
                                            <?php foreach ($pinfo as $v) { ?>
                                                <dl class="nc-store-sales">
                                                    <dt><?php echo $v[0]; ?></dt>
                                                    <dd><?php echo $v[1]; ?></dd>
                                                </dl>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                                </tfoot>
                            <?php } ?>
                            <!-- E 促销信息 -->
                        </table>
                    </dd>
                </dl>
            <?php } ?>
            <dl class="row">
                <dt class="tit">
                    <label for="site_name">订单总金额 </label>
                </dt>
                <dd class="opt"><?php echo ncPriceFormat($output['order_info']['order_amount']); ?>
                </dd>
            </dl>
            <?php if ($output['order_info']['order_type'] == 2) { ?>
                <dl class="row">
                    <dt class="tit">
                        <label for="site_name">订单进度 </label>
                    </dt>
                    <dd class="opt">
                        <?php foreach ($output['order_info']['book_list'] as $book_info) { ?>
                            <?php echo $book_info['book_step']; ?>，
                            应付金额：<?php echo $book_info['book_amount']; ?>，
                            支付方式：<?php echo $book_info['book_pay_name']; ?>，
                            支付充值卡：<?php echo ncPriceFormat($book_info['book_rcb_amount']); ?>，
                            支付预存款：<?php echo ncPriceFormat($book_info['book_pd_amount']); ?>，
                            支付交易号：<?php echo $book_info['book_trade_no']; ?>，
                            支付时间：
                            <?php if ($book_info['book_pay_time']) { ?>
                                <?php echo !intval(date('His', $book_info['book_pay_time'])) ? date('Y-m-d', $book_info['book_pay_time']) : date('Y-m-d H:i:s', $book_info['book_pay_time']); ?>
                            <?php } ?>，
                            备注：<?php echo $book_info['book_state']; ?><br/>
                        <?php } ?>
                    </dd>
                </dl>
            <?php } ?>
            <!--<dl class="row">
                <dt class="tit">
                    <label for="closed_reason">物流公司</label>
                </dt>
                <dd class="opt">
                    <input type="text" class="txt2" name="shipping_express" id="shipping_express" maxlength="40">
                    <span class="err"></span>
                    <p class="notic"><span class="vatop rowform">物流公司名称</span></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="closed_reason">物流单号</label>
                </dt>
                <dd class="opt">
                    <input type="text" class="txt2" name="shipping_code" id="shipping_code" maxlength="40">
                    <span class="err"></span>
                    <p class="notic"><span class="vatop rowform">物流单号</span></p>
                </dd>
            </dl>-->
            <div class="bot"><a href="JavaScript:void(0);" id="ncsubmit"
                                class="ncap-btn-big ncap-btn-green"><?php echo $lang['nc_submit']; ?></a></div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function () {
        $('#payment_time').datepicker({dateFormat: 'yy-mm-dd', maxDate: '<?php echo date('Y-m-d', TIMESTAMP);?>'});
        $('#ncsubmit').click(function () {
            if ($("#form1").valid()) {
                if (confirm("操作提醒：确认要标记发货吗?")) {
                } else {
                    return false;
                }
                $('#form1').submit();
            }
        });
        $("#form1").validate({
            errorPlacement: function (error, element) {
                var error_td = element.parent('dd').children('span.err');
                error_td.append(error);
            },
            rules: {},
            messages: {}
        });
    });
</script> 