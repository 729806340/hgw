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
                    <h4>退款信息</h4>
                    <input title="manage-type" id="manage-type" type="hidden"
                           value="<?php echo $output['rec_info']['manage_type']; ?>">
                    <input title="obId" id="ob-id" type="hidden" value="<?php echo $output['ob_id']; ?>">
                    <dl>
                        <dt>订单编号<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['refund_info']['order_sn']; ?></dd>
                        <dt>退款单编号<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['refund_info']['refund_sn']; ?></dd>
                        <dt>创建时间<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo date('Y-m-d H:i:s', $output['refund_info']['add_time']); ?></dd>
                        <dt>退款原因<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo $output['refund_info']['reason_info']; ?></dd>
                        <dt>联系方式<?php echo $lang['nc_colon']; ?></dt>
                        <dd><?php echo @$output['refund_info']['goods_name']; ?></dd>
                    </dl>
                </div>
                <div class="goods-info">
                    <table>
                        <thead>
                        <tr>
                            <th>退款金额(结算基数)</th>
                            <th>支付金额</th>
                            <th>成本金额</th>
                            <th>佣金比例(%)</th>
                            <th>红包金额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $goods = $output['rec_info'];
                        $originalAmount = $output['refund_info']['refund_amount_bill']==-1?$output['refund_info']['refund_amount']:$output['refund_info']['refund_amount_bill'];
                        $refund_id = isset($goods['extend_refund']) && !empty($goods['extend_refund']) ? $goods['extend_refund']['refund_id'] : $output['refund_info']['refund_all']['refund_id'];
                        ?>
                        <tr data-key="<?php echo $output['rec_info']['rec_id']; ?>" class="order-goods">
                            <td class="w80"><input class="editable" type="text" data-field="refund_amount"
                                                   data-original="<?php echo $originalAmount; ?>"
                                                   value="<?php echo $originalAmount; ?>" title="可编辑"></td>
                            <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_pay_price']); ?></td>
                            <?php if ($output['rec_info']['manage_type'] == 'platform') { ?>
                                <td class="w80"><?php echo $lang['currency'] . ncPriceFormat(($goods['goods_pay_price'] + $goods['rpt_bill']) * $goods['commis_rate'] / 100); ?></td>
                            <?php } else { ?>
                                <td class="w60"><input class="editable" type="text" data-field="goods_cost"
                                                       data-original="<?php echo $goods['goods_cost']; ?>"
                                                       value="<?php echo $goods['goods_cost']; ?>" title="可编辑"></td>
                            <?php } ?>
                            <td class="w80"><input class="editable" type="text" data-field="commis_rate"
                                                   data-original="<?php echo $goods['commis_rate'] == 200 ? '' : $goods['commis_rate']; ?>"
                                                   value="<?php echo $goods['commis_rate'] == 200 ? '' : $goods['commis_rate']; ?>"
                                                   title="可编辑"></td>
                            <td class="w60"><?php echo $goods['rpt_bill']; ?></td>
                        </tr>
                        </tbody>
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
                manageType = $('#manage-type').val(),
            url = '/admin/modules/shop/index.php?act=bill&op=edit_rec&ob_id=<?php echo $output['ob_id']; ?>&order_id=<?php echo $output['refund_info']['order_id']; ?>&rec_id='
                + data.key + '&field=' + data.field + '&value=' + data.value;
            if(data.field=='refund_amount')
                url = '/admin/modules/shop/index.php?act=bill&op=edit_refund&form_submit=ok&ob_id=<?php echo $output['ob_id']; ?>&refund_id=<?php echo $output['refund_info']['refund_id']; ?>&field=' + data.field + '&value=' + data.value;
            if (parseFloat(data.original) == parseFloat(data.value)) return false;
            if (confirm('确定要将此项的值从' + data.original + '修改为' + data.value + '吗？') == false) {
                $this.val(data.original);
                return false;
            }
            $.post(url, data, function (res) {
                if (res.error) {
                    alert(res.msg);
                    $this.val(data.original);
                    return;
                }
                $this.data('original', data.value);
                // 如果修改的是平台商家的佣金比例，则同时更新佣金和成本
                if (data.field == 'commis_rate') {
                    var td = $this.parent();
                    td.next().text(res.commis);
                    if (manageType == 'platform')
                        td.prev().text(res.cost);
                }
            }, 'json');
        })
    });
</script>