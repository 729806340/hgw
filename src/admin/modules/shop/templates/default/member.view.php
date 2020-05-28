<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=member&op=member" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['member_index_manage']?> - 查看会员“<?php echo $output['member_array']['member_name'];?>”</h3>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
  </div>
  <form id="user_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="member_id" value="<?php echo $output['member_array']['member_id'];?>" />
    <input type="hidden" name="old_member_avatar" value="<?php echo $output['member_array']['member_avatar'];?>" />
    <input type="hidden" name="member_name" value="<?php echo $output['member_array']['member_name'];?>" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_index_name']?></label>
        </dt>
        <dd class="opt">
          <?php echo $output['member_array']['member_name'];?>
          &nbsp;&nbsp;
          <a target="_blank" href="/admin/modules/shop/index.php?act=order&buyer_name=<?php echo $output['member_array']['member_name'];?>">查看该会员订单</a>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="member_email"><em>*</em><?php echo $lang['member_index_email']?></label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_email'];?></label>
           <p class="notic">(邮箱绑定状态：<?php echo $output['member_array']['member_email_bind'] ? '已绑定' : '未绑定';?>)</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><em>*</em>手机号</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_mobile'];?></label>
           <p class="notic">(手机绑定状态：<?php echo $output['member_array']['member_mobile_bind'] ? '已绑定' : '未绑定';?>)</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>总下单次数</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['order_count'];?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>登录次数</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_login_num'];?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>注册时间</label>
        </dt>
        <dd class="opt">
          <label><?php echo date('Y-m-d H:i:s', $output['member_array']['member_time']);?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>注册ip</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_ip'];?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>当前登录时间</label>
        </dt>
        <dd class="opt">
          <label><?php echo date('Y-m-d H:i:s', $output['member_array']['member_login_time']);?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>上次登录时间</label>
        </dt>
        <dd class="opt">
          <label><?php echo date('Y-m-d H:i:s', $output['member_array']['member_old_login_time']);?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>当前登录ip</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_login_ip'];?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>上次登录ip</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['member_old_login_ip'];?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>qq账号相关信息</label>
        </dt>
        <dd class="opt">
          <label><?php echo var_export(unserialize($output['member_array']['member_qqinfo']), true);?></label>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>平台来源</label>
        </dt>
        <dd class="opt">
          <label><?php echo $output['member_array']['source'];?></label>
        </dd>
      </dl>

      <dl class="row">
        <dt class="tit">
          <label for="member_truename"><?php echo $lang['member_index_true_name']?></label>
        </dt>
        <dd class="opt">
          <?php echo $output['member_array']['member_truename'];?>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_edit_sex']?></label>
        </dt>
        <dd class="opt">
          <input type="radio" <?php if($output['member_array']['member_sex'] == 0){ ?>checked="checked"<?php } ?> value="0" name="member_sex" id="member_sex0">
          <label for="member_sex0"><?php echo $lang['member_edit_secret']?></label>
          <input type="radio" <?php if($output['member_array']['member_sex'] == 1){ ?>checked="checked"<?php } ?> value="1" name="member_sex" id="member_sex1">
          <label for="member_sex1"><?php echo $lang['member_edit_male']?></label>
          <input type="radio" <?php if($output['member_array']['member_sex'] == 2){ ?>checked="checked"<?php } ?> value="2" name="member_sex" id="member_sex2">
          <label for="member_sex2"><?php echo $lang['member_edit_female']?></label>
          <span class="err"></span> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label class="member_qq">QQ</label>
        </dt>
        <dd class="opt">
          <?php echo $output['member_array']['member_truename'];?>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label class="member_ww"><?php echo $lang['member_edit_wangwang']?></label>
        </dt>
        <dd class="opt">
          <?php echo $output['member_array']['member_truename'];?>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_edit_pic']?></label>
        </dt>
        <dd class="opt">
          <div class="input-file-show"><span class="show"><a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR; ?>/<?php echo $output['member_array']['member_avatar'];?>"><i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR; ?>/<?php echo $output['member_array']['member_avatar'];?>>')" id="view_img" onMouseOut="toolTip()"></i></a></span><span class="type-file-box">
          </div>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_index_inform'];?></label>
        </dt>
        <dd class="opt">
          <div class="onoff">
            <label for="inform_allow1" class="cb-enable <?php if($output['member_array']['inform_allow'] == '1'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_allow'];?></span></label>
            <label for="inform_allow2" class="cb-disable <?php if($output['member_array']['inform_allow'] == '2'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_deny'];?></span></label>
            <input id="inform_allow1" name="inform_allow" <?php if($output['member_array']['inform_allow'] == '1'){ ?>checked="checked"<?php } ?>  value="1" type="radio">
            <input id="inform_allow2" name="inform_allow" <?php if($output['member_array']['inform_allow'] == '2'){ ?>checked="checked"<?php } ?> value="2" type="radio">
          </div>
          <p class="notic">如果禁止该项则会员不能在商品详情页面进行举报。</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_edit_allowbuy']; ?></label>
        </dt>
        <dd class="opt">
          <div class="onoff">
            <label for="isbuy_1" class="cb-enable <?php if($output['member_array']['is_buy'] == '1'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_allow'];?></span></label>
            <label for="isbuy_2" class="cb-disable <?php if($output['member_array']['is_buy'] == '0'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_deny'];?></span></label>
            <input id="isbuy_1" name="isbuy" <?php if($output['member_array']['is_buy'] == '1'){ ?>checked="checked"<?php } ?>  value="1" type="radio">
            <input id="isbuy_2" name="isbuy" <?php if($output['member_array']['is_buy'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio">
          </div>
          <p class="notic"><?php echo $lang['member_edit_allowbuy_tip']; ?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_edit_allowtalk']; ?></label>
        </dt>
        <dd class="opt">
          <div class="onoff">
            <label for="allowtalk_1" class="cb-enable <?php if($output['member_array']['is_allowtalk'] == '1'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_allow'];?></span></label>
            <label for="allowtalk_2" class="cb-disable <?php if($output['member_array']['is_allowtalk'] == '0'){ ?>selected<?php } ?>" ><span><?php echo $lang['member_edit_deny'];?></span></label>
            <input id="allowtalk_1" name="allowtalk" <?php if($output['member_array']['is_allowtalk'] == '1'){ ?>checked="checked"<?php } ?>  value="1" type="radio">
            <input id="allowtalk_2" name="allowtalk" <?php if($output['member_array']['is_allowtalk'] == '0'){ ?>checked="checked"<?php } ?> value="0" type="radio">
          </div>
          <p class="notic"><?php echo $lang['member_edit_allowtalk_tip']; ?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_index_points']?></label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['member_points']; ?></strong>&nbsp;积分 </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>经验值</label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['member_exppoints']; ?></strong>&nbsp;经验点 </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_index_available'];?><?php echo $lang['member_index_prestore'];?></label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['available_predeposit']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['member_index_frozen'];?><?php echo $lang['member_index_prestore'];?></label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['freeze_predeposit']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?> </dd>
      </dl>
    
       <dl class="row">
        <dt class="tit">
          <label>可用充值卡余额</label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['available_rc_balance']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?> </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>冻结充值卡余额</label>
        </dt>
        <dd class="opt"><strong class="red"><?php echo $output['member_array']['freeze_rc_balance']; ?></strong>&nbsp;<?php echo $lang['currency_zh']; ?> </dd>
      </dl>

    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">
//裁剪图片后返回接收函数
function call_back(picname){
	$('#member_avatar').val(picname);
	$('#view_img').attr('src','<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/'+picname+'?'+Math.random())
	   .attr('onmouseover','toolTip("<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/'+picname+'?'+Math.random()+'>")');
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
						ajax_form('cutpic','<?php echo $lang['nc_cut'];?>','<?php echo ADMIN_SITE_URL?>/index.php?act=common&op=pic_cut&type=member&x=120&y=120&resize=1&ratio=1&filename=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_AVATAR;?>/avatar_<?php echo $_GET['member_id'];?>.jpg&url='+data.url,690);
					}else{
						alert(data.msg);
					}
					$('input[class="type-file-file"]').bind('change',uploadChange);
				},
				error: function (data, status, e)
				{
					alert('上传失败');$('input[class="type-file-file"]').bind('change',uploadChange);
				}
			}
		)
	};
// 点击查看图片
	$('.nyroModal').nyroModal();
	
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
                maxlength: 20,
                minlength: 6
            },
            member_email   : {
                required : true,
                email : true,
				remote   : {
                    url :'index.php?act=member&op=ajax&branch=check_email',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#member_email').val();
                        },
                        member_id : '<?php echo $output['member_array']['member_id'];?>'
                    }
                }
            }
        },
        messages : {
            member_passwd : {
                maxlength: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_password_tip']?>',
                minlength: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_password_tip']?>'
            },
            member_email  : {
                required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_email_null']?>',
                email   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_valid_email']?>',
				remote : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['member_edit_email_exists']?>'
            }
        }
    });
});
</script> 
