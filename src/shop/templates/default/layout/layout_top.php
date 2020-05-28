<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<script type="text/javascript">
	$(function() {
		Hangowa.initial();
		Hangowa.initialUser();
		Hangowa.updateCartCount();
	});

</script>
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<?php if ($output['hidden_nctoolbar'] != 1) {?>
<div id="ncToolbar" class="nc-appbar">
	<div class="nc-appbar-tabs" id="appBarTabs">
		<div class="ever">
			<?php if (!$output['hidden_rtoolbar_cart']) { ?>
			<div class="cart"><a href="javascript:void(0);" id="rtoolbar_cart"><span class="icon"></span> <span class="name">购物车</span><i id="rtoobar_cart_count" class="new_msg" style="display:none;"></i></a></div>
			<?php } ?>
			<?php if (!$output['hidden_rtoolbar_compare']) { ?>
			<div class="compare"><a href="javascript:void(0);" id="compare"><span class="icon"></span><span class="tit">商品对比</span></a></div>
			<?php } ?>
		</div>
		<div class="variation">
			<div class="middle">
				<div id="bar-user-info">
					<div class="user" onclick="Hangowa.barUserInfo()">
						<a href="javascript:void(0);">
							<div class="avatar"><img src="/data/upload/shop/common/default_user_portrait.gif" /></div>
							<span class="tit">我的账户</span>
						</a>
					</div>
					<div class="user-info" style="display:none;"><i class="arrow"></i>
						<div class="avatar"><img src="/data/upload/shop/common/default_user_portrait.gif" />
							<div class="frame"></div>
						</div>
						<dl>
							<dt>Hi, <span id="bar-user-name"></span></dt>
							<dd>当前等级：<strong id="bar-user-level"></strong></dd>
							<dd>当前经验值：<strong id="bar-user-exp"></strong></dd>
						</dl>
					</div>
				</div>
				<div id="bar-user-login">

					<div class="user" nctype="a-barLoginBox">
						<a href="javascript:void(0);">
							<div class="avatar"><img src="/data/upload/shop/common/default_user_portrait.gif" /></div>
							<span class="tit">会员登录</span>
						</a>
					</div>
					<div class="user-login-box" nctype="barLoginBox" style="display:none;"> <i class="arrow"></i> <a href="javascript:void(0);" class="close-a" nctype="close-barLoginBox" title="关闭">X</a>
						<form id="login_form" method="post" action="<?php echo urlLogin('login', 'login');?>" onsubmit="ajaxpost('login_form', '', '', 'onerror')">
							<?php Security::getToken();?> <input type="hidden" name="form_submit" value="ok" />
							<input name="nchash" type="hidden" value="<?php echo getNchash('login','index');?>">
							<dl>
								<dt><strong>登录名</strong></dt>
								<dd>
									<input type="text" class="text" autocomplete="off" name="user_name" autofocus>
									<label></label>
								</dd>
							</dl>
							<dl>
								<dt><strong>登录密码</strong><a href="<?php echo urlLogin('login', 'forget_password');?>" target="_blank">忘记登录密码？</a></dt>
								<dd>
									<input type="password" class="text" name="password" autocomplete="off">
									<label></label>
								</dd>
							</dl>
							<dl>
								<dt><strong>验证码</strong><a href="javascript:void(0)" class="ml5" onclick="javascript:document.getElementById('codeimage').src='index.php?act=seccode&amp;op=makecode&amp;nchash=<?php echo getNchash('login','index');?>&amp;t=' + Math.random();">更换验证码</a></dt>
								<dd>
									<input type="text" name="captcha" autocomplete="off" class="text w130" id="captcha" maxlength="4" size="10">
									<img src="" name="codeimage" border="0" id="codeimage" class="vt">
									<label></label>
								</dd>
							</dl>
							<div class="bottom">
								<input type="submit" class="submit" value="确认">
								<input type="hidden" value="" name="ref_url">
								<a href="<?php echo urlLogin('login', 'register', array('ref_url' => $_GET['ref_url']));?>" target="_blank">注册新用户</a>
								<?php if (C('qq_isuse') == 1 || C('sina_isuse') == 1 || C('weixin_isuse') == 1){?>
								<h4><?php echo $lang['nc_otherlogintip'];?></h4>
								<?php if (C('weixin_isuse') == 1){?>
								<a href="javascript:void(0);" onclick="ajax_form('weixin_form', '微信账号登录', '<?php echo urlLogin('connect_wx', 'index');?>', 360);" title="微信账号登录" class="mr20">微信</a>
								<?php } ?>
								<?php if (C('sina_isuse') == 1){?>
								<a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=tosina" title="新浪微博账号登录" class="mr20">新浪微博</a>
								<?php } ?>
								<?php if (C('qq_isuse') == 1){?><a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=toqq" title="QQ账号登录" class="mr20">QQ账号</a>
								<?php } ?>
								<?php } ?>
							</div>
						</form>
					</div>
				</div>
				<div class="prech">&nbsp;</div>
				<?php if(C('node_chat')){ ?>
				<div class="chat"><a href="javascript:void(0);" id="chat_show_user"><span class="icon"></span><i id="new_msg" class="new_msg" style="display:none;"></i><span class="tit">在线联系</span></a></div>
				<?php } ?>
			</div>
			<div class="l_qrcode"><a href="javascript:void(0);" class=""><span class="icon"></span><code><img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.C('mobile_wx');?>"></code></a></div>
			<div class="gotop"><a href="javascript:void(0);" id="gotop"><span class="icon"></span><span class="tit">返回顶部</span></a></div>
		</div>
		<div class="content-box" id="content-compare">
			<div class="top">
				<h3>商品对比</h3>
				<a href="javascript:void(0);" class="close" title="隐藏"></a>
			</div>
			<div id="comparelist"></div>
		</div>
		<div class="content-box" id="content-cart">
			<div class="top">
				<h3>我的购物车</h3>
				<a href="javascript:void(0);" class="close" title="隐藏"></a>
			</div>
			<div id="rtoolbar_cartlist"></div>
		</div>
	</div>
</div>


<script>
	//返回顶部
	$(function() {
		$("#gotop").hide();
        //当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失
        $(function() {
            $(window).scroll(function() {
                if ($(window).scrollTop() > 100) {
                    $("#gotop").fadeIn(1000);
                } else {
                    $("#gotop").fadeOut(1000);
                }
            });
            //当点击跳转链接后，回到页面顶部位置
            $("#gotop").click(function() {
                $('body,html').animate({
                    scrollTop: 0
                },
                1000);
                return false;
            });
        });

		$("#chat_show_user").click(function(){
			/*会员登录了之后没有会员值，只有强制刷新网页的时候才有会员值*/
			var arr={ uid:uid,uname:name};
			<?php if($output['is_goods'] == '1'){ ?>
			arr.itemid=<?php echo $output["goods"]["goods_id"];?>;
			arr.itemparam="www.hangowa.com";
			<?php } elseif($output['is_paycenter'] == '1') { ?>
			arr.orderid = '<?php echo $output["orderid"]; ?>';	//订单ID,
			arr.orderprice = '<?php echo $output["orderprice"]; ?>';	//订单总价,
			<?php } ?>
			NTKF.im_updatePageInfo(arr);
			NTKF.im_openInPageChat('hf_1000_1508484886546');
		})
	})

</script>


<script type="text/javascript">
	//登录开关状态
	var connect_qq = "<?php echo C('qq_isuse')?>";
	var connect_sn = "<?php echo C('sina_isuse')?>";
	var connect_wx = "<?php echo C('weixin_isuse')?>";
	$(function() {
		$(".l_qrcode a").hover(function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			});

	});


	//动画显示边条内容区域
	$(function() {
		ncToolbar();
		$(window).resize(function() {
			ncToolbar();
		});

		function ncToolbar() {
			if ($(window).width() >= 1240) {
				$('#appBarTabs >.variation').show();
			} else {
				$('#appBarTabs >.variation').hide();
			}
		}
		$('#appBarTabs').hover(
			function() {
				$('#appBarTabs >.variation').show();
			},
			function() {
				ncToolbar();
			}
		);
		$("#compare").click(function() {
			if ($("#content-compare").css('right') == '-210px') {
				loadCompare(false);
				$('#content-cart').animate({
					'right': '-210px'
				});
				$("#content-compare").animate({
					right: '35px'
				});
			} else {
				$(".close").click();
				$(".chat-list").css("display", 'none');
			}
		});
		$("#rtoolbar_cart").click(function() {
			if ($("#content-cart").css('right') == '-210px') {
				$('#content-compare').animate({
					'right': '-210px'
				});
				$("#content-cart").animate({
					right: '35px'
				});
				if (!$("#rtoolbar_cartlist").html()) {
					$("#rtoolbar_cartlist").load('index.php?act=cart&op=ajax_load&type=html');
				}
			} else {
				$(".close").click();
				$(".chat-list").css("display", 'none');
			}
		});
		$(".close").click(function() {
			$(".content-box").animate({
				right: '-210px'
			});
		});

		$(".quick-menu dl").hover(function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			});
		$(".links_a").hover(function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			});

		// 右侧bar用户信息
		$('div[nctype="a-barUserInfo"]').click(function() {
			$('div[nctype="barUserInfo"]').toggle();
		});
		// 右侧bar登录
		$('div[nctype="a-barLoginBox"]').click(function() {
			$('div[nctype="barLoginBox"]').toggle();
			document.getElementById('codeimage').src = 'index.php?act=seccode&op=makecode&nchash=<?php echo getNchash('
			login ','
			index ');?>&t=' + Math.random();
		});
		$('a[nctype="close-barLoginBox"]').click(function() {
			$('div[nctype="barLoginBox"]').toggle();
		});
	});

