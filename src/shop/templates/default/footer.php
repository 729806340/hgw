<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script language="javascript">
	function fade() {
		$("img[rel='lazy']").each(function() {
			var $scroTop = $(this).offset();
			if ($scroTop.top <= $(window).scrollTop() + $(window).height()) {
				$(this).hide();
				$(this).attr("src", $(this).attr("shopwwi-url"));
				$(this).removeAttr("rel");
				$(this).removeAttr("name");
				$(this).fadeIn(500);
			}
		});
	}

	if ($("img[rel='lazy']").length > 0) {
		$(window).scroll(function() {
			fade();
		});
	};
	fade();

</script>
<?php echo getChat($layout);?>


<!--start 底部-->
<footer>

	<div class="foot_bg1">
    	<div class="wrapper">
            <div class="con">
				<?php if(is_array($output['article_list']) && !empty($output['article_list'])){ ?>
                <ul class="clearfix">
					<?php foreach ($output['article_list'] as $k=> $article_class){ ?>
					<?php if(!empty($article_class)){ ?>
                    <li>
                        <dl class="s<?php echo ''.$k+1;?>">
                            <dt><?php if(is_array($article_class['class'])) echo $article_class['class']['ac_name'];?></dt>
							<?php if(is_array($article_class['list']) && !empty($article_class['list'])){ ?>
							<?php foreach ($article_class['list'] as $article){ ?>
                            <dd><a href="<?php if($article['article_url'] != '')echo $article['article_url'];else echo urlMember('article', 'show',array('article_id'=> $article['article_id']));?>" title="<?php echo $article['article_title']; ?>">
								<?php echo $article['article_title'];?> </a>
							</dd>
							<?php }}?>
                        </dl>
                    </li>
					<?php }}?>
                    <!--<div class="ewm">
						<p class="left"><img src="<?php echo UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.C('mobile_wx');?>" ></p>
						<h2>汉购网官方微信二维码 <br /><span>扫一扫<br />惊喜等你哟！</span></h2> 
					</div>-->
                </ul>
				<?php }?>
            </div>
        </div>
    </div>
    <div class="foot_bg2">
    	<div class="wrapper">
            <div class="con2">
                <p><a href="<?php echo SHOP_SITE_URL;?>">返回首页</a>
						<?php if(!empty($output['nav_list']) && is_array($output['nav_list'])){?>
						<?php foreach($output['nav_list'] as $nav){?>
						<?php if($nav['nav_location'] == '2'){?><span>|</span> <a <?php if($nav[ 'nav_new_open']){?>target="_blank" <?php }?>href="<?php switch($nav['nav_type']){case '0':echo $nav['nav_url'];break; case '1':echo urlShop('search', 'index', array('cate_id'=>$nav['item_id']));break; case '2':echo urlMember('article', 'article',array('ac_id'=>$nav['item_id']));break; case '3':echo urlShop('activity', 'index',array('activity_id'=>$nav['item_id']));break;}?>"><?php echo $nav['nav_title'];?></a>
						<?php }}}?><span>|</span><a href="<?php echo urlshop('link');?>">友情链接</a>
				</p>
                <p>
                	&copy;2005-2018 火凤凰农商互联科技（湖北）有限公司版权所有，并保留所有权利
                </p>
				
            </div>
			
			
        </div>
        <p><?php echo html_entity_decode($output['setting_config']['statistics_code'],ENT_QUOTES); ?></p>
    </div>
	<div class="footer-fp-img">
		<!--<a  key ="549264e13b05a3da0fbd611a"  logo_size="83x30"  logo_type="realname"  href="http://www.anquan.org" ><script src="//static.anquan.org/static/outer/js/aq_auth.js"></script></a>-->
<!--		<a  key ="549264e13b05a3da0fbd611a"  logo_size="124x47"  logo_type="realname"  href="http://www.anquan.org" ><script src="//static.anquan.org/static/outer/js/aq_auth.js"></script></a>-->
		<a href="javascrit:void(0);"><img src="/shop/templates/default/images/gangting.gif" /></a>
		<a href="javascrit:void(0);"><img src="/shop/templates/default/images/beian.jpg" /></a>
		<div style="display:inline-block"><script type="text/javascript" src="http://wljg.scjgj.wuhan.gov.cn/whwjww/VieidServlet?webId=d236db5313158648424291c098c6a25e&width=70&heigth=70"></script></div>
		<div style="display:inline-block"><script type="text/javascript" src="http://whgswj.whhd.gov.cn:8089/whwjww/VieidServlet?webId=d236db5313158648424291c098c6a25e&width=70&heigth=70"></script></div>
	</div>
</footer>
<!--end 底部-->
<script>
	
	
$(function(){
	$(".foot_bg1 ul li:last").css("border","none");
})
</script>

 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.cookie.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/qtip/jquery.qtip.min.css" rel="stylesheet" type="text/css">
<!-- 对比 -->
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/compare.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/main.js?2"></script>
<script type="text/javascript">
	$(function() {
		// Membership card
		$('[nctype="mcard"]').membershipCard({
			type: 'shop'
		});
	});

	var name = jQuery.cookie('hango_member_name')? jQuery.cookie('hango_member_name'):'';
	var uid = jQuery.cookie('hango_member_id')?jQuery.cookie('hango_member_id'):'';
</script>

