<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .flexigrid .hDiv th div, .flexigrid .bDiv td div, .colCopy div{
        white-space: initial;
        height: auto;
    }
</style>
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
              <dt>订单编号</dt>
              <dd>
                <label><input type="text" value="" name="order_sn" id="order_sn" class="s-input-txt"></label>
                  <label><input type="checkbox" value="1" name="log_role">只看商家调整</label>
              </dd>
            </dl>
          </div>
        </div>
        <div class="bottom">
          <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green">提交查询</a>
          <a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a>
        </div>
      </form>
    </div>
<script type="text/javascript">
$(function(){
    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=bill&op=get_bill_info_xml&query_type=<?php echo $_GET['query_type'];?>&ob_id=<?php echo $_GET['ob_id'];?>&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });

    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=bill&op=get_bill_info_xml&query_type=<?php echo $_GET['query_type'];?>&ob_id=<?php echo $_GET['ob_id'];?>'}).flexReload();
        $("#formSearch")[0].reset();
    });
    $("#flexigrid").flexigrid({
        url: 'index.php?act=bill&op=get_bill_info_xml&query_type=<?php echo $_GET['query_type'];?>&ob_id=<?php echo $_GET['ob_id'];?>',
        colModel : [
            {display: '操作', name : 'operation', width : 130, sortable : false, align: 'center'},
            {display: '调整时间', name : 'log_time', width : 130, sortable : false, align: 'center'},
            {display: '调整内容', name : 'log_msg', width : 500, sortable : false, align: 'center'},
			{display: '操作角色', name : 'log_role', width : 100, sortable : false, align: 'center'},
            {display: '操作人', name : 'log_user', width : 100, sortable : false, align: 'left'},
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate}
        ],
        searchitems : [
            {display: '操作人', name : 'log_user', isdefault: true},
       ],
        sortname: "order_id",
        sortorder: "desc",
        title: '账单-订单列表'
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
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString() +'&ob_id=<?php echo $_GET['ob_id'];?>&op=export_modify&order_id='+id;
}
</script>
