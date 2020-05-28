<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php if(!empty($output['goods_list']) && is_array($output['goods_list'])){ ?>

<ul class="search-goods-list">
  <?php foreach($output['goods_list'] as $key => $value){ ?>
  <li>
    <div class="goods-pic"><img src="<?php echo thumb($value, 60);?>" /></div>
    <div class="goods-name"><?php echo $value['goods_name'];?></div>
    <div class="goods-price">￥<?php echo $value['goods_price'];?></div>
    <div class="goods-price">￥<?php echo $value['goods_return_price'];?></div>
    <!--<a nctype="btn_add_goods" data-goods-id="<?php /*echo $value['goods_id'];*/?>" data-goods-name="<?php /*echo $value['goods_name'];*/?>" data-goods-price="<?php /*echo $value['goods_promotion_price'];*/?>" data-goods-image="<?php /*echo thumb($value, 240);*/?>" href="javascript:;">添加</a> </li>-->
    <a href="<?php echo urlAdminShop('tuan_config', 'save_tuan_config_goods', array('goods_id' => $value['goods_id'], 'tuan_config_id' => $output['tuan_config_id']))?>" data-goods-id="<?php echo $value['goods_id'];?>">添加</a>
  </li>
  <?php } ?>
</ul>
<div id="goods_pagination" class="pagination"> <?php echo $output['show_page'];?> </div>
<?php }else { ?>
<p class="no-record"><?php echo $lang['nc_no_record'];?></p>
<?php } ?>
