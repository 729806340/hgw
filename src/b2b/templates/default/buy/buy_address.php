<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<!--<div class="ncc-receipt-info">
  <div class="ncc-receipt-info-title">
    <h3>收货人信息</h3>
    <a href="javascript:void(0)" nc_type="buy_edit" id="edit_reciver">[修改]</a></div>
  <div id="addr_list" class="ncc-candidate-items">
    <ul>
      <li><span class="true-name"><?php /*echo $output['address_info']['true_name'];*/?></span><span class="address"><?php /*echo $output['address_info']['area_info'],$output['address_info']['address'];*/?></span><span class="phone"><i class="icon-mobile-phone"></i><?php /*echo $output['address_info']['mob_phone'] ? $output['address_info']['mob_phone'] : $output['address_info']['tel_phone'];*/?></span></li>
    </ul>
  </div>
</div>-->
<script type="text/javascript">
//隐藏收货地址列表
function hideAddrList(addr_id,true_name,address,phone) {
    $('#edit_reciver').show();
	$("#address_id").val(addr_id);
	$("#addr_list").html('<ul><li><span class="true-name">'+true_name+'</span><span class="address">'+address+'</span><span class="phone"><i class="icon-mobile-phone"></i>'+phone+'</span></li></ul>');
	$('.current_box').removeClass('current_box');
	ableOtherEdit();
	$('#edit_payment').click();
}
//加载收货地址列表
$('#edit_reciver').on('click',function(){
    $(this).hide();
    disableOtherEdit('如需修改，请先保存收货人信息 ');
    $(this).parent().parent().addClass('current_box');
    var url = SITEURL+'/index.php?act=buy&op=load_addr';
    <?php if ($output['ifshow_chainpay']) { ?>
    url += '&ifchain=1';
    <?php } ?>
    $('#addr_list').load(url);
});


$(function(){
    <?php if (!empty($output['address_info']['address_id'])) {?>
    <?php } else {?>
    $('#edit_reciver').click();
    <?php }?>
});
</script>