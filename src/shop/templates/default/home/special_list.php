<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<style type="text/css">
	.public-nav-layout,
	.classtab a.curr,
	.head-search-bar .search-form,
	.public-nav-layout .category .hover .class {
		/*background: #a2375f;*/
	}
	
	.public-head-layout .logo-test {
		color: #a2375f
	}
	
	.public-nav-layout.category .sub-class {
		border-color: #a2375f;
	}
	
	.no-content {
		font: normal 16px/20px Arial, "microsoft yahei";
		color: #999999;
		text-align: center;
		padding: 150px 0;
	}
	/*专题活动列表*/

</style>
<div class="wrapper">
	<div class="wwi-zt-mainbox">
		<?php if(!empty($output['special_list']) && is_array($output['special_list'])) {?>
		<ul class="special-list clearfix">
			<?php foreach($output['special_list'] as $value) {?>
			
			<li class="ml-item">
				<div class="mli-img"><a href="<?php echo urlshop('special','special_detail', array('special_id'=>$value['special_id']));?>" target="_blank"><img width="380" height="213" src="<?php echo getCMSSpecialImageUrl($value['special_image']);?>" class=""></a></div>
				<div class="mli-info">
					<div class="brand-active">
						<a href="<?php echo urlshop('special','special_detail', array('special_id'=>$value['special_id']));?>" target="_blank">
							<?php echo $value['special_title'];?>
						</a>
					</div>
					<div class="brand-rebate"><span><?php echo $value['special_stitle'];?></span> <a href="<?php echo urlshop('special','special_detail', array('special_id'=>$value['special_id']));?>" class="brand-con">点击查看</a></div>
				</div>
			</li>
			<?php } ?>
		</ul>
		<div class="pagination">
			<?php echo $output['show_page'];?> </div>
		<?php } else { ?>
		<div class="no-content">暂无专题内容</div>
		<?php } ?>
	</div>
</div>
