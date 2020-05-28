<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/index.css" rel="stylesheet" type="text/css">
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo B2B_RESOURCE_SITE_URL;?>/js/home_index.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<div class="b2b-banner" style="max-width:1920px; overflow:hidden;">
    <img src="<?php  echo B2B_TEMPLATES_URL; ?>/images/banner.jpg" />
</div>

<div class="b2b-floor w1200">
    <?php
    foreach($output['category_list'] as $key_c => $val) {?>
    <div class="floor-item">
        <div class="floor-head  b2bclearfix">
            <h2 class="floor-title fl"><?php echo $key_c+1?>F&nbsp;<?php echo $val['bc_name']?></h2>
            <div class="floor-classify fr">
                <?php foreach($output['category_list'] as $val1) {?>
                <a href='<?php echo  urlB2B('search','index',array('bc_id'=>$val1['bc_id'])); ?>'><?php echo $val1['bc_name']?></a>
                <?php } ?>
            </div>
        </div>
        <div class="floor-con b2bclearfix">
            <div class="f-left-3 fl">
                <ul class="b2bclearfix">
                    <?php  foreach($val['goods_list'] as $key => $item) {
                        ?>
                        <?php if($key <= 2){?>
                    <li>
                        <div class="goods-thumb"><a target="_blank" href='<?php echo urlB2b('goods','index',array('goods_commonid'=>$item['goods_commonid']))?>'><img src="<?php  echo $item['img']; ?>" /></a></div>
                        <div class="goods-name"><a href='<?php echo urlB2b('goods','index',array('goods_commonid'=>$item['goods_commonid']))?>'><?php echo $item['goods_name']?></a></div>
                        <div class="goods-price">
                            <span class="cost-price">&yen;<?php echo $item['min_price']?></span>
                        </div>
                    </li>
                    <?php } ?>
                    <?php } ?>
                </ul>
            </div>
            <div class="f-right-4 fl">
                <ul class="b2bclearfix">
                    <?php foreach($val['goods_list'] as $key => $item) {?>
                    <?php if($key > 2){?>
                    <li>
                        <div class="goods-name"><a href='<?php echo urlB2b('goods','index',array('goods_commonid'=>$item['goods_commonid']))?>'><?php echo $item['goods_name']?></a></div>
                        <div class="goods-price">
                            <span class="cost-price">&yen;<?php echo $item['min_price']?></span>
                        </div>
                        <div class="goods-thumb"><a target="_blank" href='<?php echo urlB2b('goods','index',array('goods_commonid'=>$item['goods_commonid']))?>'><img src="<?php  echo $item['img']; ?>" /></a></div>
                    </li>
                    <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <?php } ?>
</div>