$(function() {
    var e = getQueryString("mobile");
    var a = getQueryString("captcha");
    var register_submit = true;
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
                $.map(a,
                function(e, a) {
                    s += "<p>" + e + "</p>"
                });
                errorTipsShow(s)
            } else {
                errorTipsHide()
            }
        }
    });
    $("#completebtn").click(function() {
        if (!register_submit) {
            return false
        }
        var r = $("#password").val();
        if ($.sValid()) {
            register_submit = false;
            $.ajax({
                type: "post",
                url: ApiUrl + "/index.php?act=connect&op=sms_register",
                data: {
                    phone: e,
                    captcha: a,
                    password: r,
                    client: "wap"
                },
                dataType: "json",
                success: function(e) {
                    register_submit = true;
                    if (!e.datas.error) {
                       // addCookie("username", e.datas.username);
                       // addCookie("key", e.datas.key);
                        location.href = WapSiteUrl + "/tmpl/member/login.html"
                    } else {
                        errorTipsShow("<p>" + e.datas.error + "</p>")
                    }
                }
            })
        }
    })
});