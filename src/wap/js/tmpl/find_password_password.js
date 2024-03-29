$(function() {
	var e = getQueryString("mobile");
	var a = getQueryString("captcha");
	$("#checkbox").click(function() {
		if ($(this).prop("checked")) {
			$("#password").attr("type", "text")
		} else {
			$("#password").attr("type", "password")
		}
	});
	$.sValid.init({
		rules: {
			password: "required"
		},
		messages: {
			password: "密码必填!"
		},
		callback: function(e, a, r) {
			if (e.length > 0) {
				var s = "";
				$.map(a, function(e, a) {
					s += "<p>" + e + "</p>"
				});
				errorTipsShow(s)
			} else {
				errorTipsHide()
			}
		}
	});
	$("#completebtn").click(function() {
		if (!$(this).parent().hasClass("ok")) {
			return false
		}
		var r = $("#password").val();
		if ($.sValid()) {
			$.ajax({
				type: "post",
				url: ApiUrl + "/index.php?act=connect&op=find_password",
				data: {
					phone: e,
					captcha: a,
					password: r,
					client: "wap"
				},
				dataType: "json",
				success: function(e) {
					if (!e.datas.error) {
						//addCookie("username", e.datas.username);
						//addCookie("key", e.datas.key);
                        errorTipsShow("<p>密码设置成功，请重新登录！</p>");
                        setTimeout(function() {
							location.href = WapSiteUrl + "/tmpl/member/login.html"
						},
						3e3);
					} else {
						errorTipsShow("<p>" + e.datas.error + "</p>")
					}
				}
			})
		}
	})
});