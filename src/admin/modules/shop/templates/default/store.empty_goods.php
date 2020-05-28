<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=store&op=store" title="返回<?php echo $lang['manage'];?>列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>零成本商品列表</h3>
        <h5><?php echo $output['store']['store_name'];?></h5>
      </div>
    </div>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=store&op=get_empty_cost_xml&store_id=<?php echo $output['store']['store_id'];?>',
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: 'SPU', name : 'goods_commonid', width : 60, sortable : true, align: 'center'},
            {display: '商品名称', name : 'goods_name', width : 150, sortable : false, align: 'left'},
            {display: '商品价格(元)', name : 'goods_price', width : 100, sortable : true, align: 'center'},
            {display: '商品图片', name : 'goods_image', width : 60, sortable : true, align: 'center'},
            {display: '广告词', name : 'goods_jingle', width : 150, sortable : true, align: 'left'},
            {display: '分类ID', name : 'gc_id', width : 60, sortable : true, align: 'center'},
            {display: '店铺名称', name : 'store_name', width : 80, sortable : true, align: 'left'},
            {display: '店铺类型', name : 'is_own_shop', width : 80, sortable : true, align: 'center'},
            {display: '发布时间', name : 'goods_addtime', width : 100, sortable : true, align: 'center'},
            {display: '成本价格(元)', name : 'goods_cost', width : 100, sortable : true, align: 'center'},
            {display: '运费(元)', name : 'goods_freight', width : 100, sortable : true, align: 'center'},
        ],

        buttons : [
            //{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operation }	,
        ],
        searchitems : [
            {display: '商品名称', name : 'goods_name'},
        ],
        sortname: "store_id",
        sortorder: "asc",
        title: '商品列表'
    });

});

function fg_operation(name, bDiv) {
    if (name == 'csv') {
        if ($('.trSelected', bDiv).length == 0) {
            if (!confirm('您确定要下载全部数据吗？')) {
                return false;
            }
        }
        var itemids = new Array();
        $('.trSelected', bDiv).each(function(i){
            itemids[i] = $(this).attr('data-id');
        });
        fg_csv(itemids);
    }
}
function fg_operations(name, bDiv) {
    if (name == 'shopwwi_add') {
        window.location.href = 'index.php?act=store&op=shopwwi_add';
    }
}

function fg_csv(ids) {
    id = ids.join(',');
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_csv&id=' + id;
}
</script>