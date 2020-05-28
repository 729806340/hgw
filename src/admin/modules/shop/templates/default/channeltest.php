<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>渠道接口调试</h3>
            </div>
        </div>
    </div>
    <!-- 操作说明 -->
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span>
        </div>
        <ul>
            <li>选择渠道->选择测试接口->填写接口所需参数->提交->显示反馈结果</li>
        </ul>
    </div>
    <form method="post"  target="nm_iframe" action="index.php?act=channelfill&op=get_channelorder"  id="get_channelorder">
        选择调试渠道:
        <select id="channel_name" name="channel_name">
            <?php foreach($output['member_fenxiao'] as $key=>$item){?>
                <option value="<?php echo $key;?>"><?php echo $item; ?></option>
            <?php } ?>
        </select>&nbsp;&nbsp;
        <select id="api_type" name="api_type">
            <?php foreach($output['config'] as $key=>$value){ ?>
            <option value="<?php echo $key;?>" <?php if($value=='orderlist') echo 'selected';?>><?php echo $value;?></option>
            <!--option value="2">退货抓取</option-->
            <?php }?>
        </select>&nbsp;&nbsp;
    </form>
    <div class="frame"><div class="form"></div><div class="transport"><button class="btn btn-success transport_bt">传送>></button></div><div class="result"></div></div>
</div>
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<script charset="utf-8" type="text/javascript"  src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"  href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon.js"></script>
<style type="text/css">
    .frame{
        margin-top:5px;
        width:100%;
        height:55em;
    }
    .form{
        margin:0.4em 0.4em 0.4em 0.4em;
        border: #acafbc solid 1px;
        width:44%;
        height:54.5em;
        float:left;
        display: inline-block;
        border-radius: 5px;
    }
    .result{
        margin:0.4em 0.4em 0.4em 0.4em;
        border: #acafbc solid 1px;
        width: 48%;
        height:54.5em;
        float:right;
        display: inline-block;
        border-radius: 5px;
    }
    .transport{
        width: 4.5%;
        float: left;
        margin: 18.8em auto auto 0.3em;
        display: inline-block;
    }
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
    .ui-datepicker-month{
        background-color:#ffffff;
        color:black;
    }
    .ui-datepicker-year{
        background-color:#ffffff;
        color:black;
    }
</style>
<script type="text/javascript">
    $(function(){
        function getFrom(channel, api) {
            $.ajax({
                url: '/admin/modules/shop/index.php?act=channeltest&op=getConfigForAjax',
                data: {
                    channel: channel,
                    api: api
                },
                type: "POST",
                dataType: "JSON",
                success: function (data) {
                    var html = "<form style='margin:15px 15px auto 15px;'><input type='hidden' class=\"form-element\" name='channel' value='"+channel+"'>";
                    html += "<input type='hidden' class=\"form-element\" name='api' value='"+api+"'>";
                    html += "<input type='hidden' class=\"form-element\" name='method' value='"+data.request.method+"'>";
                    html += "<input type='hidden' class=\"form-element\" name='curl_type' value='"+data.request.curl_type+"'>";
                    html += "<input type='hidden' class=\"form-element\" name='para_is_json' value='"+data.request.para_is_json+"'>";
                    if(data.comment!==""){html += "<h5>注意:<small>" + data.comment + "</small></h5>";}
                    $.each(data.form, function (index, item) {
                        html += '<div class="form-group"><label for="'+item.name+'">' + item.label + "</label>";
                        html += "<small>" + item.comment;
                        if (!item.is_null){html += "&nbsp;&nbsp;<font style='color:red'>必填项</font></small>";}else{html+="</small>"}
                        if (item.item_type !== 2) {
                            html += '<input class="form-element form-control" id="'+item.name+'" name="' + item.name+'"' ;
                            if (!item.is_null){html += "required=\"true\">";}else{
                                html+=">";
                            }
                        }
                        if (item.item_type == 2) {
                            html += '<select class="form-element form-control" id="'+item.name+'" name="' + item.select_name + '">';
                            $.each(item.select_op, function (i, v) {
                                html += '<option value="' + i + '"'+v.check+'>' + v.cn + '</option>';
                            });
                            html += '</select>';
                        }
                        html+="</div>";
                    });
                    html+='</form>'
                    $('.form').empty();
                    $('.form').append(html);
                    $("#"+data.startTime).datetimepicker({
                        showSecond: true,
                        showMillisec: true,
                        timeFormat: 'hh:mm:ss'
                    });
                    $("#"+data.endTime).datetimepicker({
                        showSecond: true,
                        showMillisec: true,
                        timeFormat: 'hh:mm:ss'
                    });
                }
            })
        }
        //初始化订单页面
        getFrom('beibeiwang','orderlist');
        $('#start_time').datetimepicker({
            showSecond: true,
            showMillisec: true,
            timeFormat: 'hh:mm:ss'
        });
        $('#end_time').datetimepicker({
            showSecond: true,
            showMillisec: true,
            timeFormat: 'hh:mm:ss'
        });
        $("#channel_name").change(function(){
            var channel=$('#channel_name').val();
            $.ajax({
                url: '/admin/modules/shop/index.php?act=channeltest&op=getConfigForAjax',
                data: {
                    channel: channel,
                    api:'selectItem'
                },
                type: "POST",
                dataType: "JSON",
                success: function (data) {
                    var html = "";
                    $.each(data, function (index, item) {
                            html+='<option value="'+index+'">'+item+'</option>';
                    });
                    $('#api_type').empty();
                    $('#api_type').append(html);
                }
            });
            var channel=$('#channel_name').val();
            var api_type=$('#api_type').val();
            getFrom(channel,api_type);
        })
        $("#api_type").change(function(){
            var channel=$('#channel_name').val();
            var api_type=$('#api_type').val();
            getFrom(channel,api_type);
        });
        $("#id_iframe").load(function(){
            $("#message").hide();
        })
            $('.transport_bt').click(function(){
                var form=$('.form-element');
                var data='{';
                var flag=1;
                $.each(form,function(index){
                    data+="\""+$(this).attr('name')+"\":\""+$(this).val()+"\"";
                    if($(this).prop('required')!==false&&$(this).val()==""){
                        alert('请填写必填项：'+$(this).prev().prev().html());
                        flag=0;
                        return false;
                    }
                    if(index<form.length-1) data+=',';
                });
                data+="}";
                if(flag==0) return false;
                $.ajax({
                    url: "/admin/modules/shop/index.php?act=channeltest&op=getApi",
                    data:{
                        data:data
                    },
                    type: "POST",
                    dataType: "JSON",
                    success: function (data) {
                        $('.result').empty();
                        $('.result').append("<textarea style='width:100%;height:100%;resize: none;'>"+JSON.stringify(data, null, 4)+"</textarea>");
                    }
                });
        });
    });
</script>
