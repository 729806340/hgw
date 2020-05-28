<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.line2-goods-style2{ width: 1000px; margin: auto; overflow: hidden;}
.line2-goods-style2 ul{ width: 1015px; overflow: hidden;}
.line2-goods-style2 ul li{
    width: 450px;
    float: left;
    border: solid 1px #ddd;
    overflow: hidden;
    padding: 15px;
    margin:10px 9px 15px 9px;
    -moz-transition: all 0.3s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out;
    -o-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
    background: #fff;
}
.line2-goods-style2 ul li:hover{ -webkit-box-shadow: 0px 0px 10px #999;
-moz-box-shadow: 0px 0px 10px #999;
box-shadow: 0px 0px 10px #999;}
.line2-goods-style2 ul li .goods-item{ overflow: hidden;}
.line2-goods-style2 ul li .goods-item .item-l{ float: left; width: 240px;}
.line2-goods-style2 ul li .goods-item .item-r{ float: right; width: 200px;}
.line2-goods-style2 ul li .goods-item .item-r .goods-name{ margin: 20px 0;}
.line2-goods-style2 ul li .goods-item .item-r .goods-name a{ font-size: 16px; height: 40px; display: block; overflow: hidden;}
.line2-goods-style2 ul li .goods-item .item-r .goods-price,.line2-goods-style2 ul li .goods-item .item-r .marketprice{ text-align: center;}
.line2-goods-style2 ul li .goods-item .item-r .goods-price span{ font-size: 24px; color: #D62C3D;}
.line2-goods-style2 ul li .goods-item .item-r .marketprice{ margin-top: 10px;}
.line2-goods-style2 ul li .goods-item .item-r .marketprice span{ font-size: 15px; color: #ccc; text-decoration: line-through;}
.line2-goods-style2 ul li .goods-item .item-r .gd-buy{ margin: 20px auto 0 auto;width: 130px;height: 36px;background: #D62C3D;text-align: center;line-height: 34px;font-size: 16px;font-weight: 700;color: #FFF;display: block;}
</style>
<div class="index_block goods-list">
    <div nctype="item_content" class="line2-goods-style2">
        <ul>

            <?php if(!empty($item_data['item']['goods']) && is_array($item_data['item'])) {?>
            <?php foreach($item_data['item']['goods'] as $item_value) {
                  //v($item_value);
                ?>
            <li>
                <div nctype="item_image" class="goods-item">
                    <div class="goods-pic item-l"><a target="_blank" href="<?php echo $item_value['url']; ?>" title="<?php echo $item_value['goods_name'];?>"><img nctype="goods_image" shopwwi-url="<?php echo cthumb($item_value['goods_image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a></div>
                    <div class="item-r">
                        <div class="goods-name" nctype="goods_name">
                            <a target="_blank" href="<?php echo $item_value['url']; ?>" title="<?php echo $item_value['goods_name'];?>"><?php echo $item_value['goods_name'];?></a>
                        </div>
                        <div class="goods-price" nctype="goods_price">
                            特价：￥<span><?php echo $item_value['goods_promotion_price'];?></span>
                        </div>
                        <div class="marketprice">原价：<span><?php echo $item_value['goods_marketprice'];?></span></div>
                        <a href="<?php echo $item_value['url']; ?>" class="gd-buy">立即购买</a>
                    </div>
                </div>
            </li>
            <?php } ?>
            <?php } ?>

        </ul>
    </div>
</div>

