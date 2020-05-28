<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['refund_manage'];?> - 修改退款金额“退单编号：<?php echo $output['refund']['refund_sn']; ?>”</h3>
        <h5>客服人员可以修改退款金额</h5>
      </div>
    </div>
  </div>
  <form id="post_form" method="post" action="index.php?act=refund&op=edit_refund_amount&refund_id=<?php echo $output['refund']['refund_id']; ?>">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <div class="title">
        <h3>买家退款申请</h3>
      </div>
      <dl class="row">
        <dt class="tit">申请时间</dt>
        <dd class="opt"><?php echo date('Y-m-d H:i:s',$output['refund']['add_time']); ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">商品名称</dt>
        <dd class="opt">
        <?php if ($output['refund']['goods_id'] > 0) { ?>
        <a href="<?php echo urlShop('goods','index',array('goods_id'=> $output['refund']['goods_id']));?>" target="_blank"><?php echo $output['refund']['goods_name']; ?></a>
        <?php }else { ?>
        <?php echo $output['refund']['goods_name']; ?>
        <?php } ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['refund_order_refund'];?></dt>
        <dd class="opt"><?php echo ncPriceFormat($output['refund']['refund_amount']); ?></dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['refund_buyer_message'];?></dt>
        <dd class="opt"><?php echo $output['refund']['reason_info']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">退款说明</dt>
        <dd class="opt"><?php echo $output['refund']['buyer_message']; ?> </dd>
      </dl>
      <div class="title">
        <h3>客服退款处理</h3>
      </div>
        <dl class="row">
        <dt class="tit">修改退款金额</dt>
        <dd class="opt"><input type="text" name="refund_amount"> </dd>
      </dl>
        <dl class="row">
            <dt class="tit">备注信息</dt>
            <dd class="opt"><textarea id="log_msg" name="log_msg"></textarea> </dd>
        </dl>

      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/refund.js"></script>
<script type="text/javascript">
$(function(){
    $('.nyroModal').nyroModal();
	$("#submitBtn").click(function(){
        if($("#post_form").valid()){
            if(confirm('提交后将不能恢复，确认吗？')) $("#post_form").submit();
    	}
	});
});
</script>