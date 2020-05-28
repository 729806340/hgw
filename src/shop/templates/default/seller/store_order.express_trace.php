<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>

<form method="get" action="index.php" target="_self">
    <table class="search-form">
        <input type="hidden" name="act" value="store_deliver" />
        <input type="hidden" name="op" value="express_trace" />
        <?php if ($_GET['state'] !='') { ?>
            <input type="hidden" name="state"
                   value="<?php echo $_GET['state']; ?>" />
        <?php } ?>
        <tr>
            <th>发货时间</th>
            <td class="w240"><input type="text" class="text w70" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>
                &nbsp;&#8211;&nbsp; <input id="query_end_date" class="text w70" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
            <th><?php echo $lang['store_order_order_sn'];?></th>
            <td class="w160"><input type="text" id="order_sn" class="text w150" name="order_sn" value="<?php echo trim($_GET['order_sn']); ?>" /></td>
            <th>分销订单编号</th>
            <td class="w160"><input type="text" id="fx_order_id" class="text w150" name="fx_order_id" value="<?php echo trim($_GET['fx_order_id']); ?>" /></td>


            <th>状态</th>
            <td class="w160">
                <select name="trace_status" id="trace_status">
                    <option value="">全部</option>
                    <?php
                    foreach($output['trace_status'] as $item =>$value){
                    ?>
                        <option value="<?php echo $item?>" <?php if($_GET['trace_status'] !=null && $item==$_GET['trace_status'] ){?>selected<?php }?>><?php echo $value?></option>
                    <?php }?>
                </select>
            </td>
            <td class="w70 tc">
                <label class="submit-border">
                    <input type="submit" class="submit" value="<?php echo $lang['store_order_search'];?>" />
                </label>
            </td>
            <!--<td><label class="submit-border">
                    <input id="toExcel" name='execl' type="button" class="submit" value="<?php /*echo $lang['store_order_toexcel']; */?>" />
                </label></td>
            <td></td>-->
        </tr>
    </table>
</form>
<div class="alert alert-info mt10" id="showBatchResult" style="display:none;">

</div>

<table class="ncsc-default-table">
<thead>
<tr>
    <th class="w10"></th>
    <th>订单编号</th>
    <th>分销编号</th>
    <th>物流公司</th>
    <th>物流单号</th>
    <th>发货时间</th>
    <th>分销平台</th>
    <th>状态</th>
    <th><?php echo $lang['nc_handle'];?></th>
</tr>
</thead>
<tbody>
<?php if (is_array($output['traceList']) && !empty($output['traceList'])) { ?>
    <?php foreach($output['traceList'] as $order_info) { ?>
        <tr class="bd-line">
            <td></td>
            <td class="w90"><?php echo $order_info['order_sn'];?></td>
            <td><?php echo $order_info['fx_order_id'];?></td>
            <td><?php echo isset($output['express'][$order_info['express_id']])?$output['express'][$order_info['express_id']]['e_name']:'其他';?></td>
            <td><?php echo $order_info['shipping_code'];?></td>
            <td><?php echo date('Y-m-d',$order_info['add_time']);?></td>
            <td><?php echo $output['fenxiaoMember'][$order_info['buyer_name']]['member_cn_code'];?></td>
            <td><?php echo isset($output['trace_status'][$order_info['is_sendfx']])?$output['trace_status'][$order_info['is_sendfx']]:'异常';?></td>
            <td>
        <?php if(!$order_info['is_sendfx']){?>

        <a class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="查看物流" dialog_id="add_map" dialog_width="480" uri="index.php?act=store_deliver&op=show_trace&et_id=<?php echo $order_info['et_id']?>">查看物流</a>
                <a class="ncbtn ncbtn-mint" href="index.php?act=store_deliver&op=fxorder_send&et_id=<?php echo $order_info['et_id']?>">手动推送</a>
        <?php } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
    </tr>
<?php } ?>
</tbody>
<tfoot>
<?php if (is_array($output['traceList']) && !empty($output['traceList'])) { ?>
    <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
<?php } ?>
</tfoot>
</table>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
        $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});

        //显示上传div
        $('#deliver').click(function(){
            var display =$('#deliverDiv').css('display');
            if(display == 'none'){
                $('#deliverDiv').show('fast');
            }else{
                $('#deliverDiv').hide('fast');
            }
        });

        //上传前进行判断
        $('#upload').click(function (){
            var file = $('#uploadsfile');
            var fileType = getFiletype(file.val());
            var allowtype = ['CSV','XLS','XLSX'];
            if($.trim(file.val())==''){
                showError('请选择文件');return false;
            }
            if ($.inArray(fileType,allowtype) == -1)
            {
                showError('请选择正确的文件类型');return false;
            }
        });

        //ajax上传文件
        $('#batch_file').fileupload({
            dataType: 'json',
            url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload',
            done: function (e,data) {
                var param = data.result;
                if(param.state==false){
                    showError(param.msg);
                    return false;
                }
                var tips = "<ul class=\"mt5\"><li>文件上传成功!</li>";
                tips +="<li>批量设置发货的订单有<font color=\"red\">"+param.result.totals+"</font>个；</li>";
                tips +="<li>发货成功的订单有<font color=\"red\">"+param.result.succNum+"</font>个；</li>";
                tips +="<li>发货失败的订单有<font color=\"red\">"+param.result.failNum+"</font>个；</li>";
                if(parseInt(param.result.failNum)>0){
                    tips +="<li>发送的失败的订单编号有：</li>";
                    for(var i = 0 ; i<param.result.failOrderids.length; i++){
                        tips +=param.result.failOrderids[i]+"、";
                    }
                }
                if(parseInt(param.result.failNum)>0){
                    tips +="<li>失败原因：</li>";
                    for(var i = 0 ; i<param.result.errorMsg.length; i++){
                        tips +=	"<li>"+param.result.errorMsg[i]+"</li>";
                    }
                }
                tips +="</ul>";
                $("#showBatchResult").html(tips);
                $("#showBatchResult").show('fast');
                //showSucc(tips);
                //setTimeout("window.location.reload()", 3000);
            }
        });
        //获取上传文件类型
        function getFiletype(filePath)
        {
            var extStart  = filePath.lastIndexOf(".")+1;
            return filePath.substring(extStart,filePath.length).toUpperCase();
        }

        $("#toExcel").click(function () {
            var query_start_date = $("#query_start_date").val();
            var query_end_date   = $("#query_end_date").val();
            var express_code = $("#express_code").val();
            var trace_status  =$("#trace_status").val();
            var order_sn  = $("#order_sn").val();

            if(query_start_date==''){
                showError('请输入开始时间');
                return false;
            }

            if(query_end_date==''){
                showError('请输入结束时间');
                return false;
            }

            if(query_start_date>query_end_date){
                showError('开始时间不能大于结束时间');
                return false;
            }
            var urls = window.location;
            urls += urls+"?act=store_deliver&op=excel_printship";
            urls += "&query_start_date="+query_start_date+"&query_end_date="+query_end_date+"&express_code="+express_code;
            urls += "&ship_status="+ship_status+"&order_sn="+order_sn;
            window.location.href=urls;
        })
    });
</script>
