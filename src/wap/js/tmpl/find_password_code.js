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
var loadGeetest = function(){
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
};
$(function() {
	/*var e = getQueryString("mobile");
	var c = getQueryString("captcha");
	var a = getQueryString("codekey");
	$("#usermobile").html(e);
	send_sms(e, c, a);
	$("#again").click(function() {
		c = $("#captcha").val();
		a = $("#codekey").val();
		send_sms(e, c, a)
	});*/
	loadSeccode();
	//loadGeetest();

	$("#refreshcode").bind("click", function() {
		loadSeccode()
	});

    var e = getQueryString("mobile");
    var c = getQueryString("captcha");
    var a = getQueryString("codekey");
    var geetest_challenge = getQueryString("geetest_challenge");
    var geetest_validate = getQueryString("geetest_validate");
    var geetest_seccode = getQueryString("geetest_seccode");
    $("#usermobile").html(e);
    send_sms(e, c, a,geetest_challenge,geetest_validate,geetest_seccode);
    $("#again").click(function() {
        c = $("#captcha").val();
        a = $("#codekey").val();
        var geetest_challenge = $("#geetest_challenge").val();
        var geetest_validate = $("#geetest_validate").val();
        var geetest_seccode = $("#geetest_seccode").val();
        send_sms(e, c, a,geetest_challenge,geetest_validate,geetest_seccode)
    });

    $("#find_password_code").click(function() {
        if (!$(this).parent().hasClass("ok")) {
            return false
        }
        var c = $("#mobilecode").val();
        if (c.length == 0) {
            errorTipsShow("<p>请填写验证码<p>")
        }
        check_sms_captcha(e, c);
        return false
    });
});

function send_sms(e, c, a,geetest_challenge,geetest_validate,geetest_seccode) {
    loadSeccode();
	$.getJSON(ApiUrl + "/index.php?act=connect&op=get_sms_captcha", {
        type: 3,
        phone: e,
        sec_val: c,
        sec_key: a,
        geetest_challenge: geetest_challenge,
        geetest_validate: geetest_validate,
        geetest_seccode: geetest_seccode,
    }, function(e) {
		if (e.datas.error == '1') {
			$.sDialog({
				skin: "green",
				content: "发送成功",
				okBtn: false,
				cancelBtn: false
			});
			$(".code-again").hide();
			$(".code-countdown").show().find("em").html(e.datas.sms_time);
			var c = setInterval(function() {
				var e = $(".code-countdown").find("em");
				var a = parseInt(e.html() - 1);
				if (a == 0) {
					$(".code-again").show();
					$(".code-countdown").hide();
					clearInterval(c)
				} else {
					e.html(a)
				}
			}, 1e3)
		} else {
            loadGeetest();

			errorTipsShow("<p>" + e.datas.error + "<p>")
		}
	})
}

function check_sms_captcha(e, c) {
	$.getJSON(ApiUrl + "/index.php?act=connect&op=check_sms_captcha", {
		type: 3,
		phone: e,
		captcha: c
	}, function(a) {
        console.log(a);
        console.log(a.datas);
        if (a.datas === "true"||a.datas === true) {
			window.location.href = "find_password_password.html?mobile=" + e + "&captcha=" + c
		} else {
            loadGeetest();
            loadSeccode();
			errorTipsShow("<p>" + a.datas + "<p>")
		}
	})
}