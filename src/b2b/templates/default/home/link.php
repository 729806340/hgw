<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<style type="text/css">
.public-nav-layout, .classtab a.curr, .head-search-bar .search-form, .public-nav-layout .category .hover .class {/*background: #f55;*/
}
.public-head-layout .logo-test {
	color: #f55
}
.public-nav-layout .category .sub-class {
	border-color: #f55;
}
.classtab, .classtab a {
	width: 500px;
}
</style>
<div class="goodsclass">
  <!--<div class="classtab"><a class="curr all" href="javascript:void(0);">添加友情链接请联系QQ群111731672</a></div>-->
  <div class="brandlog">
    <?php if(is_array($output['$link_list']) && !empty($output['$link_list'])) { foreach($output['$link_list'] as $val) {  if($val['link_pic'] != ''){  ?>
    <dl>
      <dt><a href="<?php echo $val['link_url']; ?>"><img src="<?php echo $val['link_pic']; ?>" alt="<?php echo $brand_r['brand_name'];?>" title="<?php echo $val['link_title']; ?>" width="150" height="50" /></a></dt>
      <dd><a href="<?php echo $val['link_url']; ?>"><?php echo $val['link_title']; ?></a> </dd>
    </dl>
    <?php }}}?>
  </div>
  <div class="brandtxt">
    <dl>
      <dt>文字链接</dt>
      <ul>
        <?php if(is_array($output['$link_list']) && !empty($output['$link_list'])) {foreach($output['$link_list'] as $val) { if($val['link_pic'] == ''){ ?>
        <li><a href="<?php echo $val['link_url']; ?>" tit="<?php echo $val['link_title']; ?>" src="<?php echo $val['link_title']; ?>" title="<?php echo $val['link_title']; ?>"><?php echo $val['link_title']; ?></a></li>
        <?php }}} ?>
        <div class="clear"></div>
      </ul>
    </dl>
  </div>
</div>
