<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div id="flexigrid"></div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=bill&op=get_bill_info_xml&query_type=<?php echo $_GET['query_type'];?>&ob_id=<?php echo $_GET['ob_id'];?>',
        colModel : [
//            {display: '操作', name : 'operation', width : 130, sortable : false, align: 'center'},
           {display: '日志编号', name : 'obl_id', width : 130, sortable : false, align: 'center'},
            {display: '付款日期', name : 'obl_pay_date', width : 130, sortable : false, align: 'center'},
            {display: '支付金额', name : 'obl_success_amount', width : 100, sortable : false, align: 'center'},
            {display: '供应商名称', name : 'supplier_name', width : 100, sortable : false, align: 'center'},
            {display: '支付单号', name : 'payment_sn', width : 200, sortable : false, align: 'center'},
            {display: '付款凭证', name : 'attachment', width : 100, sortable : false, align: 'center'},
            {display: '支付备注', name : 'obl_pay_content', width : 300, sortable : false, align: 'center'},
            ],
        buttons : [
        ],
        searchitems : [
            {display: '日志编号', name : 'obl_id', isdefault: true},
       ],
        sortname: "obl_id",
        sortorder: "desc",
        title: '账单-结算日志列表'
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
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString() +'&ob_id=<?php echo $_GET['ob_id'];?>&op=export_order&order_id='+id;
}
</script>
