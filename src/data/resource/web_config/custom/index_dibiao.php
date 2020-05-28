<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<!-- 图片部分 -->
<div class="pic-con">
	<ul>
		<?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
		<?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
		<?php if (is_array($val) && !empty($val)) { ?>
		<li>
			<div class="goods-thumb"><a href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>" target="_blank"><img alt="<?php echo $val['pic_name'];?>" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" /></a></div>
		</li>
		<?php }}} ?>
	</ul>
</div>
