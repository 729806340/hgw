<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>订单管理-错误日志</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
    </head>

    <body>
        <div class="wrap-DRP">

            <div class="drp-main clear">

                <div class="right-content fl">
                    <div class="con-inside">
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">错误日志</a></div>
                        <div class="operation clear">
                            <form method="get" action="#">
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" placeholder="输入订单号查询" id="oid" />
                                </div>
								<div class="fl">
									<span class="fl"></span><i class="fl">错误类型：</i>
									<select name="log_type" id="log_type">
										<option value="0">---------</option>
										<option value="order">导入订单</option>
										<option value="ship">发货</option>
										<option value="refund">退款</option>
									</select>
								</div>
                                <div class="query-btn fl">
                                    <button id="search">查询</button>
                                </div>
                                <div class="reset-btn fl">
                                    <button id="clearbutton">重置</button>
                                </div>
								<input type="hidden" name="hidoid" id="hidoid" value=""/>
                            </form>
                        </div>
                        <!-- 另写... -->
                        <div class="">
                            
                            <div class="teblor"></div>
                            <table class="titlea" id="orderlist">
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

    function getData(page, orderid) { //获取当前页数据
        var oid = orderid ? orderid : $("#hidoid").val();
		var logtype = $("#log_type").val();

        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/refund/ajax'); ?>',
            data: {'page': page, 'action': 'loglist',  'oid': oid, 'logtype': logtype },
            dataType: 'json',
            beforeSend: function(XMLHttpRequest) {
                $('body').showLoading();
            },
            success: function(json) {
                if (json.status == '1') {
                    json = json.msg;
                    $("#orderlist").empty();
                    total_num = json.total_num; //总记录数
                    page_size = json.page_size; //每页数量
                    page_cur = page; //当前页 
                    $("#status").val(json.status);
                    page_total_num = json.page_total_num; //总页数businessScope unix_to_datetime(unix);   getLocalTime(parseInt(array.ctime,10)) SProductName out_logi_no
                    var li = "<tr><th>日志id</th><th>分销订单号</th><th>信息</th><th>日志时间</th><th>类型</th></tr>";
                    var list = json.list;
                    $.each(list, function(index, array) { //遍历返回json
					
                        var log_type = "";
						switch(array.log_type){
							case 'order':
								log_type = "导入订单";
								break;
							case 'ship':
								log_type = "发货";
								break;
							case 'refund':
								log_type = "退款";
								break;
						}
						li += "<tr class='o-title'>" +
								"<td style='text-align:center;'><span>" + array.id + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.orderno + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.error + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.logtime + "</span> </td>" +
								"<td style='text-align:center;'><span>" + log_type + "</span> </td>" +
								"</tr>";

                    });
                    $("#orderlist").append(li);
                    getPageBar();
                } else {
					if (json.msg == '暂无数据！') { 
						$("#orderlist").empty();
						$("#page-tag").html("暂无记录！");
						return; 
					}
                    alert(json.msg);
                }
            },
            complete: function() {
//                getPageBar(); //js生成分页，可用程序代替
                $('body').hideLoading();
            },
            error: function() {
                $('body').hideLoading();
                alert("数据异常！");
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
        getData(1, ''); //默认第一页
        $("#page a").live('click', function() { //live 向未来的元素添加事件处理器,不可用bind
            var page = $(this).attr("data-page"); //获取当前页
            getData(page, '')
        });
    });
    $('#search').click(function() {
        var oid = $("#oid").val();
		var log_type = $("#log_type").val();
        $("#hidoid").val(oid);
        getData(1, oid);
		return false;
    });

    $('#clearbutton').click(function() {
        $("#hidoid").val('');
        $("#oid").val('');
       // getData(1, '');
//        return false;
    });
</script>