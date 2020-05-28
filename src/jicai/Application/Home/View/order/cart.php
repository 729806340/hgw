<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>购物车-订单列表</title>
    <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css"/>
    <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css"/>

    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet"
          type="text/css"/>
    <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
    <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
    <script type="text/javascript" src="__PUBLIC__/js/cart.js"></script>
    <script type="text/javascript" src="__PUBLIC__/js/baiduTemplate.js"></script>

</head>

<body>
<div class="wrap-DRP">

    <div class="drp-main clear">

        <div class="right-content fl">
            <div class="con-inside">
                <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">购物车</a></div>
                <!-- 另写... -->
                <div class="panel">
                    <div class="panel-heading"><h3>商品列表</h3></div>
                    <div class="panel-body form-horizontal" id="goods-list"></div>

                </div>
                <div class="panel">
                    <div class="panel-heading"><h3>收货人信息</h3></div>
                    <div class="panel-body form-horizontal">
                        <div class="form-group">
                            <label for="receiver_name" class="col-sm-2 control-label">收货人姓名</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="receiver-name" name="receiver_name"
                                       placeholder="收货人姓名" value="集采"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="receiver_phone" class="col-sm-2 control-label">收货人电话</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="receiver-phone" name="receiver_phone"
                                       placeholder="收货人电话" value=""/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="receiver_address" class="col-sm-2 control-label">收货地址</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="receiver-address" name="receiver_address"
                                       placeholder="收货地址" value=""/>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="message" class="col-sm-2 control-label">买家留言</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="message" name="message" rows="3" cols="60"
                                          placeholder="买家留言"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="panel">
                    <div class="panel-heading"><h3>订单合计</h3></div>
                    <div class="panel-body form-horizontal">

                        <div class="form-group">
                            <label for="message" class="col-sm-2 control-label">商品金额</label>
                            <div class="col-sm-10">
                                <p class="form-control-static">&yen;<span id="goods-amount">0</span>元</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="freight" class="col-sm-2 control-label">运费</label>
                            <div class="col-sm-10">

                                <div class="input-group">
                                    <div class="input-group-addon">&yen;</div>
                                    <input type="text" class="form-control" id="freight" name="freight" value="0"/>
                                    <div class="input-group-addon">元</div>
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="message" class="col-sm-2 control-label">订单金额</label>
                            <div class="col-sm-10">
                                <p class="form-control-static">&yen;<span id="order-amount">0</span>元</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="button" class="btn btn-success" id="submit-order">提交订单</button>
                                <button type="button" class="btn btn-default" id="clear-cart">清空购物车</button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
<script id="goods-template" type="text/html">
    <%if(items&&items.length>0){%>
    <div class="row" style="padding: 10px; margin: 0 10px">
        <div class="name col-md-6">商品名称</div>
        <div class="price col-md-2">单价</div>
        <div class="count col-md-2">数量</div>
        <div class="count col-md-2">合计</div>
    </div>
    <%
    var active = 0;
    for(var i = 0; i < items.length;i++){
    var item = items[i];
    %>
    <div class="row goods-item" data-key="<%=item.id%>"
         style="border-top: 1px dashed #ccc; padding: 10px; margin: 0 10px">
        <div class="name col-md-6"><%=item.name%></div>
        <div class="price col-md-2">
            <div class="input-group">
                <div class="input-group-addon">&yen;</div>
                <input type="text" class="form-control goods-price" value="<%=item.price%>" placeholder="Amount">
                <div class="input-group-addon">元</div>
            </div>
        </div>
        <div class="count col-md-2"><input type="number" class="form-control goods-count" value="<%=item.count%>"></div>
        <div class="count col-md-2">&yen;<span class="goods-total"><%=item.count*item.price%></span>元 <a
                    href="javascript:;" class="btn btn-danger pull-right goods-remove">删除</a></div>
    </div>
    <% } %>
    <%}else{%>
    <div class="no_content">您尚未选择商品</div>
    <%}%>
