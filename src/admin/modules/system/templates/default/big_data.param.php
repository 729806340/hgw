<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>大屏数据设置</h3>
        <h5>大屏数据设置</h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>设置用于蔡甸一楼大屏</li>
    </ul>
  </div>
  <form id="form" method="post" enctype="multipart/form-data" name="settingForm">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="big_data_rate">基础数据比例</label>
        </dt>
        <dd class="opt">
         实际数据 x <input id="big_data_rate" name="big_data_rate" type="text" class="input-txt" style="width:30px !important;" value="<?php echo $output['bigdata_setting']['big_data_rate']>1 ? $output['bigdata_setting']['big_data_rate'] : '1' ;?>"/>
          <p class="notic">数据按上面设置的比例调整，为1时显示实际数据</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="big_data_rate_sale">30天商品销售数据比例</label>
        </dt>
        <dd class="opt">
         实际数据 x <input id="big_data_rate_sale" name="big_data_rate_sale" type="text" class="input-txt" style="width:30px !important;" value="<?php echo $output['bigdata_setting']['big_data_rate_sale']>1 ? $output['bigdata_setting']['big_data_rate_sale'] : '1' ;?>"/>
          <p class="notic">数据按上面设置的比例调整，为1时显示实际数据</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="big_data_rate_logistics">物流发货数据比例</label>
        </dt>
        <dd class="opt">
         实际数据 x <input id="big_data_rate_logistics" name="big_data_rate_logistics" type="text" class="input-txt" style="width:30px !important;" value="<?php echo $output['bigdata_setting']['big_data_rate_logistics']>1 ? $output['bigdata_setting']['big_data_rate_logistics'] : '1' ;?>"/>
          <p class="notic">数据按上面设置的比例调整，为1时显示实际数据</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="big_data_rate_channel">渠道数据比例</label>
        </dt>
        <dd class="opt">
         实际数据 x <input id="big_data_rate_channel" name="big_data_rate_channel" type="text" class="input-txt" style="width:30px !important;" value="<?php echo $output['bigdata_setting']['big_data_rate_channel']>1 ? $output['bigdata_setting']['big_data_rate_channel'] : '1' ;?>"/>
          <p class="notic">数据按上面设置的比例调整，为1时显示实际数据</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="big_data_rate_province">省份数据比例</label>
        </dt>
        <dd class="opt">
         实际数据 x <input id="big_data_rate_province" name="big_data_rate_province" type="text" class="input-txt" style="width:30px !important;" value="<?php echo $output['bigdata_setting']['big_data_rate_province']>1 ? $output['bigdata_setting']['big_data_rate_province'] : '1' ;?>"/>
          <p class="notic">数据按上面设置的比例调整，为1时显示实际数据</p>
        </dd>
      </dl>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" onclick="document.settingForm.submit()"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript">
//<!CDATA[
$(function(){
	$('#form').validate({
		rules : {
		    big_data_rate : {
				number : true,
				maxlength : 4
			},
		},
		messages : {
		    big_data_rate : {
				number   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['image_max_size_only_num'];?>',
				maxlength: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['image_max_size_c_num'];?>'
			},
			image_allow_ext : {
				required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['image_allow_ext_not_null'];?>'
			}
		}
	});
});
//]]>
</script>