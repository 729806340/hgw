<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>订单管理-订单列表</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />

        <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap-theme.min.css" rel="stylesheet"
              type="text/css"/>
        <script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
        <script type="text/javascript" src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/baiduTemplate.js"></script>

    </head>

    <body>
        <div class="wrap-DRP">

            <div class="drp-main clear">

                <div class="right-content fl">
                    <div class="con-inside">
                        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">订单管理</a></div>
                        <div class="operation clear">
                            <form method="post" action="#" onsubmit="return false;">
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" id="oid" placeholder="输入订单ID" />
                                </div>
                                <!--<div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" id="fxoid" placeholder="输入分销订单号" />
                                </div>-->
                                <div class="date fl">
                                    <input type="text" class="w100 mh_date" id="starttime" placeholder="请选择起始日期" readonly="true" />
                                </div>
                                <div class="date fl">
                                    <input type="text" class="w100 mh_date"  id="endtime" placeholder="请选择结束日期" readonly="true" />
                                </div>
                                <div class="query-btn fl">
                                    <button id="search">查询</button>
                                </div>
                                <div class="reset-btn fl">
                                    <button id="clearbutton">重置</button>
                                </div>
                            </form>
                            <!--
                            <div class="import-order fl">
                                <button class="io-btn">导入订单</button>
                                <div class="import-wrap p-dialog">
                                    <div class="dialog">
                                        <div class="d-con">
                                            <div class="i-inside">
                                                <form method="post"  id="importorder" >
                                                    <p style="color: red;">导入订单前请确认所有分销商品已添加，且商品关系已映射</p>
													<select name="category" id='category'>
                                                        <option value=''>商品类目</option>
                                                        <?php /*foreach ($category as $v):; */?>
                                                            <option value='<?php /*echo $v['id']; */?>'><?php /*echo $v['catename']; */?></option>
                                                        <?php /*endforeach; */?>
                                                    </select>
                                                    <select name="pid" id='productname' style="width:100px;">
                                                        <option value=''>商品名称</option>
                                                    </select>
                                                    <div class="file-upload clear">
                                                        <input class="fl" type="file" name="uploadsfile" id="uploadsfile" multiple="multiple/form-data" />
                                                        <button class="fl" >上传</button>
                                                        <input type="hidden" name="checkproductname" id="checkproductname" value=""/>
                                                        <input type="hidden" name="action"  value="importorder"/>
                                                    </div>
                                                </form>

                                            </div>
                                            <p class="close-btn">×</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="export-order fl">
                                <button class="eo-btn">导出订单</button>
                                <div class="export-wrap p-dialog">
                                    <div class="dialog">
                                        <div class="d-con">
                                            <div class="i-inside">
                                                <form method="post" action="<?php /*echo U('index.php/order/exportExcel'); */?>" id="exportForm" >
                                                    <input type="hidden" name="begintime" value="" id="exp_begintime">
                                                    <input type="hidden" name="endtime" value="" id="exp_endtime">
                                                    <input type="hidden" name="status" value="" id="exp_status">
                                                    <input type="hidden" name="oid" value="" id="exp_oid">
                                                    <input type="hidden" name="fxoid" value="" id="exp_fxoid">
                                                </form>
                                            </div>	
                                            <p class="close-btn">×</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
							<div class="download fl">
                                <a href="http://admin.hangowa.com:8081/fenxiao/Public/uploads/ordertpl.rar">下载模板</a>
                            </div>-->
                            
                        </div>
                        <!-- 另写... -->
                        <div class="">
                            <div class="orderball">
                                <ul>
                                    <li class="titl  <?php echo $status == '' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist"); ?>">全部订单</a></li>
                                    <!--<li class="titl <?php /*echo $status == '0' ? 'bolda' : ''; */?>"><a href="<?php /*echo U("index.php/order/orderlist?status=0"); */?>">待付款</a></li>
                                    <li class="titl <?php /*echo $status == '4' ? 'bolda' : ''; */?>"><a href="<?php /*echo U("index.php/order/orderlist?status=4"); */?>">已付款待发货</a></li>
                                    <li class="titl <?php /*echo $status == '1' ? 'bolda' : ''; */?>"><a href="<?php /*echo U("index.php/order/orderlist?status=1"); */?>">已发货 </a></li>
                                    <li class="titl <?php /*echo $status == '2' ? 'bolda' : ''; */?>"><a href="<?php /*echo U("index.php/order/orderlist?status=2"); */?>">已作废</a></li>
                                    <li class="titl borderri <?php /*echo $status == '3' ? 'bolda' : ''; */?>"><a href="<?php /*echo U("index.php/order/orderlist?status=3"); */?>">已完成</a></li>-->
                                    <!--<li class="titl borderri <?php // echo $status == '4' ? 'bolda' : '';  ?>"><a href="<?php // echo U("index.php/order/orderlist?status=4");  ?>">退款</a></li>-->
                                    <!--<li class="titl borderri"><a href="<?php // echo U("index.php/order/orderlist?status=3");      ?>">交易结算</a></li>-->
                                </ul>
                            </div>
                            <div class="teblor"></div>
                            <table class="titlea" id="order-list"></table>
                            <div class="jiange clear page" id="page">
                                <div class="page-tag" id="page-tag">
                                </div>
                            </div>
                            <input type="hidden" name="status" id="status" value="<?php echo isset($status) ? $status : ''; ?>"/>
                            <input type="hidden" name="hidoid" id="hidoid" value=""/>
                            <input type="hidden" name="hidfxoid" id="hidfxoid" value=""/>
                            <input type="hidden" name="hidstarttime" id="hidstarttime" value=""/>
                            <input type="hidden" name="hidendtime" id="hidendtime" value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script id="orders-template" type="text/html">
            <%if(items&&items.length>0){%>
            <tr><th>订单号</th><th>下单时间</th><th>商品总价</th><th>订单金额</th><th>运费</th><th>收货人</th><th>电话</th><th>收货地址</th><th>备注信息</th><th>操作</th></tr>
            <%
            var active = 0;
            for(var i = 0; i < items.length;i++){
            var item = items[i];
            %>
            <tr class='o-title'>
                <td><%=item.id%></td>
                <td><%=item.datetime%></td>
                <td><%=item.goods_amount%></td>
                <td><%=item.amount%></td>
                <td><%=item.freight%></td>
                <td><%=item.receiver_name%></td>
                <td><%=item.receiver_phone%></td>
                <td><%=item.receiver_address%></td>
                <td><%=item.remark%></td>
                <td><a href="<?php echo U('index.php/order/view'); ?>?id=<%=item.id%>">查看</a></td>
            </tr>
            <% } %>
            <%}else{%>
            <tr class="no_content"><td>暂无订单</td></tr>
            <%}%>
        </script>


