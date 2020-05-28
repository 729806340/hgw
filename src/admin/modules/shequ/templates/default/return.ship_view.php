<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['refund_manage'];?> - 设置退货物流信息“退单编号：<?php echo $output['return']['refund_sn']; ?>”</h3>
        <h5>客服人员可以修改退款金额</h5>
      </div>
    </div>
  </div>
  <form id="post_form" method="post" action="index.php?act=return&op=ship&return_id=<?php echo $output['return']['refund_id']; ?>">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <div class="title">
        <h3>填写物流信息</h3>
      </div>
        <dl class="row">
        <dt class="tit">物流公司</dt>
        <dd class="opt">
            <select name="express_id" class="valid">
                <option value="0">-请选择-</option>
                <?php foreach ($output['express_list'] as $v) { ?>
                    <option value="<?php echo $v['id']; ?>"><?php echo $v['e_name'];?></option>
                <?php } ?>
            </select>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">物流单号</dt>
            <dd class="opt"><input type="text" id="invoice_no" name="invoice_no"> </dd>
        </dl>
        <dl class="row">
            <dt class="tit">提示</dt>
            <dd class="opt">发货 5 天后，当商家选择未收到则要进行延迟时间操作；
                如果超过 7 天不处理按弃货处理，直接由管理员确认退款。</dd>
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