<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="hd"><a href="javascript:;" class="changeBnt" id="xxlChg"><i></i>换一换</a></div>
<ul class="picLB" id="picLBxxl">
	<?php if (!empty($output['code_sale_list']['code_info']) && is_array($output['code_sale_list']['code_info'])) { ?>
	<?php foreach ($output['code_sale_list']['code_info'] as $key => $val) { ?>
	<?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
	<li>
		<dl class="picDl">
			<?php foreach($val['goods_list'] as $k => $v){ ?>
			<dd><a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" class="pic"><img shopwwi-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" mff="sqde" alt="<?php echo $v['goods_name']; ?>"/></a>
				<div class="ftBox">
					<div class="tit">
						<a href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>">
							<?php echo $v['goods_name']; ?>
						</a>
					</div>
					<div class="text">
						<?php echo '商城价'.'：';?><em><?php echo ncPriceFormatForList($v['goods_price']); ?></em></div>
				</div>
			</dd>
			<?php } ?> </dl>
	</li>
	<?php } }} ?>
</ul>
