<style>
    .wid1065{ text-align: center;}
    .wid1065 .tabItem{ width: 1065px;}
    .wid1065 .item{ float: left; width: 355px; text-align: center; margin-bottom: 60px;}
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="wid1065 wrapper">
  <div nctype="item_content" class="content clearfix">
	<div class="tabItem">  
		<?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
		<?php foreach($item_data['item'] as $item_key => $item_value) {?>
		  <div nctype="item_image" class="item"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="image" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a></div>
		<?php } ?>
		<?php } ?>
	</div>
	  
	
	
  </div>
</div>
