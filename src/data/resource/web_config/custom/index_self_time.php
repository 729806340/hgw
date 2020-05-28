<style>
    .xssg-goods-list{ padding-top: 10px;}
    .xssg-goods-list li{ float: left; width: 172px; margin-left: 18px;}
    .xssg-goods-list li .goods-img{ width: 172px; height: 172px; overflow: hidden; position: relative;}
    .xssg-goods-list li .goods-img a{ display: table-cell; width: 172px; height: 172px; vertical-align: middle; text-align: center;}
    .xssg-goods-list li .goods-img img{ max-width: 172px; max-height: 172px; vertical-align: middle;}
    .xssg-goods-list li .goods-info .goods-name{ font-size: 14px; color: #333; line-height: 20px; padding: 5px 0;}
    .xssg-goods-list li .goods-info .goods-name a{ display: block; height: 40px; overflow: hidden;}
    .xssg-goods-list li .goods-info .price{ line-height: 30px; overflow: hidden; width: 100%; font-weight: normal;}
    .xssg-goods-list li .goods-info .price .current-price{ font-size: 18px; color: #19C549; float: left !important;}
    .xssg-goods-list li .goods-info .price .original-price{ text-decoration: line-through; font-size: 14px; color: #999; margin-left: 10px;}
    
    
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<ul class="xssg-goods-list clearfix">
    <?php if (!empty($output['code_sale_list']['code_info']) && is_array($output['code_sale_list']['code_info'])) { ?>
    <?php foreach ($output['code_sale_list']['code_info'] as $key => $val) { ?>
    <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
    <?php foreach($val['goods_list'] as $k => $v){ ?>
    <li>
        <div class="goods-img">
            <a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" class="pic">
                <img shopwwi-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" mff="sqde" alt="<?php echo $v['goods_name']; ?>"/>
            </a>
        </div>
        <div class="goods-info">
            <div class="goods-name">
                <a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>">
                    <?php echo $v['goods_name']; ?>
                </a>
            </div>
            <div class="price">
                <span class="current-price fl"><?php echo ncPriceFormatForList($v['goods_price']); ?></span>
                <span class="original-price fl"><?php echo ncPriceFormatForList($v['market_price']); ?></span>
            </div>
        </div>
    </li>
    <?php } ?>
    <?php } }} ?>
</ul>
