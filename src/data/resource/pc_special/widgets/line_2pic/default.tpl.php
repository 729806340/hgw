<style>
	.line_pic2 .item{ float: left; width: 50%;}
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="line_pic2 wrapper">
  <div nctype="item_content" class="content clearfix">
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {
	  //v($item_value);
	  ?>
    <div nctype="item_image" class="item"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="image" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>
