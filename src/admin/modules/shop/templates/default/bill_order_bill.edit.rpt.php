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
                        <dt>收货人<?php echo $lang['nc_colon']; ?></dt>
                        <dd><a class="nyroModal"
                               href="/admin/modules/shop/index.php?act=member&op=member_view&member_id=<?php echo $output['order_info']['buyer_id']; ?>"><?php echo $output['order_info']['buyer_name']; ?></a>
                        </dd>
                        <dt>联系方式<?php echo $lang['nc_colon']; ?></dt>
                        <dd><span
                                class="r_mobile"><?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone']; ?></span>
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
                                    <td class="w80"><?php echo $lang['currency'] . ncPriceFormat(($goods['goods_pay_price']+$goods['rpt_bill'])*$goods['commis_rate']/100); ?></td>
                                <?php }else{ ?>
                                <td class="w60">
                                    <?php echo $goods['goods_cost']; ?>
                                </td>
                                <?php } ?>
                                <td class="w80"><?php echo $goods['commis_rate']; ?></td>
                                <td class="w60"><input class="editable" type="text" data-field="rpt_bill"
                                                       data-original="<?php echo $goods['rpt_bill']; ?>"
                                                       value="<?php echo $goods['rpt_bill']; ?>"
                                                       title="可编辑"></td>
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
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('.editable').blur(function (e) {
            var $this = $(this),
                data = {
                    key: $this.parents('tr.order-goods').data('key'),
                    field: $this.data('field'),
                    original: $this.data('original'),
                    value: $this.val()
                },
                manageType = $('#manage-type').val();
                url = '/admin/modules/shop/index.php?act=bill&op=edit_rec&ob_id=<?php echo $output['ob_id']; ?>&order_id=<?php echo $output['order_info']['order_id']; ?>&rec_id='
                +data.key+'&field='+data.field+'&value='+data.value;
            if (parseFloat(data.original) == parseFloat(data.value)) return false;
            if (confirm('确定要将此项的值从' + data.original + '修改为' + data.value + '吗？') == false) {
                $this.val(data.original);
                return false;
            }
            $.post(url, data, function (res) {
                if(res.error){
                    alert(res.msg);
                    $this.val(data.original);
                    return;
                }
                $this.data('original',data.value);
                // 如果修改的是平台商家的佣金比例，则同时更新佣金和成本
                if(data.field == 'commis_rate') {
                    var td = $this.parent();
                    td.next().text(res.commis);
                    if(manageType=='platform')
                        td.prev().text(res.cost);
                }
            },'json');
        })
    });
</script>