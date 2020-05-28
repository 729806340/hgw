<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>自有平台分析</h3>
        <h5>平台针对各自有平台销售情况的各项数据统计</h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>
 
  <div id="glist" class=" "></div>
  <div id="flexigrid"></div>
  <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
  <div class="ncap-search-bar">
    <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
    <div class="title">
      <h3>高级搜索</h3>
    </div>
    <form method="get" action="index.php" name="formSearch" id="formSearch">
      <div id="searchCon" class="content">
        <div class="layout-box">
          <dl>
            <dt>按时间周期筛选</dt>
            <dd>
              <label>
                <select name="search_type" id="search_type" class="class-select">
                  <option value="day" <?php echo $output['search_arr']['search_type']=='day'?'selected':''; ?>>按照天统计</option>
                  <option value="week" <?php echo $output['search_arr']['search_type']=='week'?'selected':''; ?>>按照周统计</option>
                  <option value="month" <?php echo $output['search_arr']['search_type']=='month'?'selected':''; ?>>按照月统计</option>
                </select>
              </label>
            </dd>
            <dd id="searchtype_day" style="display:none;">
              <label>
                <input class="s-input-txt" type="text" value="<?php echo @date('Y-m-d',$output['search_arr']['day']['search_time']);?>" id="search_time" name="search_time">
              </label>
            </dd>
            <dd id="searchtype_week" style="display:none;">
              <label>
                <select name="searchweek_year" class="s-select">
                  <?php foreach ($output['year_arr'] as $k => $v){?>
                  <option value="<?php echo $k;?>" <?php echo $output['search_arr']['week']['current_year'] == $k?'selected':'';?>><?php echo $v; ?>年</option>
                  <?php } ?>
                </select>
              </label>
              <label>
                <select name="searchweek_month" class="s-select">
                  <?php foreach ($output['month_arr'] as $k => $v){?>
                  <option value="<?php echo $k;?>" <?php echo $output['search_arr']['week']['current_month'] == $k?'selected':'';?>><?php echo $v; ?>月</option>
                  <?php } ?>
                </select>
              </label>
              <label>
                <select name="searchweek_week" class="s-select">
                  <?php foreach ($output['week_arr'] as $k => $v){?>
                  <option value="<?php echo $v['key'];?>" <?php echo $output['search_arr']['week']['current_week'] == $v['key']?'selected':'';?>><?php echo $v['val']; ?></option>
                  <?php } ?>
                </select>
              </label>
            </dd>
            <dd id="searchtype_month" style="display:none;">
              <label>
                <select name="searchmonth_year" class="s-select">
                  <?php foreach ($output['year_arr'] as $k => $v){?>
                  <option value="<?php echo $k;?>" <?php echo $output['search_arr']['month']['current_year'] == $k?'selected':'';?>><?php echo $v; ?>年</option>
                  <?php } ?>
                </select>
              </label>
              <label>
                <select name="searchmonth_month" class="s-select">
                  <?php foreach ($output['month_arr'] as $k => $v){?>
                  <option value="<?php echo $k;?>" <?php echo $output['search_arr']['month']['current_month'] == $k?'selected':'';?>><?php echo $v; ?>月</option>
                  <?php } ?>
                </select>
              </label>
            </dd>
          </dl>
        </div>
      </div>
      <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green">提交查询</a> </div>
    </form>
  </div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/statistics.js"></script>
<script>
//展示搜索时间框
function show_searchtime(){
	s_type = $("#search_type").val();
	$("[id^='searchtype_']").hide();
	$("#searchtype_"+s_type).show();
}
function fg_operate(name, grid) {
    if (name == 'csv') {
        window.location.href = 'index.php?act=stat_channel&op=exportCvs&'+$("#formSearch").serialize();
    }
}
function update_flex(){
	//加载统计列表
    $("#glist").flexigrid({
        url: 'index.php?act=stat_channel&op=get_device_xml&'+$("#formSearch").serialize(),
        colModel : [
            {display: '平台名称', name : 'channel', width : 120, sortable : false, align: 'center'},
            {display: '下单金额', name : 'order_amount', width : 150, sortable : false, align: 'center'},
            {display: '成本金额', name : 'cost_amount', width : 150, sortable : false, align: 'center'},
            {display: '下单量', name : 'order_num', width : 80, sortable : true, align: 'center'},
            {display: '下单会员数', name : 'membernum', width : 80, sortable : true, align: 'center'},
            {display: '退单金额', name : 'refund_amount', width : 150, sortable : false, align: 'center'},
            {display: '退单量', name : 'refund_num', width : 80, sortable : true, align: 'center'},
            //{display: '商品量', name : 'goods_num', width : 80, sortable : true, align: 'center'},
            {display: '复购率', name : 'reporate', width : 80, sortable : true, align: 'center'},
            {display: '均单价', name : 'aveprice', width : 80, sortable : true, align: 'center'},
            {display: '均单量', name : 'uniform', width : 80, sortable : true, align: 'center'},
            {display: '金额占比',name : 'scale', width : 80, sortable : true, align: 'center'},
            {display: '增长率', name : 'growthrate', width : 80, sortable : true, align: 'center'},
            {display: '增长数', name : 'growth', width : 80, sortable : true, align: 'center'},
            {display: '区域分布', name : 'area', width : 80, sortable : true, align: 'center'},
            {display: '订单明细', name : 'order_detail', width : 80, sortable : true, align: 'center'},
        ],
        buttons : [
            //{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operation }
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'excel', bclass : 'csv', title : '导出EXCEL文件', onpress : fg_operation }
        ],
        sortname: "order_amount",
        sortorder: "desc",
        usepager: true,
        rp: 15,
        title: '自有平台分析 '
    });
}

$(function () {
	//统计数据类型
	var s_type = $("#search_type").val();
	$('#search_time').datepicker({dateFormat: 'yy-mm-dd'});

	show_searchtime();
	$("#search_type").change(function(){
		show_searchtime();
	});

	//更新周数组
	$("[name='searchweek_month']").change(function(){
		var year = $("[name='searchweek_year']").val();
		var month = $("[name='searchweek_month']").val();
		$("[name='searchweek_week']").html('');
		$.getJSON('<?php echo ADMIN_SITE_URL?>/index.php?act=common&op=getweekofmonth',{y:year,m:month},function(data){
	        if(data != null){
	        	for(var i = 0; i < data.length; i++) {
	        		$("[name='searchweek_week']").append('<option value="'+data[i].key+'">'+data[i].val+'</option>');
			    }
	        }
	    });
	});

	$('#searchBarOpen').click();

	update_flex();

	$('#ncsubmit').click(function(){
	    $("#glist").flexOptions({url: 'index.php?act=stat_channel&op=get_device_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
	    //$("#flexigrid").flexOptions({url: 'index.php?act=stat_marketing&op=get_groupgoods_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
	    update_flex();
    });
});
function fg_operation(name, bDiv){
    //window.location.href = 'index.php?act=stat_channel&op=exportCvs&'+$("#formSearch").serialize();
    var stat_url = 'index.php?act=stat_channel&op=exportDeviceCvs';
    get_search_excel(stat_url,bDiv);
}
</script>