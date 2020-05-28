<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
    .line4-goods-style02{ width: 1000px; margin: auto; overflow: hidden;}
    .line4-goods-style02 ul{ overflow: hidden; border: solid 1px #ddd; border-right: none; border-bottom:none;}
    .line4-goods-style02 ul li{
        float: left;
        width: 248px;
        height: 340px;
        border: solid 1px #ddd;
        border-top: none;
        border-left: none;
        overflow: hidden;
        background: #fff;
    }
    .line4-goods-style02 ul li .gd-bg .goods-pic{ width: 220px; height: 220px; overflow: hidden; margin: 14px auto;}
    .line4-goods-style02 ul li .gd-bg .goods-name{ width: 220px; height: 20px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; margin: auto; text-align: center;} 
    .line4-goods-style02 ul li .gd-bg .goods-name a{ font-size: 14px; }
    .line4-goods-style02 ul li .gd-bg .goods-price p{ text-align: center; margin: 8px 0;}
    .line4-goods-style02 ul li .gd-bg .goods-price .current-price{ color: #428961; font-size: 16px;}
    .line4-goods-style02 ul li .gd-bg .goods-price .original{ text-decoration: line-through;}
    .line4-goods-style02 ul li:hover{ background:#428961;}
    .line4-goods-style02 ul li:hover .goods-name a{ color: #fff;}
    .line4-goods-style02 ul li:hover .goods-price .current-price{ color: #fff;}
    .line4-goods-style02 ul li:hover .goods-price .original{ color: #fff;}
</style>

<div class="wrapper default-style">
    <div nctype="item_content" class="line4-goods-style02">
        <ul class="clearfix">
            <?php if(!empty($item_data['item']['goods']) && is_array($item_data['item'])) { ?>
            <?php foreach($item_data['item']['goods'] as $item_value) {
		  //v($item_value);
		  ?>
            <li nctype="item_image" class="item">
                <div class="gd-bg">
                    <div class="goods-pic"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="goods_image" shopwwi-url="<?php echo cthumb($item_value['goods_image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" title="<?php echo $item_value['goods_name'];?>" alt="<?php echo $item_value['goods_name'];?>"></a></div>
                    <div class="goods-name" nctype="goods_name">
                        <a target="_blank" href="<?php echo $item_value['url']; ?>" title="<?php echo $item_value['goods_name'];?>">
                            <?php echo $item_value['goods_name'];?>
                        </a>
                    </div>
                    <div class="goods-price" nctype="goods_price">
                        <p class="current-price">
                            促销价：<i>￥</i><?php echo $item_value['goods_promotion_price']=='0.00'?$item_value['goods_price']:$item_value['goods_promotion_price'];?>
                        </p>
                        <p class="original">
                            原价：<i>￥</i><?php echo $item_value['goods_marketprice'];?>
                        </p>
                    </div>
                    <p class="buy-btn">
                        <a target="_blank" href="<?php echo $item_value['url']; ?>" title="立即购买"></a>
                    </p>
                </div>
            </li>
            <?php } ?>
            <?php } ?>
        </ul>
    </div>
</div>