<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<!doctype html>
<html lang="zh">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET;?>">
<meta name="360-site-verification" content="ec7175246571f9d93b70f7f441504732" />
<title><?php echo $output['html_title'];?></title>
<meta name="keywords" content="<?php echo $output['seo_keywords']; ?>" />
<meta name="description" content="<?php echo $output['seo_description']; ?>" />
<meta name="renderer" content="webkit">
<meta name="renderer" content="ie-stand">
<?php echo html_entity_decode($output['setting_config']['qq_appcode'],ENT_QUOTES); ?><?php echo html_entity_decode($output['setting_config']['sina_appcode'],ENT_QUOTES); ?><?php echo html_entity_decode($output['setting_config']['share_qqzone_appcode'],ENT_QUOTES); ?><?php echo html_entity_decode($output['setting_config']['share_sinaweibo_appcode'],ENT_QUOTES); ?>
<style type="text/css">
body { _behavior: url(<?php echo SHOP_TEMPLATES_URL;
?>/css/csshover.htc);
}
</style>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/base.css?3" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/home_header.css?20180914" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo SHOP_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/html5shiv.js"></script>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/respond.min.js"></script>
<![endif]-->
<script>
var COOKIE_PRE = '<?php echo COOKIE_PRE;?>';var _CHARSET = '<?php echo strtolower(CHARSET);?>';var LOGIN_SITE_URL = '<?php echo LOGIN_SITE_URL;?>';var MEMBER_SITE_URL = '<?php echo MEMBER_SITE_URL;?>';var SITEURL = '<?php echo SHOP_SITE_URL;?>';var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';var SHOP_TEMPLATES_URL = '<?php echo SHOP_TEMPLATES_URL;?>';
</script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common.js" charset="utf-8"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.validation.min.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript">
var PRICE_FORMAT = '<?php echo $lang['currency'];?>%s';
$(function(){
	//首页左侧分类菜单
	// $(".category").find("li").each(
	// 	function() {
	// 		$(this).hover(
	// 			function() {
	// 			    var cat_id = $(this).attr("cat_id");
	// 				var menu = $(this).find("div[cat_menu_id='"+cat_id+"']");
					
	// 				//$(this).find(".class").animate({textIndent:"5px"})
	// 				menu.show();
	// 				$(this).addClass("hover");					
	// 				var menu_height = menu.height();
	// 				if (menu_height < 60) menu.height(80);
	// 				menu_height = menu.height();
	// 				var li_top = $(this).position().top;
	// 				$(menu).css("top",-li_top + 50);
	// 			},
	// 			function() {
	// 				//$(this).find(".class").animate({textIndent:""})
	// 				$(this).removeClass("hover");
	// 			    var cat_id = $(this).attr("cat_id");
	// 				$(this).find("div[cat_menu_id='"+cat_id+"']").hide();
	// 			}
	// 		);
	// 	}
	// );
	$(".mod_minicart").hover(function() {
		$("#nofollow,#minicart_list").addClass("on");
	},
	function() {
		$("#nofollow,#minicart_list").removeClass("on");
	});
	$('.mod_minicart').mouseover(function(){// 运行加载购物车
		load_cart_information();
		$(this).unbind('mouseover');
	});
    <?php if (C('fullindexer.open')) { ?>
	// input ajax tips
	$('#keyword').focus(function(){
		if ($(this).val() == $(this).attr('title')) {
			$(this).val('').removeClass('tips');
		}
	}).blur(function(){
		if ($(this).val() == '' || $(this).val() == $(this).attr('title')) {
			$(this).addClass('tips').val($(this).attr('title'));
		}
	}).blur().autocomplete({
        source: function (request, response) {
            $.getJSON('<?php echo SHOP_SITE_URL;?>/index.php?act=search&op=auto_complete', request, function (data, status, xhr) {
                $('#top_search_box > ul').unwrap();
                response(data);
                if (status == 'success') {
                 $('body > ul:last').wrap("<div id='top_search_box'></div>").css({'zIndex':'1000','width':'362px'});
                }
            });
       },
		select: function(ev,ui) {
			$('#keyword').val(ui.item.label);
			$('#top_search_form').submit();
		}
	});
	<?php } ?>

	$('#button').click(function(){
      if ($('#keyword').val() == '') {
        if ($('#keyword').attr('data-value') == '') {
          return false
      } else {
        window.location.href="<?php echo SHOP_SITE_URL?>/index.php?act=search&op=index&keyword="+$('#keyword').attr('data-value');
          return false;
      }
      }
  });
  $(".head-search-bar").hover(null,
  function() {
    $('#search-tip').hide();
  });
  // input ajax tips
  $('#keyword').focus(function(){$('#search-tip').show()}).autocomplete({
    //minLength:0,
        source: function (request, response) {
            $.getJSON('<?php echo SHOP_SITE_URL;?>/index.php?act=search&op=auto_complete', request, function (data, status, xhr) {
                $('#top_search_box > ul').unwrap();
                response(data);
                if (status == 'success') {
                    $('#search-tip').hide();
                    $(".head-search-bar").unbind('mouseover');
                    $('body > ul:last').wrap("<div id='top_search_box'></div>").css({'zIndex':'1000','width':'362px'});
					
					//追加店铺搜索
					//var keyword = $('#keyword').val();
					//var $htmlLi = $("<li class=\"ui-menu-item\" role=\"menuitem\"><a class=\"ui-corner-all\" tabindex=\"-1\" href=\"http://www.baidu.com\">搜索店铺："+keyword+"</a></li>");
					//var $ul = $(".ui-autocomplete");
					//$ul.append($htmlLi);
                }
            });
       },
    select: function(ev,ui) {
	  var t = ui.item.label.indexOf('店铺');
	  if( t > 0 ) {
		  var keyword = $('#keyword').val();
		  location.href = "<?php echo urlShop('search','store')?>&keyword="+keyword;
	  }else {
		  $('#keyword').val(ui.item.label);
		  $('#top_search_form').submit();
	  }
    }
  });
  $('#search-his-del').on('click',function(){$.cookie('<?php echo C('cookie_pre')?>his_sh',null,{path:'/'});$('#search-his-list').empty();});
});
</script>
</head>
<body>
<!-- PublicTopLayout Begin -->
<?php require_once template('layout/layout_top');?>
<!-- PublicHeadLayout Begin -->
<div class="header-wrap">
  <header class="public-head-layout wrapper clearfix">
    <h1 class="site-logo"><a href="<?php echo SHOP_SITE_URL;?>"><img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$output['setting_config']['site_logo']; ?>" class="pngFix"></a>
     <p class="new_text">臻品，健康，实惠</p>
    </h1>
    <div class="logo-test"><?php echo $output['setting_config']['shopwwi_stitle']; ?></div>
