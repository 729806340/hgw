<style>
.flexslider{position:relative;height:400px;overflow:hidden;background:url(../../js/loading.gif) 50% no-repeat;}
.flexslider .slides{position:relative;z-index:1;}
.flexslider .slides li{height:400px;}
.flexslider .flex-control-nav{position:absolute;bottom:10px;z-index:2;width:100%;text-align:center;}
.flexslider .flex-control-nav li{display:inline-block;width:14px;height:14px;margin:0 5px;*display:inline;zoom:1;}
.flexslider .flex-control-nav a{display:inline-block;width:14px;height:14px;line-height:40px;overflow:hidden; background:#ccc; cursor:pointer;}
.flexslider .flex-control-nav .flex-active{background:#f00;}

.flexslider .flex-direction-nav{position:absolute;z-index:3;width:100%;top:45%;}
.flexslider .flex-direction-nav li a{display:block;width:50px;height:40px;overflow:hidden;cursor:pointer;position:absolute;}
.flexslider .flex-direction-nav li a.flex-prev{left:50px;background:url(../../js/prev.png) center center no-repeat;}
.flexslider .flex-direction-nav li a.flex-next{right:40px;background:url(../../js/next.png) center center no-repeat;}
</style>


<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<!--<div class="index_block adv_list">
  <div nctype="item_content" class="content">
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {?>
    <div nctype="item_image" class="item"><img nctype="image" width="100%" style="max-width:1920px;" src="<?php echo getMbSpecialImageUrl($item_value['image']);?>" alt="">
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>-->




<div class="flexslider">
	<ul class="slides">
		<?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    	<?php foreach($item_data['item'] as $item_key => $item_value) {?>
		<li nctype="item_image" ><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="image" width="100%" style="max-width:1920px;" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" /></a></li>
		<?php } ?>
    	<?php } ?>
	</ul>
</div>

<script src="/data/resource/pc_special/js/jquery.flexslider-min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('.flexslider').flexslider({
		directionNav: true,
		pauseOnAction: false
	});
});
</script>