</script>

<script type="text/javascript">
    $(function () {
        var goodsList = $("#goods-list"),busy=false;
        var loadGoods = function () {
            var cart = Cart.get(), count = Cart.count(), ids = [];
            if (count <= 0) {
                return alert('购物车为空');
            }
            for (var i in cart) {
                ids.push(cart[i].id);
            }
            $.post('#', {ids: ids}).done(function (res) {
                if (res.status < 0) {
                    alert(res.msg);
                    return;
                }
                var items = [];
                for (var i in res.items) {
                    var item = res.items[i], goods = Cart.get(item.id);
                    item.count = goods.count;
                    items.push(item);
                }
                console.log(items);
                var html = baidu.template('goods-template',{items: items});
                name='main';
                $("#goods-list").html(html);
                initEvent();
            }).fail(function (xhr) {
                alert('请求失败');
            });
        };
        var initEvent = function () {
            updateOrder();
            goodsList.find(".goods-price").change(function (e) {
                var $this = $(this);
                if ($this.val() == '') $this.val(0);
                // 更新单个商品金额/商品合计/订单进入
                updateGoods($(this).parents('.goods-item'));
            });
            goodsList.find(".goods-remove").click(function (e) {
                var $this = $(this), row = $(this).parents('.goods-item'), key = row.data('key');
                Cart.remove(key);
                row.remove();
                updateOrder();
            });
            goodsList.find(".goods-count").change(function (e) {
                var $this = $(this), row = $(this).parents('.goods-item'), key = row.data('key');
                if ($this.val() == '') $this.val(0);
                Cart.set({id: key, count: parseInt($this.val())});
                updateGoods(row);
            });
            $("#freight").change(function (e) {
                updateOrder();
            });
            $("#clear-cart").click(function (e) {
                Cart.clear();
                goodsList.find('.goods-item').remove();
                updateOrder();
            });
            $("#submit-order").click(function (e) {
                if(busy) return alert('请勿重复提交');
                var $this = $(this),items= [];
                busy = true;
                $this.addClass('disabled');
                goodsList.find('.goods-item').each(function (index, item) {
                    var $item = $(item),key = $item.data('key'),price = $item.find('.goods-price').val(), count = $item.find('.goods-count').val();
                    items.push({
                        goods_id:key,
                        goods_price:price,
                        goods_num:count
                    })
                });
                // 提交订单
                $.post('<?php echo U('index.php/order/create'); ?>',{
                    items:items,
                    receiver_name:$("#receiver-name").val(),
                    receiver_phone:$("#receiver-phone").val(),
                    receiver_address:$("#receiver-address").val(),
                    message:$("#message").val(),
                    freight:$("#freight").val(),
                }).done(function (res) {
                    busy =false;
                    $this.removeClass('disabled');
                    Cart.clear();
                    window.location.href = '<?php echo U('index.php/order/orderlist'); ?>';
                }).fail(function (xhr) {
                    busy =false;
                    $this.removeClass('disabled');
                    alert('订单提交失败');
                });
            });
        };
        var updateGoods = function (item) {
            // 更新商品统计
            var price = item.find('.goods-price').val(), count = item.find('.goods-count').val();
            item.find('.goods-total').text((price * count).toFixed(2));
            updateOrder();
        };
        var updateOrder = function () {
            // 更新订单信息
            var goodsAmount = 0, orderAmount = 0, freight = parseInt($("#freight").val());
            goodsList.find('.goods-item').each(function (index, item) {
                var $item = $(item);
                var price = $item.find('.goods-price').val(), count = $item.find('.goods-count').val();
                goodsAmount += price * count;
            });
            orderAmount = freight + goodsAmount;
            $("#goods-amount").text((goodsAmount).toFixed(2));
            $("#order-amount").text(orderAmount.toFixed(2));
        };
        loadGoods();
    });
</script>



</body>
</html>