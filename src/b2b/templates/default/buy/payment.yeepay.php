<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
</style>
<h1>正在转向支付页面...</h1>
<form action="https://www.yeepay.com/app-merchant-proxy/node" method="post" id="pay-form" accept-charset="gbk">
    <!-- TODO 提交支付请求 -->
    <?php
    $payment_info = $output['payment_info'];
    // Generate all the hidden field.
    foreach ($payment_info as $key=>$value)
    {
        $strHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
    }

    $strHtml .= '<input type="submit" name="btn_purchase" value="buy" style="display:none;" />';
    echo $strHtml;
    ?>
</form>
<script>
    $(document).ready(function(){
        $("#pay-form").submit();
    });
</script>
