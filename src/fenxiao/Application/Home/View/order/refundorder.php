<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>订单管理-退款列表</title>
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
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">退款列表</a></div>
                        <div class="operation clear">
                            <form method="get" action="#">
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" placeholder="输入订单号查询" id="oid" />
                                </div>
                                <div class="query-btn fl">
                                    <button id="search">查询</button>
                                </div>
                                <div class="reset-btn fl">
                                    <button id="clearbutton">重置</button>
                                </div>
                            </form>
                            <div class="import-order fl">
								<div class="export-order fl">
									<button class="import-refund-btn">导入退款</button>
									<div class="import-refund-wrap p-dialog">
										<div class="dialog">
											<div class="d-con">
												<div class="i-inside">
													<form method="post"  id="importrefund" >
														<p style="color: red;">导入退款前请确认所有分销商品已添加，且商品关系已映射</p>
														<div class="file-upload clear">
															<input class="fl" type="file" name="uploadsrefund" id="uploadsrefund" multiple="multiple/form-data" />
															<button class="fl" >上传</button>
															<input type="hidden" name="action"  value="importrefund"/>
														</div>
													</form>

												</div>
												<p class="close-btn">×</p>
											</div>
										</div>
									</div>
								</div>
								<div class="download fl"><a href="http://admin.hangowa.com:8081/fenxiao/Public/uploads/refundtpl.zip">下载模板</a></div>
                            </div>
                            
                            
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
                            <input type="hidden" name="hidoid" id="hidoid" value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


<!--订单导入、导出弹窗-->
<script type="text/javascript">
    $(function() {

        /* 导入退款单 */
        $(".import-refund-btn").click(function() {
            //检查商品映射
            var action = 'checkgoodsrel';
            $.ajax({
                type: "POST",
                url: "<?php echo U('index.php/goods/ajax'); ?>",
                data: {'action': action},
                dataType: "json",
                success: function(data)
                {
                    if (data.status == 1) {
                        $(".import-refund-wrap").stop().fadeIn(500);
                    } else {
                        alert('还有未映射的商品');
                        location.href = "<?php echo U('index.php/goods/distributorgoods'); ?>";
                    }
                }
            });

            $(".close-btn").click(function() {
                $(".import-refund-wrap").fadeOut();
            })

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


<script>
    $(function() {
        $("#importorder").submit(function() {
            var checkedcategory = $('#category option:selected').val();
            var checkedproduct = $('#product option:selected').val();
            var uploadsfile = $('#uploadsfile');
//            alert(uploadsfile);return false;
            if (checkedcategory == '') {
                alert('请选择类目');
                return false;
            }
            if (checkedproduct == '') {
                alert('请选择商品');
                return false;
            }
            if ($.trim(uploadsfile.val()) == '') {
                alert("请选择文件");
                return false;
            }
            var formdata = new FormData();
            var fileObj = document.getElementById("uploadsfile").files;
            for (var i = 0; i < fileObj.length; i++)
                formdata.append("file" + i, fileObj[i]);
            formdata.append('action', 'importorder');
            formdata.append('catid', $('#category').find("option:selected").val());
            formdata.append('productname', $('#productname').find("option:selected").text());
            formdata.append('pid', $('#productname').find("option:selected").val());
            $.ajax({
                type: "POST",
                url: "<?php echo U('index.php/order/ajax'); ?>",
                data: formdata,
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function(XMLHttpRequest) {
                    $('body').showLoading();
                },
                success: function(data)
                {
                    if (data.status == '1') {
                        $(".import-wrap").fadeOut();
                        alert(data.msg);
                        getData(1, 0);
                    } else {
                        alert(data.msg);
                    }
                },
                complete: function(XMLHttpRequest, textStatus) {
                    $('body').hideLoading();
                },
                error: function() {
                    $('body').hideLoading();
                    alert('请求出错，请稍候再试！');
                }
            });
            return false;
        });

        /* 导入退款单 */
        $("#importrefund").submit(function() {
            var uploadsfile = $('#uploadsrefund');
//            alert(uploadsfile);return false;

            if ($.trim(uploadsfile.val()) == '') {
                alert("请选择文件");
                return false;
            }
            var formdata = new FormData();
            var fileObj = document.getElementById("uploadsrefund").files;
            for (var i = 0; i < fileObj.length; i++)
                formdata.append("file" + i, fileObj[i]);
            formdata.append('action', 'importrefund');

            $.ajax({
                type: "POST",
                url: "<?php echo U('index.php/order/ajax'); ?>",
                data: formdata,
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function(XMLHttpRequest) {
                    $('body').showLoading();
                },
                success: function(data)
                {
                    if (data.status === true) {
                        $(".import-refund-wrap").fadeOut();
                        var answer = confirm(data.msg);
                        if(answer) {
                            location.href="<?php echo U('index.php/order/exportRefundResult') . '?key_name='; ?>" + data.key_name;
                        };
                        getData(1, 0);
                    } else {
                        alert(data.msg);
                    }
                },
                complete: function(XMLHttpRequest, textStatus) {
                    $('body').hideLoading();
                },
                error: function() {
                    $('body').hideLoading();
                    alert('请求出错，请稍候再试！');
                }
            });
            return false;
        });
    })
</script>

<script type="text/javascript">
    var page_cur = 1; //当前页
    var total_num, page_size, page_total_num; //总记录数,每页条数,总页数

    function getData(page, orderid) { //获取当前页数据
        var oid = orderid ? orderid : $("#hidoid").val();

        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/refund/ajax'); ?>',
            data: {'page': page, 'action': 'refundlist',  'oid': oid },
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
                    var li = "<tr><th>退款记录id</th><th>订单号</th><th>退款类型/标题</th><th>售后状态</th><th>商家处理状态</th><th>用户申请时间</th><th>商家退款时间</th><th>订单金额</th><th>退款金额</th></tr>";
                    var list = json.list;
                    $.each(list, function(index, array) { //遍历返回json
					
                        li += "<tr class='o-title'>" +
								"<td style='text-align:center;'><span>" + array.refund_id + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.order_sn + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.refund_type + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.refund_state + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.seller_state + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.add_time + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.pay_time + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.order_money + "</span> </td>" +
								"<td style='text-align:center;'><span>" + array.refund_amount + "</span> </td>" +
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
        getData(1, status); //默认第一页
        $("#page a").live('click', function() { //live 向未来的元素添加事件处理器,不可用bind
            var page = $(this).attr("data-page"); //获取当前页
            var status = $("#status").val();
            getData(page, status)
        });
    });
    $('#search').click(function() {
        var oid = $("#oid").val();
        $("#hidoid").val(oid);
        getData(1, oid);
    });

    $('#clearbutton').click(function() {
        $("#hidoid").val('');
        $("#oid").val('');
        getData(1);
//        return false;
    });
</script>