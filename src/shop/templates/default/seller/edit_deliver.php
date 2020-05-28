<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds" style=" min-height:240px;">

    <table class="ncsc-default-table">
      <thead>
        <tr>
          <th class="w80">公司名称</th>
          <th class="w80">物流单号</th>
        </tr>
      </thead>
      
      <tbody>
		<?php if (is_array($output['my_express_list']) && !empty($output['my_express_list'])){?>
			
			<tr class="bd-line">
				<td class="tc">
					<select name="express_id" id="express_id">
					<option value="0">请选择</option>
					<?php foreach ($output['my_express_list'] as $express_id) {?>
					<option value="<?php echo $express_id;?>" <?php if($output['order_info']['extend_order_common']['shipping_express_id'] == $express_id){?> selected="" <?php } ?> ><?php echo $output['express_list'][$express_id]['e_name']; ?></option>
					<?php }?>
					</select>
				</td>
				<td><input type="text" name="shipping_code" id="shipping_code"></td>
			</tr>
			<tr><td colspan="2"><a class="ncbtn" href="javascript:void(0);" >确认</a></td></tr>
		<?php }?>
      </tbody>
      
    </table>

  </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('.ncbtn').on('click',function(){
        var express_id = $("#express_id").val();
        var shipping_code = $("#shipping_code").val();
		if( express_id==0 || shipping_code=='' ){
			showError('不能为空');
		}

		$.post(
		"index.php?act=store_order&op=edit_deliver&order_id=<?php echo $output['order_id'];?>", 
		{express_id: express_id, shipping_code: shipping_code},
		   function(data){
			 if(data.status == 'true') {
                $('.e_name').html(data.e_name);
				$('.s_code').html(shipping_code);
                DialogManager.close('edit_deliver');
            } else {
                showError(data.msg);
            }
		   }, "json");
	});
});
</script>
