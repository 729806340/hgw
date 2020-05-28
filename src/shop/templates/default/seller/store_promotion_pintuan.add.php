<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <?php if(empty($output['pintuan_info'])) { ?>
    <form id="add_form" action="index.php?act=store_promotion_pintuan&op=pintuan_save" method="post">
    <?php } else { ?>
    <form id="add_form" action="index.php?act=store_promotion_pintuan&op=pintuan_edit_save" method="post">
        <input type="hidden" name="pintuan_id" value="<?php echo $output['pintuan_info']['pintuan_id'];?>">
    <?php } ?>
    <dl>
      <dt><i class="required">*</i>活动名称<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="pintuan_name" name="pintuan_name" type="text" <?php if (isset($output['pintuan_info']['config_id']) && $output['pintuan_info']['config_id'] > 0) {?> readonly style="background:#E7E7E7 none;" <?php } ?> maxlength="25" class="text w400" value="<?php echo empty($output['pintuan_info'])?'':$output['pintuan_info']['pintuan_name'];?>"/>
          <span></span>
        <p class="hint">活动名称最多为25个字符</p>
      </dd>
    </dl>
    <dl>
      <dt>活动标题<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="pintuan_title" name="pintuan_title" type="text"  maxlength="10" <?php if (isset($output['pintuan_info']['config_id']) && $output['pintuan_info']['config_id'] > 0) {?> readonly style="background:#E7E7E7 none;" <?php } ?> class="text w200" value="<?php echo empty($output['pintuan_info'])?'':$output['pintuan_info']['pintuan_title'];?>"/>
          <span></span>
        <p class="hint">活动标题是商家对拼团活动的别名操作，请使用例如“新品拼团”、“月末拼团”类短语表现，最多可输入10个字符；<br/>非必填选项。</p>
      </dd>
    </dl>
    <dl>
      <dt>活动描述<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="pintuan_description" name="pintuan_description" type="text"  maxlength="30" class="text w400" <?php if (isset($output['pintuan_info']['config_id']) && $output['pintuan_info']['config_id'] > 0) {?> readonly style="background:#E7E7E7 none;" <?php } ?> value="<?php echo empty($output['pintuan_info'])?'':$output['pintuan_info']['pintuan_description'];?>"/>
          <span></span>
        <p class="hint">活动描述是商家对拼团活动的补充说明文字，在商品详情页-优惠信息位置显示；<br/>非必填选项，最多可输入30个字符。</p>
      </dd>
    </dl>
    <?php if(empty($output['pintuan_info'])) { ?>
    <dl>
      <dt><i class="required">*</i>开始时间<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="start_time" name="start_time" type="text" class="text w130"  /><em class="add-on"><i class="icon-calendar"></i></em><span></span>
        <p class="hint">
        </p>
      </dd>
    </dl>
    <dl>
      <dt><i class="required">*</i>结束时间<?php echo $lang['nc_colon'];?></dt>
      <dd>
          <input id="end_time" name="end_time" type="text" class="text w130"/><em class="add-on"><i class="icon-calendar"></i></em><span></span>
        <p class="hint">
        </p>
      </dd>
    </dl>
    <?php } ?>
        <dl>
            <dt><i class="required">*</i>成团时限<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="limit_time" name="limit_time" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':intval($output['pintuan_info']['limit_time']/3600);?>"/><span></span>
                <p class="hint">参加活动的成团时限，默认为1(单位：小时)</p>
            </dd>
        </dl>
    <dl>
      <dt><i class="required">*</i>成团人数<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input id="limit_user" name="limit_user" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':$output['pintuan_info']['limit_user'];?>"/><span></span>
        <p class="hint">参加活动的成团人数，默认为2</p>
      </dd>
    </dl>
        <dl>
      <dt><i class="required">*</i>凑团人数<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input id="minimum_user" name="minimum_user" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':$output['pintuan_info']['minimum_user'];?>"/><span></span>
        <p class="hint">到期时达到此人数系统自动凑人，默认为1</p>
      </dd>
    </dl>
        <dl>
      <dt><i class="required">*</i>购买下限<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input id="limit_floor" name="limit_floor" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':$output['pintuan_info']['limit_floor'];?>"/><span></span>
        <p class="hint">参加活动的最低购买数量，默认为1</p>
      </dd>
    </dl>
        <dl>
      <dt><i class="required">*</i>购买上限<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input id="limit_ceilling" name="limit_ceilling" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':$output['pintuan_info']['limit_ceilling'];?>"/><span></span>
        <p class="hint">参加活动的最高购买数量，默认为0（0或者低于下限不限制）</p>
      </dd>
    </dl>
        <dl>
      <dt><i class="required">*</i>累计购买上限<?php echo $lang['nc_colon'];?></dt>
      <dd>
        <input id="limit_total" name="limit_total" type="text" class="text w130" value="<?php echo empty($output['pintuan_info'])?'1':$output['pintuan_info']['limit_total'];?>"/><span></span>
        <p class="hint">参加活动的最高购买数量，默认为0（0或者低于下限不限制）</p>
      </dd>
    </dl>
    <div class="bottom">
      <label class="submit-border"><input id="submit_button" type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"></label>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script>
$(document).ready(function(){
    <?php if(empty($output['pintuan_info'])) { ?>
    $('#start_time').datetimepicker({
        controlType: 'select'
    });

    $('#end_time').datetimepicker({
        controlType: 'select'
    });
    <?php } ?>

    jQuery.validator.methods.greaterThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };
    jQuery.validator.methods.lessThanDate = function(value, element, param) {
        var date1 = new Date(Date.parse(param.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 > date2;
    };
    jQuery.validator.methods.greaterThanStartDate = function(value, element) {
        var start_date = $("#start_time").val();
        var date1 = new Date(Date.parse(start_date.replace(/-/g, "/")));
        var date2 = new Date(Date.parse(value.replace(/-/g, "/")));
        return date1 < date2;
    };

    //页面输入内容验证
    $("#add_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd').children('span');
            error_td.append(error);
        },
        onfocusout: false,
    	submitHandler:function(form){
    		ajaxpost('add_form', '', '', 'onerror');
    	},
        rules : {
            pintuan_name : {
                required : true
            },
            start_time : {
                required : true,
            },
            end_time : {
                required : true,
            },
            limit_floor: {
                required: true,
                digits: true,
                min: 1
            }
        },
        messages : {
            pintuan_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['pintuan_name_error'];?>'
            },
            start_time : {
            required : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['pintuan_add_start_time_explain'],date('Y-m-d H:i',$output['current_pintuan_quota']['start_time']));?>',
                greaterThanDate : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['pintuan_add_start_time_explain'],date('Y-m-d H:i',$output['current_pintuan_quota']['start_time']));?>'
            },
            end_time : {
            required : '<i class="icon-exclamation-sign"></i><?php echo sprintf($lang['pintuan_add_end_time_explain'],date('Y-m-d H:i',$output['current_pintuan_quota']['end_time']));?>',
                greaterThanStartDate : '<i class="icon-exclamation-sign"></i><?php echo $lang['greater_than_start_time'];?>'
            },
            limit_floor: {
                required : '<i class="icon-exclamation-sign"></i>购买下限不能为空',
                digits: '<i class="icon-exclamation-sign"></i>购买下限必须为数字',
                min: '<i class="icon-exclamation-sign"></i>购买下限不能小于1'
            }
        }
    });
});
</script>
