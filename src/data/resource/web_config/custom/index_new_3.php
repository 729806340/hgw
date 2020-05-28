<div class="top_3_sale new_right f1">
    <p class="sale_top">新品推荐</p>
    <ul>
        <?php if (!empty($output['code_sale_list']['code_info']) && is_array($output['code_sale_list']['code_info'])) { ?>
        <?php foreach ($output['code_sale_list']['code_info'] as $key => $val) { ?>
        <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
        <?php foreach($val['goods_list'] as $k => $v){ ?>
        <li class="clearfix">
            <a class="shop_img" href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>">
                <img src="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>" alt="<?php echo $v['goods_name']; ?>">
            </a>
            <div class="sale_content">
                <a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>"><?php echo $v['goods_name']; ?></a>
                <p class="clearfix">
                    <span class="con_l">￥<?php echo ncPriceFormat($v['goods_price']); ?></span>
                    <span class="con_r">￥<?php echo ncPriceFormat($v['market_price']); ?></span>
                </p>
            </div>
        </li>
        <?php } ?>
        <?php } }} ?>
    </ul>
</div>