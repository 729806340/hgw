<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
        <?php if($_GET['shequ_tuan_id']){ ?>
    <!--    <a class="back" href="index.php?act=tuan_list&op=tuan" title="返回列表">
            <i class="fa fa-arrow-circle-o-left"></i>
        </a>-->
        <?php }?>
      <div class="subject">
        <h3><?php echo $lang['order_manage'];?></h3>
        <h5><?php echo $lang['order_manage_subhead'];?></h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo $lang['order_help1'];?></li>
      <li><?php echo $lang['order_help2'];?></li>
      <li><?php echo $lang['order_help3'];?></li>
    </ul>
  </div>
    <div id="result"></div>
  <div id="flexigrid"></div>
  <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
  <div class="ncap-search-bar">
    <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
    <div class="title">
      <h3>高级搜索</h3>
    </div>
    <form method="get" name="formSearch" id="formSearch">
      <div id="searchCon" class="content">
        <div class="layout-box">
          <dl>
            <dt>关键字搜索</dt>
            <dd>
              <label>
                <select class="s-select" name="keyword_type">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="order_sn">订单编号</option>
                  <option value="buyer_name">买家账号</option>
                  <option value="buyer_phone">买家手机</option>
                  <option value="store_name">店铺名称</option>
                  <option value="pay_sn">支付单号</option>
                  <option value="shipping_code">发货单号</option>
                </select>
              </label>
              <label>
                <input type="text" value="" placeholder="请输入关键字" name="keyword" class="s-input-txt">
              </label>
              <label>
                <input type="checkbox" id="jq_query" value="1" name="jq_query">精确

              </label>
            </dd>
          </dl>
          <dl>
            <dt>日期筛选</dt>
            <dd>
              <label>
                <select class="s-select" name="qtype_time">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="add_time">下单时间</option>
                  <option value="payment_time">支付时间</option>
                  <option value="finnshed_time">完成时间 </option>
                    <option value="shipping_time">发货时间 </option>
                    <option value="import_time">导单时间 </option>
                    <option value="config_start_time">团购开始时间 </option>
                    <option value="config_end_time">团购结束时间 </option>
                    <option value="send_product_date">团购发货时间 </option>
                </select>
              </label>
              <label>
                <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
          <dl>
            <dt>金额筛选</dt>
            <dd>
              <label>
                <select class="s-select" name="query_amount">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="order_amount">订单金额</option>
                  <option value="shipping_fee">运费金额</option>
                  <option value="refund_amount">退款金额</option>
                </select>
              </label>
              <label>
                <input placeholder="请输入起始金额" name=query_start_amount value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input placeholder="请输入结束金额" name="query_end_amount" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
          <dl>
            <dt>支付方式</dt>
            <dd>
              <label>
                <select name="payment_code" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <?php foreach($output['payment_list'] as $val) { ?>
                  <option <?php if($_GET['payment_code'] == $val['payment_code']){?>selected<?php }?> value="<?php echo $val['payment_code']; ?>"><?php echo $val['payment_name']; ?></option>
                  <?php } ?>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>订单状态</dt>
            <dd>
              <label>
                <select name="order_state" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="10">待付款</option>
                  <option value="15">拼团中</option>
                  <option value="20">已支付(待发货)</option>
                  <option value="21">备货中</option>
                  <option value="30">待收货</option>
                  <option value="40">已完成</option>
                  <option value="0">已取消</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>订单来源</dt>
            <dd>
              <select name="order_from" class="s-select">
                <option value=""><?php echo $lang['nc_please_choose'];?></option>
                <option value="1">网站</option>
                <option value="2">移动端</option>
                <option value="3">分销</option>
                <option value="4">集采</option>
                <option value="6">小程序</option>
              </select>
            </dd>
            </dd>
          </dl>
          <dl>
            <dt>是否退款</dt>
            <dd>
              <label>
                <select name="refund_state" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="0">无退款</option>
                  <option value="1">部分退款</option>
                  <option value="2">全部退款</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>是否超区</dt>
            <dd>
              <label>
                <select name="non_deliver" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="0">未检测</option>
                  <option value="-1">未超区</option>
                  <option value="1">超区</option>
                  <option value="10">部分超区</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>是否导出商品明细</dt>
            <dd>
              <select name="export_goods" class="s-select">
                <option value=""><?php echo $lang['nc_please_choose'];?></option>
                <option value="">否</option>
                <option value="1">是</option>
              </select>
            </dd>
            </dd>
          </dl>
            <input type="hidden" name="shequ_tuan_id" id="shequ_tuan_id" value="">
            <input type="hidden" name="shequ_tz_id" id="shequ_tz_id" value="">
        </div>
      </div>
      <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
    </form>
  </div>
