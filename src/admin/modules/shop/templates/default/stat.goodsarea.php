<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>商品销量区域分析</h3>
                <h5>平台针对商品的区域数据统计</h5>
            </div>
            <?php echo $output['top_link'];?> </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa "></i>
            <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
        <ul>
            <li><?php echo $lang['stat_validorder_explain'];?></li>
            <li>以下列表为符合搜索条件的有效订单中所有商品区域数据</li>
            <li>默认按照“下单商品件数”降序排列</li>
        </ul>
    </div>
    <div id="stat_tabs" class="  ui-tabs" style="min-height:500px">
        <ul class="tab-base nc-row">
            <li><a href="#goodsnum_div" nc_type="showmap" data-param='{"type":"goodsnum"}'>下单商品件数</a></li>
            <li><a href="#ordernum_div" nc_type="showmap" data-param='{"type":"ordernum"}'>下单单量</a></li>
            <li><a href="#goodsamount_div" nc_type="showmap" data-param='{"type":"goodsamount"}'>下单金额</a></li>
        </ul>

        <!-- 下单会员数 -->
        <div id="goodsnum_div" class="" style="text-align:center;"></div>
        <!-- 下单量 -->
        <div id="ordernum_div" class="" style="text-align:center;"></div>
        <!-- 下单金额 -->
        <div id="goodsamount_div" class="" style="text-align:center;"></div>
    </div>
  <!-- 统计列表 -->
  <div id="statlist" class=" " style="text-align:right;"></div>
  <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
  <div class="ncap-search-bar">
    <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
    <div class="title">
      <h3>高级搜索</h3>
    </div>
    <form method="get" action="index.php" name="formSearch" id="formSearch">
      <input type="hidden" name="act" value="stat_member" />
      <input type="hidden" name="op" value="area" />
      <input type="hidden" name="source" value="<?php echo $output['source'];?>" />
      <input type="hidden" name="channel" value="<?php echo $_REQUEST['channel'];?>" />
      <div id="searchCon" class="content">
        <div class="layout-box">
          <dl>
            <dt>按时间周期筛选</dt>
            <dd>
              <label>
                <select name="search_type" id="search_type" class="s-select">
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
  <script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/highcharts.js"></script>
  <script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/statistics.js"></script>
</div>
<script>
//展示搜索时间框
function show_searchtime(){
	s_type = $("#search_type").val();
	$("[id^='searchtype_']").hide();
	$("#searchtype_"+s_type).show();
}

$(function () {
	//切换登录卡
	$('#stat_tabs').tabs();

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

	$('#ncsubmit').click(function(){
    	$('#formSearch').submit();
    });

    //加载统计地图
    getStatMap('goodsnum');
    $("[nc_type='showmap']").click(function(){
    	var data_str = $(this).attr('data-param');
		eval('data_str = '+data_str);
		getStatMap(data_str.type);
    });
    //加载统计列表
    $("#statlist").flexigrid({
        url: 'index.php?act=stat_goods&op=get_goodsale_xml&source=<?php echo $output['source'];?>&t=<?php echo $output['searchtime'];?>&goods_id=<?php echo $output['goods_id'];?>&buyer_name=<?php echo $output['buyer_name'];?>',
        colModel : [
            {display: '操作', name : 'operation', width : 60, sortable : false, align: 'center', className: 'handle-s'},
            {display: '省份', name : 'provincename', width : 100, sortable : false, align: 'center'},
            {display: '下单商品件数', name : 'goodsnum',  width : 150, sortable : true, align: 'center'},
            {display: '下单单量', name : 'ordernum',  width : 150, sortable : true, align: 'center'},
            {display: '下单金额', name : 'goodsamount',  width : 150, sortable : true, align: 'center'}
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'excel', bclass : 'csv', title : '导出EXCEL文件', onpress : fg_operation }
        ],
        sortname: "membernum",
        sortorder: "desc",
        usepager: true,
        rp: 15,
        title: '区域分析'
    });
});
function fg_operation(name, bDiv){
    var stat_url = 'index.php?act=stat_goods&op=area_list&source=<?php echo $output['source'];?>&exporttype=excel&t=<?php echo $output['searchtime'];?>&goods_id=<?php echo $output["goods_id"];?>&buyer_name=<?php echo $output['buyer_name'];?>';
    get_excel(stat_url,bDiv);
}
//加载统计地图
function getStatMap(type){
	$('#'+type+'_div').load('index.php?act=stat_goods&op=map&goods_id=<?php echo $output["goods_id"];?>&source=<?php echo $output['source'];?>&type='+type+'&t=<?php echo $output['searchtime'];?>&buyer_name=<?php echo $output['buyer_name'];?>');
}
</script>