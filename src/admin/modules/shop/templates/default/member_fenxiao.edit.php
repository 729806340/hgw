<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=member_fenxiao&op=index" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>渠道管理 - <?php echo $lang['nc_update']?>渠道</h3>
<!--        <h5>--><?php //echo $lang['member_shop_manage_subhead']?><!--</h5>-->
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
        <li>使用渠道拼音作为用户名登录</li>
        <li>修改密码请联系管理员</li>
        <li>
            <a target="_blank" href="/fenxiao/index.php/login/index.html" class="ncap-btn ncap-btn-green" >渠道管理登录</a>
        </li>
    </ul>
  </div>
  <form id="user_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
        <dl class="row">
            <dt class="tit">
                <label for="member_cn_code"><em>*</em>渠道名称</label>
            </dt>
            <dd class="opt">
                <input type="text" value="<?php echo $output['member_fenxiao']['member_cn_code'] ?>" readonly style="background:#E7E7E7 none;" id="member_cn_code" class="input-txt">
                <span class="err"></span>
                <p class="notic">如拼多多、人人店、有赞</p>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label for="member_en_code"><em>*</em>渠道拼音</label>
            </dt>
            <dd class="opt">
                <input type="text" value="<?php echo $output['member_fenxiao']['member_en_code'] ?>" readonly style="background:#E7E7E7 none;" id="member_en_code" class="input-txt">
                <span class="err"></span>
                <p class="notic">如pinduoduo、renrendian、youzan</p>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label><em>*</em>是否贴标</label>
            </dt>
            <dd class="opt">
                <input name="is_sign" <?php if ($output['member_fenxiao']['is_sign'] == 1) echo 'checked';?> type="radio" class="text" value="1" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">是&nbsp;
                <input name="is_sign" <?php if ($output['member_fenxiao']['is_sign'] == 0) echo 'checked';?> type="radio" class="text" value="0" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">否
                <span class="err"></span>
            </dd>
        </dl>

      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript">
//裁剪图片后返回接收函数
function call_back(picname){
	$('#member_avatar').val(picname);
	$('#view_img').attr('src','<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/'+picname)
	   .attr('onmouseover','toolTip("<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/'+picname+'>")');
}
$(function(){
	$('input[class="type-file-file"]').change(uploadChange);
	function uploadChange(){
		var filepath=$(this).val();
		var extStart=filepath.lastIndexOf(".");
		var ext=filepath.substring(extStart,filepath.length).toUpperCase();
		if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
			alert("file type error");
			$(this).attr('value','');
			return false;
		}
		if ($(this).val() == '') return false;
		ajaxFileUpload();
	}	
	function ajaxFileUpload()
	{
		$.ajaxFileUpload
		(
			{
				url : '<?php echo ADMIN_SITE_URL?>/index.php?act=common&op=pic_upload&form_submit=ok&uploadpath=<?php echo ATTACH_AVATAR;?>',
				secureuri:false,
				fileElementId:'_pic',
				dataType: 'json',
				success: function (data, status)
				{
					if (data.status == 1){
						ajax_form('cutpic','<?php echo $lang['nc_cut'];?>','<?php echo ADMIN_SITE_URL?>/index.php?act=common&op=pic_cut&type=member&x=120&y=120&resize=1&ratio=1&url='+data.url,690);
					}else{
						alert(data.msg);
					}
					$('input[class="type-file-file"]').bind('change',uploadChange);
				},
				error: function (data, status, e)
				{
					alert('上传失败');
					$('input[class="type-file-file"]').bind('change',uploadChange);
				}
			}
		)
	};
	//按钮先执行验证再提交表单
	$("#submitBtn").click(function(){
    if($("#user_form").valid()){
     $("#user_form").submit();
	}
	});
    $('#user_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            member_passwd: {
				required : true,
                maxlength: 20,
                minlength: 6
            }
        },
        messages : {
            member_passwd : {
				required : '<i class="fa fa-exclamation-circle"></i><?php echo '密码不能为空'; ?>',
                maxlength: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_password_tip']?>',
                minlength: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_password_tip']?>'
            }
        }
    });
});
</script> 
