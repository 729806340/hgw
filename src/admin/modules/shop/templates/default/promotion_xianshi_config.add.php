<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=promotion_xianshi&op=config_xianshi_list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台限时活动</h3>
        <h5>平台限时活动设置与管理</h5>
      </div>
    </div>
  </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>
            <li>平台可以在此处添加平台活动</li>
            <li>新增的平台活动可以提供给商家进行活动绑定</li>
        </ul>
    </div>
  <form id="store_class_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_name"><em>*</em>活动名称</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="config_xianshi_name" id="config_xianshi_name" class="input-txt">
          <span class="err"></span>
          <p class="notic">活动名称将显示在限时折扣活动列表中，方便商家管理使用，最多可输入25个字符。</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_title">活动标题</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="config_xianshi_title" id="config_xianshi_title" class="input-txt">
          <span class="err"></span>
          <p class="notic">活动标题是商家对限时折扣活动的别名操作，请使用例如“新品打折”、“月末折扣”类短语表现，最多可输入10个字符； 非必填选项。</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_explain">活动描述</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="config_xianshi_explain" id="config_xianshi_explain" class="input-txt">
          <span class="err"></span>
          <p class="notic">活动描述是商家对限时折扣活动的补充说明文字，在商品详情页-优惠信息位置显示； 非必填选项，最多可输入30个字符。</p>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>开始时间</label>
        </dt>
        <dd class="opt">
            <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="" type="text" class="s-input-txt" />
          <span class="err"></span>
          <p class="notic">开始时间不能为空</p>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>结束时间</label>
        </dt>
        <dd class="opt">
            <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="" type="text" class="s-input-txt" />
          <span class="err"></span>
          <p class="notic">结束时间不能为空</p>
        </dd>
      </dl>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script>
//按钮先执行验证再提交表单
$(function() {
    $('#query_start_date').datetimepicker({
        controlType: 'select'
    });

    $('#query_end_date').datetimepicker({
        controlType: 'select'
    });
    $("#submitBtn").click(function() {
        if ($('#config_xianshi_name').val() == '') {
            alert('请输入活动名称！');return false;
        }
        $("#store_class_form").submit();
    });
});

</script>