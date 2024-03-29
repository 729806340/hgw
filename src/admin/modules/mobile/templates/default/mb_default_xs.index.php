<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h5>默认多列秒杀头部图片上传</h5>
      </div>
    </div>
  </div>
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>默认头部图片</li>
    </ul>
  </div>
  <form id="link_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="">默认头部图片</label>
        </dt>
        <dd class="opt">
          <div class="input-file-show"><span class="show"> <a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.DS.ATTACH_MOBILE.'/'.$output['default_xianshi_pic'];?>"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.DS.ATTACH_MOBILE.'/'.$output['default_xianshi_pic'];?>>')" onMouseOut="toolTip()"></i></a> </span> <span class="type-file-box">
            <input name="default_xianshi_pic" type="file" class="type-file-file" id="mobile_wx" size="30" hidefocus="true">
            </span></div>
          <span class="err"></span>
          <p class="notic">建议大小750px*280px</p>
        </dd>
      </dl>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script>
$(function(){
	//图片上传
 	var textButton="<input type='text' name='textfield' id='textfield1' class='type-file-text' /><input type='button' name='button' id='button1' value='选择上传...' class='type-file-button' />"
	$(textButton).insertBefore("#mobile_wx");
	$("#mobile_wx").change(function(){
	$("#textfield1").val($("#mobile_wx").val());
});	
//按钮先执行验证再提交表单	
	$("#submitBtn").click(function(){
    if($("#link_form").valid()){
        <?php if ($output['default_xianshi_pic']) { ?>
        if ($('#mobile_wx').val() == '') {
        	if(!confirm('您未选择要上传的图片，继续保存会清除现有图片，确认继续提交吗')){
            	return false;
        	}
        }
        <?php } ?>
        $("#link_form").submit();
	}
	});

	$('#link_form').validate({
        errorPlacement: function(error, element){
        	var error_td = element.parents('dl').find('span.err');
            error_td.append(error);
        },
        rules : {
        	mobile_wx : {
                accept : 'png|jpe?g|gif'
            }
        },
        messages : {
        	mobile_wx : {
                accept   : '<i class="fa fa-exclamation-circle"></i>图片格式错误'
            }
        }
    });
});
</script> 
