<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>分销商管理</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />

    </head>

    <body>
    <style>
        .fxprice {width: 60px;}
        .promotion_price {width: 60px;}
        .goods-name {width: 240px;
            display: block;}
        .fxprice {width: 60px;}
    </style>
        <div class="wrap-DRP">

            <div class="drp-main clear">

                <div class="right-content fl">
                    <div class="con-inside">
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">活动列表</a></div>
                        <!-- 另写... -->
                        <div class="">
                            <form method="post" action="<?php echo U('index.php/goods/savelist'); ?>" name="listform" id="listform">
                            <div class="orderball">
                                <ul>
                                    <li class="titl bolda"><a href="#">全部活动列表</a></li>
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<!-- 隔行加背景色 -->
<script type="text/javascript">
    $(function() {
        $(".titlea tr:even").addClass("even-bg");
        $(".titlea tr").hover(function() {
            $(this).addClass("hover-bg").siblings().removeClass("hover-bg");
        }, function() {
            $(this).removeClass("hover-bg");
        })
    })
</script>


<script type="text/javascript">
    function getLocalTime(nS) {
        return new Date(parseInt(nS) * 1000).toLocaleString();//.replace(/:\d{1,2}$/,' ');
    }

    var page_cur = 1; //当前页
    var total_num, page_size, page_total_num; //总记录数,每页条数,总页数
    var distributorname = $("#distributorname").val();
//     alert(base_url);
    function getData(page, distributorname) { //获取当前页数据
//        var base_url = "<?php // echo U('index.php/distributor/distributoradd') . '?sid='; ?>";
        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/goods/ajax_promotion'); ?>',
            data: {id:<?php echo $id; ?>,'page': page, 'action': 'getdistributorgoodslist', 'distributorname': distributorname},
            dataType: 'json',
            beforeSend: function(XMLHttpRequest) {
//                $('body').showLoading();
            },
            success: function(json) {
                if (json.status == '1') {
                    json = json.msg;
                    $("#distributorlist").empty();
                    total_num = json.total_num; //总记录数
                    page_size = json.page_size; //每页数量
                    page_cur = page; //当前页 
                    distributorname = json.distributorname;
                    page_total_num = json.page_total_num; //总页数businessScope unix_to_datetime(unix);   getLocalTime(parseInt(array.ctime,10))
                    var li = "<tr><th>ID</th><th>促销价格</th><th>起始时间</th><th>结束时间</th><th>添加时间</th></tr>";
                    var list = json.list;
                    $.each(list, function(index, array) { //遍历返回json
                        li += "<td>" + array.id + "</td><td>" + array.price + "</td><td>" + getLocalTime(array.start_at) + "</td><td>" + getLocalTime(array.end_at) + "</td><td>" + getLocalTime(array.created_at) + "</td></tr>";
                    });
                    $("#distributorlist").append(li);
                } else {
                    alert(json.msg);
                }
            },
            complete: function() {
                getPageBar(); //js生成分页，可用程序代替
//                $('body').hideLoading();
            },
            error: function() {
//                $('body').hideLoading();
                alert("数据异常,请检查是否json格式");
            }
        });
    }
    function getPageBar() { //js生成分页
        page_str = "";
        if (page_cur > page_total_num)
            page_cur = page_total_num; //当前页大于最大页数
        if (page_cur < 1)
            page_cur = 1; //当前页小于1
        page_str += "<span>共" + total_num + "条</span><span>" + page_cur + "/" + page_total_num + "</span>";
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

    $(function() {
        getData(1, distributorname); //默认第一页
        $("#page a").live('click', function() { //live 向未来的元素添加事件处理器,不可用bind
            var page = $(this).attr("data-page"); //获取当前页
            var distributorname = $("#distributorname").val();
            getData(page, distributorname)
        });
    });
    $('#searchform').submit(function() {
        var distributorname = $("#distributorname").val();
        if (distributorname == '') {
            alert('请输入分销商名称！');
            return false;
        }
        getData(1, distributorname);
        return false;

    });


</script>
