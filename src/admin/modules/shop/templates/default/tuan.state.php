<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <a class="back" href="index.php?act=tuan_list&op=index" title="返回列表">
        <i class="fa fa-arrow-circle-o-left"></i>
      </a>
      <div class="subject">
        <h3>团长列表-详情</h3>
        <h5>查看社区团长资质</h5>
      </div>
    </div>
  </div>

  <form id="add_form" method="post" action="index.php?act=tuan_list&op=change_state">
    <input type="hidden" id="form_submit" name="form_submit" value="ok"/>
    <input type="hidden" id="id" name="id" value="<?php echo $output['info']['id'];?>"/>
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label><?php echo "姓名";?></label>
        </dt>
        <dd class="opt">
            <?php echo  $output['info']['name'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>手机号码</label>
        </dt>
        <dd class="opt">
         <?php echo  $output['info']['phone'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>身份证号</label>
        </dt>
        <dd class="opt">
            <?php echo  $output['info']['sn'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">
                <label><?php echo "身份证正面";?></label>
            </dt>
            <dd class="opt">
                <?php if (!empty($output['info']['sn_image2'])){?>
                    <img onload="javascript:DrawImage(this,160,160);" src="<?php echo UPLOAD_SITE_URL.DS.$output['info']['sn_image1'];?>"/>
                <?php }?>
                <span class="err"></span>
                <p class="notic"></p>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label><?php echo "身份证反面";?></label>
            </dt>
            <dd class="opt">
                <?php if (!empty($output['info']['sn_image2'])){?>
                    <img onload="javascript:DrawImage(this,160,160);" src="<?php echo UPLOAD_SITE_URL.DS.$output['info']['sn_image2'];?>"/>
                <?php }?>
                <span class="err"></span>
                <p class="notic"></p>
            </dd>
        </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo "类型";?></label>
        </dt>
        <dd class="opt">
            <?php echo  $output['info']['type'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo "店铺名称";?></label>
        </dt>
        <dd class="opt">
            <?php echo  $output['info']['store_name'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo "战队名称";?></label>
        </dt>
        <dd class="opt">
            <?php echo   $output['info']['zhandui'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">
                <label><?php echo "开户人名称";?></label>
            </dt>
            <dd class="opt">
                <?php echo   $output['info']['bank_ren'];?>
                <span class="err"></span>
                <p class="notic"></p>
            </dd>
        </dl>
      <dl class="row">
        <dt class="tit">
          <label>银行名称：</label>
        </dt>
        <dd class="opt"><?php echo  $output['info']['bank_name'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo "银行卡号";?></label>
        </dt>
        <dd class="opt">
            <?php echo  $output['bank_sn'];?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
<!--      <dl class="row">
        <dt class="tit">
          <label><?php /*echo "状态:";*/?></label>
        </dt>
        <dd class="opt">
            <?php /* echo $output['info']['state'];*/?>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>-->
<!--        --><?php //if($output['info']['state'] == '待审核' ){ ?>
<!--      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn">--><?php //echo "审核通过";?><!--</a></div>-->
<!--        --><?php //} ?>
    </div>
  </form>
</div>
<script>
//按钮先执行验证再提交表单
$(function(){
	$("#submitBtn").click(function(){
		$("#add_form").submit();
	});
});
</script>