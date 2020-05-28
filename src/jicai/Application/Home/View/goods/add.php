<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>添加商品</title>
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
                                <div class="form-group">
                                    <label for="goods-name" class="col-sm-2 control-label">商品名称</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-name" name="name" placeholder="商品名称">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-price" class="col-sm-2 control-label">价格</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-price" name="price" placeholder="商品价格">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-cost" class="col-sm-2 control-label">成本</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-cost" name="cost" placeholder="商品成本">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-stock" class="col-sm-2 control-label">库存</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-stock" name="stock" placeholder="商品库存">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-commission" class="col-sm-2 control-label">佣金比例</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-commission" name="commission" placeholder="商品佣金比例">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="goods-tax" class="col-sm-2 control-label">税率</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="goods-tax" name="tax" placeholder="商品税率">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <button type="submit" class="btn btn-success">添加商品</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>

                    <!--          <div class="import-order fl">
                                <button class="io-btn">导入订单</button>
                                <div class="import-wrap p-dialog">
                                  <div class="dialog">
                                    <div class="d-con">
                                        <div class="i-inside">
                                            <form method="get" action="#">
                                              <select><option>商品类目</option><option>类目1</option><option>类目2</option><option>类目3</option></select>
                                              <select><option>商品名字</option><option>商品1</option><option>商品2</option><option>商品3</option></select>
                                              <div class="file-upload clear"><input class="fl" type="file" /><button class="fl">上传</button></div>
                                            </form>
                                        </div>
                                        <p class="close-btn">×</p>
                                    </div>
                                  </div>
                                </div>
                              </div>-->
                    <!--          <div class="export-order fl">
                                <button class="eo-btn">导出订单</button>
                                <div class="export-wrap p-dialog">
                                  <div class="dialog">
                                    <div class="d-con">
                                      <div class="i-inside">
                                            <form method="get" action="#">
                                            <label><span>订单状态：</span><select><option>订单列表</option><option>退款订单</option><option>异常订单</option><option>已发订单</option><option>交易结算</option></select></label>
                                            <div class="s-date-con clear">
                                                <div class="date fl">
                                                  <input type="text" class="w100 mh_date" placeholder="请选择起始日期" readonly="true" />
                                                </div>
                                                <div class="date fl">
                                                  <input type="text" class="w100 mh_date" placeholder="请选择结束日期" readonly="true" />
                                                </div>
                                                <div class="file-export"><button>导出</button></div>
                                            </div>
                                        </form>
                                      </div>
                                      <p class="close-btn">×</p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="download fl"><a href="#">下载模板</a></div>-->
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
            $.post('<?php echo U('index.php/goods/add'); ?>',form).done(function (res) {
                if(res.status < 0){
                    alert(res.msg);
                    return;
                }
                alert('添加成功');
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