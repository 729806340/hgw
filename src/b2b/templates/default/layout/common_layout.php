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
body { _behavior: url(<?php echo B2B_TEMPLATES_URL;
?>/css/csshover.htc);
}
</style>
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/base.css" rel="stylesheet" type="text/css">
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/home_header.css" rel="stylesheet" type="text/css">
<link href="<?php echo B2B_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/b2b_layout.css" rel="stylesheet" type="text/css">
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/b2b_reset.css" rel="stylesheet" type="text/css">
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo B2B_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome-ie7.min.css">



<![endif]-->
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/html5shiv.js"></script>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/respond.min.js"></script>
<![endif]-->
<script>
var COOKIE_PRE = '<?php echo COOKIE_PRE;?>';var _CHARSET = '<?php echo strtolower(CHARSET);?>';var LOGIN_SITE_URL = '<?php echo LOGIN_SITE_URL;?>';var MEMBER_SITE_URL = '<?php echo MEMBER_SITE_URL;?>';var SITEURL = '<?php echo B2B_SITE_URL;?>';var B2B_SITE_URL = '<?php echo B2B_SITE_URL;?>';var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';var B2B_TEMPLATES_URL = '<?php echo B2B_TEMPLATES_URL;?>';
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
	$(".category ul.menu").find("li").each(
		function() {
			$(this).hover(
				function() {
				    var cat_id = $(this).attr("cat_id");
					var menu = $(this).find("div[cat_menu_id='"+cat_id+"']");
					
					$(this).find(".class").animate({marginLeft:"10px"})
					menu.show();
					$(this).addClass("hover");					
					var menu_height = menu.height();
					if (menu_height < 60) menu.height(80);
					menu_height = menu.height();
					var li_top = $(this).position().top;
					$(menu).css("top",-li_top + 50);
				},
				function() {
					$(this).find(".class").animate({marginLeft:""})
					$(this).removeClass("hover");
				    var cat_id = $(this).attr("cat_id");
					$(this).find("div[cat_menu_id='"+cat_id+"']").hide();
				}
			);
		}
	);
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
            $.getJSON('<?php echo B2B_SITE_URL;?>/index.php?act=search&op=auto_complete', request, function (data, status, xhr) {
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
        window.location.href="<?php echo B2B_SITE_URL?>/index.php?act=search&op=index&keyword="+$('#keyword').attr('data-value');
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
            $.getJSON('<?php echo B2B_SITE_URL;?>/index.php?act=search&op=auto_complete', request, function (data, status, xhr) {
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

<script>
        $(function() {
            /*顶部工具栏下拉*/
            $('.quick-menu dl').hover(function() {
                $(this).addClass("hover");
                $(this).find("dd").show();
            }, function() {
                $(this).removeClass("hover");
                $(this).find("dd").hide();
            })

            /*去掉边线*/
            $(".b2b-nav .nav-con ul li").last().find("a").css("border-right", "none");
            $(".goods-model .model").last().css("border-right", "none");

            /*展开分类*/
            $(".unfold-btn").toggle(function(){
                $(this).addClass("click-event");
                $(".classify-con").css("height","80px")
            },function(){
                $(this).removeClass("click-event");
                $(".classify-con").css("height","40px")
            });

        })
    </script>
</head>
<body>
<!-- PublicTopLayout Begin -->
<?php require_once template('layout/layout_top');?>
<!-- PublicHeadLayout Begin -->
<div class="b2b-header">
    <div class="w1200 b2bclearfix">
        <div class="logo fl">
            <a href="#"><img height="60" src="<?php  echo B2B_TEMPLATES_URL;?>/images/b2b_icon/logo.png" /></a>
        </div>
        <div class="search fl">
            <form class="b2bclearfix">
                <input class="i-box" type="text"  name="keyword"  placeholder="批发农产品" value="<?php echo  isset($_GET['keyword'])? $_GET['keyword']:''; ?>"/>
                <input class="i-btn" type="button"  value="搜索" />
            </form>
            <?php if(is_array($output['hot_search']) && !empty($output['hot_search'])) { ?>
            <div class="search-hot">
                <span>热搜：</span>
                <?php foreach($output['hot_search'] as $val) { ?>
                <a href="<?php echo urlB2B('search', 'index', array('keyword' => $val));?>"><?php echo $val; ?></a>
                <?php }?>
            </div>
            <?php } ?>
        </div>
        <div class="con-cart fr">
            <a target="_self" href="<?php echo urlB2b('cart','index')?>" class="cart-btn">
                <i class="cart-icon"></i>
                <em class="cart-num" id="top-cart-count"><?php echo isset($output['cart_goods_num']) ? $output['cart_goods_num']:"0";?></em>
                <span>采购清单</span>
            </a>
        </div>
    </div>
</div>
<!-- PublicHeadLayout End -->
<nav class="b2b-nav">
    <div class="w1200 nav-con">
        <ul class="b2bclearfix">
            <li><a href='<?php echo  B2B_SITE_URL; ?>'>首页</a></li>
            <li><a href='#'>天天特价</a></li>
            <li><a href='#'>万“粽”风情</a></li>
            <li><a href='#'>土道家</a></li>
            <li><a href='#'>森林特产</a></li>
        </ul>
    </div>
</nav>
<script>
   $(function(){
      $("input.i-btn").click(function(){
          var keyword=$("input.i-box").val();
          if(keyword==""){
              $("input.i-box").attr("placeholder","请填写你要查询的商品名称");
              return false;
          }
          window.location.href="?act=search&op=index&keyword="+keyword;
      })
   })
</script>

