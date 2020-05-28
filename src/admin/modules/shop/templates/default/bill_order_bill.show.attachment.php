<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .flexigrid .hDiv th div, .flexigrid .bDiv td div, .colCopy div{
        white-space: initial;
        height: auto;
    }
</style>
<div id="flexigrid"></div>
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
            {display: '附件编号', name : 'log_id', width : 130, sortable : false, align: 'center'},
            {display: '添加时间', name : 'log_time', width : 130, sortable : false, align: 'center'},
            {display: '附件描述', name : 'log_msg', width : 600, sortable : false, align: 'center'},
			{display: '上传角色', name : 'log_role', width : 100, sortable : false, align: 'center'},
            {display: '上传人', name : 'log_user', width : 100, sortable : false, align: 'left'},
            ],
        buttons : [
        ],
        searchitems : [
            {display: '附件编号', name : 'log_id', isdefault: true},
       ],
        sortname: "log_id",
        sortorder: "desc",
        title: '账单-附件列表'
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