</div>

<div id="mask" style="position: fixed;top: 0;bottom: 0;left: 0;right: 0;background: #333;opacity: .3;z-index: 9999; display: none;">
</div>
<div id="loading" style="z-index:9999;position: fixed; top: 100px; width: 100%; text-align: center;display: none;">
    <p style="background:#FFF;margin: 100px auto; width: 300px; padding: 20px 30px; font-size: 16px;">正在处理，请勿关闭页面...</p>
</div>
<div style="display: none;">
    <input type="file" id="data-file" name="file">
</div>


<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script type="text/javascript">
$(function(){
	$('#query_start_date').datepicker();
    $('#query_end_date').datepicker();

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=order&op=get_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });
    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=order&op=get_xml'}).flexReload();
        $("#formSearch")[0].reset();
    });

    var file = $('#data-file'),mask = $('#mask'),loading = $('#loading'),resultPanel = $('#result'),uploaded=false;
    file.change(function (e) {
        console.log(e,this);
        if(this.files.length>0){
            console.log(e,this.files);
            var file = this.files[0],filename = file.name;
            if(/.*\.csv$/i.test(filename) === false) return alert('仅支持CSV文件');
            mask.fadeIn();loading.fadeIn();
        }
    });

    file.fileupload({
        dataType: 'json',
        url: 'index.php?act=order&op=import_service_fee',
        done: function (e,data) {
            mask.fadeOut();loading.fadeOut();
            uploaded=true;
            var param = data.result;
            if(param.state==false){
                showError(param.msg);
                return false;
            }
            showSucc('导入完成。请核对导入结果，且不要重复导入。');
            var tips = '<div class="title"><h4>文件上传成功</h4> </div>';
            tips += "<ul class=\"mt5\">";
            tips +="<li>批量更新服务费的订单有<font color=\"red\">"+param.result.total+"</font>个；</li>";
            tips +="<li>更新服务费成功的有<font color=\"red\">"+param.result.success+"</font>个；</li>";
            tips +="<li>更新服务费失败的有<font color=\"red\">"+param.result.fail.length+"</font>个；</li>";
            if(param.result.fail.length>0){
                tips +="<li>更新服务费的失败的编号有：</li>";
                for(var i = 0 ; i<param.result.fail.length; i++){
                    tips +=param.result.fail[i]+"、";
                }
                tips +="<li>失败原因：</li>";
                for(var i = 0 ; i<param.result.errorMsg.length; i++){
                    tips +=	"<li>"+param.result.errorMsg[i]+"</li>";
                }
            }
            tips +="</ul>";
            resultPanel.html(tips).fadeIn('fast');
        },
        fail : function () {
            mask.fadeOut();loading.fadeOut();
            uploaded=true;
            showError('上传失败');
        }
    });
    //判断是否是站外请求
    var query_state_date = '<?php echo $_GET['query_start_date']?>';
    var url = 'index.php?act=order&op=get_xml';
    if(query_state_date){
        $("#query_start_date").val(query_state_date);
        $("#query_end_date").val('<?php echo $_GET['query_end_date']?>');
        $("input[name='keyword']").val('<?php echo $_GET['keyword']?>');
        $("#jq_query").attr("checked","checked");;
        $("select[name='qtype_time']").val('<?php echo $_GET['qtype_time']?>');
        $("select[name='keyword_type']").val('<?php echo $_GET['keyword_type']?>');
        $("select[name='order_from']").val('<?php echo $_GET['order_from']?>');
        url = url+"&"+$("#formSearch").serialize();
    }
    //社区团订单详情
    var  shequ_tuan_id  = '<?php echo $_GET['shequ_tuan_id']; ?>'
      console.log(shequ_tuan_id);
        if(shequ_tuan_id){
            $("#shequ_tuan_id").val(<?php echo $_GET['shequ_tuan_id']; ?>);
            $("#shequ_tz_id").val(<?php echo $_GET['shequ_tz_id']; ?>);
            url = url+"&"+$("#formSearch").serialize();
        }
        console.log(url);
    $("#flexigrid").flexigrid({
        url: url,
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: '订单编号', name : 'order_sn', width : 150, sortable : false, align: 'left'}, 
			{display: '订单来源', name : 'order_from', width : 50, sortable : true, align : 'center'},           
			{display: '下单时间', name : 'order_id', width : 140, sortable : true, align: 'left'},
			{display: '订单金额(元)', name : 'order_amount', width : 100, sortable : true, align: 'left'},
			{display: '订单状态', name : 'order_state', width: 120, sortable : true, align : 'center'},                                           
            {display: '支付单号', name : 'pay_sn', width : 140, sortable : false, align: 'left'},
			{display: '支付方式', name : 'payment_code', width: 60, sortable : true, align : 'center'},
			{display: '支付时间', name : 'payment_time', width: 140, sortable : true, align : 'left'}, 
            {display: '充值卡支付(元)', name : 'rcb_amount', width : 70, sortable : true, align: 'center'},
            {display: '预存款支付(元)', name : 'pd_amount', width : 70, sortable : true, align: 'center'},                       
            {display: '发货物流单号', name : 'shipping_code', width : 120, sortable : false, align : 'left'},
			{display: '退款金额(元)', name : 'refund_amount', width : 80, sortable : true, align: 'center'},
			{display: '订单完成时间', name : 'finnshed_time', width: 120, sortable : true, align : 'left'},
            {display: '是否评价', name : 'evaluation_state', width : 80, sortable : true, align: 'center'},            
            {display: '店铺ID', name : 'store_id', width : 40, sortable : true, align: 'center'},
			{display: '店铺名称', name : 'store_name', width : 200, sortable : true, align: 'left'}, 
			{display: '买家ID', name : 'buyer_id', width : 40, sortable : true, align: 'center'},
			{display: '买家账号', name : 'buyer_name', width : 150, sortable : true, align: 'left'},
            {display: 'sap应收推送状态', name : 'send_sap', width : 60, sortable : true, align: 'left'},
            {display: 'sap应付推送状态', name : 'purchase_sap', width : 60, sortable : true, align: 'left'},
			{display: '分销订单号', name : 'fx_order_id', width : 150, sortable : true, align: 'center'},
            {display: '订单发货时间', name : 'shipping_time', width : 150, sortable : true, align: 'center'}
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出excel文件,如果不选中行，将导出列表所有数据', onpress : fg_operate },
            /*{display: '<i class="fa fa-file-excel-o"></i><a href="/admin/public/service_fee_tpl.csv">服务费模板</a>', name : 'csv', bclass : 'csv', title : '下载服务费修改模板' },
            {display: '<i class="fa fa-plus"></i>导入数据', name : 'import', bclass : 'add', title : '批量导入新数据到列表', onpress : import_service_fee}*/
        ],
        searchitems : [
            {display: '订单编号', name : 'order_sn', isdefault: true},
            {display: '订单序号', name : 'order_id'},
            {display: '买家账号', name : 'buyer_name'},
            {display: '买家手机', name : 'buyer_phone'},
            {display: '店铺名称', name : 'store_name'},
            {display: '支付单号', name : 'pay_sn'},
            {display: '分销订单号', name : 'fx_order_id'},
            {display: '团长姓名', name : 'name'},
            {display: '团长电话', name : 'phone'}
            ],
        sortname: "order_id",
        sortorder: "desc",
        title: '线上交易实物订单明细'
    });
});
function fg_operate(name, grid) {
    if (name == 'csv') {
    	var itemlist = new Array();
        if($('.trSelected',grid).length>0){
            $('.trSelected',grid).each(function(){
            	itemlist.push($(this).attr('data-id'));
            });
        }
        fg_csv(itemlist);
    }
}
function fg_csv(ids) {
    id = ids.join(',');
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_step1&order_id=' + id;
}
function fg_cancel(id, is_fx=0) {
	if (typeof id == 'number') {
    	var id = new Array(id.toString());
	};
	if(confirm('取消后将不能恢复，确认取消这 ' + id.length + ' 项吗？')){
		id = id.join(',');
	} else {
        return false;
    }
	$.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php?act=order&op=change_state&state_type=cancel&is_fx="+is_fx,
        data: "order_id="+id,
        success: function(data){
            if (data.state){
                $("#flexigrid").flexReload();
            } else {
            	alert(data.msg);
            }
        }
    });
}
function opb2b(id, op, opname) {
	if (typeof id == 'number') {
    	var id = new Array(id.toString());
	};
	if(confirm('操作后将不能恢复，确认执行 "'+ opname +'" 吗？')){
		id = id.join(',');
	} else {
        return false;
    }
	$.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php?act=order&op=change_state&state_type="+op,
        data: "order_id="+id,
        success: function(data){
            if (data.state){
                $("#flexigrid").flexReload();
            } else {
            	alert(data.msg);
            }
        }
    });
}

function import_service_fee() {
    $('#data-file').click();
}

<?php if ($output['buyer_name']) { ?>
  setTimeout(function() {
    $('select[name="qtype"]').val('buyer_name');
    $('.qsbox').val('<?php echo $output["buyer_name"]; ?>');
    $(':button').click();
  }, 1000);
<?php } ?>


</script> 
