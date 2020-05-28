<div style="width: 350px; margin-top: 15px;padding: 10px 10px; border: 1px dashed  black" class="wrapper">
    <div class="order-info">
        <h4>退款信息</h4>
        <p>订单号： <?php echo $output['refund_info']['order_sn']; ?></p>
        <p>退款单号： <?php echo $output['refund_info']['refund_sn']; ?></p>
        <p>订单状态： <span style="color: red"><?php echo $output['refund_info']['refund_state']; ?></span></p>
        <p>申请时间： <?php echo $output['refund_info']['add_time']; ?></p>
        <p>退款商品： <?php echo $output['refund_info']['goods_name']; ?></p>
        <p>退款金额： <?php echo $output['refund_info']['refund_amount']; ?></p>
        <p>申请原因： <?php echo $output['refund_info']['buyer_message']; ?></p>
    </div>
</div>