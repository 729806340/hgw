<style>
    table{width:80%;margin-left:10%;}
    table th{text-align: center;line-height:20px}
    p{font-size:15px;text-align:left;}
</style>
<?php  if(isset($output['message'])){?>
<p style="padding-top:10px;font-size:20px;padding-top:20px;text-align:center"><?php echo $output['message'];?></p>
<?php } ?>
<?php if(!isset($output['message']) &&(isset($output['channelfill_num']) && isset($output['channelfill_ordersn']))){ ?>
    <p>【同步数量】：<?php echo $output['channelfill_num'];?></p>
    <p>【同步的订单号】：<?php echo $output['channelfill_ordersn']=="" ? "无":$output['channelfill_ordersn'];?></p>
<?php } ?>