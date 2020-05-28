$(function () {

    var handlerEmbed = function (captchaObj) {
        $("#embed-submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                $("#notice")[0].className = "show";
                setTimeout(function () {
                    $("#notice")[0].className = "hide";
                }, 2000);
                e.preventDefault();
            }
        });
        // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
        captchaObj.appendTo("#embed-captcha");
        captchaObj.onReady(function () {
            $("#wait")[0].className = "hide";
        });
        captchaObj.onSuccess(function(){
            var result = captchaObj.getValidate();
            console.log(result);
            $("#geetest_challenge").val(result.geetest_challenge);
            $("#geetest_validate").val(result.geetest_validate);
            $("#geetest_seccode").val(result.geetest_challenge);
            $("#captcha").val(result.geetest_validate)
            writeClear($("#geetest_seccode"))
            //your code
        })
        // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
    };
    var loadGeetest = function() {

        $("#embed-captcha").html('');
        $("#geetest_challenge").val('');
        $("#geetest_validate").val('');
        $("#geetest_seccode").val('');
        $("#captcha").val('')
        $.ajax({
            // 获取id，challenge，success（是否启用failback）
            url: "/member/?act=login&op=geetest&t=" + (new Date()).getTime(), // 加随机数防止缓存
            type: "get",
            dataType: "json",
            success: function (data) {
                console.log(data);
                if (data.success) {
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
                } else {
                    $("#captcha-geetest").hide();
                    $("#captcha-image").show();
                }
            }
        });
    };
    loadGeetest();

    loadSeccode();
    $("#refreshcode").bind("click", function () {
        loadSeccode()
    });
    $.sValid.init({
        rules: {usermobile: {required: true, mobile: true}},
        messages: {usermobile: {required: "请填写手机号！", mobile: "手机号码不正确"}},
        callback: function (e, i, r) {
            if (e.length > 0) {
                var l = "";
                $.map(i, function (e, i) {
                    l += "<p>" + e + "</p>"
                });
                errorTipsShow(l)
            } else {
                errorTipsHide()
            }
        }
    });
    $("#refister_mobile_btn").click(function () {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }

        if ($.sValid()) {
            var hrefurl = 'register_mobile_code.html?mobile='+$('#usermobile').val();
            if($("#captcha").val().length === 4) {
                hrefurl += '&captcha='+$('#image_captcha').val();
                hrefurl += "&codekey=" + $("#codekey").val();
            }else if($("#geetest_seccode").val().length > 10){
                hrefurl += '&geetest_challenge='+$('#geetest_challenge').val();
                hrefurl += '&geetest_validate='+$('#geetest_validate').val();
                hrefurl += '&geetest_seccode='+$('#geetest_seccode').val();
            }
            $(this).attr("href", hrefurl)
        } else {
            return false
        }
    })
});