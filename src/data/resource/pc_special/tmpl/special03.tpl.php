<style>
	.tab_pic3{ display: none;}
</style>

<div id="body" style="background:#FFCC00;">
	<div class="cms-content">
			<?php loop_include_widgets($output); ?>
	</div>
</div>


<script>
$(function(){

	var tab_date = $(".tab-date .content .item");
	
	$(".tab_pic3").first().show();
	
	tab_date.hover(function(){
		
		var index = tab_date.index(this);
		
		$(".cms-content .tab_pic3").eq(index).show().siblings(".tab_pic3").hide();
	});
	$(".cms-content .tab_pic3").eq(2).show().siblings(".tab_pic3").hide();
})
</script>

