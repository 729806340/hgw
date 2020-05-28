<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>订单管理-订单列表</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/showLoading.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>
        <script type="text/javascript" src="__PUBLIC__/js/jquery.showLoading.min.js"></script>
        <script language="javascript" type="text/javascript" src="__PUBLIC__/dataPicker/WdatePicker.js"></script>
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
                                    <input class="w100 fl" type="text" id="oid" placeholder="输入订单编号" />
                                </div>
                                <div class="search fl"><span class="fl"></span><i class="fl"></i>
                                    <input class="w100 fl" type="text" id="fxoid" placeholder="输入分销订单号" />
                                </div>
                                <div class="date fl">
                                    下单时间：<input class="w100 Wdate" type="text" id="starttime"  placeholder="请选择起始日期" readonly="true" onClick="WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})">
                                </div>
                                <div class="date fl">
                                    <input class="w100 Wdate" type="text" id="endtime"  placeholder="请选择结束日期"  readonly="true" onClick="WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})">
                                </div>
                                <div class="date fl">
                                    导入时间：<input class="w100 Wdate" type="text" id="istarttime"  placeholder="请选择起始日期" readonly="true" onClick="WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})">
                                </div>
                                <div class="date fl">
                                    <input class="w100 Wdate" type="text" id="iendtime"  placeholder="请选择结束日期"  readonly="true" onClick="WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})">
                                </div>
                                <div class="query-btn fl">
                                    <button id="search">查询</button>
                                </div>
                                <div class="reset-btn fl">
                                    <button id="clearbutton">重置</button>
                                </div>
                            </form>
                        </div>
                        <div class="operation clear">
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
                                                        <?php foreach ($category as $v):; ?>
                                                            <option value='<?php echo $v['id']; ?>'><?php echo $v['catename']; ?></option>
                                                        <?php endforeach; ?>
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
                                                <form method="post" action="<?php echo U('index.php/order/exportExcel'); ?>" id="exportForm" >
                                                    <input type="hidden" name="begintime" value="" id="exp_begintime">
                                                    <input type="hidden" name="endtime" value="" id="exp_endtime">
                                                    <input type="hidden" name="istarttime" value="" id="exp_istarttime">
                                                    <input type="hidden" name="iendtime" value="" id="exp_iendtime">
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
                            <div class="download fl"><a href="http://admin2018.hangowa.com/fenxiao/Public/uploads/ordertpl.rar">下载模板</a></div></div>
                        <!-- 另写... -->
                        <div class="">
                            <div class="orderball">
                                <ul>
                                    <li class="titl  <?php echo $status == '' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist"); ?>">全部订单</a></li>
<!--                                    <li class="titl --><?php //echo $status == '0' ? 'bolda' : ''; ?><!--"><a href="--><?php //echo U("index.php/order/orderlist?status=0"); ?><!--">待付款</a></li>-->
                                    <li class="titl <?php echo $status == '4' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist?status=4"); ?>">已支付</a></li>
                                    <li class="titl <?php echo $status == '5' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist?status=5"); ?>">备货中</a></li>
                                    <li class="titl <?php echo $status == '1' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist?status=1"); ?>">已发货 </a></li>
                                    <li class="titl <?php echo $status == '2' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist?status=2"); ?>">已作废</a></li>
                                    <li class="titl borderri <?php echo $status == '3' ? 'bolda' : ''; ?>"><a href="<?php echo U("index.php/order/orderlist?status=3"); ?>">已完成</a></li>
                                    <!--<li class="titl borderri <?php // echo $status == '4' ? 'bolda' : '';  ?>"><a href="<?php // echo U("index.php/order/orderlist?status=4");  ?>">退款</a></li>-->
                                    <!--<li class="titl borderri"><a href="<?php // echo U("index.php/order/orderlist?status=3");      ?>">交易结算</a></li>-->
                                </ul>
                            </div>
                            <div class="teblor"></div>
                            <table class="titlea" id="orderlist">
                            </table>
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
    </body>
