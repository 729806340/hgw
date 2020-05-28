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

        <div class="ncap-order-details">
            <div class="tabs-panels">
                <div class="misc-info">
                    <h4>订单信息</h4>
                    <input title="manage-type" id="manage-type" type="hidden" value="<?php echo $output['order_info']['manage_type'];?>">
                    <input title="obId" id="ob-id" type="hidden" value="<?php echo $output['ob_id'];?>">
                    <dl>
                        <dt>订单编号<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['order_info']['order_sn']; ?><?php if ($output['order_info']['order_type'] == 2) echo '[预定]'; ?><?php if ($output['order_info']['order_type'] == 3) echo '[门店自提]'; ?></dd>
                        <dt>订单来源<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo str_replace(array(1, 2), array('PC端', '移动端'), $output['order_info']['order_from']); ?></dd>
                        <dt>创建时间<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo date('Y-m-d H:i:s', $output['order_info']['add_time']); ?></dd>
                        <dt>订单金额<?php echo $lang['nc_colon']; ?></dt>
                        <dd>￥<?php echo $output['order_info']['order_amount'] ; ?></dd>
                        <dt>收货人<?php echo $lang['nc_colon']; ?></dt>
                        <dd><a class="nyroModal"
                               href="/admin/modules/shop/index.php?act=member&op=member_view&member_id=<?php echo $output['order_info']['buyer_id']; ?>"><?php echo $output['order_info']['buyer_name']; ?></a>
                        </dd>
                        <dt>联系方式<?php echo $lang['nc_colon']; ?></dt>
                        <dd><span
                                class="r_mobile"><?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone']; ?></span>
                        </dd>
                        <dt>技术服务费：<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['order_info']['technical_fee'] ; ?>
                        </dd>
                        <dt>金融服务费<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['order_info']['financial_fee'] ; ?>
                        </dd>
                    </dl>
                </div>
                <div class="goods-info">
                    <table>
                        <thead>
                        <tr>
                            <th colspan="2">商品</th>
                            <th>单价</th>
                            <th>商品数量</th>
                            <th>支付金额</th>
                            <th>成本金额</th>
                            <th>佣金比例(%)</th>
                            <th>红包金额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0; ?>
                        <?php foreach ($output['order_info']['goods_list'] as $goods) { ?>
                            <?php $i++; ?>
                            <?php
                            $refund_id = isset($goods['extend_refund']) && !empty($goods['extend_refund']) ? $goods['extend_refund']['refund_id'] : $output['order_info']['refund_all']['refund_id'];
                            ?>
                            <tr data-key="<?php echo $goods['rec_id']; ?>" class="order-goods">
                                <td class="w30">
                                    <div class="goods-thumb"><a
                                            href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                            target="_blank"><img alt="<?php echo $lang['product_pic']; ?>"
                                                                 src="<?php echo thumb($goods, 60); ?>"/> </a></div>
                                </td>
                                <td style="text-align: left;"><a
                                        href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                        target="_blank"><?php echo $goods['goods_name']; ?></a><br/><?php echo $goods['goods_spec']; ?>
                                </td>
                                <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_price']); ?></td>
                                <td class="w60"><?php echo $goods['goods_num']; ?></td>
                                <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_pay_price']); ?></td>
                                <?php if($output['order_info']['manage_type']=='platform'){?>
                                    <td class="w80">
                                        <?php echo $lang['currency'] . ncPriceFormat(($goods['goods_pay_price']+$goods['rpt_bill'])*$goods['commis_rate']/100); ?>
                                    </td>
                                <?php }else{ ?>
                                <td class="w60">
                                    <?php echo $lang['currency'] . ncPriceFormat($goods['goods_cost']); ?>
                                </td>
                                <?php } ?>
                                <td class="w80">
                                    <?php echo $goods['commis_rate'] == 200 ? '' : $goods['commis_rate']; ?>
                                </td>
                                <td class="w60">
                                    <?php echo $lang['currency'] . ncPriceFormat($goods['rpt_bill']); ?>
                                </td>
                            </tr>
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

                </div>
            </div>
        </div>
        <form id="punish_form" method="post" action="index.php?act=order&op=add_service_fee"  enctype="multipart/form-data">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="order_sn" id="order_sn" value="<?php echo $output['order_info']['order_sn'] ; ?>" />
            <div class="ncap-form-default">
                <dl class="row">
                    <dt class="tit"><em>*</em>技术服务费</dt>
                    <dd class="opt">
                        <input title="" type="text" value="<?php echo $output['order_info']['technical_fee'] ; ?>" name="technical_fee" id="technical_fee" class="input-txt" />
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit"><em>*</em>金融服务费</dt>
                    <dd class="opt">
                        <input title="" type="text" value="<?php echo $output['order_info']['financial_fee'] ; ?>" name="financial_fee" id="financial_fee" class="input-txt" />
                    </dd>
                </dl>
                <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
            </div>
        </form>

    </div>

</div>
<script type="text/javascript">
    $(function () {
        $('#submitBtn').click(function (e) {
            var order_sn = $('#order_sn').val();
            var technical_fee = $('#technical_fee').val();
            var financial_fee = $('#financial_fee').val();
            if(technical_fee===''||financial_fee ==='') return alert('服务费金额不能为空');
            $.ajax({url: 'index.php?act=order&op=add_service_fee',
                type: 'POST',
                dataType: 'json',
                data: {order_sn:order_sn,technical_fee:technical_fee,financial_fee:financial_fee,form_submit:'ok'}}).done(function (res) {
                console.log(res);
                if(!res.state){
                    return alert(res.msg)
                }
                alert('服务费更新成功');
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