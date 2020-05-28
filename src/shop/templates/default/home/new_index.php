<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/index.css?20180908" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo SHOP_TEMPLATES_URL;?>/css/dnSlide.css"/>
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/home_index.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<style type="text/css">
    body{ overflow-y: auto; overflow-x: hidden; background-image: url("/shop/templates/default/images/bg_index1.png");}
    /*.category {
        display: block !important;
    }*/
    .jinrizhutui .home-l-ad{ width: 220px; height: 277px;}
</style>
<div class="clear"></div> 
<!-- HomeFocusLayout Begin-->
<div style="position:relative; width:1200px; margin:auto;">
    <div style="position:absolute;left:-360px"><img width="360"src="/shop/templates/default/images/ch_011.png"></div>
    <div style="position:absolute;right:-360px"><img width="360" src="/shop/templates/default/images/ch_021.png"></div>
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

            <!--限时活动 start-->
            <?php echo $output['custom_web_html']['custom_index_limit_time']; ?>
            <!--限时活动 end-->

            <!--新品推荐 start-->
            <?php echo $output['self_web_html']['self_index_self_recommend'];?>
            <!--新品推荐 end-->

            <!--农谷鲜 start-->
            <?php echo $output['custom_web_html']['custom_index_nongguxian']; ?>
            <!--农谷鲜 end-->

            <!--汉美特 start-->
            <?php echo $output['custom_web_html']['custom_index_hanmeite']; ?>
            <!--汉美特 end-->

            <!--楼层分类 start-->
            <div class="home-floor">
                <!--StandardLayout Begin-->
                <?php echo $output['web_html']['index'];?>
                <!--StandardLayout End-->
            </div>
            <!--楼层分类 end-->

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
<script src="<?php echo RESOURCE_SITE_URL;?>/js/PicCarousel.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/super_slider.js"></script>
<script>
    $(function(){
        $(".picScroll-left").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"left",autoPlay:true,vis:4,trigger:"click"});

        /*$(".dnSlide-main").each(function(index, el) {
            var setting = {
                "response" : true ,
                "autoPlay" : true ,
                afterClickBtnFn :function(i){
//	                	console.log(i);
                }
            };
            $(el).dnSlide(setting);
        });*/

        $("#hanmeite_scroll_list").superSlider({
            prevBtn: 	 ".hanmeite_prev",//左按钮
            nextBtn: 	 ".hanmeite_next",//右按钮
            listCont:    "#scroll_list",//滚动列表外层
            scrollWhere: "next",//自动滚动方向next
            delayTime: 	 2000,//自动轮播时间间隔
            speed: 		 300,//滚动速度
            amount: 	 1,//单次滚动数量
            showNum: 	 3,//显示数量
            autoPlay: 	 true//自动播放
        });

        $(".B_Demo").PicCarousel({
            "width":609,		//幻灯片的宽度
            "height":355,		//幻灯片的高度
            "posterWidth":232,	//幻灯片第一帧的宽度
            "posterHeight":280, //幻灯片第一张的高度
            "scale":0.9,		//记录显示比例关系
            "speed":300,		//记录幻灯片滚动速度
            "autoPlay":true,	//是否开启自动播放
            "delay":2500,		//自动播放间隔
            "verticalAlign":"center"	//图片对齐位置
        });
    })
    
</script>