<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<!-- 焦点图部分start -->
<div>
		<ul class="clearfix">
		<?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
        <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
		<li>
			<a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>"> <img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="<?php echo $val['pic_name'];?>"></a>
		</li>
		<?php } } ?>
	</ul>
</div>
<script type="text/javascript">

	update_screen_focus();

</script>
<!-- 焦点图部分end -->

<script>
$(function(){
	var add_elementClass=$(".jinrizhutui ul li");
	var index=add_elementClass.index();
	
	add_elementClass.each(function(){
		add_elementClass.eq(2).addClass("item"+index);
		
	})
})
</script>