<!--
    <div class="nm-nav">
        <ul class="clearfix">
            <li><a href="/" target="_top">首页</a></li>
            <li><a href="http://www.hangomart.com/nongmao/index" target="_top">汉购农猫</a></li>
            <li><a href="http://www.hangomart.com/bigdata/" target="_top">农商互联</a></li>
            <li class=""><a href="http://www.hangochina.com" target="_blank">汉贸网</a></li>
            <li class=""><a href="http://ycxy.egowork.com/" target="_blank">农猫课堂</a></li>
            <li class=""><a href="http://www.hangomart.com/article/news" target="_blank">行业资讯</a></li>
            <li class=""><a href="http://www.hangomart.com/about/index">公司资讯</a></li>
        </ul> 
    </div>
-->
      <div class="head-search-layout">
        <div class="head-search-bar" id="head-search-bar">
            <div class="hd_serach_tab" id="hdSearchTab">
                <i></i>
            </div>
            <form action="<?php echo SHOP_SITE_URL;?>" method="get" class="search-form clearfix" id="top_search_form">
                <input name="act" id="search_act" value="search" type="hidden">
                <?php
                if ($_GET['keyword']) {
                    $keyword = stripslashes($_GET['keyword']);
                } elseif ($output['rec_search_list']) {
                    $_stmp = $output['rec_search_list'][array_rand($output['rec_search_list'])];
                    $keyword_name = $_stmp['name'];
                    $keyword_value = $_stmp['value'];
                } else {
                    $keyword = '';
                }
            ?>
                    <input name="keyword" id="keyword" type="text" class="input-text" value="<?php echo $keyword;?>" maxlength="60" x-webkit-speech lang="zh-CN" onwebkitspeechchange="foo()" placeholder="<?php echo $keyword_name ? $keyword_name : '请输入您要搜索的商品关键字';?>" data-value="<?php echo rawurlencode($keyword_value);?>" x-webkit-grammar="builtin:search" autocomplete="off" />
                    <!-- <input type="submit" id="button" value="<?php echo $lang['nc_common_search'];?>" class="input-submit"> -->
                    <input type="submit" id="button" value="" class="search-icon">
            </form>
            <div class="search-tip" id="search-tip">
                <div class="search-history">
                    <div class="title">历史纪录<a href="javascript:void(0);" id="search-his-del">清除</a></div>
                    <ul id="search-his-list">
                        <?php if (is_array($output['his_search_list']) && !empty($output['his_search_list'])) { ?>
                        <?php foreach($output['his_search_list'] as $v) { ?>
                        <li>
                            <a href="<?php echo urlShop('search', 'index', array('keyword' => $v));?>">
                                <?php echo $v ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
                <div class="search-hot">
                    <div class="title">热门搜索...</div>
                    <ul>
                        <?php if (is_array($output['rec_search_list']) && !empty($output['rec_search_list'])) { ?>
                        <?php foreach($output['rec_search_list'] as $v) { ?>
                        <li>
                            <a href="<?php echo urlShop('search', 'index', array('keyword' => $v['value']));?>">
                                <?php echo $v['value']?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- <div class="keyword">
            <ul>
                <?php if(is_array($output['hot_search']) && !empty($output['hot_search'])) { foreach($output['hot_search'] as $val) { ?>
                <li>
                    <a href="<?php echo urlShop('search', 'index', array('keyword' => $val));?>">
                        <?php echo $val; ?>
                    </a>
                </li>
                <?php } }?>
            </ul>
        </div> -->
        <a class="new_shopping" href="<?php echo SHOP_SITE_URL;?>/index.php?act=cart">
            <img src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.img.DS.'gwc_icon.png'?>" alt="">    
            <p>购物车</p>
        </a>
    </div>
      <!--<div style="float: right;"><img width="224px" src="<?php /*echo  SHOP_SITE_URL.DS.'resource'.DS.'img'.DS. 'custom_service.png'; */?>" class="pngFix"></div>
--><script>
$(function(){
    $(".nm-nav ul li").hover(function(){
        $(this).find("dl").show();
        $(this).addClass("hover");
    },function(){
        $(this).find("dl").hide();
        $(this).removeClass("hover");
    })
})      
</script>   
    
<!--
	<div class="advantage">  
		<ul>
			<li>100%正品  真品真实惠</li>
			<li>产地直供  原汁原味</li>
			<li>优质配送</li>
		</ul>
	</div>	
-->
  </header>
</div>
<!-- PublicHeadLayout End --> 

<!-- publicNavLayout Begin -->
<nav class="public-nav-layout <?php if($output['channel']) {echo 'channel-'.$output['channel']['channel_style'].' channel-'.$output['channel']['channel_id'];} ?>">
  <div class="wrapper">
    <div class="all-category"> 
      <?php require template('layout/home_goods_class');?>
    </div>
    <ul class="site-menu">
<!--      <li><a href="<?php echo SHOP_SITE_URL;?>" <?php if($output['index_sign'] == 'index' && $output['index_sign'] != '0') {echo 'class="current"';} ?>><span><?php echo $lang['nc_index'];?></span></a></li>-->
      <?php if (C('groupbuy_allow')){ ?>
      <!--<li class="navitems-on"><a href="<?php echo urlShop('show_groupbuy', 'index');?>" <?php if($output['index_sign'] == 'groupbuy' && $output['index_sign'] != '0') {echo 'class="current"';} ?>> <span><?php echo $lang['nc_groupbuy'];?></span></a></li>-->
      <?php } ?>
      <?php if (0){ //隐藏品牌页?>
      <li><a href="<?php echo urlShop('brand', 'index');?>" <?php if($output['index_sign'] == 'brand' && $output['index_sign'] != '0') {echo 'class="current"';} ?>> <span><?php echo $lang['nc_brand'];?></span></a></li>
            <li><a href="<?php echo urlShop('promotion','index');?>" <?php if($output['index_sign'] == 'promotion' && $output['index_sign'] != '0') {echo 'class="current"';} ?>> <span>疯抢</span></a></li>
      <?php } ?>
      <?php if (C('points_isuse') && C('pointshop_isuse')){ ?>
      <li><a href="<?php echo urlShop('pointshop', 'index');?>" <?php if($output['index_sign'] == 'pointshop' && $output['index_sign'] != '0') {echo 'class="current"';} ?>> <span><?php echo $lang['nc_pointprod'];?></span></a></li>
      <?php } ?>
      <?php if (C('cms_isuse')){ ?>
      <li><a href="<?php echo urlShop('special', 'special_list');?>" <?php if($output['index_sign'] == 'special' && $output['index_sign'] != '0') {echo 'class="current"';} ?>> <span>专题</span></a></li>
      <?php } ?>
 <?php if(!empty($output['nav_list']) && is_array($output['nav_list'])){?>
      <?php foreach($output['nav_list'] as $nav){?>
      <?php if($nav['nav_location'] == '1'){?>
      <?php
        $target = $class = "";
        if($nav['nav_new_open']) {
            $target = ' target="_blank"';
        }
        switch($nav['nav_type']) {
            case '0':
                $href = ' href="' . $nav['nav_url'] . '"';
                
                if ('hot' == $nav['style_id']) {
                   $class = ' class="nav_hot"';
                }
                
                if ('new' == $nav['style_id']) {
                   $class = ' class="nav_new"';
                }
                
                break;
            case '1':
                $href = ' href="' . urlShop('search', 'index',array('cate_id'=>$nav['item_id'])) . '"';
                if (isset($_GET['cate_id']) && $_GET['cate_id'] == $nav['item_id']) {
                    $class = ' class="current"';
                }
                break;
            case '2':
                $href = ' href="' . urlMember('article', 'article',array('ac_id'=>$nav['item_id'])) . '"';
                if (isset($_GET['ac_id']) && $_GET['ac_id'] == $nav['item_id']) {
                    $class = ' class="current"';
                }
                break;
            case '3':
                $href = ' href="' . urlShop('activity', 'index', array('activity_id'=>$nav['item_id'])) . '"';
                if (isset($_GET['activity_id']) && $_GET['activity_id'] == $nav['item_id']) {
                    $class = ' class="current"';
                }
                break;
        }
        ?>
        <li <?php echo $class; ?> >
        <a <?php echo $href.$target; ?> >
        <?php echo $nav['nav_title'];?></a><div class="icon-box"><i class="hover-icon"></i></div></li>
      <?php }?>
      <?php }?>
      <?php }?>
    </ul>
      <div style="float: right;margin-right:22px"><img src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.'img'.DS. 'custom_service1.png'; ?>" class="pngFix"></div>
  </div>
  
</nav>
