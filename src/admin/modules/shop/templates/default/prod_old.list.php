<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>竞品分析</h3>
                <h5>查询商品历史记录信息</h5>
            </div>
            <ul class="tab-base nc-row">
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'index');?>">竞价数据列表</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'prodConf');?>">竞价信息抓取配置</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'shopList');?>">店铺数据列表</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'marketList');?>">店铺信息抓取配置</a></li>
                <li><a href="JavaScript:void(0);" class="current">抓取数据历史查询</a></li>
            </ul>
        </div>
    </div>
    <div id="flexigrid"></div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/statistics.js"></script>
<script type="text/javascript">
    function update_flex(){
        $("#flexigrid").flexigrid({
            url: 'index.php?act=sale_analysis&op=getOldXml&'+$("#formSearch").serialize(),
            colModel : [
                {display: '抓取时间', name : 'fetch_time', width : 100, sortable : true, align: 'center'},
                {display: '商品名', name : 'goods_name', width : 250, sortable : false, align: 'left'},
                {display: '店铺名', name : 'store_name', width : 250, sortable : false, align: 'left'},
                {display: '抓取时的累计量', name : 'sales', width: 150, sortable : false, align : 'center'},
                {display: '来源', name : 'prod_from', width : 150, sortable : false, align: 'center'},
            ],
            searchitems : [
                {display : '商品名',name : 'name'}
            ],
            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate }
            ],
            sortname: "fetch_time",
            sortorder: "desc",
            title: '数据列表'
        });
    };
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
        window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=exportOld&id=' + id;
        //window.location.href ='index.php?act=sale_analysis&op=exportOld&id=' + id+'&'+$("#formSearch").serialize();
    }

    $(function () {
        update_flex();
    });
</script>