<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/index.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/home_index.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<style type="text/css">
    body{ overflow-y: auto; overflow-x: hidden; background-image: url("/shop/templates/default/images/bg_index.png");}
    /* .category {
        display: block !important;
    } */
    .jinrizhutui .home-l-ad{ width: 220px; height: 277px;}
</style>
<div class="clear"></div>
<!-- HomeFocusLayout Begin-->
<div style="position:relative; width:1200px; margin:auto;">
    <div style="position:absolute;left:-360px"><img  width="360"src="/shop/templates/default/images/ch_01.png"></div>
    <div style="position:absolute;right:-360px"><img width="360" src="/shop/templates/default/images/ch_02.png"></div>
    <div class="home-focus-layout">
        <div class="focus-con"><?php echo $output['web_html']['index_pic'];?></div>
        <div class="suspend-box">
            <div class="right-con">
                <div class="user-info">
                    <div class="avatar">
                        <a href="<?php echo urlMember('member_information', 'avatar');?>" title="修改头像">
                            <img src="<?php echo getMemberAvatar($output['member_info']['member_avatar']);?>">
                            <div class="frame"></div>
                        </a>
                    </div>
                    <div class="login-info">
                        <div class="user-entry">
                            <div id="n-user-info" style="display: none;">
                                <div style="padding:10px 0; font-size:14px; color:#666;">
                                    hi!，
                                    <span>
                                      <a href="<?php echo urlShop('member','home');?>" id="n-top-username"></a>
                                    </span>
                                </div>
                                <?php if ($output['member_info']['level_name']){ ?>
                                    <div class="nc-grade-mini" style="cursor:pointer;" onclick="javascript:go('<?php echo urlShop('pointgrade','index');?>');"><?php echo $output['member_info']['level_name'];?>会员</div>
                                <?php } ?>
                                <span style="margin-left:20px;">[<a style="color:#999" href="<?php echo urlLogin('login','logout');?>"><?php echo $lang['nc_logout'];?></a>] </span>
                            </div>
                            <div id="n-user-login" style="display:block !important;">
                                <div style="padding:10px 0; font-size:14px; color:#666;">
                                    <?php echo 'hi!'.$lang['nc_comma'].$lang['welcome_to_site'];?>
                                    <a href="<?php echo SHOP_SITE_URL;?>" title="<?php echo $lang['homepage'];?>" alt="<?php echo $lang['homepage'];?>">
                                        <?php echo $output['setting_config']['site_name']; ?>
                                    </a>
                                    <?php if (C('qq_isuse') == 1 || C('sina_isuse')  == 1 || C('weixin_isuse') == 1){?>

                                    <span class="other">
                                      <?php if (C('qq_isuse') == 1){?>
                                          <a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=toqq" title="QQ账号登录" class="qq"><i></i></a>
                                      <?php } ?>
                                        <?php if (C('sina_isuse') == 1){?>
                                            <a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=tosina" title="<?php echo $lang['nc_otherlogintip_sina']; ?>" class="sina"><i></i></a>
                                        <?php } ?>
                                        <?php if (C('weixin_isuse') == 1){?>
                                        <a href="javascript:void(0);" onclick="ajax_form('weixin_form', '微信账号登录', '<?php echo urlLogin('connect_wx', 'index');?>', 360);" title="微信账号登录" class="wx"><i></i></a><?php } ?>
                                    </span>
                                </div>
                                <?php } ?>
                                <div style="text-align:center; font-size:12px; color:#999;">
                                    <span class="login-link"><a href="<?php echo urlMember('login');?>">登录</a></span>/<span class="login-link"><a href="<?php echo urlLogin('login','register');?>">注册</a></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="hgk">
                    <div class="Palace_list">
                        <ul>
                            <li>
                                <img src="/shop/templates/default/images/shop/01_login_icon.png"/>
                                <a href="/?act=special&op=show&special_id=126">登录注册</a>
                            </li>
                            <li>
                                <img src="/shop/templates/default/images/shop/02_mine_icon.png"/>
                                <a href="/?act=special&op=show&special_id=126">个人中心</a>
                            </li>
                            <li>
                                <img src="/shop/templates/default/images/shop/03_wallet_icon.png"/>
                                <a href="/?act=special&op=show&special_id=126">账户余额</a>
                            </li>
                            <li>
                                <img src="/shop/templates/default/images/shop/04_card_icon.png"/>
                                <a href="/?act=special&op=show&special_id=126">充值系统</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--HomeFocusLayout End-->

    <!--横幅广告-->
    <!--    <div class="home-banner-ad wrapper mt20"><?php// echo loadadv(56);?></div>-->

    <!--汉购网切换栏组合 stat-->
    <div class="outermost">
        <div class="wrapper">
            <div class="mod-item01 clearfix">
                <!--排行榜-->
                <!--<div class="ranking fl">
                    <?php /*echo $output['custom_web_html']['custom_index_paihangbang'];*/?>
                </div>-->
                <!--秒杀专区-->
                <!--<div class="seckill fr">
                    <?php /*echo $output['custom_web_html']['custom_index_miaosha'];*/?>
                </div>-->
            </div>

            <!--<div class="mod-item02 clearfix mt20">
                <div class="nongguxian fl">
                    <div class="title"><h3><img src="shop/templates/default/images/ngx_icon.png"></h3></div>
                    <div class="layout-con clearfix">
                        <div class="left fl">
                            <div class="l-top"><?php /*echo loadadv(57);*/?></div>
                            <div class="l-bottom"><?php /*echo loadadv(58);*/?></div>
                        </div>
                        <div class="right fr">
                            <div class="r-top">
                                <div class="r-tit"><h4>热销爆款</h4><h5>中国农谷，湖北荆门</h5></div>
                                <div class="r-con">
                                    <div><?php /*echo loadadv(59);*/?></div>
                                    <div><?php /*echo loadadv(60);*/?></div>
                                </div>
                            </div>
                            <div class="r-bottom">
                                <div class="r-tit"><h4>粮油专区</h4><h5>中国农谷，湖北荆门</h5></div>
                                <div class="r-con">
                                    <div><?php /*echo loadadv(61);*/?></div>
                                    <div><?php /*echo loadadv(62);*/?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hanmeite fr">
                    <div class="title"><h3><img src="shop/templates/default/images/hmt_icon.png"></h3></div>
                    <div class="layout-con clearfix">
                        <div class="left fl">
                            <div class="l-top"><?php /*echo loadadv(63);*/?></div>
                            <div class="l-bottom"><?php /*echo loadadv(64);*/?></div>
                        </div>
                        <div class="right fr">
                            <div class="r-top">
                                <div class="r-tit"><h4>原创精品</h4><h5>茶香悠扬，包装精美</h5></div>
                                <div class="r-con">
                                    <div><?php /*echo loadadv(65);*/?></div>
                                    <div><?php /*echo loadadv(66);*/?></div>
                                </div>
                            </div>
                            <div class="r-bottom">
                                <div class="r-tit"><h4>热销推荐</h4><h5>有情有味，用心送礼</h5></div>
                                <div class="r-con">
                                    <div><?php /*echo loadadv(67);*/?></div>
                                    <div><?php /*echo loadadv(68);*/?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>-->
            <!--
            <div class="left-newYearPic"></div>
            <div class="right-newYearPic"></div>
            -->
            <!--<div class="new-add-mod clearfix">
                <div><?php /*echo loadadv(47);*/?></div>
                <div><?php /*echo loadadv(49);*/?></div>
            </div>-->
            <!--汉购农猫 stat-->
            <!--
            <div class="jinrizhutui">
                <div class="nm-item clearfix">
                    <div class="home-l-ad fl"><?php echo loadadv(53);?></div>
                    <?php // echo $output['custom_web_html']['custom_index_mart_price'];?>
                </div>
                <div class="nm-item clearfix">
                    <div class="home-l-ad fl"><?php echo loadadv(54);?></div>
                    <?php // echo $output['custom_web_html']['custom_index_mart_speed'];?>
                </div>
                <div class="nm-item clearfix">
                    <div class="home-l-ad fl"><?php echo loadadv(55);?></div>
                    <?php // echo $output['custom_web_html']['custom_index_mart_love'];?>
                </div>
            </div>
    -->
            <!--汉购农猫 end-->
            <div class="zhenpingzhenshihui">
                <!--<div class="mod-title">
                    <h2>真品真实惠</h2>
                </div>-->
                <div class="clearfix">
                    <div class="w390">
                        <div class="z-item01">
                            <?php echo loadadv(39);?>
                        </div>
                        <div class="z-item02">
                            <?php echo loadadv(40);?>
                        </div>
                    </div>
                    <div class="w480">
                        <div class="z-item03">
                            <?php echo loadadv(41);?>
                        </div>
                        <div class="z-item04">
                            <?php echo loadadv(42);?>
                        </div>
                        <div class="z-item05">
                            <?php echo loadadv(43);?>
                        </div>
                        <div class="z-item06">
                            <?php echo loadadv(44);?>
                        </div>
                    </div>
                    <div class="w320">
                        <div class="z-item07">
                            <?php echo loadadv(45);?>
                        </div>
                        <div class="z-item08">
                            <?php echo loadadv(46);?>
                        </div>
                    </div>
                </div>
            </div>
            <!--整点抢先end-->
            <!--今日主推 stat-->
            <!--
            <div class="jinrizhutui mt30">
                <?php //echo $output['custom_web_html']['custom_index_zhutui'];?>
            </div>
    -->
            <!--今日主推end-->

            <!--地标推荐 stat-->
            <!--
            <div class="dibiaotuijian mt30">
                <?php //echo $output['custom_web_html']['custom_index_dibiao'];?>
            </div>
    -->
            <!--地标推荐end-->

            <!--导购-->
            <?php/* echo $output['custom_web_html']['custom_index_daogou']; */?>
            <!--导购end-->
            <div class="hangoziying mt30">
                <div class="title"><h3>汉购自营</h3></div>
                <?php echo $output['self_web_html']['self_index_self_goods'];?>
            </div>
            <div class="wrapper clearfix mt30">
                <div class="xinpinshangshi fl">
                    <div class="title"><h3>新品上市</h3></div>
                    <?php echo $output['self_web_html']['self_index_self_new'];?>
                </div>
                <div class="xiaoshishangou fr">
                    <div class="title"><h3>小时闪购</h3></div>
                    <?php echo $output['self_web_html']['self_index_self_time'];?>
                </div>
            </div>
            <div class="home-floor">
                <!--StandardLayout Begin-->
                <?php echo $output['web_html']['index'];?>
                <!--StandardLayout End-->
                <!--<div class="mt50">
            <?php echo loadadv(9,'html');?>
        </div>-->
            </div>

            <!--<div class="wwi-main-footr">
        <div class="wrapper">
            <div class="sale_lum clearfix">
                <div class="m" id="sale_cx">
                    <div class="mt">
                        <div class="title-line"></div>
                        <h2><span>特卖TeMai</span></h2></div>
                    <div class="sale_cx">
                        <?php if(!empty($output['group_list']) && is_array($output['group_list'])) { ?>
                        <div class="groupbuy">
                            <ul>
                                <?php foreach($output['group_list'] as $val) { ?>
                                <li>
                                    <dl style=" background-image:url(<?php echo gthumb($val['groupbuy_image1'], 'small');?>)"><dt><?php echo $val['groupbuy_name']; ?></dt>
                                        <dd class="price"><span class="groupbuy-price"><?php echo ncPriceFormatForList($val['groupbuy_price']); ?></span><span class="buy-button"><a href="<?php echo urlShop('show_groupbuy','groupbuy_detail',array('group_id'=> $val['groupbuy_id']));?>">立即抢</a></span></dd>
                                        <dd class="time"><span class="sell">已售<em><?php echo $val['buy_quantity']+$val['virtual_quantity'];?></em></span> <span class="time-remain" count_down="<?php echo $val['end_time']-TIMESTAMP; ?>"> <em time_id="d">0</em><?php echo $lang['text_tian'];?><em time_id="h">0</em><?php echo $lang['text_hour'];?> <em time_id="m">0</em><?php echo $lang['text_minute'];?><em time_id="s">0</em><?php echo $lang['text_second'];?> </span></dd>
                                    </dl>
                                </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="m" id="sale_xs">
                    <div class="mt">
                        <div class="title-line"></div>
                        <h2><span>疯抢FengQiang</span></h2></div>
                    <div class="sale_xs">
                        <div class="home-sale-layout">
                            <div class="left-sidebar">
                                <?php if(!empty($output['special_list']) && is_array($output['special_list'])) {?>
                                <?php foreach($output['special_list'] as $value) {?><a href="<?php echo $value['special_link'];?>" title="<?php echo $value['special_title'];?>" target="_blank"><img width="275" title="<?php echo $value['special_title'];?>" height="135" shopwwi-url="<?php echo getCMSSpecialImageUrl($value['special_image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" class="" alt="<?php echo $value['special_title'];?>"></a>
                                <?php }} ?>
                            </div>
                            <?php if(!empty($output['xianshi_item']) && is_array($output['xianshi_item'])) { ?>
                            <div class="right-sidebar">
                                <div id="saleDiscount" class="sale-discount">
                                    <ul>
                                        <?php foreach($output['xianshi_item'] as $val) { ?>
                                        <li>
                                            <dl><dt class="goods-name"><?php echo $val['goods_name']; ?></dt>
                                                <dd class="goods-thumb"><a href="<?php echo urlShop('goods','index',array('goods_id'=> $val['goods_id']));?>"> <img shopwwi-url="<?php echo thumb($val, 240);?>"   rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"></a></dd>
                                                <dd class="goods-price">
                                                    <?php echo ncPriceFormatForList($val['xianshi_price']); ?> <span class="original"><?php echo ncPriceFormatForList($val['goods_price']);?></span></dd>
                                                <dd class="goods-price-discount"><em><?php echo $val['xianshi_discount']; ?></em></dd>
                                                <dd class="time-remain" count_down="<?php echo $val['end_time']-TIMESTAMP;?>"><i></i><em time_id="d">0</em>
                                                    <?php echo $lang['text_tian'];?><em time_id="h">0</em>
                                                    <?php echo $lang['text_hour'];?> <em time_id="m">0</em>
                                                    <?php echo $lang['text_minute'];?><em time_id="s">0</em>
                                                    <?php echo $lang['text_second'];?> </dd>
                                                <dd class="goods-buy-btn"></dd>
                                            </dl>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="m" id="share">
                    <div class="mt">
                        <div class="title-line"></div>
                        <h2><span>晒单ShaiDan</span></h2></div>
                    <div class="share" id="sl">
                        <ul class="show_share">
                            <?php if(!empty($output['goods_evaluate_info']) && is_array($output['goods_evaluate_info'])){?>
                            <?php foreach($output['goods_evaluate_info'] as $k=>$v){?>
                            <li>
                                <div class="p-img"><a href="<?php echo urlShop('goods','comments_list',array('goods_id'=> $v['geval_goodsid']));?>" target="_blank"><img src="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".ATTACH_GOODS."/".$v['geval_storeid']."/".$v['geval_goodsimage'];?>" alt="<?php echo $v['geval_goodsname'];?>" width="100" height="100"></a></div>
                                <div class="p-info">
                                    <div class="author-info"><img title="<?php echo str_cut($v['geval_frommembername'],2).'***';?>" shopwwi-url="<?php echo getMemberAvatarForID($v['geval_frommemberid']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo str_cut($v['geval_frommembername'],2).'***';?>" width="28" height="28"><span><?php echo str_cut($v['geval_frommembername'],2).'***';?></span></div>
                                    <div class="p-detail">
                                        <a target="_blank" title="<?php echo $v['geval_content'];?>" href="<?php echo urlShop('goods','comments_list',array('goods_id'=> $v['geval_goodsid']));?>">
                                            <?php echo $v['geval_content'];?><span class="icon-r">”</span></a><span class="icon-l">“</span></div>
                                </div>
                            </li>
                            <?php }}?>
                        </ul>
                        <script type="text/javascript">
                            $(document).ready(function() {
                                function statusRunner() {
                                    setTimeout(function() {
                                        var sl = $('#sl li'),
                                            f = $('#sl li:last');
                                        f.hide().insertBefore(sl.eq(0)).css('opacity', '0.1');
                                        f.slideDown(500, function() {
                                            f.animate({
                                                opacity: 1
                                            });
                                        });
                                        statusRunner();
                                    }, 7000);
                                }
                                statusRunner();
                            });
                            $(".home-standard-layout .left-sidebar .title a ").hover(function() {
                                $(".home-standard-layout .tabs-nav").addClass("wwi-hover");
                            });
                            $(".home-standard-layout .tabs-nav .close").click(function() {
                                $(".home-standard-layout .tabs-nav").removeClass("wwi-hover");
                            });

                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
            <!--<div id="nav_box" style="display: none;">
        <ul>
            <div class="m-logo"></div>
            <?php if (is_array($output['lc_list']) && !empty($output['lc_list'])) {$i=0 ?>
            <?php foreach($output['lc_list'] as $v) { $i++?>
            <li class="nav_Sd_<?php echo $i;?> <?php if($i==1) echo 'hover'?>"> <a class="word" href="javascript:;"><em class="em"><?php echo $v['value']?></em><?php echo $v['name']?></a></li>
            <?php }} ?>
        </ul>
    </div>-->
        </div>
    </div>
</div>
<script>
    $(function(){

        //排行榜添加不同class名
        $(".rank-con ul li").each(function(index,element){
            var add_name="num"+index;
            $(element).addClass(add_name);
        })


        //分类图标切换
        $('.category ul li').hover(function(){
            $(this).find('.ico img').animate({'marginLeft': '-25px'},0);
        },function(){
            $(this).find('.ico img').animate({'marginLeft': ''},0);
        })
    })
</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.SuperSlide.2.1.1.js"></script>
<script>
    $(function(){
        $(".picScroll-left").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"left",autoPlay:true,vis:4,trigger:"click"});
    })

</script>

<script>
    //秒杀倒计时
    //    $(function(){
    //        var starttime = new Date();
    //        var nowTime = new Date().getHours("12");
    //
    //        var mytime=starttime.toLocaleTimeString();
    //
    //        setInterval(function () {
    //            var time = starttime - nowTime;
    //
    //            var day = parseInt(time / 1000 / 60 / 60 / 24);
    //            var hour = parseInt(time / 1000 / 60 / 60 % 24);
    //            console.log('888',hour);
    //            var minute = parseInt(time / 1000 / 60 % 60);
    //            var seconds = parseInt(time / 1000 % 60);
    //
    //            //$('.countdown').html( hour + "小时" + minute + "分钟" + seconds + "秒");
    //
    //
    //        }, 1000);
    //
    //    })
</script>