<style>
.tab-date .item{ float: left; width: 33.33%}
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="tab-date wrapper">
  <div nctype="item_content" class="content clearfix">
	<div class="tabItem">  
		<?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
		<?php foreach($item_data['item'] as $item_key => $item_value) {?>
		  <div nctype="item_image" class="item"><a href="<?php echo $item_value['url']; ?>"><img nctype="image" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a></div>
		<?php } ?>
		<?php } ?>
	</div>
	  
	
	
  </div>
</div>
