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
<footer class="b2b-foot">
	<div class="foot_bg1">
		<div class="w1200">
			<div class="con">
				<ul class="b2bclearfix">
					<li>
						<dl class="s1">
							<dt>新手指南</dt>
							<dd><a href="http://www.hangowa.com/member/article-6.html" title="如何注册成为会员">
									如何注册成为会员 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-8.html" title="手机/邮箱修改">
									手机/邮箱修改 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-40.html" title="忘记密码/会员名">
									忘记密码/会员名 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-39.html" title="账号无法登录">
									账号无法登录 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-10.html" title="账户安全">
									账户安全 </a>
							</dd>
						</dl>
					</li>
					<li>
						<dl class="s2">
							<dt>购物指南</dt>
							<dd><a href="http://www.hangowa.com/member/article-21.html" title="购物流程">
									购物流程 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-19.html" title="商品挑选/购买">
									商品挑选/购买 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-18.html" title="订单查询">
									订单查询 </a>
							</dd>
						</dl>
					</li>
					<li>
						<dl class="s3">
							<dt>物流配送</dt>
							<dd><a href="http://www.hangowa.com/member/article-13.html" title="常用快递">
									常用快递 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-15.html" title="修改收货地址">
									修改收货地址 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-14.html" title="货物签收">
									货物签收 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-12.html" title="发货问题">
									发货问题 </a>
							</dd>
						</dl>
					</li>
					<li>
						<dl class="s4">
							<dt>支付方式</dt>
							<dd><a href="http://www.hangowa.com/member/article-30.html" title="礼品卡优惠券">
									礼品卡优惠券 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-29.html" title="支付宝/微信支付">
									支付宝/微信支付 </a>
							</dd>
						</dl>
					</li>
					<li>
						<dl class="s5">
							<dt>售后服务</dt>
							<dd><a href="http://www.hangowa.com/member/article-33.html" title="退换货政策">
									退换货政策 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-32.html" title="退换货流程">
									退换货流程 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/member/article-34.html" title="退款申请">
									退款申请 </a>
							</dd>
						</dl>
					</li>
					<li style="border: medium none;">
						<dl class="s6">
							<dt>商家服务</dt>
							<dd><a href="http://www.hangowa.com/member/article-41.html" title="关于汉购网">
									关于汉购网 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/index.php?act=show_help&amp;op=index&amp;t_id=91&amp;help_id=101" title="招商政策">
									招商政策 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/index.php?act=show_help&amp;op=index&amp;t_id=91&amp;help_id=102" title="收费标准">
									收费标准 </a>
							</dd>
							<dd><a href="http://www.hangowa.com/index.php?act=show_joinin&amp;op=index" title="联系我们">
									联系我们 </a>
							</dd>
						</dl>
					</li>
					<!--<div class="ewm">
                    <p class="left"><img src="http://www.hangowa.com/data/upload/mobile/05234711548071503.png" ></p>
                    <h2>汉购网官方微信二维码 <br /><span>扫一扫<br />惊喜等你哟！</span></h2>
                </div>-->
				</ul>
			</div>
		</div>
	</div>
	<div class="foot_bg2">
		<div class="w1200">
			<div class="con2">
				<p>
					©2005-2018 火凤凰农商互联科技（湖北）有限公司版权所有，并保留所有权利
				</p>
			</div>
		</div>
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
<script src="<?php echo B2B_RESOURCE_SITE_URL;?>/js/compare.js"></script>
<script src="<?php echo B2B_RESOURCE_SITE_URL;?>/js/main.js"></script>
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

<?php if($output['is_goods'] == '1'){ ?>
    NTKF_PARAM.itemid=<?php echo $output["goods"]["goods_id"];?>;
    NTKF_PARAM.itemparam="www.hangowa.com";
<?php } elseif($output['is_gallery'] == '1') { ?>	
	NTKF_PARAM.itemparam  = {
　		categoryid:	'<?php echo $output["cat_id"]; ?>',//分类ID,多分类可以用分号(;)分隔, 长路径父子间用冒号(:)分割
　		brandid:	"" //品牌ID，多品牌可以用分号(;)分隔
	};
<?php } elseif($output['is_brand'] == '1') { ?>		
	NTKF_PARAM.itemparam  = {
　		categoryid:	"",//分类ID,多分类可以用分号(;)分隔, 长路径父子间用冒号(:)分割
　		brandid:	"<{$brandid}>" //品牌ID，多品牌可以用分号(;)分隔
	};
<?php } elseif($output['is_cart'] == '1') { ?>		
	NTKF_PARAM.ntalkerparam = {
		cartprice:	'<?php echo $output["cartprice"]; ?>',	//购物车总价
　		items:	'<?php echo $output["items"]; ?>'
	};
<?php } elseif($output['is_checkout'] == '1') { ?>		
	NTKF_PARAM.ntalkerparam = {
		cartprice:	'<?php echo $output["cartprice"]; ?>',	//购物车总价
　		items:	'<?php echo $output["items"]; ?>'
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
