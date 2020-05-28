<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="<?php echo urlAdminShop('sale_analysis', 'prodConf');?>" title="返回配置列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>编辑配置</h3>
      </div>
    </div>
  </div>
  <form id="refund_form" name="form"method="post" action="index.php?act=sale_analysis&op=edit"  enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $output['data']['id'];?>">
    <div class="ncap-form-default">
        <dl class="row">
            <dt class="tit">商品名</dt>
            <dd class="opt">
                <input type="text" name="prod_name" class="input-txt" value="<?php echo $output['data']['prod_name'];?>"/>&nbsp<font style="color: red">*</font>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">店铺名</dt>
            <dd class="opt">
                <input type="text" name="store_name" class="input-txt" value="<?php echo $output['data']['store_name'];?>"/>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">商品链接</dt>
            <dd class="opt">
                <input type="text" name="prod_url" class="input-txt" value="<?php echo $output['data']['prod_url'];?>"/>&nbsp<font style="color: red">*</font>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">商品名正则</dt>
            <dd class="opt">
                <input type="text" name="prod_rule" class="input-txt" value="<?php echo $output['data']['prod_rule'];?>"/>&nbsp<font style="color: red">已经提交过相关来源的配置后，此项可不用填</font>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">价格正则</dt>
            <dd class="opt">
                <input type="text" name="prize_rule" class="input-txt" value="<?php echo $output['data']['prize_rule'];?>"/>&nbsp<font style="color: red">已经提交过相关来源的配置后，此项可不用填</font>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">销量正则</dt>
            <dd class="opt">
                <input type="text" name="sales_rule" class="input-txt" value="<?php echo $output['data']['sales_rule'];?>"/>&nbsp<font style="color: red">已经提交过相关来源的配置后，此项可不用填</font>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">来源</dt>
            <dd class="opt">
                <select id="operatetype" name="prod_from">
                    <option value="拼多多" <?php if($output['data']['prod_from']=="拼多多") echo "selected";?>>拼多多</option>
                    <option value="天猫" <?php if($output['data']['prod_from']=="天猫") echo "selected";?>>天猫</option>
                    <option value="返利" <?php if($output['data']['prod_from']=="返利") echo "selected";?>>返利</option>
                    <option value="楚楚街" <?php if($output['data']['prod_from']=="楚楚街") echo "selected";?>>楚楚街</option>
                    <option value="云联美购" <?php if($output['data']['prod_from']=="云联美购") echo "selected";?>>云联美购</option>
                    <option value="萌店" <?php if($output['data']['prod_from']=="萌店") echo "selected";?>>萌店</option>
                    <option value="格格家" <?php if($output['data']['prod_from']=="格格家") echo "selected";?>>格格家</option>
                    <option value="会过" <?php if($output['data']['prod_from']=="会过") echo "selected";?>>会过</option>
                </select>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">状态</dt>
            <dd class="opt">
                <select id="operatetype" name="status">
                    <option value="1" <?php if($output['data']['status']) echo "selected"; ?>>开启</option>
                    <option value="0" <?php if(!$output['data']['status']) echo "selected"; ?>>关闭</option>
                </select>
            </dd>
      </dl>
      <!-- 商品 -->
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
