<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="<?php echo urlAdminShop('sale_analysis', 'marketList');?>" title="返回店铺配置列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>编辑配置</h3>
      </div>
    </div>
  </div>
  <form id="refund_form" name="form"method="post" action="index.php?act=sale_analysis&op=saveShopConf"  enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $output['data']['id'];?>">
    <div class="ncap-form-default">

        <dl class="row">
            <dt class="tit">店铺名</dt>
            <dd class="opt">
                <input type="text" value="<?php echo $output['data']['shop_name'];?>" name="shop_name" class="input-txt"/>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">店铺链接</dt>
            <dd class="opt">
                <input type="text" name="shop_url" value="<?php echo $output['data']['shop_url'];?>" class="input-txt"/>&nbsp<font style="color: red">*</font>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">来源</dt>
            <dd class="opt">
                <select name="channel_name">
                    <option value="拼多多" <?php if($output['data']['channel_name']=="拼多多") echo "selected";?>>拼多多</option>
                    <!--      <option value="天猫">天猫</option>
                          <option value="返利">返利</option>
                          <option value="楚楚街">楚楚街</option>
                          <option value="云联美购">云联美购</option>
                          <option value="萌店">萌店</option>
                          <option value="格格家">格格家</option>
                          <option value="会过">会过</option>-->
                </select>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">状态</dt>
            <dd class="opt">
                <select name="status">
                    <option value="2" <?php if($output['data']['status'] == 2) echo "selected"; ?>>开启</option>
                    <option value="1" <?php if($output['data']['status'] == 1) echo "selected"; ?>>关闭</option>
                </select>
            </dd>
        </dl>

      </div>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn" onclick="document.form.submit()"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
  </form>
</div>
<style>
    .bot{
        margin-left: 15%;
    }
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script>
</script> 
