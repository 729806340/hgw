<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>竞品分析</h3>
                <h5>其他电商平台与汉购网同类商品比较</h5>
            </div>
            <ul class="tab-base nc-row">
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'index');?>">竞价数据列表</a></li>
                <li><a href="JavaScript:void(0);" class="current">竞价信息抓取配置</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'shopList');?>">店铺数据列表</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'marketList');?>">店铺信息抓取配置</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'oldList');?>">抓取数据历史查询</a></li>
            </ul>
        </div>
    </div>
    <div id="flexigrid"></div>
</div>
<script type="text/javascript">
    $(function(){
        $("#flexigrid").flexigrid({
            url: 'index.php?act=sale_analysis&op=getXmlConf',
            colModel : [
                {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
                {display: '创建时间', name : 'created_at', width : 130, sortable : false, align: 'left'},
                {display: '商品名', name : 'prod_name', width : 250, sortable : false, align: 'left'},
                {display: '店铺名', name : 'store_name', width : 250, sortable : false, align: 'left'},
                {display: '来源', name : 'prod_from', width : 80, sortable : true, align: 'center'},
                {display: '商品链接', name : 'prod_url', width : 150, sortable : false, align : 'left'},
                {display: '商品名正则', name : 'prod_rule', width : 140, sortable : false, align : 'center'},
                {display: '价格正则', name : 'prize_rule', width : 140, sortable : true, align : 'center'},
                {display: '销量正则', name : 'sales_rule', width: 140, sortable : true, align : 'center'},
                {display: '状态', name : 'status', width : 80, sortable : true, align: 'center'}
            ],
            buttons : [
                {display: '<i class="fa fa-plus"></i>新增数据', name: 'add', bclass: 'add', onpress: fg_operate},
                {display: '<i class="fa fa-trash"></i>批量删除', name : 'delete', bclass : 'del', title : '将选定行数据批量删除', onpress : fg_operate }
            ],
            searchitems : [
                {display : '商品名',name : 'prod_name'},
                {display : '店铺名',name : 'store_name'}
            ],
            sortname: "prod_name",
            sortorder: "desc",
            title: '竞品分析配置列表'
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
            }else {
                return false;
            }
        } else if(name=='add'){
            window.location.href ='index.php?act=sale_analysis&op=addItem';
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
        window.location.href ='index.php?act=sale_analysis&op=delItem&id='+id;
    }
</script>