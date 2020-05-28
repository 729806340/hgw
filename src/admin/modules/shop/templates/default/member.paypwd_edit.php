<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=member&op=member" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['member_index_manage']?> - 会员“<?php echo $output['member_array']['member_name'];?>”设置支付密码</h3>
        <h5>客服给会员账号设置支付密码管理</h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>重新给用户设置支付密码。</li>
    </ul>
  </div>
  <form id="user_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="member_id" value="<?php echo $output['member_array']['member_id'];?>" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="pay_passwd"><em>*</em>设置支付密码</label>
        </dt>
        <dd class="opt">
          <input type="password" id="pay_passwd" name="pay_passwd" class="input-txt" value="" />
          <p class="notic">6-20位字符，可由英文、数字及标点符号组成。</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="re_pay_passwd"><em>*</em>确认支付密码</label>
        </dt>
        <dd class="opt">
          <input type="password" id="re_pay_passwd" name="re_pay_passwd" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">
    $("#submitBtn").click(function(){
        if($("#user_form").valid()){
            $("#user_form").submit();
        }
    });
</script>
