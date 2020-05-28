<div style="width: 350px; margin-top: 15px;padding: 10px 10px; border: 1px dashed  black" class="wrapper">
    <div class="order-info">
        <h4>订单信息</h4>
        <p>订单号： <?php echo $output['order_info']['order_sn']; ?></p>
        <p>订单状态： <span style="color: red"><?php echo $output['order_info']['order_state']; ?></span></p>
        <p>下单时间： <?php echo $output['order_info']['add_time']; ?></p>
        <p>总金额： <?php echo $output['order_info']['order_amount']; ?></p>
    </div>
    <div class="goods-info" style="padding-top: 10px;">
        <h4>商品信息</h4>
        <table style="font-size: 10px;">
            <?php foreach ($output['order_goods_list'] as $k => $v) { ?>
                <tr>
                    <td><img src="<?php echo thumb($v,'60'); ?>"></td>
                    <td><?php echo $v['goods_name'];?></td>
                    <td><?php echo "{$v['goods_pay_price']}（成交价） * {$v['goods_num']}（个）"?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php if ($output['order_info']['if_deliver'] && $output['order_info']['express_info']['e_info']) { ?>
    <div class="ship-info" style="padding-top: 10px;">
        <h4>物流信息</h4>
        <?php foreach($output['order_info']['express_info']['e_info'] as $k=>$v) { ?>
            <p><?php echo $v['time'] ?></p><p class="e_name"><?php echo $v['context'] ?></p>
        <?php }?>
    </div>
    <?php } ?>
</div>