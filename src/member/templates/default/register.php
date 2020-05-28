<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="nc-register-bg">
  <div class="nc-register-box">
    <div class="nc-register-layout">
      <div class="left">
        <div class="nc-register-mode">
          <ul class="tabs-nav">
            <li><a href="#default">账号注册<i></i></a></li>
            <?php if (C('sms_register') == 1){?>
            <li><a href="#mobile">手机注册<i></i></a></li>
            <?php } ?>
          </ul>
          <div id="tabs_container" class="tabs-container">
            <div id="default" class="tabs-content">
              <form id="register_form" method="post" class="nc-login-form" action="<?php echo urlLogin('login', 'usersave');?>">
			  <input type="hidden" id="rec_id" name="rec_id" class="text" value="<?php echo $_GET['rec'];?>"/>
                <?php Security::getToken();?>
                <dl>
                  <dt><?php echo $lang['login_register_username'];?>：</dt>
                  <dd>
                    <input type="text" id="user_name" name="user_name" class="text" tipMsg="<?php echo $lang['login_register_username_to_login'];?>"/>
                  </dd>
                </dl>
                <dl>
                  <dt><?php echo $lang['login_register_pwd'];?>：</dt>
                  <dd>
                    <input type="password" id="password" name="password" class="text" tipMsg="<?php echo $lang['login_register_password_to_login'];?>"/>
                  </dd>
                </dl>
                <dl>
                  <dt><?php echo $lang['login_register_ensure_password'];?>：</dt>
                  <dd>
                    <input type="password" id="password_confirm" name="password_confirm" class="text" tipMsg="<?php echo $lang['login_register_input_password_again'];?>"/>
                  </dd>
                </dl>
                <dl class="mt15">
                  <dt><?php echo $lang['login_register_email'];?>：</dt>
                  <dd>
                    <input type="text" id="email" name="email" class="text" tipMsg="<?php echo $lang['login_register_input_valid_email'];?>"/>
                  </dd>
                </dl>
                <?php if(C('captcha_status_register') == '1') { ?>
                <div class="code-div mt15">
                  <dl>
                    <dt><?php echo $lang['login_register_code'];?>：</dt>
                    <dd>
                      <input type="text" id="captcha" name="captcha" class="text w80" size="10" tipMsg="<?php echo $lang['login_register_input_code'];?>" />
                    </dd>
                  </dl>
                  <span><img src="index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>" name="codeimage" id="codeimage"/> <a class="makecode" href="javascript:void(0)" onclick="javascript:document.getElementById('codeimage').src='index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>&t=' + Math.random();"><?php echo $lang['login_password_change_code']; ?></a></span></div>
                <?php } ?>
                <dl class="clause-div">
                  <dd>
                    <input name="agree" type="checkbox" class="checkbox" id="clause" value="1" checked="checked" />
                    <?php echo $lang['login_register_agreed'];?><a href="<?php echo urlShop('document', 'index',array('code'=>'agreement'));?>" target="_blank" class="agreement" title="<?php echo $lang['login_register_agreed'];?>"><?php echo $lang['login_register_agreement'];?></a></dd>
                </dl>
                <div class="submit-div">
                  <input type="submit" id="Submit" value="<?php echo $lang['login_register_regist_now'];?>" class="submit"/>
                </div>
                <input type="hidden" value="<?php echo $_GET['ref_url']?>" name="ref_url">
                <input name="nchash" type="hidden" value="<?php echo getRegisterNchash();?>" />
                <input type="hidden" name="form_submit" value="ok" />
              </form>
            </div>
            <?php if (C('sms_register') == 1){?>
            <div id="mobile" class="tabs-content">
              <form id="post_form" method="post" class="nc-login-form">
                <?php Security::getToken();?>
                <input type="hidden" name="form_submit" value="ok" />
                <input name="nchash" type="hidden" value="<?php echo getRegisterNchash();?>" />
                <dl>
                  <dt>手机号：</dt>
                  <dd>
                    <input type="text" class="text" tipMsg="请输入手机号码" autocomplete="off" value="" name="phone" id="phone"  >
                  </dd>
                </dl>
                  <dl id="captcha-geetest">
                  <dt>验证码：</dt>
                  <dd style="padding: 3px 0">
                      <div id="embed-captcha"></div>
                      <p id="wait" class="show">正在加载验证码......</p>
                      <p id="notice" class="none">请先完成验证</p>
                      <input type="hidden" name="geetest_challenge" id="geetest_challenge">
                      <input type="hidden" name="geetest_validate" id="geetest_validate">
                      <input type="hidden" name="geetest_seccode" id="geetest_seccode">
                  </dd>
                </dl>
                <div class="code-div none" id="captcha-image">
                  <dl>
                    <dt><?php echo $lang['login_register_code'];?>：</dt>
                    <dd>
                      <input type="text" name="captcha" class="text w100" id="image_captcha" size="10" tipMsg="<?php echo $lang['login_register_input_code'];?>" />
                    </dd>
                  </dl>
                  <span><img src="index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>" title="<?php echo $lang['login_index_change_checkcode'];?>" name="codeimage" id="sms_codeimage"><a class="makecode" href="javascript:void(0);" onclick="javascript:document.getElementById('sms_codeimage').src='index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>&t=' + Math.random();"><?php echo $lang['login_password_change_code']; ?></a></span> </div>
                <div class="tiptext" id="sms_text">确保上方验证码输入正确，点击<span><a href="javascript:void(0);" onclick="get_sms_captcha('1','<?php echo getRegisterNchash();?>')"><i class="icon-mobile-phone"></i>发送短信验证</a></span>，并将您手机短信所接收到的“6位动态码”输入到下方短信验证，再提交下一步。</div>
                <dl>
                  <dt>短信验证：</dt>
                  <dd>
                    <input type="text" name="sms_captcha" autocomplete="off" tipMsg="输入6位短信验证码" class="text" id="sms_captcha" size="15" />
                  </dd>
                </dl>
                <div class="submit-div">
                  <input type="button" id="submitBtn" class="submit" value="下一步">
                </div>
              </form>
              <form style="display: none;" id="register_sms_form" class="nc-login-form" method="post" action="<?php echo urlLogin('connect_sms', 'register');?>">
                <input type="hidden" name="form_submit" value="ok" />
                <input type="hidden" name="register_captcha" id="register_sms_captcha" value="" />
                <input type="hidden" name="register_phone" id="register_phone" value="" />
                <dl>
                  <dt><?php echo $lang['login_register_username'];?>：</dt>
                  <dd>
                    <input type="text" id="member_name" name="member_name" class="text w150" value=""/>
                  </dd>
                  <span class="note">系统生成随机用户名，可选择默认或自行修改一次。</span>
                </dl>
                <dl>
                  <dt><?php echo $lang['login_register_pwd'];?>：</dt>
                  <dd>
                    <input type="text" id="sms_password" name="password" class="text w150" value=""/>
                  </dd>
                  <span class="note">系统生成随机密码，请牢记或修改为自设密码。</span>
                </dl>
                <dl class="mt15">
                  <dt><?php echo $lang['login_register_email'];?>：</dt>
                  <dd>
                    <input type="text" id="sms_email" name="email" class="text" value="" tipMsg="<?php echo $lang['login_register_input_valid_email'];?>" />
                  </dd>
                </dl>
                <dl class="clause-div">
                  <dd>
                    <input name="agree" type="checkbox" class="checkbox" id="sms_clause" value="1" checked="checked" />
                    <?php echo $lang['login_register_agreed'];?><a href="<?php echo urlShop('document', 'index',array('code'=>'agreement'));?>" target="_blank" class="agreement" title="<?php echo $lang['login_register_agreed'];?>"><?php echo $lang['login_register_agreement'];?></a></dd>
                </dl>
                <div class="submit-div">
                  <input type="submit" value="提交注册" class="submit" title="提交注册" />
                </div>
              </form>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="right">
        <?php if (C('qq_isuse') == 1 || C('sina_isuse') == 1 || C('weixin_isuse') == 1){?>
        <div class="api-login">
          <h4>使用合作网站账号直接登录</h4>
          <?php if (C('qq_isuse') == 1){?>
          <a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=toqq" title="QQ账号登录" class="qq"><i></i></a>
          <?php } ?>
          <?php if (C('sina_isuse') == 1){?>
          <a href="<?php echo MEMBER_SITE_URL;?>/api.php?act=tosina" title="<?php echo $lang['nc_otherlogintip_sina']; ?>" class="sina"><i></i></a>
          <?php } ?>
          <?php if (C('weixin_isuse') == 1){?>
          <a href="javascript:void(0);" onclick="ajax_form('weixin_form', '微信账号登录', '<?php echo urlLogin('connect_wx', 'index');?>', 360);" title="微信账号登录" class="wx"><i></i></a>
          <?php } ?>
        </div>
        <?php } ?>
        <div class="reister-after">
          <h4><?php echo $lang['login_register_after_regist'];?></h4>
          <ol>
            <li class="ico01"><i></i><?php echo $lang['login_register_buy_info'];?></li>
            <li class="ico02"><i></i><?php echo $lang['login_register_collect_info'];?></li>
            <li class="ico03"><i></i><?php echo $lang['login_register_honest_info'];?></li>
            <li class="ico04"><i></i><?php echo $lang['login_register_openstore_info'];?></li>
            <li class="ico05"><i></i><?php echo $lang['login_register_talk_info'];?></li>
            <li class="ico06"><i></i><?php echo $lang['login_register_sns_info'];?></li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
	//初始化Input的灰色提示信息  
	$('input[tipMsg]').inputTipText({pwd:'password,password_confirm'});
	//注册方式切换
	$('.nc-register-mode').tabulous({
		 //动画缩放渐变效果effect: 'scale'
		 effect: 'slideLeft'//动画左侧滑入效果
		//动画下方滑入效果 effect: 'scaleUp'
		//动画反转效果 effect: 'flip'
	});
	var div_form = '#default';
	$(".nc-register-mode .tabs-nav li a").click(function(){
        if($(this).attr("href") !== div_form){
            div_form = $(this).attr('href');
            $(""+div_form).find(".makecode").trigger("click");
    	}
	});
	
//注册表单验证
    $("#register_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
            error_td.append(error);
            element.parents('dl:first').addClass('error');
        },
        success: function(label) {
            label.parents('dl:first').removeClass('error').find('label').remove();
        },
    	submitHandler:function(form){
    	    ajaxpost('register_form', '', '', 'onerror');
    	},
        onkeyup: false,
        rules : {
            user_name : {
                required : true,
                lettersmin : true,
                lettersmax : true,
                letters_name : true,
                remote   : {
                    url :'index.php?act=login&op=check_member&column=ok',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#user_name').val();
                        }
                    }
                }
            },
            password : {
                required : true,
                minlength: 6,
				maxlength: 20
            },
            password_confirm : {
                required : true,
                equalTo  : '#password'
            },
            email : {
                required : true,
                email    : true,
                remote   : {
                    url : 'index.php?act=login&op=check_email',
                    type: 'get',
                    data:{
                        email : function(){
                            return $('#email').val();
                        }
                    }
                }
            },
			<?php if(C('captcha_status_register') == '1') { ?>
            captcha : {
                required : true,
                remote   : {
                    url : 'index.php?act=seccode&op=check&nchash=<?php echo getRegisterNchash();?>',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#captcha').val();
                        }
                    },
                    complete: function(data) {
                        if(data.responseText == 'false') {
                        	document.getElementById('codeimage').src='index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>&t=' + Math.random();
                        }
                    }
                }
            },
			<?php } ?>
            agree : {
                required : true
            }
        },
        messages : {
            user_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_username'];?>',
                lettersmin : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_range'];?>',
                lettersmax : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_range'];?>',
				letters_name: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_lettersonly'];?>',
				remote	 : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_exists'];?>'
            },
            password  : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_password'];?>',
                minlength: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_password_range'];?>',
				maxlength: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_password_range'];?>'
            },
            password_confirm : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_password_again'];?>',
                equalTo  : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_password_not_same'];?>'
            },
            email : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_email'];?>',
                email    : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_invalid_email'];?>',
				remote	 : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_email_exists'];?>'
            },
			<?php if(C('captcha_status_register') == '1') { ?>
            captcha : {
                required : '<i class="icon-remove-circle" title="<?php echo $lang['login_register_input_text_in_image'];?>"></i>',
				remote	 : '<i class="icon-remove-circle" title="<?php echo $lang['login_register_code_wrong'];?>"></i>'
            },
			<?php } ?>
            agree : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_must_agree'];?>'
            }
        }
    });
});
</script>
<?php if (C('sms_register') == 1){?>
<script type="text/javascript" src="<?php echo LOGIN_RESOURCE_SITE_URL;?>/js/connect_sms.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/gt.js" charset="utf-8"></script>
    <script>
        var handlerEmbed = function (captchaObj) {
            $("#embed-submit").click(function (e) {
                var validate = captchaObj.getValidate();
                if (!validate) {
                    $("#notice")[0].className = "show";
                    setTimeout(function () {
                        $("#notice")[0].className = "none";
                    }, 2000);
                    e.preventDefault();
                }
            });
            // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
            captchaObj.appendTo("#embed-captcha");
            captchaObj.onReady(function () {
                $("#wait")[0].className = "none";
            });
            captchaObj.onSuccess(function(){
                var result = captchaObj.getValidate();
                console.log(result);
                $("#geetest_challenge").val(result.geetest_challenge);
                $("#geetest_validate").val(result.geetest_validate);
                $("#geetest_seccode").val(result.geetest_challenge);
                //your code
            })
            // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
        };
        $.ajax({
            // 获取id，challenge，success（是否启用failback）
            url: "?act=login&op=geetest&t=" + (new Date()).getTime(), // 加随机数防止缓存
            type: "get",
            dataType: "json",
            success: function (data) {
                console.log(data);
                if(data.success){
                    // 使用initGeetest接口
                    // 参数1：配置参数
                    // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
                    initGeetest({
                        gt: data.gt,
                        challenge: data.challenge,
                        new_captcha: data.new_captcha,
                        product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                        offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
                        // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
                    }, handlerEmbed);
                }else{
                    $("#captcha-geetest").hide();
                    $("#captcha-image").show();
                }
            }
        });
    </script>

    <script>
$(function(){
	$("#submitBtn").click(function(){
        if($("#post_form").valid()){
            check_captcha();
    	}
	});
	$("#post_form").validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
            error_td.append(error);
            element.parents('dl:first').addClass('error');
        },
        success: function(label) {
            label.parents('dl:first').removeClass('error').find('label').remove();
        },
        onkeyup: false,
		rules: {
			phone: {
                required : true,
                mobile : true
            },
			captcha : {
                required : true,
                minlength: 4,
                remote   : {
                    url : 'index.php?act=seccode&op=check&nchash=<?php echo getRegisterNchash();?>',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#image_captcha').val();
                        }
                    },
                    complete: function(data) {
                        if(data.responseText == 'false') {
                        	document.getElementById('sms_codeimage').src='index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getRegisterNchash();?>&t=' + Math.random();
                        }
                    }
                }
            },
			sms_captcha: {
                required : function(element) {
                    return $("#image_captcha").val().length == 4;
                },
                minlength: 6
            }
		},
		messages: {
			phone: {
                required : '<i class="icon-exclamation-sign"></i>输入正确的手机号',
                mobile : '<i class="icon-exclamation-sign"></i>输入正确的手机号'
            },
			captcha : {
                required : '<i class="icon-remove-circle" title="<?php echo $lang['login_register_input_text_in_image'];?>"></i>',
                minlength: '<i class="icon-remove-circle" title="<?php echo $lang['login_register_input_text_in_image'];?>"></i>',
				remote	 : '<i class="icon-remove-circle" title="<?php echo $lang['login_register_code_wrong'];?>"></i>'
            },
			sms_captcha: {
                required : '<i class="icon-exclamation-sign"></i>请输入六位短信动态码',
                minlength: '<i class="icon-exclamation-sign"></i>请输入六位短信动态码'
            }
		}
	});
    $('#register_sms_form').validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
            error_td.append(error);
            element.parents('dl:first').addClass('error');
        },
        success: function(label) {
            label.parents('dl:first').removeClass('error').find('label').remove();
        },
    	submitHandler:function(form){
    	    ajaxpost('register_sms_form', '', '', 'onerror');
    	},
        rules : {
            member_name : {
                required : true,
                lettersmin : true,
                lettersmax : true,
                letters_name : true,
                remote   : {
                    url :'index.php?act=login&op=check_member&column=ok',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#member_name').val();
                        }
                    }
                }
            },
            password : {
                required   : true,
                minlength: 6,
				maxlength: 20
            },
            email : {
                email    : true,
                remote   : {
                    url : 'index.php?act=login&op=check_email',
                    type: 'get',
                    data:{
                        email : function(){
                            return $('#sms_email').val();
                        }
                    }
                }
            },
            agree : {
                required : true
            }
        },
        messages : {
            member_name : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_username'];?>',
                lettersmin : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_range'];?>',
                lettersmax : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_range'];?>',
				letters_name: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_lettersonly'];?>',
				remote	 : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_username_exists'];?>'
            },
            password  : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_input_password'];?>',
                minlength: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_password_range'];?>',
				maxlength: '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_password_range'];?>'
            },
            email : {
                email    : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_invalid_email'];?>',
				remote	 : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_email_exists'];?>'
            },
            agree : {
                required : '<i class="icon-exclamation-sign"></i><?php echo $lang['login_register_must_agree'];?>'
            }
        }
    });
});
</script>
<?php } ?>
