<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="wwi-footer"><div id="cti" class="wrapper"></div><div id="footer"><div class="wrapper"><div class="screen clearfix"><div class="fl right-flag"><a href="http://www.hangowa.com" target="_blank" rel="nofollow"><img src="<?php echo SHOP_SITE_URL;?>/img/credit-flag3.png"></a><a href="http://www.hangowa.com/" target="_blank" rel="nofollow"><img src="<?php echo SHOP_SITE_URL;?>/img/isc2.png"></a></div><div class="fl about-us"><p><a href="<?php echo SHOP_SITE_URL;?>">返回首页</a><?php if(!empty($output['nav_list']) && is_array($output['nav_list'])){?><?php foreach($output['nav_list'] as $nav){?><?php if($nav['nav_location'] == '2'){?><span>|</span> <a  <?php if($nav['nav_new_open']){?>target="_blank" <?php }?>href="<?php switch($nav['nav_type']){case '0':echo $nav['nav_url'];break; case '1':echo urlShop('search', 'index', array('cate_id'=>$nav['item_id']));break; case '2':echo urlMember('article', 'article',array('ac_id'=>$nav['item_id']));break; case '3':echo urlShop('activity', 'index',array('activity_id'=>$nav['item_id']));break;}?>"><?php echo $nav['nav_title'];?></a><?php }}}?><span>|</span><a href="<?php echo urlshop('link');?>">友情链接</a></p><p>CopyRight © 2007-2016 汉购网交流中心 <a href="http://www.miibeian.gov.cn/" target="_blank" style="color:#666"><?php echo $output['setting_config']['icp_number']; ?></a> NewPower Co. 版权所有 </p><p><?php echo html_entity_decode($output['setting_config']['statistics_code'],ENT_QUOTES); ?></p></div></div><?php if (C('debug') == 1){?><div id="think_page_trace" class="trace"><fieldset id="querybox"><legend><?php echo $lang['nc_debug_trace_title'];?></legend><div><?php print_r(Tpl::showTrace());?></div></fieldset></div><?php }?>
<script type="text/javascript">
var _mvq = _mvq || [];
_mvq.push(['$setAccount', 'm-98554-0']);

_mvq.push(['$logConversion']);
(function() {
var mvl = document.createElement('script');
mvl.type = 'text/javascript'; mvl.async = true;
mvl.src = ('https:' == document.location.protocol ? 'https://static-ssl.mediav.com/mvl.js' : 'http://static.mediav.com/mvl.js');
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(mvl, s);
})();

</script>