</html>


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
            var istarttime = $("#istarttime").val();
            var iendtime = $("#iendtime").val();
            var status = $("#status").val();
            var oid = $("#oid").val();
            var fxoid = $("#fxoid").val();
            if(begintime==""&&endtime==""&&istarttime==""&&iendtime==""&&oid==""&&fxoid==""){
                alert("必须输入【筛选条件】");
                return false;
            }
            if(begintime!==""&&endtime==""){
                alert("必须输入【下单时间】的结束时间");
                return false;
            }
            if(begintime==""&&endtime!==""){
                alert("必须输入【下单时间】的开始时间");
                return false;
            }
            if(istarttime!==""&&iendtime==""){
                alert("必须输入【导入时间】的结束时间");
                return false;
            }
            if(istarttime==""&&iendtime!==""){
                alert("必须输入【导入时间】的开始时间");
                return false;
            }
            d1=new Date(Date.parse(begintime));
            d2=new Date(Date.parse(endtime));
            d3=new Date(Date.parse(istarttime));
            d4=new Date(Date.parse(iendtime));
            if(d1>=d2){
                alert('【下单时间】的结束时间必须不小于开始时间');
                return false;
            }
            if(d3>=d4){
                alert('【导入时间】的结束时间必须不小于开始时间');
                return false;
            }
            $("#exp_begintime").val( begintime );
            $("#exp_endtime").val( endtime );
            $("#exp_istarttime").val( istarttime );
            $("#exp_iendtime").val( iendtime );
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
</script>

<script>
    $(function() {
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
                        var answer = confirm(data.msg);
                        if(answer) {
                            location.href="<?php echo U('index.php/order/exportExcelResult') . '?key_name='; ?>" + data.data.key_name;
                        }
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
        $("#importrefund").submit(function () {
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
                beforeSend: function (XMLHttpRequest) {
                    $('body').showLoading();
                },
                success: function (data) {
                    if (data.status == '1') {
                        $(".import-refund-wrap").fadeOut();
                        alert(data.msg);
                        getData(1, 0);
                    } else {
                        alert(data.msg);
                    }
                },
                complete: function (XMLHttpRequest, textStatus) {
                    $('body').hideLoading();
                },
                error: function () {
                    $('body').hideLoading();
                    alert('请求出错，请稍候再试！');
                }
            });
            return false;
        });
    })
</script>

