<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>分销商管理</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
    </head>

    <body>
        <div class="wrap-DRP">

            <div class="drp-main clear">

                <div class="right-content fl">
                    <div class="con-inside">
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">分销商列表</a></div>
                        <div class="operation clear">
                            <form method="post" action="#" name="searchform" id="searchform">
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" id="distributorname" placeholder="输入分销商名称查询" value="" />
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
    </body>
</html>
<script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>

<!--日期选择-->
<script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
<script type="text/javascript">
    $(function() {
        $("input.mh_date").manhuaDate({
            Event: "click", //可选				       
            Left: 0, //弹出时间停靠的左边位置
            Top: -16, //弹出时间停靠的顶部边位置
            fuhao: "-", //日期连接符默认为-
            isTime: false, //是否开启时间值默认为false
            beginY: 2000, //年份的开始默认为1949
            endY: 2015//年份的结束默认为2049
        });
    });</script>

<!--订单导入、导出弹窗-->
<script type="text/javascript">
    $(function() {

        /*导入*/
        $(".io-btn").click(function() {
            $(".import-wrap").stop().fadeIn(500);
            $(".close-btn").click(function() {
                $(".import-wrap").fadeOut();
            })
            $("body").css({overflow: "hidden"});
        })

        /*导出*/
        $(".eo-btn").click(function() {
            $(".export-wrap").stop().fadeIn(500);
            $(".close-btn").click(function() {
                $(".export-wrap").fadeOut();
            })
            $("body").css({overflow: "hidden"});
        })
    })
</script>

<!-- 隔行加背景色 -->
<script>
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
    var page_cur = 1; //当前页
    var total_num, page_size, page_total_num; //总记录数,每页条数,总页数
    var distributorname = $("#distributorname").val();
    
    function getData(page, distributorname) { //获取当前页数据
        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/goods/ajax'); ?>',
            data: {'page': page, 'action': 'getlist', 'distributorname': distributorname},
            dataType: 'json',
            beforeSend: function(XMLHttpRequest) {
//                $("body").showLoading();
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
                    var li = "<tr><th>序号</th><th>商品图片</th><th>商品名称</th><th>价格</th><th>库存</th><th>商品id</th><th>货品id</th><th>上下架状态</th><th>操作</th></tr>";
                    var list = json.list;
                    $.each(list, function(index, array) { //遍历返回json
                        if (null !== array.spec_info) {
                            array.p_name += "（" + array.spec_info + "）";
                        }
                        array.goods_state = array.goods_state==1?'上架':'下架';

                        li += "<tr><td class='leftbor'><input type='checkbox' name='car' /></td><td><img src='"+array.goods_image+"' width='60px' height='60px' /></td><td><a title='" + array.goods_name + "' target='_blank' href='/shop/index.php?act=goods&op=index&goods_id=" + array.goods_id + "'>" + array.goods_name;
                        li += "</a></td><td>" + array.goods_price + "</td><td>" + array.goods_storage + "</td><td>" + array.goods_commonid + "</td><td>" + array.goods_id + "</td><td>" + array.goods_state + "</td>";
                        li += "<td><a href='javascript:;' onclick='add(this)' data_name='"+array.goods_name+"' data_pid='"+array.goods_id+" 'data_gid='"+array.goods_commonid+"'>添加为分销商品</a></td></tr>";
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
<script>
    function add(obj) {
        var name=$(obj).attr('data_name');
        var pid=$(obj).attr('data_pid');
        var gid=$(obj).attr('data_gid');
        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/goods/ajax'); ?>',
            data: {'action': 'add', 'name': name, 'pid': pid, 'gid': gid},
            dataType: 'json',
            beforeSend: function(XMLHttpRequest) {
//                $('body').showLoading();
            },
            success: function(json) {
                if (json.status == '1') {
                    alert(json.msg);
                } else {
                    alert(json.msg);
                }
            },
            complete: function() {
                getPageBar(); //
//                $('body').hideLoading();
            },
            error: function() {
//                $('body').hideLoading();
                alert("数据异常,请检查是否json格式");
            }
        });
    }
</script> 