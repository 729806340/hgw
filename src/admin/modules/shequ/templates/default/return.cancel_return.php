<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['refund_manage'];?> - 改为商家拒绝“退单编号：<?php echo $output['return']['refund_sn']; ?>”</h3>
        <h5>客服人员可以撤销退货</h5>
      </div>
    </div>
  </div>
  <form id="post_form" method="post" action="index.php?act=return&op=cancel_return&refund_id=<?php echo $output['return']['refund_id']; ?>">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <div class="title">
        <h3>买家退货申请</h3>
      </div>
      <dl class="row">
        <dt class="tit">申请时间</dt>
        <dd class="opt"><?php echo date('Y-m-d H:i:s',$output['return']['add_time']); ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">商品名称</dt>
        <dd class="opt">
        <?php if ($output['return']['goods_id'] > 0) { ?>
        <a href="<?php echo urlShop('goods','index',array('goods_id'=> $output['return']['goods_id']));?>" target="_blank"><?php echo $output['return']['goods_name']; ?></a>
        <?php }else { ?>
        <?php echo $output['return']['goods_name']; ?>
        <?php } ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['refund_order_refund'];?></dt>
        <dd class="opt"><?php echo ncPriceFormat($output['return']['refund_amount']); ?></dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['refund_buyer_message'];?></dt>
        <dd class="opt"><?php echo $output['return']['reason_info']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">退款说明</dt>
        <dd class="opt"><?php echo $output['return']['buyer_message']; ?> </dd>
      </dl>
      <div class="title">
        <h3>客服退货处理</h3>
      </div>
        <dl class="row">
            <dt class="tit">备注信息</dt>
            <dd class="opt"><textarea id="log_msg" name="log_msg"></textarea> </dd>
        </dl>
        <dl class="row">
        <dt class="tit">提示</dt>
        <dd class="opt">点击确认提交按钮，退款改为商家拒绝，并把订单状态改变。 </dd>
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