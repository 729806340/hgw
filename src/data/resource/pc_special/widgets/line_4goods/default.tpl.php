<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.default-style{ padding: 50px 0 10px 0;}
.default-style .content{ width: 1200px; overflow: hidden;}
.default-style .content ul{ width:1240px; }
.default-style .content ul li{ float:left; width:280px; height:360px; margin:0 25px 20px 0; position:relative; background:#fff; position: relative;}
.default-style .content ul li .goods-pic{ width:280px; height: 250px; text-align: center; display: table-cell; vertical-align: middle;}
.default-style .content ul li .goods-pic img{ max-width:240px; vertical-align: middle;} 
.default-style .content ul li .goods-name{ width:260px; margin: 7px auto; max-height: 30px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;}
.default-style .content ul li .goods-name a{ font-size: 16px; white-space: nowrap;}
.default-style .content ul li .goods-price{ width: 260px; margin: 10px auto 0 auto; font-size: 22px; color: #fe6601;}
.default-style .content ul li .goods-price .current-price{}
.default-style .content ul li .goods-price .current-price i{ font-size: 16px;}
.default-style .content ul li .goods-price .original{ font-size: 14px; text-decoration: line-through; color: #666;}
.default-style .content ul li .buy-btn{ position: absolute; right: 20px; bottom: 27px; width: 40px; height: 40px; background:url(data/resource/pc_special/js/buy-icon.jpg) no-repeat; }
.default-style .content ul li .buy-btn a{ display: block; width: 40px; height: 40px;}
.default-style .content ul li .storage_null{ position: absolute; width: 280px; height: 360px; background: url(data/resource/pc_special/js/storage_null.png) no-repeat; z-index: 2;}
    
    
</style>

<div class="wrapper default-style">
  <div nctype="item_content" class="content">
	  <ul class="clearfix">
		<?php if(!empty($item_data['item']['goods']) && is_array($item_data['item'])) { ?>
		<?php foreach($item_data['item']['goods'] as $item_value) {
		  //v($item_value);
		  ?>
		<li nctype="item_image" class="item">
          <div class="<?php if (1 != $item_value['goods_state'] || empty($item_value['goods_storage'])) {
            echo "storage_null";
        } 
        ?>"></div>    
                
		  <div class="goods-pic"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="goods_image" shopwwi-url="<?php echo cthumb($item_value['goods_image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" title="<?php echo $item_value['goods_name'];?>" alt="<?php echo $item_value['goods_name'];?>"></a></div>
		  <div class="goods-name" nctype="goods_name"><a target="_blank" href="<?php echo $item_value['url']; ?>" title="<?php echo $item_value['goods_name'];?>"><?php echo $item_value['goods_name'];?></a></div>
		  <div class="goods-price" nctype="goods_price">
			  <p class="current-price"><i>￥</i><?php echo $item_value['goods_promotion_price']=='0.00'?$item_value['goods_price']:$item_value['goods_promotion_price'];?></p>
			  <p class="original"><i>￥</i><?php echo $item_value['goods_marketprice'];?></p>
		  </div>
          <!--<div>库存为<?php echo $item_value['goods_storage'];?></div>-->
		  <p class="buy-btn"><a target="_blank" href="<?php echo $item_value['url']; ?>" title="立即购买"></a></p>
		</li>
		<?php } ?>
		<?php } ?>
	  </ul>
  </div>
</div>
