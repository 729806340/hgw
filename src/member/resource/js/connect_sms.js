	function get_sms_captcha(type,nchash){
        if($("#phone").val().match(/^1[3-9]\d{9}/)){
            var ajaxurl = 'index.php?act=connect_sms&op=get_captcha&nchash='+nchash+'&type='+type+'&phone='+$('#phone').val();
        	if($("#image_captcha").val().length === 4) {
        		ajaxurl += '&captcha='+$('#image_captcha').val();
            }else if($("#geetest_seccode").val().length > 10){
                ajaxurl += '&geetest_challenge='+$('#geetest_challenge').val();
                ajaxurl += '&geetest_validate='+$('#geetest_validate').val();
                ajaxurl += '&geetest_seccode='+$('#geetest_seccode').val();
			}else{
                return alert('请输入验证码');
			}
			$.ajax({
				type: "GET",
				url: ajaxurl,
				async: false,
				success: function(rs){
                    if(rs == 'true') {
                    	$("#sms_text").html('短信动态码已发出');
                    } else {
                        showError(rs);
                    }
			    }
			});
    	}else {
        	alert('请输入正确的手机号码');
		}
	}
	function check_captcha(){
        if($("#phone").val().length == 11 && $("#sms_captcha").val().length == 6){
            var ajaxurl = 'index.php?act=connect_sms&op=check_captcha';
            ajaxurl += '&sms_captcha='+$('#sms_captcha').val()+'&phone='+$('#phone').val();
			$.ajax({
				type: "GET",
				url: ajaxurl,
				async: false,
				success: function(rs){
            	    if(rs == 'true') {
            	        $.getScript('index.php?act=connect_sms&op=register'+'&phone='+$('#phone').val());
            	        $("#register_sms_form").show();
            	        $("#post_form").hide();
            	    } else {
            	        showError(rs);
            	    }
			    }
			});
    	}
	}