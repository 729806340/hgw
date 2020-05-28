<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script src="<?php echo SHOP_RESOURCE_SITE_URL.'/js/search_goods.js';?>"></script>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/layout.css" rel="stylesheet" type="text/css">
<style type="text/css">
body { _behavior: url(<?php echo SHOP_TEMPLATES_URL;
?>/css/csshover.htc);
}
</style>
<style>
    /**{margin:0; padding:0; font-size: 12px;}
    a{ text-decoration: none;}
    ol,ul,dl{ list-style: none;}*/
    body{ background: #fff;}
    .w1200{ width:1200px; margin:auto;}
    .total .sort-row{ height: 40px; border: solid 1px #e8e8e8; background: #f5f5f5;}
    .sortbar-array{ padding:0;}
    .sortbar-array ul{ border:none;}
    .total .sort-row ul{ overflow: hidden;}
    .total .sort-row ul li{ float: left; line-height: 40px;}
    .total .sort-row ul li a{ color: #6d6d6d; display: block; padding: 0 20px; border-left: solid 1px #f5f5f5; border-right: solid 1px #f5f5f5;}
    .total .sort-row ul li.first a{ border-left:none;}
    .total .sort-row ul li a:hover{ color: #f50; background: #fff; border-color:#e5e5e5; }

    .total .shop-list .list-container .list-item{ overflow: auto; margin-top: 20px; color:#666; padding-bottom:20px; border-bottom: solid 1px #e6e6e6; }
    .total .shop-list .list-container .list-item .shop-intro{ float: left; overflow: hidden; position: relative; height: 190px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-logo{ float: left; width: 70px; height: 70px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-logo a{ display: block;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info{ float: left; margin-left: 15px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info h4{ line-height: 24px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info h4 a{ font-size: 14px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info p{ line-height: 21px; padding-top: 3px;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info a{ color: #0063DC; }
    .total .shop-list .list-container .list-item .shop-intro .shop-info a:hover{ color: #f60; text-decoration: underline;}
    .total .shop-list .list-container .list-item .shop-intro .shop-info .main-cat{width: 230px; display: inline-block; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;}
    .total .shop-list .list-container .list-item .shop-intro .other{ position: absolute; bottom: 15px;}
    .total .shop-list .list-container .list-item .shop-intro .other em{ font-style: normal; font-weight: 700;}
    .total .shop-list .list-container .list-item .shop-intro .other .info-sale{ margin-right: 8px;}
    .total .shop-list .list-container .list-item .shop-intro .other p{ margin-top: 10px;}
	.total .shop-list .not-found{ margin:200px auto; text-align: center; height:90px; line-height:90px;}
	.total .shop-list .not-found .taogongzai{ display:inline-block; width:80px; height:90px; background:url(/shop/templates/default/images/T1ocbJXjxiXXXZPI3b-100-270.png) no-repeat 0 0; vertical-align: middle; margin-right:40px;}
	.total .shop-list .not-found p{ display:inline-block; line-height:90px; font-size:14px;}
	.total .shop-list .not-found p span{ color:#f40; font-weight:bold;}
	
    .pro-list{ float: left; width:850px; overflow: hidden; margin-left: 35px;}
    .pro-list ul{ width:860px;}
    .pro-list ul li{ float: left; margin-left:8px; width: 160px; height: 190px; border: solid 1px #e6e6e6;}
    .pro-list ul li a{ display: block;}
    .pro-list ul li a img{ max-width: 160px; max-height: 160px;}
    .pro-list ul li .price{ line-height: 30px; padding-left: 10px;}
</style>
<div class="nch-breadcrumb-layout">
    <div class="nch-breadcrumb wrapper"><i class="icon-home"></i>
        <span><a href="http://www.hangowa.com">首页</a></span><span class="arrow">&gt;</span>
        <span>搜索结果</span>
    </div>
</div>

<div class="wwi-container wrapper">


    <div class="wwi-module wwi-padding25">
        <div class="title" style="border-bottom: 1px solid #eee;">
            <h3>
                <em><?php echo $output['show_keyword']; ?></em>
                &nbsp;&nbsp;&nbsp;&nbsp;搜索到<b><?php echo count($output['store_list']); ?></b>个相关店铺</h3>
        </div>

    </div>

    <!-- 分类下的推荐商品 -->
    <div class="shop_con_list" id="main-nav-holder">
        <nav class="sort-bar" id="main-nav">
            <div class="pagination"> </div>
            <div class="sortbar-array">
                <ul>
                    <li <?php if(!$_GET['key']){?>class="selected"<?php }?>><a href="<?php echo urlShop('search','store', array('keyword'=>$output['show_keyword'],'key'=>'0', 'order'=>'0') );?>" title="默认排序">默认</a></li>
                    <li <?php if($_GET['key']==1){?>class="selected"<?php }?>><a href="<?php echo urlShop('search','store', array('keyword'=>$output['show_keyword'],'key'=>'1', 'order'=>$_GET['key']==1&&$_GET['order']==2 ? '1':'2') );?>" <?php echo $_GET['key']==1&&$_GET['order']==1?"class='asc'":"class='desc'"; ?> >销量<i></i></a></li>
                    <li <?php if($_GET['key']==2){?>class="selected"<?php }?>><a href="<?php echo urlShop('search','store', array('keyword'=>$output['show_keyword'],'key'=>'2', 'order'=>$_GET['key']==2&&$_GET['order']==2 ? '1':'2') );?>" <?php echo $_GET['key']==2&&$_GET['order']==1?"class='asc'":"class='desc'"; ?> >评分<i></i></a></li>
                </ul>
            </div>
        </nav>
        <!-- 商品列表循环  -->
        <div>
            <style type="text/css">
                #box { background: #FFF; width: 238px; height: 410px; margin: -390px 0 0 0; display: block; border: solid 4px #D93600; position: absolute; z-index: 999; opacity: .5 }
                .shopMenu { position: fixed; z-index: 1; right: 25%; top: 0; }
            </style>
            <div class="squares" nc_type="current_display_mode">

                <div class="total w1200">
                    <div class="shop-list">
                        <?php if(is_array($output['store_list']) && !empty($output['store_list'])){ ?>
                        <ul class="list-container">
                            <?php foreach ($output['store_list'] as $store) { ?>
                            <li class="list-item">
                                <div class="shop-intro">
                                    <div class="shop-logo"><a href="<?php echo urlShop('show_store','index', array('store_id'=>$store['store_id']) );?>"><img src="<?php echo $store['store_avatar']; ?>" width="60" height="60" /></a></div>
                                    <div class="shop-info">
                                        <h4><a href="<?php echo urlShop('show_store','index', array('store_id'=>$store['store_id']) );?>"><?php echo $store['store_name'];?></a> </h4>
                                        <p>卖家：<?php echo $store['member_name'];?></p>
                                        <p class="main-cat"> </a></p>
                                    </div>
                                    <div class="other">
                                        <p><span class="info-sale">销量<em><?php echo $store['store_sales'];?></em></span> <span class="info-sum">共<em><?php echo intval($store['goods_num']);?></em>件商品</span></p>
                                        <p><span>好评率：<?php echo $store['store_credit'];?>%</span></p>
                                    </div>
                                </div>
                                <div class="pro-list">
                                    <?php if(is_array($store['goods_list']) && !empty($store['goods_list'])) {?>
                                    <ul>
                                        <?php foreach ($store['goods_list'] as $goods) {?>
                                        <li>
                                            <a href="<?php echo urlShop('goods','index', array('goods_id'=>$goods['goods_id']) );?>"><img src="<?php echo cthumb($goods['goods_image'], 160, $goods['store_id']); ?>" width="160" height="160" /></a>
                                            <p class="price">&yen;<?php echo $goods['goods_promotion_price'] == '0.00' ? $goods['goods_price'] : $goods['goods_promotion_price']; ?></p>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                    <?php } ?>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } else { ?>
						
							<div class="not-found"><div class="taogongzai"></div><p>没有找到与<span>“<?php echo $output['show_keyword']; ?>”</span>相关的店铺</p></div>
						
						<?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="clear"></div>

    <!-- S 推荐展位 -->
    <div nctype="booth_goods" class="s3-module" style="display:none;"> </div>

    <!-- 猜你喜欢 --><div id="guesslike_div" style="width:1200px;"></div>

    <?php if(!empty($output['viewed_goods']) && is_array($output['viewed_goods'])){?><div class="s3-module"><div class="title"><h3><b><?php echo $lang['goods_class_viewed_goods']; ?></b>你最近一段时间浏览的商品</h3> </div><div class="content"><div class="s3-sidebar-viewed ps-container" id="wwiSidebarViewed"> <ul><?php foreach ($output['viewed_goods'] as $k=>$v){?><li class="wwi-sidebar-bowers"><div class="goods-pic"><a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" target="_blank"><img src="<?php echo thumb($v, 240); ?>" title="<?php echo $v['goods_name']; ?>" alt="<?php echo $v['goods_name']; ?>" ></a></div><div class="goods-name"><a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" target="_blank"><?php echo $v['goods_name']; ?></a></div><div class="goods-price" title="商品价格<?php echo $lang['nc_colon'].$lang['currency'].ncPriceFormat($value['goods_promotion_price']);?>"><?php echo $lang['currency'];?><?php echo ncPriceFormat($v['goods_promotion_price']); ?></div> </li> <?php } ?></ul></div> <a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_goodsbrowse&op=list" class="wwi-sidebar-all-viewed">全部浏览历史</a></div></div><?php } ?></div></div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/search_category_menu.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fly/jquery.fly.min.js" charset="utf-8"></script>
<!--[if lt IE 10]>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fly/requestAnimationFrame.js" charset="utf-8"></script>
<![endif]-->
<script type="text/javascript">
    var defaultSmallGoodsImage = '<?php echo defaultGoodsImage(240);?>';
    var defaultTinyGoodsImage = '<?php echo defaultGoodsImage(60);?>';

    $(function(){
        $('#files').tree({
            expanded: 'li:lt(2)'
        });
        //品牌索引过长滚条
        $('#ncBrandlist').perfectScrollbar({suppressScrollX:true});
        //浮动导航  waypoints.js
        $('#main-nav-holder').waypoint(function(event, direction) {
            $(this).parent().toggleClass('sticky', direction === "down");
            event.stopPropagation();
        });
        // 单行显示更多
        $('span[nc_type="show"]').click(function(){
            s = $(this).parents('dd').prev().find('li[nc_type="none"]');
            if(s.css('display') == 'none'){
                s.show();
                $(this).html('<i class="icon-angle-up"></i><?php echo $lang['goods_class_index_retract'];?>');
            }else{
                s.hide();
                $(this).html('<i class="icon-angle-down"></i><?php echo $lang['goods_class_index_more'];?>');
            }
        });

        <?php if(isset($_GET['area_id']) && intval($_GET['area_id']) > 0){?>
        // 选择地区后的地区显示
        $('[nc_type="area_name"]').html('<?php echo $output['province_array'][intval($_GET['area_id'])]; ?>');
        <?php }?>

        <?php if(isset($_GET['cate_id']) && intval($_GET['cate_id']) > 0){?>
        // 推荐商品异步显示
        $('div[nctype="booth_goods"]').load('<?php echo urlShop('search', 'get_booth_goods', array('cate_id' => $_GET['cate_id']))?>', function(){
            $(this).show();
        });
        <?php }?>
        //浏览历史处滚条
        $('#wwiSidebarViewed').perfectScrollbar({suppressScrollY:true});

        //猜你喜欢
        $('#guesslike_div').load('<?php echo urlShop('search', 'get_guesslike', array()); ?>', function(){
            $(this).show();
        });

        //商品分类推荐
        $('#gc_goods_recommend_div').load('<?php echo urlShop('search', 'get_gc_goods_recommend', array('cate_id'=>$output['default_classid'])); ?>');
    });
</script>