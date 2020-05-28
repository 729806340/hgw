<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['refund_manage'];?> - 处理退款“退单编号：<?php echo $output['refund']['refund_sn']; ?>”</h3>
        <h5><?php echo $lang['refund_manage_subhead'];?></h5>
      </div>
    </div>
  </div>
  <form id="post_form" method="post" action="index.php?act=refund&op=seller_edit&refund_id=<?php echo $output['refund']['refund_id']; ?>">
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
        <dd class="opt"><?php echo ncPriceFormat($output['refund']['refund_amount']); ?> 
            <span id="pay_amount">
            <?php if ($output['detail_array']['pay_time'] > 0) { ?>
            (已完成在线退款金额 <?php echo ncPriceFormat($output['detail_array']['pay_amount']); ?>)
            <?php } ?>
            </span>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['refund_buyer_message'];?></dt>
        <dd class="opt"><?php echo $output['refund']['reason_info']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">退款说明</dt>
        <dd class="opt"><?php echo $output['refund']['buyer_message']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">凭证上传</dt>
        <dd class="opt">
          <?php if (is_array($output['pic_list']) && !empty($output['pic_list'])) { ?>
          <?php foreach ($output['pic_list'] as $key => $val) { ?>
          <?php if(!empty($val)){ ?>
          <a href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>" class="nyroModal" rel="gal"> <img height="64" class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>"></a>
          <?php } ?>
          <?php } ?>
          <?php } ?>
        </dd>
      </dl>
      <div class="title">
        <h3>商家退款处理</h3>
      </div>
      <dl class="row">
        <dt class="tit"><em>*</em><?php echo $lang['refund_seller_confirm'].$lang['nc_colon'];?></dt>
        <dd class="opt"><label class="mr20">
              <input type="radio" class="radio vm" name="seller_state" value="2" />
              同意</label>
            <label>
              <input type="radio" class="radio vm" name="seller_state" value="3" />
              拒绝</label>
              <span class="error"></span> </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><em>*</em><?php echo $lang['refund_message'].$lang['nc_colon'];?></dt>
        <dd class="opt">
            <textarea id="seller_message" name="seller_message" class="tarea"></textarea>
            <span class="error"></span>
            <p class="hint">只能提交一次，请认真选择。</p>
          </dd>
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
	$("#wxpayBtn").click(function(){
	    var ajaxurl = '<?php echo ADMIN_SITE_URL;?>/index.php?act=refund&op=wxpay&refund_id=<?php echo $output['refund']['refund_id']; ?>';
		show_msg(ajaxurl);
	});
	$("#alipayBtn").click(function(){
	    var ajaxurl = '<?php echo ADMIN_SITE_URL;?>/index.php?act=refund&op=get_detail&refund_id=<?php echo $output['refund']['refund_id']; ?>';
		show_msg(ajaxurl);
	});
    $('#post_form').validate({
		errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            seller_message : {
                required   : true
            },
			seller_state : {
                required   : true
            }
        },
        messages : {
			seller_state  : {
                required : '不能为空'
            },
            seller_message  : {
                required : '<i class="fa fa-exclamation-circle"></i>商家处理意见不能为空'
            }
        }
    });
});
</script>