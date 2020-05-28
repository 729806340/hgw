<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>商家中心</title>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/base.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/seller_center.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo SHOP_RESOURCE_SITE_URL;?>/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<script>
var COOKIE_PRE = 'hango_';var _CHARSET = 'utf-8';var SITEURL = '<?php echo SHOP_SITE_URL;?>';var MEMBER_SITE_URL = '<?php echo member_site_url;?>';var RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';var SHOP_RESOURCE_SITE_URL = '<?php echo SHOP_RESOURCE_SITE_URL;?>';var SHOP_TEMPLATES_URL = '<?php echo SHOP_TEMPLATES_URL;?>';</script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.validation.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/member.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/html5shiv.js"></script>
      <script src="<?php echo RESOURCE_SITE_URL;?>/js/respond.min.js"></script>
<![endif]-->
</head>

<body>

<!--S 分类选择区域-->
<div class="wrapper_search">
  <div class="wp_sort">
    <div id="dataLoading" class="wp_data_loading">
      <div class="data_loading"><?php echo $lang['store_goods_step1_loading'];?></div>
    </div>

    <div id="class_div" class="wp_sort_block">
      <div class="sort_list">
        <div class="wp_category_list">
          <div id="class_div_1" class="category_list">
            <ul>
              <?php if(isset($output['goods_class']) && !empty($output['goods_class']) ) {?>
              <?php foreach ($output['goods_class'] as $val) {?>
              <li class="" nctype="selClass" data-param="{gcid:<?php echo $val['gc_id'];?>,deep:1,tid:<?php echo $val['type_id'];?>}"> <a class="" href="javascript:void(0)"><i class="icon-double-angle-right"></i><?php echo $val['gc_name'];?></a></li>
              <?php }?>
              <?php }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="sort_list">
        <div class="wp_category_list blank">
          <div id="class_div_2" class="category_list">
            <ul>
            </ul>
          </div>
        </div>
      </div>
      <div class="sort_list sort_list_last">
        <div class="wp_category_list blank">
          <div id="class_div_3" class="category_list">
            <ul>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="alert">
    <dl class="hover_tips_cont">
      <dt id="commodityspan"><span style="color:#F00;"><?php echo $lang['store_goods_step1_please_choose_category'];?></span></dt>
      <dt id="commoditydt" style="display: none;" class="current_sort"><?php echo $lang['store_goods_step1_current_choose_category'];?><?php echo $lang['nc_colon'];?></dt>
      <dd id="commoditydd"></dd>
    </dl>
  </div>
  <div class="wp_confirm">
    <form method="get">
      <?php if ($output['edit_goods_sign']) {?>
      <input type="hidden" name="act" value="store_goods_online" />
      <input type="hidden" name="op" value="edit_goods" />
      <input type="hidden" name="commonid" value="<?php echo $output['commonid'];?>" />
      <input type="hidden" name="ref_url" value="<?php echo $_GET['ref_url'];?>" />
      <?php } else {?>
      <input type="hidden" name="act" value="store_goods_add" />
      <input type="hidden" name="op" value="add_step_two" />
      <?php }?>
      <input type="hidden" name="class_id" id="class_id" value="" />
      <input type="hidden" name="t_id" id="t_id" value="" />
      <div class="bottom tc">
      <label class="submit-border"><input id="transmit" disabled="disabled" nctype="buttonNextStep" value="确认" type="button" class="submit"style=" width: 200px;" /></label>
      </div>
    </form>
  </div>
</div>

</body>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.cookie.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.css" rel="stylesheet" type="text/css">

<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script> 
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script> 
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_add.step1.js"></script> 
<script>
SEARCHKEY = '<?php echo $lang['store_goods_step1_search_input_text'];?>';
RESOURCE_SITE_URL = '<?php echo RESOURCE_SITE_URL;?>';
</script>

<script>
;!function(){

var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
var id = '<?php echo $_GET['id'];?>';

//给父页面传值
$('#transmit').on('click', function(){
	var text = $('#commoditydd').html();
	//text = text.replaceAll(/<i class="icon-double-angle-right"><\/i>/, ">");
	while( text.indexOf( "icon-double-angle-right" ) != -1 ) {
		text = text.replace(/<i class="icon-double-angle-right"><\/i>/, ">");
	}
	var cate_id = $("#class_id").val();
    parent.$("#"+id).children('option').html(text);
	parent.$("#"+id).children('option').val(cate_id);
    parent.layer.close(index);
});

}();

</script>

</html>