<script type="text/javascript">
    /**************************************时间格式化处理************************************/
    function dateFtt(fmt,date)
    { //author: meizz
        var o = {
            "M+" : date.getMonth()+1,                 //月份
            "d+" : date.getDate(),                    //日
            "h+" : date.getHours(),                   //小时
            "m+" : date.getMinutes(),                 //分
            "s+" : date.getSeconds(),                 //秒
            "q+" : Math.floor((date.getMonth()+3)/3), //季度
            "S"  : date.getMilliseconds()             //毫秒
        };
        if(/(y+)/.test(fmt))
            fmt=fmt.replace(RegExp.$1, (date.getFullYear()+"").substr(4 - RegExp.$1.length));
        for(var k in o)
            if(new RegExp("("+ k +")").test(fmt))
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        return fmt;
    }
    var page_cur = 1; //当前页
    var total_num, page_size, page_total_num; //总记录数,每页条数,总页数
    var status
    function getData(page, status) { //获取当前页数据
        var status = $("#status").val();
        var oid = $("#hidoid").val();
        var fxoid = $("#hidfxoid").val();
        var starttime = $("#starttime").val();
        var endtime = $("#endtime").val();
        var istarttime=$("#istarttime").val();
        var iendtime=$("#iendtime").val();


        $.ajax({
            type: 'post',
            url: '<?php echo U('index.php/order/ajax'); ?>',
            data: {'page': page, 'action': 'getlist', 'status': status, 'oid': oid, 'fxoid': fxoid, 'starttime': starttime, 'endtime': endtime,'istarttime':istarttime,'iendtime':iendtime},
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
                    var li = "<tr><th>图片</th><th>分销订单号</th><th>分销商品名称</th><th>平台商品名称</th><th>商品单价</th><th>商品数量</th><th>状态</th><th>商品总价</th><th>物流公司</th><th>发货单号</th><th>订单总额</th></tr>";
                    var list = json.list;
                    $.each(list, function(index, array) { //遍历返回json
                        if (array.status == 'active' && array.ship_status == '0' && array.pay_status == '0') {
                            array.status = '待付款';
                        } else if (array.ship_status == 1) {
                            array.status = '已发货';
                        } else if (array.status == 'dead') {
                            array.status = '已作废';
                        } else if (array.status == 'finish') {
                            array.status = '交易完成';
                        } else if (array.status == 'active' && array.ship_status == '0' && array.pay_status == '1') {
                            array.status = '已付款待发货';
                        } else if (array.pay_status == '2') {
                            array.status = '已付款至到担保方';
                        } else if (array.pay_status == '3') {
                            array.status = '部分付款';
                        } else if (array.pay_status == '4') {
                            array.status = '部分退款';
                        } else if (array.pay_status == '5') {
                            array.status = '全额退款';
                        }else{
                            array.status = '已付款待发货';
                        }
                        li += "<tr class='o-title'><td colspan='14' style='text-align:left;'><input type='checkbox' name='car' /> <span>平台订单号:" + array.order_sn + "(编号：" + array.order_id + ")</span> <span>总金额:" + array.order_amount + "</span> <span>下单时间：" + array.datetime + "</span>   <span>导入时间:"+(array.import_time == 0 ? '未记录' : dateFtt("yyyy-MM-dd hh:mm:ss",new Date(parseInt(array.import_time) * 1000)))+"</span>  <span>收货人:<input type='text' class='shipinfo' column='reciver_name' column_name='收货人' order_id='"+array.order_id+"' value='"+array.reciver_name+"'></span> <span>电话:<input type='text' class='shipinfo' column='reciver_info' column_name='电话' id='phone_"+array.order_id+"' order_id='"+array.order_id+"' value='"+array.reciver_info.phone+"'></span> <span>收货地址：<input type='text' class='shipinfo' column='reciver_info' id='address_"+array.order_id+"' order_id='"+array.order_id+"' column_name='收货地址' style='width:260px;' value='"+array.reciver_info.address+"'></span></tr>";
//                        var arrlength = array.suborder.length;
                        $.each(array.suborder, function(x, y) {

                            var stateName='';
                            if(array.order_state=='0'){
                            	stateName='已取消';
                            }else if(array.order_state=='10'){
                            	stateName='未支付';
                            }else if(array.order_state=='20'){
                            	stateName='已付款';
                            }else if(array.order_state=='30'){
                            	stateName='已发货';
                            }else if(array.order_state=='40'){
                            	stateName='已完成';
                            }
                            $expressId='';
                            $expressName='';
                            if(array.shipping_code!=null&&array.shipping_code!=''){
                            	$expressId=array.shipping_code;
                            }
                            if(array.express_name!=null&&array.express_name!=''){
                            	$expressName=array.express_name;
                            }
                            var goods_total = y.goods_price*y.goods_num;
                            li += "<tr><td><img src='http://www.hangowa.com/" + y.goods_image + "' width='60px' height='60px'  /></td><td>" + array.fx_order_id + "</td><td>" + y.goods_name + "</td><td>" + y.goods_name + "</td><td>" + y.goods_price + "</td><td>" + y.goods_num + "</td><td>" + stateName + "</td><td>" + goods_total.toFixed(2) + "</td><td>" + $expressName + "</td><td>" + $expressId + "</td><td>" + (x == 0 ? array.order_amount : '') + "</td>"+"</tr>";//rowspan='"+arrlength+"'
                        });
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
        $("#page a").live('click', function() { //live 向未来的元素添加事件处理器,不可用bind
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
        var istarttime=$('#istarttime').val();
        var iendtime=$('#iendtime').val();
        var status = $("#status").val();
        $("#hidoid").val(oid);
        $("#hidfxoid").val(fxoid);
        $("#hidstarttime").val(starttime);
        $("#hidendtime").val(endtime);
        $("#exp_istarttime").val( istarttime );
        $("#exp_iendtime").val( iendtime );
        if(starttime==""&&endtime==""&&istarttime==""&&iendtime==""&&oid==""&&fxoid==""){
            alert("必须输入【筛选条件】");
            return false;
        }
        if(starttime!==""&&endtime==""){
            alert("必须输入【下单时间】的结束时间");
            return false;
        }
        if(starttime==""&&endtime!==""){
            alert("必须输入【下单时间】的开始时间");
            return false;
        }
        if(istarttime!==""&&iendtime==""){
            alert("必须输入【导入时间】的结束时间");
            return false;
        }
        if(istarttime==""&&iendtime!==""){
            alert("必须输入【导入时间】的开始时间");
            return false;
        }
        d1=new Date(Date.parse(starttime));
        d2=new Date(Date.parse(endtime));
        d3=new Date(Date.parse(istarttime));
        d4=new Date(Date.parse(iendtime));
        if(d1>=d2){
            alert('【下单时间】的结束时间必须不小于开始时间');
            return false;
        }
        if(d3>=d4){
            alert('【导入时间】的结束时间必须不小于开始时间');
            return false;
        }
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
        $("#istarttime").val('');
        $('#iendtime').val('');
        $("#oid").val('');
        $("#fxoid").val('');
        var status = $("#status").val();
        getData(1, status);
//        return false;
    });
</script>

<script>
    $(function() {
        $("input").blur(function(){
            console.log("abc");
        });
    });
</script>
