<style>
.line_pic3 .item{ float: left; width: 33.33%}
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="line_pic3 wrapper tab_pic3">
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


<!--<script>
$(function(){
	
	
	$(".content .tabItem").each(function(index,element){
		//console.log(index);
		//console.log(element);
		var cname = 'item' + index;
		$(element).addClass(cname);
	});
})
</script>

-->