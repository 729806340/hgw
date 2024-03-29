<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="ncm-flow-layout">
  <div class="ncm-flow-container">
    <div class="title">
      <h3>退款服务</h3>
    </div><div class="alert">
      <h4>提示：</h4>
      <ul>
        <li>1. 若提出申请后，商家拒绝退款或退货，可再次提交申请或选择<em>“商品投诉”</em>，请求商城客服人员介入。</li>
        <li>2. 成功完成退款/退货；经过商城审核后，会将退款金额以<em>“预存款”</em>的形式返还到您的余额账户中（充值卡部分只能退回到充值卡余额）。</li>
      </ul>
    </div>
    <div id="saleRefund" show_id="1">
      <div class="ncm-flow-step">
        <dl class="step-first current">
          <dt>买家申请退款</dt>
          <dd class="bg"></dd>
        </dl>
        <dl class="<?php echo $output['refund']['seller_time'] > 0 ? 'current':'';?>">
          <dt>商家处理退款申请</dt>
          <dd class="bg"> </dd>
        </dl>
        <dl class="<?php echo $output['refund']['admin_time'] > 0 ? 'current':'';?>">
          <dt>平台审核，退款完成</dt>
          <dd class="bg"> </dd>
        </dl>
      </div>
      <div class=" ncm-default-form">
      <h3>我的退款申请</h3>
          <dl>
              <dt>联系客服<?php echo $lang['nc_colon'];?></dt>
              <dd><div class="mall-msg">有疑问可咨询<a href="javascript:void(0);" id="chat_show_user" onclick="chat_show_user();"><i class="icon-comments-alt"></i>平台客服</a></div></dd>
          </dl>
        <dl>
          <dt><?php echo $lang['refund_order_refundsn'].$lang['nc_colon'];?></dt>
          <dd><?php echo $output['refund']['refund_sn']; ?> </dd>
        </dl>
        <dl>
          <dt>退款原因：</dt>
          <dd><?php echo $output['refund']['reason_info']; ?> </dd>
        </dl>
        <dl>
          <dt>退款金额：</dt>
          <dd><?php echo $lang['currency'];?><?php echo $output['refund']['refund_amount']; ?> </dd>
        </dl>
        <dl>
          <dt>退款说明：</dt>
          <dd><?php echo $output['refund']['buyer_message']; ?> </dd>
        </dl>
        <dl>
          <dt>凭证上传：</dt>
          <dd>
            <?php if (is_array($output['pic_list']) && !empty($output['pic_list'])) { ?>
            <ul class="ncm-evidence-pic">
              <?php foreach ($output['pic_list'] as $key => $val) { ?>
              <?php if(!empty($val)){ ?>
              <li><a href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>" nctype="nyroModal" rel="gal"> <img class="show_image" src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$val;?>"></a></li>
              <?php } ?>
              <?php } ?>
            </ul>
            <?php } ?>
          </dd>
        </dl>
        <h3>商家退款处理</h3>
        <dl>
          <dt><?php echo $lang['refund_state'].$lang['nc_colon'];?></dt>
          <dd><?php echo $output['state_array'][$output['refund']['seller_state']]; ?> </dd>
        </dl>
        <?php if ($output['refund']['seller_time'] > 0) { ?>
        <dl>
          <dt><?php echo $lang['refund_seller_message'].$lang['nc_colon'];?></dt>
          <dd><?php echo $output['refund']['seller_message']; ?> </dd>
        </dl>
        <?php } ?>
        <?php if ($output['refund']['seller_state'] == 2) { ?>
        <h3>商城退款审核</h3>
        <dl>
          <dt><?php echo '平台确认'.$lang['nc_colon'];?></dt>
          <dd><?php echo $output['admin_array'][$output['refund']['refund_state']]; ?> </dd>
        </dl>
        <?php } ?>
        <?php if ($output['refund']['admin_time'] > 0) { ?>
        <dl>
          <dt><?php echo '平台备注'.$lang['nc_colon'];?></dt>
          <dd><?php echo $output['refund']['admin_message']; ?> </dd>
        </dl>
        <?php if ($output['detail_array']['refund_state'] == 2) { ?>
        <h3>退款详细</h3>
        <dl>
          <dt>支付方式：</dt>
          <dd><?php echo orderPaymentName($output['detail_array']['refund_code']);?></dd>
        </dl>
        <dl>
          <dt>在线退款金额：</dt>
          <dd><?php echo ncPriceFormat($output['detail_array']['pay_amount']); ?> </dd>
        </dl>
        <dl>
          <dt>预存款金额：</dt>
          <dd><?php echo ncPriceFormat($output['detail_array']['pd_amount']); ?> </dd>
        </dl>
        <dl>
          <dt>充值卡金额：</dt>
          <dd><?php echo ncPriceFormat($output['detail_array']['rcb_amount']); ?> </dd>
        </dl>
        <?php } ?>
        <?php } ?><div class="bottom"><a href="javascript:history.go(-1);" class="ncbtn"><i class="icon-reply"></i>返回列表</a></div>
      </div>
    </div>
  </div>
  <?php require template('member/member_refund_right');?>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" ></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script>
$(document).ready(function(){
   $('a[nctype="nyroModal"]').nyroModal();
});
function chat_show_user() {
    var arr={ uid:"<?php echo $output['member_info']['member_id']?>",uname:"<?php echo $output['member_info']['member_name']?>"};
    NTKF.im_updatePageInfo(arr);
    NTKF.im_openInPageChat('hf_1000_1508484886546');
}
var NTKF_PARAM = {
    "siteid":"hf_1000" /*网站siteid*/,
    "settingid":"hf_1000_1508484886546" /*代码ID*/,
    "uid":"<?php echo $output['member_info']['member_id']?>" /*会员ID*/,
    "uname":""/*会员名*/,
    "userlevel": "0"/*会员等级*/,
    "erpparam": 'hango:refund:<?php echo intval($_GET['refund_id'])?>'
}
</script>
<script type="text/javascript" src="http://dl.ntalker.com/js/b2b/ntkfstat.js?siteid=hf_1000" charset="utf-8"></script>
