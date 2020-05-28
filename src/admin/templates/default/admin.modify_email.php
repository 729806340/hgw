<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.validation.min.js" type="text/javascript"></script>
<div class="ncap-form-default">
  <form id="admin_form" method="post" action='index.php?act=index&op=modify_email' name="adminForm">
    <input type="hidden" name="form_submit" value="ok" />
    <dl class="row">
      <dt class="tit"><label for="email">Email</label></dt>
      <dd class="opt"><input id="email" name="admin_email" class="txt" type="text" value="<?php echo $output['admin']['admin_email']?>">
          <span class="err"></span></dd>
    </dl>
    <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green"  id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></div>
  </form>
</div>
<script>
//按钮先执行验证再提交表单
$(function(){
    $("#submitBtn").click(function(){
        if($("#admin_form").valid()){
            $("#admin_form").submit();
    	}
	});

	$("#admin_form").validate({
		errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
        	email : {
        		required : false
            },
        },
        messages : {
        	email : {
        		required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['admin_add_password_null'];?>'
            },
        }
	});
});
</script> 