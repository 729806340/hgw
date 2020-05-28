<style>
	.line_pic4 .item{ width: 25%; text-align: center; float: left;}
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="line_pic4 wrapper">
  <div nctype="item_content" class="content clearfix">
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {?>
	  <div nctype="item_image" class="item"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="image" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>
