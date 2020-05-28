
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6 0006
 * Time: 上午 9:52
 */
<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>渠道结算</h3>
                <h5>渠道结算商家显示</h5>
            </div>
        </div>
    </div>
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
                        <dt>账单编号</dt>
                        <dd>
                            <input type="text" value="" name="ob_id" id="ob_id" class="s-input-txt">
                        </dd>
                    </dl>
                    <dl>
                        <dt>商家名称</dt>
                        <dd>
                            <label><input type="text" value="" name=ob_store_name id="ob_store_name" class="s-input-txt"></label>
                            <label><input type="checkbox" value="1" name="jq_query">精确</label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>结账月份</dt>
                        <dd>
                            <select name="query_year">
                                <option value=""> 年份&nbsp;&nbsp;</option>
                                <?php for($i=date('Y',time())-4;$i<=date('Y',time())+4;$i++) { ?>
                                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                <?php } ?>
                            </select> -
                            <select name="query_month">
                                <option value=""> 月份&nbsp;&nbsp;</option>
                                <?php for($i=1;$i<=12;$i++) { ?>
                                    <option value="<?php echo str_pad($i,2,'0',STR_PAD_LEFT);?>"><?php echo $i;?></option>
                                <?php } ?>
                            </select>
                        </dd>
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
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL; ?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        // 高级搜索提交
        $('#ncsubmit').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=channel_bill&op=get_bill_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
        });

        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=channel_bill&op=get_bill_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });
        $("#flexigrid").flexigrid({
            url: 'index.php?act=channel_bill&op=get_bill_xml&gname=<?php echo $output['current']?>',
            colModel : [
                {display: '操作', name : 'operation', width : 60, sortable : false, align: 'center', className: 'handle-s'},
                {display: '账单编号', name : 'ob_id', width : 60, sortable : true, align: 'center'},
                {display: '订单金额(含运费)', name : 'ob_order_totals', width : 100, sortable : true, align: 'left'},
                {display: '运费', name : 'ob_shipping_totals', width: 50, sortable : true, align : 'left'},
                {display: '退单金额', name : 'ob_order_return_totals', width: 70, sortable : true, align : 'left'},
                {display: '本期应结', name : 'ob_result_totals', width : 80, sortable : true, align: 'left'},
                {display: '出账日期', name : 'ob_create_date', width : 70, sortable : true, align: 'center'},
                {display: '渠道名称', name : 'ob_channel_name', width : 130, sortable : false, align: 'left'},
                {display: '开始日期', name : 'ob_start_date', width : 80, sortable : true, align : 'center'},
                {display: '结束日期', name : 'ob_end_date', width : 80, sortable : true, align: 'center'},
                {display: '渠道ID', name : 'ob_channel_id', width : 90, sortable : true, align: 'center'},
                {display: '备注', name : 'ob_remark', width : 242, sortable : true, align: 'left'}
            ],
            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate}
            ],
            searchitems : [
                {display: '账单编号', name : 'ob_id'},
                {display: '渠道名称', name : 'ob_channel_name'}
            ],
            sortname: "ob_id",
            sortorder: "desc",
            title: '<?php echo !empty($_GET['os_month']) ? $_GET['os_month'].'期 ' : null;?>账单列表'
        });
        setTimeout(function(){
            $('span[nc_type="inline_edit"]').inline_edit({act: 'channel_bill', op: 'ajax'});
        },500);

    });

    function isEmpty(value) {
        return Object.keys(value).length === 0;
    }

    function fg_operate(name, grid) {
        if (name == 'csv') {
            var itemlist = new Array();
            if($('.trSelected',grid).length>0){
                $('.trSelected',grid).each(function(){
                    itemlist.push($(this).attr('data-id'));
                });
            }
            fg_csv(itemlist);
            return;
            if(!isEmpty(itemlist)){
                fg_csv(itemlist);
            } else {
                alert('请选择账单!');
            }
        }
    }
    function fg_csv(ids) {
        id = ids.join(',');
        window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_bill&ob_id='+id;
    }
</script>


