<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<div class="title">
    <h3><a href="javascript:void(0)">
        <img src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.img.DS.'fl_icon@2x.png'?>" alt="">
        全部目录
        <img src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.img.DS.'more_icon@2x.png'?>" alt="">
    </a></h3><i></i>
</div>
<div class="category">
    <div class="menu">
        <?php if (!empty($output['goods_category']) && is_array($output['goods_category'])) {
            $i = 0; ?>
            <?php foreach ($output['goods_category'] as $key => $val) {
                $i++; ?>
                <dl>
                   <dt>
                       <h3><a href=" <?php echo $val['cat_link']; ?>"><?php echo $val['cat_name']; ?></a></h3>
                   </dt>
                   <?php if (!empty($val['child']) && is_array($val['child'])) { ?>
                        <?php foreach ($val['child'] as $k => $v) { ?>
                            <dd class="goods-class">
                                <a href="/cate-<?php echo $v['cat_id'];?>-0-0-0-0-0-0-0-0.html"><?php echo $v['cat_name']; ?></a>
                            </dd>
                        <?php }
                    } ?>
                </dl>  
            <?php }?>
        <?php } ?>
    </div>
</div>
