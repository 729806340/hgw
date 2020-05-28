<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="ncap-goods-sku" >
  <div class="title">
    <h4>编号</h4>
    <h4>单位</h4>
    <h4>单价</h4>
    <h4>库存</h4>
    <h4>成本价</h4>
<!--    <h4>进项税</h4>-->
<!--    <h4>销项税</h4>-->
  </div>
  <div class="content">
    <ul>
      <?php foreach ($output['goods_list'] as $val) {?>
      <li>
        <span><?php echo $val['goods_id'];?></span>
        <span><?php echo $val['goods_calculate'];?></span>
        <span><?php echo $val['goods_price'];?></span>
        <span><?php echo $val['goods_storage'];?></span>
        <span><?php echo $val['goods_cost'];?></span>
<!--        <span>--><?php //echo $val['tax_input'];?><!--</span>-->
<!--        <span>--><?php //echo $val['tax_output'];?><!--</span>-->
      </li>
      <?php }?>
    </ul>
  </div>
</div>
<script type="text/javascript">
$(function(){
//自动加载滚动条
    $('.content').perfectScrollbar();
});
</script> 