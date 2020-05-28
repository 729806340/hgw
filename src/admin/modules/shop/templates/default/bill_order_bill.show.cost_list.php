<?php defined('ByShopWWI') or exit('Access Invalid!');?>
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
                        </dd>
                    </dl>
                    <dt>按结算状态筛选</dt>
                    <dd>
                        <label>
                            <select name="search_type" id="search_type" class="s-select">
                                <option value="" <?php echo $output['search_arr']['search_type']=='day'?'selected':''; ?>>请选择</option>
                                <option value="异常" <?php echo $output['search_arr']['search_type']=='异常'?'selected':''; ?>>异常</option>
                                <option value="第1次结算" <?php echo $output['search_arr']['search_type']=='第1次结算'?'selected':''; ?>>第1次结算</option>
                                <option value="第2次结算" <?php echo $output['search_arr']['search_type']=='第2次结算'?'selected':''; ?>>第2次结算</option>
                                <option value="第3次结算" <?php echo $output['search_arr']['search_type']=='第3次结算'?'selected':''; ?>>第3次结算</option>
                                <option value="第1次结算正常" <?php echo $output['search_arr']['search_type']=='第1次结算正常'?'selected':''; ?>>第1次结算正常</option>
                                <option value="第2次结算正常" <?php echo $output['search_arr']['search_type']=='第2次结算正常'?'selected':''; ?>>第2次结算正常</option>
                                <option value="第3次结算正常" <?php echo $output['search_arr']['search_type']=='第3次结算正常'?'selected':''; ?>>第3次结算正常</option>
                                <option value="第1次结算异常" <?php echo $output['search_arr']['search_type']=='第1次结算异常'?'selected':''; ?>>第1次结算异常</option>
                                <option value="第2次结算异常" <?php echo $output['search_arr']['search_type']=='第2次结算异常'?'selected':''; ?>>第2次结算异常</option>
                                <option value="第3次结算异常" <?php echo $output['search_arr']['search_type']=='第3次结算异常'?'selected':''; ?>>第3次结算异常</option>
                                <option value="已结算" <?php echo $output['search_arr']['search_type']=='已结算'?'selected':''; ?>>已结算</option>
                                <option value="未对账" <?php echo $output['search_arr']['search_type']=='未对账'?'selected':''; ?>>未对账</option>
                            </select>
                        </label>
                    </dd>
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
            {display: '店铺名称', name : 'store_name', width : 150, sortable : false, align: 'left'},
            {display: '促销名称', name : 'cost_remark', width : 130, sortable : false, align: 'left'},
            {display: '促销费用', name : 'cost_price', width : 110, sortable : false, align: 'left'},
			{display: '申请日期', name : 'cost_time', width : 80, sortable : false, align : 'center'}, 
            {display: '分销订单号', name : 'fx_order_id', width : 80, sortable : false, align : 'center'},
            {display: 'send_sap', name : 'send_sap', width : 80, sortable : false, align : 'center'},
            {display: 'purchase_sap', name : 'purchase_sap', width : 80, sortable : false, align : 'center'},
            {display: '是否结算', name : 'check_result', width : 80, sortable : false, align : 'center'},
            {display: '异常原因', name : 'errInf', width : 80, sortable : false, align : 'center'},
            {display: '是否匹配', name : 'check_status', width : 80, sortable : false, align : 'center'}
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate}
        ],
        searchitems : [
            {display: '是否匹配', name : 'check_status'}
        ],
        sortname: "cost_id",
        sortorder: "desc",
        title: '账单-店铺费用列表'
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
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString() +'&ob_id=<?php echo $_GET['ob_id'];?>&op=export_cost&order_id='+id;
}


</script>