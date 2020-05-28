<?php
/** @var $goods array */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>编辑商品</title>
    <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css"/>
    <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css"/>
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet"
          type="text/css"/>
    <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
    <script type="text/javascript" src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>

<body>
<div class="wrap-DRP">
    <div class="drp-main clear">

        <div class="right-content fl">
            <div class="con-inside">
                <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">添加商品</a></div>
                <div class="panel">
                    <div class="row">
                        <div class="col-xs-12">
                            <form method="post" action="#" name="form" id="form" class="form-horizontal">
                                <!--<input type="hidden" name="id" value="<?php /*echo $goods['id'] */?>">-->
                                <div class="form-group">
                                    <label for="goods-name" class="col-sm-2 control-label">商品名称</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-name" name="name" placeholder="商品名称" value="<?php echo $goods['name'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-price" class="col-sm-2 control-label">价格</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-price" name="price" placeholder="商品价格" value="<?php echo $goods['price'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-cost" class="col-sm-2 control-label">成本</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-cost" name="cost" placeholder="商品成本" value="<?php echo $goods['cost'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-stock" class="col-sm-2 control-label">库存</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-stock" name="stock" placeholder="商品库存" value="<?php echo $goods['stock'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-commission" class="col-sm-2 control-label">佣金比例</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-commission" name="commission" placeholder="商品佣金比例" value="<?php echo $goods['commission'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-tax" class="col-sm-2 control-label">税率</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-tax" name="tax" placeholder="商品税率" value="<?php echo $goods['tax'] ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <button type="submit" class="btn btn-success">编辑商品</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!--日期选择-->
<script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
<script type="text/javascript">
    $(function () {

        $('#form').submit(function() {
            var form = $("form").serialize();
            $.post('#',form).done(function (res) {
                if(res.status < 0){
                    alert(res.msg);
                    return;
                }
                alert('修改成功');
                window.location.href = '<?php echo U('index.php/goods/goodslist'); ?>';
            }).fail(function (xhr) {
                alert('请求失败！');
            });
            return false;

        });
    });

</script>


</body>
</html>