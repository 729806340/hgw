<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>登录</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <script src="__PUBLIC__/js/jquery.min.js" type="text/javascript"></script>
    </head>
    <body>
        <div class="wrap-login w1000">
            <div class="login-box">
                <div class="inside">
                    <h1>分销商管理登录</h1>
                    <div class="f-item"><span class="item-name fl">用户名：</span><input class="w173 fl" type="text" name="username" id="username" /></div>
                    <div class="f-item"><span class="item-name fl">密码：</span><input class="w173 fl" type="password"  name="password" id="password"/></div>
                    <div class="f-item"><span class="item-name fl">验证码：</span><input class="w87 fl" type="text" name="captche"id="captche" maxlength="4" /><span class="code fr"><img src="<?php echo U('index.php/login/code'); ?>" id="code" onclick="this.src=this.src+'?'+Math.random()"/></span></div>
                    <div class="f-item"><button class="login-btn" onclick="login()"></button></div>
                </div>
            </div>
        </div>
    </body>
</html>

<script>
    function login() {
        var username = $('#username').val();
        var password = $('#password').val();
        var captche = $('#captche').val();
        if(username ==''|| password==''){
            alert('账号或者密码不能为空！');
            return false;
        }
        if(captche==''){
            alert('请输入验证码！');
            return false;
        }
        $.ajax({
            type: "post",
            data: {'username': username, 'password': password, 'captche': captche},
            dataType: 'json',
            url: "<?php echo U('index.php/login/loginajax'); ?>",
            beforeSend: function(XMLHttpRequest) {
                //ShowLoading();
            },
            success: function(data, textStatus) {
                if (data.status == '4') {
                    window.location.href ="<?php echo U('index.php/index/index','','',true); ?>";
                } else {
                    $('#code').click();
                    alert(data.msg);
                }
//            console.log(item);

            },
            complete: function(XMLHttpRequest, textStatus) {
                //HideLoading();
            },
            error: function() {
                alert('请求出错，请稍候再试！');
            }

        });
    }
</script>