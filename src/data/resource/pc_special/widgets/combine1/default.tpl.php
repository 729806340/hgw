<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.mb-item-edit-content {
background: #EFFAFE url(<?php echo ADMIN_TEMPLATES_URL;
?>/images/cms_edit_bg_line.png) repeat-y scroll 0 0;
}
</style>
<div class="index_block goods-list">
  <div nctype="item_content" class="content">
    <?php if(!empty($item_data['item']['goods']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item']['goods'] as $item_value) {?>
    <div nctype="item_image" class="item">
      <div class="goods-pic"><img nctype="goods_image" shopwwi-url="<?php echo cthumb($item_value['goods_image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"  alt=""></div>
      <div class="goods-name" nctype="goods_name"><?php echo $item_value['goods_name'];?></div>
      <div class="goods-price" nctype="goods_price">￥<?php echo $item_value['goods_promotion_price'];?></div>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>