</script>
<?php } ?>

<!--<div class="visit-hint wrapper"></div>-->
<?php if ($output['setting_config']['shopwwi_top_banner_status']>0){ ?>
<div style=" background:<?php echo $output['setting_config']['shopwwi_top_banner_color']; ?>;">
	<div id="t-sp" style="display: none;">
		<a href="javascript:void(0);" class="close" title="关闭"></a>
		<a href="<?php echo $output['setting_config']['shopwwi_top_banner_url']; ?>" title="<?php echo $output['setting_config']['shopwwi_top_banner_name']; ?>"><img border="0" src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$output['setting_config']['shopwwi_top_banner_pic']; ?>" alt=""></a></div>
	<script type="text/javascript">
		$(function() {
			//search
			var skey = getCookie('top_s');
			if (skey) {
				$("#t-sp").hide();
			} else {
				$("#t-sp").slideDown(800);
			}
			$("#t-sp .close").click(function() {
				setCookie('top_s', 'yes', 1);
				$("#t-sp").hide();
			});

		});

	</script>
</div>
<?php } ?>

<div class="public-top-layout w">
	<div class="topbar wrapper">
		<div class="user-entry clear">
			<img class="dress" src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.img.DS.'address_icon.png'?>" alt="">
			<p>送货地址：武汉</p>
		</div>
		<div class="quick-menu">
			<div style="float:right;">
				<!-- <dl class="t-cart">
								<div class="con-cart">
									<a target="_self" href="<?php echo SHOP_SITE_URL;?>/index.php?act=cart" class="cart-btn">
										<span>购物车</span>
										<em class="cart-num" id="top-cart-count">0</em>件
									</a>
								</div>
							</dl> -->
							<dl><dt><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=show_joinin&op=index">商家入驻</a></dt></dl>
							<dl class="hover-event">
								<dt><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_order">我的订单</a></dt>
								<dd>
									<ul>
										<li><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_order&state_type=state_new">待付款订单</a></li>
										<li><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_order&state_type=state_send">待确认收货</a></li>
										<li><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_order&state_type=state_noeval">待评价交易</a></li>
									</ul>
								</dd>
							</dl>
							<!-- <dl>
								<dt><a href="/index.php?act=member&op=home">个人中心</a></dt>
							</dl> -->
							<!--<dl>
						<dt><em class="ico_store"></em><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_favorite_goods&op=fglist"><?php echo $lang['nc_favorites'];?></a><i></i></dt>
						<dd>
						<ul>
							<li><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_favorite_goods&op=fglist">商品收藏</a></li>
							<li><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=member_favorite_store&op=fslist">店铺收藏</a></li>
						</ul>
						</dd>
					</dl>-->
							<dl class="hover-event">
								<dt>客户服务</dt>
								<dd>
									<ul>
										<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 2));?>">帮助中心</a></li>
										<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 5));?>">售后服务</a></li>
										<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 6));?>">客服中心</a></li>
									</ul>
								</dd>
							</dl>
							<?php
					if(!empty($output['nav_list']) && is_array($output['nav_list'])){
						foreach($output['nav_list'] as $nav){
						if($nav['nav_location']<1){
							$output['nav_list_top'][] = $nav;
						}
						}
					}
					if(!empty($output['nav_list_top']) && is_array($output['nav_list_top'])){
						?>
								<dl>
									<dt>站点导航<i></i></dt>
									<dd>
										<ul>
											<?php foreach($output['nav_list_top'] as $nav){?>
											<li><a <?php if($nav[ 'nav_new_open']) { echo ' target="_blank"'; } echo ' href="'; switch($nav[ 'nav_type']) { case '0':echo $nav[ 'nav_url'];break; case '1':echo urlShop( 'search', 'index', array( 'cate_id'=>$nav['item_id']));break;
							case '2':echo urlMember('article', 'article', array('ac_id'=>$nav['item_id']));break;
							case '3':echo urlShop('activity', 'index', array('activity_id'=>$nav['item_id']));break;
						}
						echo '"';
						?>><?php echo $nav['nav_title'];?></a></li>
											<?php }?>
										</ul>
									</dd>
								</dl>
								<?php } ?>
								<?php if (C('mobile_wx')) { ?>
								<dl class="weixin hover-event">
									<dt>关注我们</dt>
									<dd>
										<h4>扫描二维码</h4>
										<img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.C('mobile_wx');?>"> </dd>
								</dl>
								<?php } ?>
				<!-- 				<dl><dt><a href="/v1/" target="_blank">旧版网站入口...</a></dt></dl> -->

			</div>
            
			<div id="user-info" class="new_user-info" style="display: none;">
						<?php echo $lang['nc_hello'];?> <span>
						<a href="<?php echo urlShop('member','home');?>" id="top-username"></a>
			</span>
						<?php echo $lang['nc_comma'],$lang['welcome_to_site'];?> <a href="<?php echo SHOP_SITE_URL;?>" title="<?php echo $lang['homepage'];?>" alt="<?php echo $lang['homepage'];?>"><span><?php echo $output['setting_config']['site_name']; ?></span></a> <span>[<a href="<?php echo urlLogin('login','logout');?>"><?php echo $lang['nc_logout'];?></a>] </span>
					</div>
					<div id="user-login" class="new_user-login" style="display:block !important;">
						<span style="color:#000;font-size:14px;"><?php echo $lang['nc_hello'].$lang['nc_comma'].$lang['welcome_to_site'];?></span>
						<span><a href="<?php echo SHOP_SITE_URL;?>" class="home" title="<?php echo $lang['homepage'];?>" alt="<?php echo $lang['homepage'];?>">
							<?php echo $output['setting_config']['site_name']; ?>
						</a>
						</span>
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
						<?php } ?> <span class="login-link"><a href="<?php echo urlMember('login');?>">登录</a></span> <span class="login-link"><a href="<?php echo urlLogin('login','register');?>">注册</a></span>
					</div>
		</div> 
	</div>
</div>

