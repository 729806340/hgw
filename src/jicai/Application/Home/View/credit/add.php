<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>添加额度</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
    </head>
<?php // p($suser);die;?>
    <body>
        <div class="wrap-DRP">
            <div class="drp-main clear">
                <div class="right-content fl">
                    <div class="con-inside">
                        <!-- 另写... -->
                        <div class="">
                            <div class="distributor-add-form">
                                <h1>添加分销商</h1>
                                <form method="post" action="<?php echo U('index.php/distributor/distributoradd');?>" id="sub-form">
                                    <div class="form-item text01"><span class="f-name">分销商名称：</span><input class="i-box" type="text" name="shopname" value=""/><span class="n-hint">*供应商名称不能为空</span></div>
                                    <div class="form-item text02"><span class="f-name">经营类目：</span><input class="i-box" type="text" name="businessscope" value="" /><span class="n-hint">*经营类目不能为空</span></div>
                                    <div class="form-item text06"><span class="f-name">额度：</span><input class="i-box" type="text" name="limit" value="" /><span class="n-hint">*经销商额度不能为空</span></div>
                                    <div class="form-item text03"><span class="f-name">账号：</span><input class="i-box" type="text" name="username" value="" /><span class="n-hint">*账号不能为空</span></div>
                                    <div class="form-item text04"><span class="f-name">密码：</span><input class="i-box" type="password" name="password" /><span class="n-hint">*密码不能为空</span></div>
                                    <div class="form-item text05"><span class="f-name">重复密码：</span><input class="i-box" type="password"  name="reppassword" /><span class="n-hint">*重复密码不能为空</span><span class="rn-hint">*密码不一致！</span></div>
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
<!--<script src="__PUBLIC__/js/menu.js"></script>
<script>navList(12);</script>
日期选择
<script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
<script type="text/javascript">
$(function (){
        $("input.mh_date").manhuaDate({					       
                Event : "click",//可选				       
                Left : 0,//弹出时间停靠的左边位置
                Top : -16,//弹出时间停靠的顶部边位置
                fuhao : "-",//日期连接符默认为-
                isTime : false,//是否开启时间值默认为false
                beginY : 2000,//年份的开始默认为1949
                endY :2015//年份的结束默认为2049
        });
        
});
</script>

订单导入、导出弹窗
<script type="text/javascript">
$(function(){
        
        /*导入*/
        $(".io-btn").click(function(){
                $(".import-wrap").stop().fadeIn(500);
                $(".close-btn").click(function(){
                        $(".import-wrap").fadeOut();
                })
                $("body").css({overflow:"hidden"});
        })
        
        /*导出*/
        $(".eo-btn").click(function(){
                $(".export-wrap").stop().fadeIn(500);
                $(".close-btn").click(function(){
                        $(".export-wrap").fadeOut();
                })
                $("body").css({overflow:"hidden"});
        })
})
</script>

 隔行加背景色 
<script>
$(function(){
        
        $(".titlea tr:even").addClass("even-bg");
        $(".titlea tr").hover(function(){
                $(this).addClass("hover-bg").siblings().removeClass("hover-bg");
        },function(){
                $(this).removeClass("hover-bg");
                })
})
</script>

 表单验证 -->
<script>
    $(function() {
        $("#sub-form").submit(function() {
            var nName = $(".text01 input").val();
            var nCategory = $(".text02 input").val();
            var nID = $(".text03 input").val();
            var nPassword = $(".text04 input").val();
            var nResetPassWord = $(".text05 input").val();
            var limit = $(".text06 input").val();
            if (nName == '') {
                $(".text01 .n-hint").show();
                return false;
            } else if (nName.focus != '') {
                $(".text01 .n-hint").hide();
            }
            if (nCategory == '') {
                $(".text02 .n-hint").show();
                return false;
            } else if (nCategory.focus != '') {
                $(".text02 .n-hint").hide();
            }
            if (limit == '') {
                $(".text06 .n-hint").show();
                return false;
            } else if (limit.focus != '') {
                $(".text06 .n-hint").hide();
            }
            if (nID == '') {
                $(".text03 .n-hint").show();
                return false;
            } else if (nID.focus != '') {
                $(".text03 .n-hint").hide();
            }
            <?php if(!$suser['id']):;?>
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
            <?php endif;?>
        })
    })
</script>