<!--start 360点睛代码-->
<?php if($output['mvq']['is_goods'] == '1') { ?>
<script type="text/javascript">
	var _mvq = window._mvq || []; 
	window._mvq = _mvq;
	_mvq.push(['$setAccount', 'm-98554-0']);

	_mvq.push(['$setGeneral', 'goodsdetail', '', /*用户名*/ '<?php echo $output['mvq']['member_name']; ?>', /*用户id*/ '<?php echo $output['mvq']['member_id']; ?>']);
	_mvq.push(['$logConversion']);

	_mvq.push(['setPageUrl', /*单品着陆页url*/ '<?php echo urlShop('goods', 'index',array('goods'=>$output['goods']['goods_id']));?>']);	//如果不需要特意指定单品着陆页url请将此语句删掉
	_mvq.push(['$addGoods',  /*分类id*/ '<?php echo $output['goods']['gc_id']; ?>', /*品牌id*/ '<?php echo $output['goods']['brand_id']; ?>', /*商品名称*/ '<?php echo $output['goods']['goods_name']; ?>',/*商品ID*/ '<?php echo $output['goods']['goods_id']; ?>',/*商品售价*/ '<?php echo $output['goods']['goods_price']; ?>', /*商品图片url*/ '<?php echo urlShop('goods', 'index',array('goods'=>$output['goods']['goods_id']));?>', /*分类名*/ '<?php echo $output['mvq']['gc_name']; ?>', /*品牌名*/ '<?php echo $output['mvq']['brand_name']; ?>', /*商品库存状态1或是0*/ '<?php echo $output['mvq']['goods_storage'] ? 1 : 0; ?>', /*网络价*/ '<?php echo $output['mvq']['goods_marketprice']; ?>',/*收藏人数*/ '0', /*商品下架时间*/ '0']);
	_mvq.push(['$addPricing', /*价格描述*/ '销售价']);
	_mvq.push(['$logData']);
</script>
<?php } ?>

<?php if($output['mvq']['is_order'] == '1') { ?>
<script type="text/javascript">
var _mvq = _mvq || [];
_mvq.push(['$setAccount', 'm-98554-0']);

_mvq.push(['$setGeneral', 'ordercreate', '',  '<?php echo $output['mvq']['member_name']; ?>', '<?php echo $output['mvq']['member_id']; ?>']);
_mvq.push(['$logConversion']);
_mvq.push(['$addOrder', '<?php echo $_GET['pay_sn']; ?>', '<?php echo ncPriceFormat($output['pay']['pay_amount_online']); ?>']);
<?php foreach( $output['order_items'] as $k => $_item ) { ?>
_mvq.push(['$addItem',  '<?php echo $_item['order_id']; ?>',  '<?php echo $_item['goods_id']; ?>',  '<?php echo $_item['goods_name']; ?>',  '<?php echo $_item['goods_price']; ?>',  '<?php echo $_item['goods_num']; ?>',  '<?php urlShop('goods', 'index',array('goods'=>$_item['goods_id'])); ?>',  '<?php echo $_item['goods_price']; ?>']);
<?php } ?>
_mvq.push(['$logData']);
</script>
<?php } ?>
<!--end 360点睛代码-->

<!--start 小能客服代码-->
<script>
NTKF_PARAM = {
	siteid: 'hf_1000',		//企业ID，必填，取值参见文档开始表
	sellerid:"",
	settingid: 'hf_1000_1508484886546',		//缺省客服配置ID，必填，取值参见文档开始表
	uid:uid,		//用户ID,未登录可以为空
	uname:name,		//用户名，未登录可以为空
} ;

<?php if($output['is_goods'] == '1') {?>
    NTKF_PARAM.itemid=<?php echo $output["goods"]["goods_id"];?>;
    NTKF_PARAM.itemparam="www.hangowa.com";
<?php } elseif($output['is_gallery'] == '1') { ?>	
	NTKF_PARAM.itemparam = {
　		categoryid:	'<?php echo $output["cat_id"]; ?>',//分类ID,多分类可以用分号(;)分隔, 长路径父子间用冒号(:)分割
　		brandid:	"" //品牌ID，多品牌可以用分号(;)分隔
	};
<?php } elseif($output['is_brand'] == '1') { ?>		
	NTKF_PARAM.itemparam = {
　		categoryid:	"",//分类ID,多分类可以用分号(;)分隔, 长路径父子间用冒号(:)分割
　		brandid:	"<{$brandid}>" //品牌ID，多品牌可以用分号(;)分隔
	};
<?php } elseif($output['is_cart'] == '1') { ?>		
	NTKF_PARAM.ntalkerparam = {
		cartprice:	'<?php echo $output["cartprice"]; ?>',	//购物车总价
　		items:	<?php echo $output["items"]; ?>
	};
<?php } elseif($output['is_checkout'] == '1') { ?>		
	NTKF_PARAM.ntalkerparam = {
		cartprice:	'<?php echo $output["cartprice"]; ?>',	//购物车总价
　		items:	<?php echo $output["items"]; ?>
	};
<?php } elseif($output['is_paycenter'] == '1') { ?>
	NTKF_PARAM.orderid = '<?php echo $output["orderid"]; ?>';	//订单ID,
	NTKF_PARAM.orderprice = '<?php echo $output["orderprice"]; ?>';	//订单总价,
<?php } elseif($output['is_payresult'] == '1') { ?>		
	NTKF_PARAM.orderid = '<?php echo $output["orderid"]; ?>';	//订单ID,
<?php } else { ?>	

<?php } ?>	
</script>
<script type="text/javascript" src="http://dl.ntalker.com/js/b2b/ntkfstat.js?siteid=hf_1000" charset="utf-8"></script>
<!--end 小能客服代码-->
