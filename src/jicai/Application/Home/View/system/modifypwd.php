<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>密码修改</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div class="wrap-DRP">
            <div class="drp-main clear">
                <div class="right-content fl">
                    <div class="con-inside">

                        <!-- 另写... -->
                        <div class="">
                            <div class="distributor-add-form">
                                <h1>修改密码</h1>
                                <form method="post" action="<?php echo U('index.php/system/modifypwd'); ?>" id="sub-form">
                                    <!--<div class="form-item text01"><span class="f-name">分销商名称：</span><input class="i-box" type="text" /><span class="n-hint">*供应商名称不能为空</span></div>-->
                                    <!--<div class="form-item text02"><span class="f-name">经营类目：</span><input class="i-box" type="text" /><span class="n-hint">*经营类目不能为空</span></div>-->
                                    <div class="form-item text03"><span class="f-name">原密码：</span><input class="i-box" type="password" /><span class="n-hint">*旧密码不能为空</span></div>
                                    <div class="form-item text04"><span class="f-name">新密码：</span><input class="i-box" type="password" /><span class="n-hint">*新密码不能为空</span></div>
                                    <div class="form-item text05"><span class="f-name">重复新密码：</span><input class="i-box" type="password" /><span class="n-hint">*重复新密码不能为空</span><span class="rn-hint">*密码不一致！</span></div>
                                    <div class="form-item sub-btn"><span class="f-name"></span><input type="submit" value="提交" /></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>

表单验证 -->
<script>
    $(function() {
        $("#sub-form").submit(function() {
//		var nName=$(".text01 input").val();
//		var nCategory=$(".text02 input").val();
            var nID = $(".text03 input").val();
            var nPassword = $(".text04 input").val();
            var nResetPassWord = $(".text05 input").val();
//		if( nName == ''){
//			$(".text01 .n-hint").show();
//			return false;
//		}else if(nName.focus!=''){
//			$(".text01 .n-hint").hide();
//			}
//		if( nCategory == ''){
//			$(".text02 .n-hint").show();
//			return false;
//		}else if(nCategory.focus!=''){
//			$(".text02 .n-hint").hide();
//			}
            if (nID == '') {
                $(".text03 .n-hint").show();
                return false;
            } else if (nID.focus != '') {
                $(".text03 .n-hint").hide();
            }
            if (nPassword == '') {
                $(".text04 .n-hint").show();
                return false;
            } else if (nPassword.focus != '') {
                $(".text04 .n-hint").hide();
            }
            if (nResetPassWord == '') {
                $(".text05 .n-hint").show();
            } else if (nResetPassWord.focus != '') {
                $(".text05 .n-hint").hide();
            }
            if (nPassword != nResetPassWord) {
                $(".text05 .rn-hint").show();
            } else if (nPassword === nResetPassWord) {
                $(".text05 .rn-hint").hide();
            }
            $.ajax({
                type: "post",
                data: {'oldpasswod': nID, 'password': nPassword, 'reppassword': nResetPassWord},
                dataType: 'json',
                url: "<?php echo U('index.php/system/systemajax'); ?>",
                beforeSend: function(XMLHttpRequest) {
                    //ShowLoading();
                },
                success: function(data, textStatus) {
                    if (data.status == '0') {
                        alert(data.msg);
                        $(".text04 input").val('');
                        $(".text05 input").val('');
                    } else if(data.status == '1'){
                        alert(data.msg);
                        $(".text03 input").val('');
                    }else if(data.status == '2'){
                        alert(data.msg);
                        $(".text03 input").val('');
                        $(".text04 input").val('');
                        $(".text05 input").val('');
//                        window.location.href ="<?php // echo U('index.php/login/index','','',true); ?>";
                    }else{
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
            return false;
        })
    })
</script>


