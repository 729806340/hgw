<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<div class="title"><!--<i></i>-->
    <h3><a href="<?php echo urlShop('category', 'index'); ?>">全部分类</a></h3><i></i>
</div>
<div class="category">
    <ul class="menu">
        <?php
        if (!empty($output['goods_category']) && is_array($output['goods_category'])) {
            $i = 0; ?>
            <?php foreach ($output['goods_category'] as $key => $val) {
                $i++; ?>
                <li cat_id="<?php echo $val['cat_id']; ?>" class="<?php echo $i % 2 == 1 ? 'odd' : 'even'; ?>" <?php if ($i > 14){ ?>style="display:none;"<?php } ?>>
                    <div class="class">
                        <?php if ($val['logo'] != '') { ?>
                            <span class="ico"><img src="<?php echo $val['logo']; ?>" width="16" height="16"></span>
                        <?php } ?>
                        <h4><a href=" <?php echo $val['cat_link']; ?>"><?php echo $val['cat_name']; ?></a></h4>
                        <span class="arrow"></span></div>
                    <div class="sub-class" cat_menu_id="<?php echo $val['cat_id']; ?>">
                        <div class="sub-class-content">
                            <div class="recommend-class">
                                <?php if (!empty($val['recommend_cats']) && is_array($val['recommend_cats'])) { ?>
                                    <?php foreach ($val['recommend_cats'] as $k => $v) { ?>
                                        <span><a href=" <?php echo $v['cat_link']; ?>" title="<?php echo $v['cat_name']; ?>"><?php echo $v['cat_name']; ?></a></span>
                                    <?php }
                                } ?>
                            </div>
                            <?php if (!empty($val['child']) && is_array($val['child'])) { ?>
                                <?php foreach ($val['child'] as $k => $v) { ?>
                                    <dl>
                                        <dt>
                                        <h3><a href="<?php echo $v['cat_link']; ?>"><?php echo $v['cat_name']; ?></a></h3>
                                        </dt>
                                        <dd class="goods-class">
                                            <?php if (!empty($v['child']) && is_array($v['child'])) { ?>
                                                <?php foreach ($v['child'] as $k3 => $v3) { ?>
                                                    <a href="<?php echo $v3['cat_link']; ?>"><?php echo $v3['cat_name']; ?></a>
                                                <?php }
                                            } ?>
                                        </dd>
                                    </dl>
                                <?php }
                            } ?>
                        </div>
                        <div class="sub-class-right">
                            <?php if (!empty($val['ad'])) { ?>
                                <div class="brands-list">
                                    <ul>
                                        <?php foreach ($val['ad'] as $ad) { ?>
                                            <?php if($ad['is_large'] == 0){?>
                                            <li><a href="<?php echo $ad['nav_link']; ?>">
                                                    <?php if ($ad['nav_url'] != '') { ?>
                                                        <img src="<?php echo $ad['nav_url']; ?>"/>
                                                    <?php } ?>
                                                    </a>
                                            </li>
                                            <?php }?>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                            <div class="adv-promotions">
                            <?php if (!empty($val['ad'])) { ?>
                                <?php foreach ($val['ad'] as $ad) { ?>
                                <?php if($ad['is_large'] == 1){?>
                                    <a <?php echo $ad['nav_link'] == '' ? 'href="javascript:;"' : 'target="_blank" href="' . $ad['nav_link'] . '"'; ?>><img src="<?php echo $ad['nav_url']; ?>"></a>
                                <?php }?>
                                <?php }?>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </li>
            <?php }?>
        <?php } ?>
    </ul>
</div>
