<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>商品列表</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />

        <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet"
              type="text/css"/>
        <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
        <script type="text/javascript" src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/cart.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/baiduTemplate.js"></script>
    </head>

    <body>
        <div class="wrap-DRP">

            <div class="drp-main clear">

                <div class="right-content fl">
                    <div class="con-inside">
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">商品列表</a></div>
                        <div class="operation clear">
                            <form method="post" action="#" name="searchform" id="searchform">
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" id="goods_name" placeholder="输入商品名称查询" value="" />
                                </div>
                                <!--                                <div class="date fl">
                                                                    <input type="text" class="w100 mh_date" placeholder="请选择起始日期" readonly="true" />
                                                                </div>
                                                                <div class="date fl">
                                                                    <input type="text" class="w100 mh_date" placeholder="请选择结束日期" readonly="true" />
                                                                </div>-->
                                <div class="query-btn fl search">
                                    <button>查询</button>
                                </div>

                                <!--                                <div class="reset-btn fl">
                                                                    <button>重置</button>
                                                                </div>-->
                            </form>
                            <div class="fr" style="padding: 0 20px 0 0;">
                                <a class="btn btn-success" href="<?php echo U('index.php/goods/add');?>"><button id="add-goods">添加商品</button></a>
                            </div>
                            <div class="fr" style="padding: 0 20px 0 0;">
                                <a class="btn btn-danger" href="<?php echo U('index.php/order/cart');?>">购物车( <span id="cart-count">0</span>)</a>
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
                        <!-- 另写... -->
                        <div class="">
                            <div class="orderball">
                                <ul>
                                    <li class="titl bolda"><a href="#">全部商品列表</a></li>
                                </ul>
                            </div>
                            <div class="teblor"></div>
                            <!--store_place  thumbnail_pic-->
                            <table class="titlea" id="distributorlist">
                            </table>
                            <div class="jiange clear page" id="page">
                                <div class="page-tag" id="page-tag">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!--日期选择-->
<script type="text/javascript">
    $(function() {

        //
        <!--订单导入、导出弹窗-->
        $(function () {

            /*导入*/
            $(".io-btn").click(function () {
                $(".import-wrap").stop().fadeIn(500);
                $(".close-btn").click(function () {
                    $(".import-wrap").fadeOut();
                });
                $("body").css({overflow: "hidden"});
            });

            /*导出*/
            $(".eo-btn").click(function () {
                $(".export-wrap").stop().fadeIn(500);
                $(".close-btn").click(function () {
                    $(".export-wrap").fadeOut();
                });
                $("body").css({overflow: "hidden"});
            })
        });

        $(function () {

            $(".titlea tr:even").addClass("even-bg");
            $(".titlea tr").hover(function () {
                $(this).addClass("hover-bg").siblings().removeClass("hover-bg");
            }, function () {
                $(this).removeClass("hover-bg");
            })
        })
        var page_cur = 1; //当前页
        var total_num, page_size, page_total_num; //总记录数,每页条数,总页数
        var goods_name = $("#goods_name").val();

        function getData(page, goods_name) { //获取当前页数据
            $.ajax({
                type: 'post',
                url: '<?php echo U('index.php/goods/ajax'); ?>',
                data: {'page': page, 'action': 'getlist', 'goods_name': goods_name},
                dataType: 'json',
                beforeSend: function (XMLHttpRequest) {
//                $("body").showLoading();
                },
                success: function (json) {
                    if (json.status == '1') {
                        json = json.msg;
                        $("#distributorlist").empty();
                        total_num = json.total_num; //总记录数
                        page_size = json.page_size; //每页数量
                        page_cur = page; //当前页
                        goods_name = json.goods_name;
                        page_total_num = json.page_total_num; //总页数businessScope unix_to_datetime(unix);   getLocalTime(parseInt(item.ctime,10))
                        var li = "<tr><th>商品ID</th><th>商品名称</th><th>价格</th><th>库存</th><th>商品id</th><th>操作</th></tr>";
                        var list = json.list;
                        $.each(list, function (index, item) { //遍历返回json
                            if (null !== item.spec_info) {
                                //item.p_name += "（" + item.spec_info + "）";
                            }
                            //item.goods_state = item.goods_state==1?'上架':'下架';

                            li += "<tr><td class='leftbor'>" + item.id + "</td><td>" + item.name;
                            li += "</td><td>" + item.price + "</td><td>" + item.stock + "</td><td>" + item.id + "</td>";
                            li += "<td><a href='<?php echo U('index.php/goods/edit'); ?>?id=" + item.id + "' >编辑商品</a> | <a href='javascript:;' onclick='add(this)' data-key='" + item.id + "'>加入购物车</a></td></tr>";
                        });
                        $("#distributorlist").append(li);
                    } else {
                        alert(json.msg);
                    }
                },
                complete: function () {
                    getPageBar(); //js生成分页，可用程序代替
//                $('body').hideLoading();
                },
                error: function () {
//                $('body').hideLoading();
                    alert("数据异常!");
                }
            });
        }

        function getPageBar() { //js生成分页
            if (page_cur > page_total_num)
                page_cur = page_total_num; //当前页大于最大页数
            if (page_cur < 1)
                page_cur = 1; //当前页小于1
            page_str = "<span>共" + total_num + "条</span><span>" + page_cur + "/" + page_total_num + "</span>";
            //若是第一页
            if (page_cur == 1) {
                page_str += "<span>首页</span><span>上一页</span>";
            } else {
                page_str += "<a href='javascript:void(0)' data-page='1'>首页</a><a href='javascript:void(0)' data-page='" + (page_cur - 1) + "'>上一页</a>";
            }
            //若是最后页
            if (page_cur >= page_total_num) {
                page_str += "<span>下一页</span><span>尾页</span>";
            } else {
                page_str += "<a href='javascript:void(0)' data-page='" + (parseInt(page_cur) + 1) + "'>下一页</a><a href='javascript:void(0)' data-page='" + page_total_num + "'>尾页</a>";
            }
            $("#page-tag").html(page_str);
        }

        $(function () {
            getData(1, goods_name); //默认第一页
            $("#page a").on('click', function () { //live 向未来的元素添加事件处理器,不可用bind
                var page = $(this).attr("data-page"); //获取当前页
                var goods_name = $("#goods_name").val();
                getData(page, goods_name)
            });
        });
        $('#searchform').submit(function () {
            var goods_name = $("#goods_name").val();
            if (goods_name == '') {
                alert('请输入商品名称！');
                return false;
            }
            getData(1, goods_name);
            return false;

        });

        $("#cart-count").text(Cart.count());
    });
    function add(obj) {
        var key = $(obj).data('key');
        if (Cart.add(key)) {
            //alert('添加成功');
            $("#cart-count").text(Cart.count());
        }
        else
            alert('添加失败')
    }

</script>
    </body>
</html>