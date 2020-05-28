<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>平台客服</h3>
        <h5>客服经理对客服设定与处理</h5>
      </div>
    </div>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=kefu_manager&op=get_member_list_xml&kefu_id=<?php echo intval($_GET['kefu_id']); ?>',
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: '用户名称', name : 'member_name', width : 250, sortable : false, align: 'left'},
            ],
        buttons : [
            {display: '<i class="fa fa-trash"></i>批量删除', name : 'delete', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
        ],
        title: '客服《<?php echo $output['kefu_info']['admin_name'];?>》的会员列表'
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

function fg_operate(name, grid) {
    if (name == 'delete') {
        if($('.trSelected',grid).length>0){
            var itemlist = new Array();
            $('.trSelected',grid).each(function(){
                itemlist.push($(this).attr('data-id'));
            });
            fg_delete(itemlist);
        } else {
            return false;
        }
    }
}

function fg_delete(id) {
    if (typeof id == 'number') {
        var id = new Array(id.toString());
    };
    if(confirm('删除后将不能恢复，确认删除这 ' + id.length + ' 项吗？')){
        id = id.join(',');
    } else {
        return false;
    }
    window.location.href ='index.php?act=kefu_manager&op=del_member&del_id='+id;
}
</script> 
