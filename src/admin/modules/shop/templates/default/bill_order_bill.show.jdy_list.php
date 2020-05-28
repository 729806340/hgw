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
                    <dt>商品ID</dt>
                    <dd>
                        <label><input type="text" value="" name="goods_id" id="goods_id" class="s-input-txt"></label>
                    </dd>
                </dl>
                <dl>
                    <dt>商品名称</dt>
                    <dd>
                        <label><input type="text" value="" name="goods_name" id="goods_name" class="s-input-txt"></label>
                    </dd>
                </dl>
                <dl>
                    <dt>供应商编号</dt>
                    <dd>
                        <label><input type="text" value="" name="jdy_supplier_number" id="jdy_supplier_number" class="s-input-txt"></label>
                    </dd>
                </dl>
                <dl>
                    <dt>供应商名称</dt>
                    <dd>
                        <label><input type="text" value="" name="jdy_supplier_name" id="jdy_supplier_name" class="s-input-txt"></label>
                    </dd>
                </dl>
                <dl>
                    <dt>状态</dt>
                    <dd>
                        <label><select name="state" id="state">
                                <option value=''>请选择</option>
                                <?php
                                foreach ($output['entry_state'] as $key=>$state){
                                    echo "<option value='$key'>$state</option>";
                                }
                                ?>
                            </select></label>
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
<style>
    .green{
        float: left;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#query_start_date').datepicker({dateFormat:'yy-mm-dd',minDate: "<?php echo date('Y-m-d',$output['bill_info']['ob_start_date']);?>",maxDate: "<?php echo date('Y-m-d',$output['bill_info']['ob_end_date']);?>"});
        $('#query_end_date').datepicker({dateFormat:'yy-mm-dd',minDate: "<?php echo date('Y-m-d',$output['bill_info']['ob_start_date']);?>",maxDate: "<?php echo date('Y-m-d',$output['bill_info']['ob_end_date']);?>"});
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
                {display: '操作', name : 'operation', width : 220, sortable : false, align: 'center'},
                {display: '供应商编号', name : 'jdy_supplier_number', width : 110, sortable : true, align: 'left'},
                {display: '供应商名称', name : 'jdy_supplier_name', width : 110, sortable : true, align: 'left'},
                {display: '商品ID', name : 'goods_id', width : 130, sortable : false, align: 'center'},
                {display: '商品名称', name : 'goods_name', width : 300, sortable : false, align: 'center'},
                {display: '销售金额', name : 'amount', width : 110, sortable : true, align: 'left'},
                {display: '商品成本', name : 'cost', width : 110, sortable : true, align: 'left'},
                {display: '单价', name : 'price', width : 110, sortable : true, align: 'left'},
                {display: '商品数量', name : 'goods_num', width : 70, sortable : true, align: 'center'},
                {display: '状态', name : 'state', width: 60, sortable : true, align : 'left'},
                {display: '平台红包', name : 'rpt_bill', width : 70, sortable : true, align: 'left'},
                {display: '满减', name : 'mj_bill', width : 70, sortable : true, align: 'left'},
                {display: '限时', name : 'xs_bill', width : 70, sortable : true, align: 'left'},
                {display: '退款金额', name : 'refund', width : 80, sortable : true, align : 'center'},
            ],
            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate}
            ],
            searchitems : [
                {display: '供应商名称', name : 'jdy_supplier_number', isdefault: true},
                {display: '供应商编号', name : 'jdy_supplier_name'},
                {display: '商品ID', name : 'goods_id'},
                {display: '商品名称', name : 'goods_name'},
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
        window.location.href = $("#flexigrid").flexSimpleSearchQueryString() +'&ob_id=<?php echo $_GET['ob_id'];?>&op=export_order&order_id='+id+'&'+$("#formSearch").serialize();
    }
    function push(id) {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?act=bill&op=jdy_push",
            data: "id="+id,
            success: function(data){
                if (data.state){
                    alert(data.msg);
                    $("#flexigrid").flexReload();
                } else {
                    alert(data.msg);
                }
            },
            error: function(xhr){
                console.log('错误提示: '+ xhr.status + '' + xhr.statusText);
            }
        });
    }
    function remap(id) {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?act=bill&op=jdy_remapping",
            data: "id="+id,
            success: function(data){
                if (data.state){
                    alert(data.msg);
                    $("#flexigrid").flexReload();
                } else {
                    alert(data.msg);
                }
            },
            error: function(xhr){
                console.log('错误提示: '+ xhr.status + '' + xhr.statusText);
            }
        });
    }
    function push_refund(id) {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?act=bill&op=jdy_push_refund",
            data: "id="+id,
            success: function(data){
                if (data.state){
                    alert(data.msg);
                    $("#flexigrid").flexReload();
                } else {
                    alert(data.msg);
                }
            },
            error: function(xhr){
                console.log('错误提示: '+ xhr.status + '' + xhr.statusText);
            }
        });
    }
</script>
