<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>添加促销价</title>
    <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css"/>
    <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<div class="wrap-DRP">
    <div class="drp-main clear">
        <div class="right-content fl">
            <div class="con-inside">

                <!-- 另写... -->
                <div class="">
                    <div class="distributor-add-form">
                        <h1>添加促销价</h1>
                        <form method="post" action="<?php echo U('index.php/goods/save_promotion',array('id'=>$id)); ?>" id="sub-form">
                            <div class="form-item text03">
                                <span class="f-name">促销价：</span>
                                <input class="i-box" type="text"/>
                                <span class="n-hint">*促销价不能为空</span></div>
                            <div class="form-item text04">
                                <span class="f-name">促销开始时间：</span>
                                <input class="i-box" type="text" title="" id="start-at"/>
                                <span class="n-hint">*促销开始时间不能为空</span>
                            </div>
                            <div class="form-item text05">
                                <span class="f-name">促销结束时间：</span>
                                <input class="i-box" type="text"  title="" id="end-at"/>
                                <span class="n-hint">*促销结束时间不能为空</span>
                            </div>
                            <div class="form-item sub-btn">
                                <span class="f-name"></span>
                                <input type="submit" value="提交"/>
                            </div>
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
<script type="text/javascript" src="__PUBLIC__/js/inputmask/inputmask.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/inputmask/inputmask.extensions.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/inputmask/inputmask.numeric.extensions.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/inputmask/inputmask.date.extensions.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/inputmask/inputmask.phone.extensions.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/inputmask/jquery.inputmask.js"></script>

<script type="text/javascript" src="__PUBLIC__/js/inputmask/jquery.inputmask.js"></script>

<script>
    $(function () {
        $("#sub-form").submit(function () {
            var price = $(".text03 input").val();
            var start_at = $(".text04 input").val();
            var end_at = $(".text05 input").val();

            if (price == '') {
                $(".text03 .n-hint").show();
                return false;
            } else if (price.focus != '') {
                $(".text03 .n-hint").hide();
            }
            if (start_at == ''||!start_at.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/)) {
                $(".text04 .n-hint").show();
                return false;
            } else if (start_at.focus != '') {
                $(".text04 .n-hint").hide();
            }
            if (end_at == ''||!end_at.match(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/)) {
                $(".text05 .n-hint").show();
                return false;
            } else if (end_at.focus != '') {
                $(".text05 .n-hint").hide();
            }
            $.ajax({
                type: "post",
                data: {id:<?php echo $id;?>,'price': price, 'start_at': start_at, 'end_at': end_at},
                dataType: 'json',
                url: "<?php echo U('index.php/goods/save_promotion',array('id'=>$id)); ?>",
                beforeSend: function (XMLHttpRequest) {
                    //ShowLoading();
                },
                success: function (data, textStatus) {
                    if (data.status == '0') {
                        return alert(data.err);
                        //$(".text04 input").val('');
                        //$(".text05 input").val('');
                    } else {
                        alert(data.msg);
                        window.location.href = '<?php echo U('index.php/goods/distributorgoods'); ?>';
                    }
//            console.log(item);

                },
                complete: function (XMLHttpRequest, textStatus) {
                    //HideLoading();
                },
                error: function () {
                    alert('请求出错，请稍候再试！');
                }

            });
            return false;
        });
        $("#start-at").inputmask("yyyy-mm-dd hh:mm:ss");
        $("#end-at").inputmask("yyyy-mm-dd hh:mm:ss");
    })
</script>


