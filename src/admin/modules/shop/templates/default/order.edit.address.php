<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
.addr-note{padding-left: 25px;padding-bottom:10px;}
.addr-note dl{margin-bottom:10px;}
</style>

<div class="addr-note">
	<dl>
		<dt>联系电话：</dt>
		<dd><input type="text" id="mobile" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['phone'];?>"></dd>
	  </dl>

	  <dl>
		<dt>收货人：</dt>
		<dd><input type="text" id="name" value="<?php echo $output['order_info']['extend_order_common']['reciver_name'];?>"></dd>
	  </dl>
	  
	  <dl>
		<dt>地区：</dt>
		<dd><input type="text" id="area" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['area'];?>" style="width:400px;"></dd>
	  </dl>
	  
	  <dl>
		<dt>街道：</dt>
		<dd><input type="text" id="streat" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['street'];?>" style="width:400px;"></dd>
	  </dl>
<input type="button" value="修改" class="ncbtn">
</div>

	
	
<script type="text/javascript">
$(document).ready(function(){
	$('.ncbtn').on('click',function(){
		var mobile = $("#mobile").val();
        var name = $("#name").val();
        var area = $("#area").val();
		var streat = $("#streat").val();
		if( name=='' || area=='' || streat=='' ){
			showError('不能为空');
		}

		$.post(
		"index.php?act=order&op=edit_address&order_id=<?php echo $output['order_info']['order_id'];?>", 
		{name: name, area: area, streat: streat, mobile: mobile},
		   function(data){
			 if(data.status == 'true') {
				 $('.r_mobile').html(mobile);
                $('.r_name').html(name);
				$('.r_info').html(area+" "+streat);
                DialogManager.close('edit_deliver');
            } else {
                showError(data.msg);
            }
		   }, "json");
	});
});
</script>