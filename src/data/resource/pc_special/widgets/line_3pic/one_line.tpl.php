<style>

    .line_pic3 { width: 100%;height:800px;clear: both}
    .content{ overflow: hidden;height:100%;}
    .line_pic3 .item{ float: left; width: 40%;height:100%;}
    .line_pic3 .item:nth-child(2){  width: 20%;height:100%;}
    .line_pic3 .item a{ display: block;width: 100%;height: 100%;}
    .line_pic3 .item img{width: 100%; height: 100%;}
    .tabItem {height:100%;display:flex;flex-direction: row;}
</style>
<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="line_pic3" style="clear: both">
  <div nctype="item_content" class="content clearfix" style="width: 100%;">
	<div class="tabItem">  
		<?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
		<?php foreach($item_data['item'] as $item_key => $item_value) {?>
		  <div nctype="item_image" class="item" ><a href="<?php echo $item_value['url']; ?>"><img nctype="image" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a></div>
		<?php } ?>
		<?php } ?>
	</div>
  </div>
</div>
