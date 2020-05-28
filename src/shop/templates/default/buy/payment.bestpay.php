<?php defined('ByShopWWI') or exit('Access Invalid!');
header('Content-Type:text/html;charset=utf-8');
?>
<h1>正在转向支付页面...</h1>
<form action="https://capi.bestpay.com.cn/interface/v2/order" enctype="application/x-www-form-urlencoded"  method="post" id="pay-form" accept-charset="UTF-8">
    <!-- TODO 提交支付请求 -->
    <?php
    $payment_info = $output['payment_info'];
    foreach ($payment_info as $key=>$value)
    {
        $strHtml .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
    }
    echo $strHtml;
    ?>
</form>
<script>
    $(document).ready(function(){
        $("#pay-form").submit();
    });
</script>
