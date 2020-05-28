<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>渠道抓取</h3>
                <h5>根据时间段抓取未获得的订单</h5>
            </div>
            <?php echo $output['top_link'];?>
        </div>
    </div>
    <!-- 操作说明 -->
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span>
        </div>
        <ul>
            <li>目前只有部分分销商支持抓取订单的功能</li>
            <li>只能按照时间段来抓取，时间间隔不能超过一天</li>
            <li>只能查询上线的时间以后的订单</li>
            <li>抓取订单数据未保存，请及时核对或者自己建立txt保存</li>
        </ul>
    </div>
    <form method="post"  target="nm_iframe" action="index.php?act=channelfill&op=get_channelorder"  id="get_channelorder" onsubmit="return false;">
        <div style="float:left;line-height:21px;font-size:15px;margin-top:8px;padding-right:10px;padding-left: 5px;font-weight:100">搜索条件:</div>
        <select id="channel_id" name="channel_id">
            <?php foreach($output['member_fenxiao'] as $key=>$item){?>
                <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
            <?php } ?>
        </select>&nbsp;&nbsp;
        <select id="channel_type" name="channel_type">
            <option value="1">订单抓取</option>
            <!--option value="2">退货抓取</option-->
        </select>&nbsp;&nbsp;
        <input type="text" class="text w120" name="query_begin_date_by_channel" id="query_begin_date_by_channel" value="" placeholder="开始时间" />&nbsp;&nbsp;
        <input type="text" class="text w120" name="query_end_date_by_channel" id="query_end_date_by_channel" value=""  placeholder="结束时间"/>&nbsp;&nbsp;
        <a  type="button"   id="subBtn" class="ncap-btn ncap-btn-green">获取订单</a>
        <a style="padding-left:10px;font-size:15px;display:none" id="message">正在获取订单,请耐心等待.....</a>
    </form>
    <div id="id_iframe" name="nm_iframe" style="width:90%;height:550px;margin-top:20px;"> </div>

</div>
<script charset="utf-8" type="text/javascript"  src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"  href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon.js"></script>
<style type="text/css">
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
    .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
    .ui_tpicker_hour_label,.ui_tpicker_minute_label,.ui_tpicker_second_label,.ui_tpicker_millisec_label,.ui_tpicker_time_label{padding-left:20px}
    #get_channelorder{ margin-top:15px;}
    select{padding:4px;margin-top:-2px;}
    input[type="text"]{height:21px;margin-top:5px;line-height:21px;}
    #subBtn{
        padding:2px 8px;
        margin-top: -3px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#query_begin_date_by_channel').datetimepicker({
            showSecond: true,
            showMillisec: true,
            timeFormat: 'hh:mm:ss'
        });
        $('#query_end_date_by_channel').datetimepicker({
            showSecond: true,
            showMillisec: true,
            timeFormat: 'hh:mm:ss'
        });

        $("#subBtn").click(function(){
            if($("#query_begin_date_by_channel").val()=="" || $("#query_end_date_by_channel").val()==""){
                alert("请填写完整搜索时间");
                return false;
            }
            var begin_time=Date.parse($("#query_begin_date_by_channel").val().replace(/-/g,"/"))/1000;
            var end_time=Date.parse($("#query_end_date_by_channel").val().replace(/-/g,"/"))/1000;
            if(begin_time>end_time){
                alert("开始时间不能大于结束时间");
                return false;
            }
            var outtime=end_time*1-begin_time*1;
            if(outtime>7200*12){
                alert("最多只能查询间隔一天的时间");
                return false;
            }
            if(confirm("你确定要抓取此时间段的订单吗？")){
                $("#message").show();
                var html = '<ul style="padding-left: 30px;"><li>请求聚石塔调用第三方接口。请等待。。。</li></ul>';
                $('#id_iframe').html(html);
                var channel_id = $('#channel_id').val();
                $.ajax({
                    async : false,
                    type : 'GET',
                    dataType : 'JSONP',
                    url : "<?php echo $output['distributor_trades']; ?>",
                    data : {id: channel_id},
                    success : function (data){
                        // 查看分销订单是否都处理到订单表中
                        $('#id_iframe').children('ul').append('<li>请求聚石塔调用第三方接口完成，查看聚石塔数据是否处理完成。请等待。。。</li>');
                        var timer = setInterval(function () {
                            $.ajax({
                                async : false,
                                type : 'GET',
                                dataType : 'JSONP',
                                url : "<?php echo $output['distributor_orders']; ?>",
                                data : {id: channel_id},
                                success : function (data2){
                                    if (data2.count < 1) {
                                        $('#id_iframe').children('ul').append('<li>聚石塔数据处理完成，平台开始入库数据。请等待。。。</li>');
                                        clearInterval(timer);
                                        // 开始抓取汉购网数据
                                        var start_time = $('#query_begin_date_by_channel').val();
                                        var end_time = $('#query_end_date_by_channel').val();
                                        $.ajax({
                                            async : false,
                                            type : 'GET',
                                            url : "index.php?act=channelfill&op=get_channelorder",
                                            data : {id: channel_id, start_time: start_time, end_time: end_time},
                                            success : function (data3){
                                                // 开始抓取汉购网数据
                                                var data3 = eval("(" + data3 + ")");
                                                if (data3.errorCode) {
                                                    alert(data3.msg);
                                                } else {
                                                    if (data3.res == '') {
                                                        $('#id_iframe').html('<h1 style="color: red; padding-left: 30px;">数据为空！</h1>');
                                                    } else {
                                                        $('#id_iframe').html("<textarea style='width:100%;height:100%;resize: none;'>"+JSON.stringify(data3, null, 4)+"</textarea>");
                                                    }
                                                }
                                                $("#message").hide();
                                            }
                                        });
                                    }
                                }
                            });
                        }, 1000);
                    }
                });
            }
        })
    });
</script>
