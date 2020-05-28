<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script src="<?php echo SHOP_RESOURCE_SITE_URL.'/js/search_goods.js';?>"></script>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/layout.css" rel="stylesheet" type="text/css">
<style type="text/css">
  body { _behavior: url(<?php echo SHOP_TEMPLATES_URL;
?>/css/csshover.htc);
  }
</style>
<div class="wwi-container wrapper" >


  <div class="wwi-module wwi-padding25">
    <div class="title" style="border-bottom: 1px solid #eee;">
      <h3>红包信息</h3>
    </div>

      <div class="content">
        <p>红包名称：<?php echo $output['t_info']['rpacket_t_title'];?></p>
        <p>有效期：<?php echo @date('Y-m-d',$output['t_info']['rpacket_t_start_date']);?> 至
          <?php echo @date('Y-m-d',$output['t_info']['rpacket_t_end_date']);?></p>
        <p>红包适用范围：
          <?php
              if ($output['t_info']['rpacket_t_range']==1) { echo "仅选定商品适用"; }
              elseif ($output['t_info']['rpacket_t_range']==2) { echo "除选定商品外适用"; }
              elseif ($output['t_info']['rpacket_t_range']==3) { echo "选定商品分类适用"; }
              else{ echo "全场通用"; }
              // echo $output['t_info']['rpacket_t_range']==1?'仅选定商品适用':($output['t_info']['rpacket_t_range']==2?'除选定商品外适用':'全场通用');
          ?>
          </p>
      </div>
  </div>

  <!-- 分类下的推荐商品 -->
  <?php 
  // dump($output['goods_class_list']);
      if($output['t_info']['rpacket_t_range']>0){
          if(!empty($output['goods_list']) && is_array($output['goods_list'])){
            foreach($output['goods_list'] as $k=>$value) {
              $output['goods_list'][$k]['goods_name_highlight'] = $value['goods_name'];
            }
          }
          if(!empty($output['goods_class_list']) && is_array($output['goods_class_list'])){
            foreach($output['goods_class_list'] as $k=>$value) {
              $output['goods_class_list'][$k]['goods_name_highlight'] = $value['menu'];
            }
          }
  ?>
      <div class="shop_con_list" id="main-nav-holder">
        <!-- 商品列表循环  -->
        <div> <?php require_once (BASE_TPL_PATH.'/home/goods.squares.php'); ?> </div>
        <div class="tc mt20 mb20">
          <div class="pagination"> <?php echo $output['show_page']; ?> </div>
        </div>
      </div>
  <?php } ?>
  <div class="clear"></div>



  <!-- 最近浏览 -->
  </div>
</div>
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