<script type="text/javascript">
    $(function() {
        $("input.mh_date").manhuaDate({
            Event: "click", //可选				       
            Left: 0, //弹出时间停靠的左边位置
            Top: -16, //弹出时间停靠的顶部边位置
            fuhao: "-", //日期连接符默认为-
            isTime: false, //是否开启时间值默认为false
            beginY: 2016, //年份的开始默认为1949
            endY: <?php echo date("Y"); ?>//年份的结束默认为2049
        });
    });</script>

<!--订单导入、导出弹窗-->
<script type="text/javascript">
    $(function() {

        /*导入*/
        $(".io-btn").click(function() {
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
                        $(".import-wrap").stop().fadeIn(500);
                    } else {
                        alert('还有未映射的商品');
                        location.href = "<?php echo U('index.php/goods/distributorgoods'); ?>";
                    }
                }
            });

            $(".close-btn").click(function() {
                $(".import-wrap").fadeOut();
//                $("body").css({overflow: "display"});
            })
//            $("body").css({overflow: "hidden"});
        })

        /*导出*/
        $(".eo-btn").click(function() {
            var begintime = $("#starttime").val();
            var endtime = $("#endtime").val();
            if( begintime == "" || endtime == "" ) {
                alert("必须输入开始时间以及结束时间") ;
                return false;
            }
            var status = $("#status").val();
            var oid = $("#oid").val();
            var fxoid = $("#fxoid").val();
            $("#exp_begintime").val( begintime );
            $("#exp_endtime").val( endtime );
            $("#exp_status").val( status );
            $("#exp_oid").val( oid );
            $("#exp_fxoid").val( fxoid );
            $("#exportForm").submit();
        })

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
//            $("body").css({overflow: "hidden"});
        });


        $(".titlea tr:even").addClass("even-bg");
        $(".titlea tr").hover(function() {
            $(this).addClass("hover-bg").siblings().removeClass("hover-bg");
        }, function() {
            $(this).removeClass("hover-bg");
        })
    $("#category").change(function() {
        var pid = $(this).val();
        var action = 'category';
        $.ajax({
            type: "POST",
            url: "<?php echo U('index.php/order/ajax'); ?>",
            data: {'id': pid, 'action': action},
            dataType: "json",
            beforeSend: function(XMLHttpRequest) {
                $('body').showLoading();
            },
            success: function(data)
            {
                if (data.status == 1) {
                    $("#productname").html("");
                    $(data.msg).each(function(k, v) {
                        $("#productname").append($("<option value=" + v.pid + ">" + v.catename + "</option>"));
                    });
                    $("#productname").trigger("change");
                    $('body').hideLoading();
                } else {
                    $('body').hideLoading();
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
    });
    $("#productname").change(function() {
        var checkproductname = $(this).find("option:selected").text();
        $("#checkproductname").val(checkproductname);
    });

        $("#importorder").submit(function() {
            var checkedcategory = $('#category option:selected').val();
            var checkedproduct = $('#product option:selected').val();
            var uploadsfile = $('#uploadsfile');
//            alert(uploadsfile);return false;
            if (checkedcategory == '') {
                //alert('请选择类目');
                //return false;
            }
            if (checkedproduct == '') {
                //alert('请选择商品');
                //return false;
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
                    if (data.status == '1') {
                        $(".import-refund-wrap").fadeOut();
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
    })
    var page_cur = 1; //当前页
    var total_num, page_size, page_total_num; //总记录数,每页条数,总页数
    var status;
    function getData(page, status) { //获取当前页数据    	
        var status = $("#status").val();
        var oid = $("#hidoid").val();
        var fxoid = $("#hidfxoid").val();
        var starttime = $("#hidstarttime").val();
        var endtime = $("#hidendtime").val();
        
      
        
        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/order/ajax'); ?>',
            data: {'page': page, 'action': 'getlist', 'status': status, 'oid': oid, 'fxoid': fxoid, 'starttime': starttime, 'endtime': endtime},
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
                    console.log(json);
                    var html = baidu.template('orders-template',{items: json.list});
                    name='main';
                    $("#order-list").html(html);
                    getPageBar();
                } else {
					if (json.msg == '暂无数据！') { 
						$("#order-list").empty();
						$("#page-tag").html("暂无记录！");
						return; 
					}
                    alert(json.msg);
                }
            },
            complete: function() {
//                getPageBar(); //js生成分页，可用程序代替
                $('body').hideLoading();
                $(".shipinfo").blur(function(){
                    var column = $(this).attr('column');
                    var column_name=$(this).attr('column_name');
                    var col_value='';
                    var order_id = $(this).attr('order_id');
                    if(column=='reciver_name'){
                    	col_value = $(this).attr('value');
                    }else if(column=='reciver_info'){
                    	col_value=$("#phone_"+order_id).val()+','+$('#address_'+order_id).val();
                    }
                    $.ajax({
                        type: 'post',
                        url: '<?php echo U('index.php/order/ajax'); ?>',
                        data: {
                            'action': 'editshiping',
                            'column': column,
                            'col_value': col_value,
                            'order_id': order_id
                        },
                        dataType: 'json',
                        beforeSend: function (XMLHttpRequest) {
                            $('body').showLoading();
                        },
                        success: function (json) {
                            if (json.status == '1') {
                                alert(column_name+" 编辑成功");
                            } else {
                                //alert(column_name+" 编辑失败");
                            }
                        },
                        complete: function() {
                            $('body').hideLoading();
                        },
                        error: function() {
                            $('body').hideLoading();
                            alert(column_name+" 编辑失败");
                        }
                    })
                });
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
        $("#page a").on('click', function() { //on 向未来的元素添加事件处理器,不可用bind
            var page = $(this).attr("data-page"); //获取当前页
            var status = $("#status").val();
            getData(page, status)
        });
    });
    $('#search').click(function() {
        var oid = $("#oid").val();
        var fxoid = $("#fxoid").val();
        var starttime = $("#starttime").val();
        var endtime = $("#endtime").val();
        var status = $("#status").val();
        $("#hidoid").val(oid);
        $("#hidfxoid").val(fxoid);
        $("#hidstarttime").val(starttime);
        $("#hidendtime").val(endtime);
        getData(1, status);
    });

    $('#clearbutton').click(function() {
        $("#hidstarttime").val('');
        $("#hidendtime").val('');
//        $("#status").val('');
        $("#hidoid").val('');
        $("#hidfxoid").val('');
        $("#starttime").val('');
        $("#endtime").val('');
        $("#oid").val('');
        $("#fxoid").val('');
        var status = $("#status").val();
        getData(1, status);
//        return false;
    });

    $(function() {
        $("input").blur(function(){
            console.log("abc");
        });
    });
</script>

    </body>
</html>