<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>竞品分析</h3>
                <h5>其他电商平台同类商品信息</h5>
            </div>
            <ul class="tab-base nc-row">
                <li><a href="JavaScript:void(0);" class="current">竞价数据列表</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'prodConf');?>">竞价信息抓取配置</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'shopList');?>">店铺数据列表</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'marketList');?>">店铺信息抓取配置</a></li>
                <li><a href="<?php echo urlAdminShop('sale_analysis', 'oldList');?>">抓取数据历史查询</a></li>

            </ul>
        </div>
    </div>
    <div id="flexigrid"></div>
    <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
    <div class="ncap-search-bar">
        <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
        <div class="title">
            <h3>高级搜索</h3>
        </div>
        <form method="get" action="index.php" name="formSearch" id="formSearch">
            <div id="searchCon" class="content">
                <div class="layout-box">
                    <dl>
                        <dt>按时间周期筛选</dt>
                        <dd>
                            <label>
                                <select name="search_type" id="search_type" class="class-select">
                                    <option value="day" <?php echo $output['search_arr']['search_type']=='day'?'selected':''; ?>>按照天统计</option>
                                    <option value="week" <?php echo $output['search_arr']['search_type']=='week'?'selected':''; ?>>按照周统计</option>
                                    <option value="month" <?php echo $output['search_arr']['search_type']=='month'?'selected':''; ?>>按照月统计</option>
                                </select>
                            </label>
                        </dd>
                        <dd id="searchtype_day" style="display:none;">
                            <label>
                                <input class="s-input-txt" type="text" value="<?php echo @date('Y-m-d',$output['search_arr']['day']['search_time']);?>" id="search_time" name="search_time">
                            </label>
                        </dd>
                        <dd id="searchtype_week" style="display:none;">
                            <label>
                                <select name="searchweek_year" class="s-select">
                                    <?php foreach ($output['year_arr'] as $k => $v){?>
                                        <option value="<?php echo $k;?>" <?php echo $output['search_arr']['week']['current_year'] == $k?'selected':'';?>><?php echo $v; ?>年</option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <select name="searchweek_month" class="s-select">
                                    <?php foreach ($output['month_arr'] as $k => $v){?>
                                        <option value="<?php echo $k;?>" <?php echo $output['search_arr']['week']['current_month'] == $k?'selected':'';?>><?php echo $v; ?>月</option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <select name="searchweek_week" class="s-select">
                                    <?php foreach ($output['week_arr'] as $k => $v){?>
                                        <option value="<?php echo $v['key'];?>" <?php echo $output['search_arr']['week']['current_week'] == $v['key']?'selected':'';?>><?php echo $v['val']; ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        </dd>
                        <dd id="searchtype_month" style="display:none;">
                            <label>
                                <select name="searchmonth_year" class="s-select">
                                    <?php foreach ($output['year_arr'] as $k => $v){?>
                                        <option value="<?php echo $k;?>" <?php echo $output['search_arr']['month']['current_year'] == $k?'selected':'';?>><?php echo $v; ?>年</option>
                                    <?php } ?>
                                </select>
                            </label>
                            <label>
                                <select name="searchmonth_month" class="s-select">
                                    <?php foreach ($output['month_arr'] as $k => $v){?>
                                        <option value="<?php echo $k;?>" <?php echo $output['search_arr']['month']['current_month'] == $k?'selected':'';?>><?php echo $v; ?>月</option>
                                    <?php } ?>
                                </select>
                            </label>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green">提交查询</a> </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/statistics.js"></script>
<script type="text/javascript">
    function update_flex(){
        $("#flexigrid").flexigrid({
            url: 'index.php?act=sale_analysis&op=getXml&'+$("#formSearch").serialize(),
            colModel : [
                {display: '抓取时间', name : 'fetch_time', width : 100, sortable : true, align: 'center'},
                {display: '商品名', name : 'prod_name', width : 250, sortable : false, align: 'left'},
                {display: '价格', name : 'prize_rule', width : 150, sortable : true, align : 'center'},
                {display: '销量', name : 'sales', width: 150, sortable : true, align : 'center'},
                {display: '抓取时的累计量', name : 'total_sales', width: 150, sortable : true, align : 'center'},
                {display: '来源', name : 'prod_from', width : 150, sortable : true, align: 'center'},
                {display: '店铺名', name : 'store_name', width : 250, sortable : false, align: 'left'},
                {display: '第三方商品名', name : 'name', width : 250, sortable : false, align: 'left'},
            ],
            searchitems : [
                {display : '商品名',name : 'prod_name'},
                {display : '店铺名',name : 'store_name'}
            ],
            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate }
            ],
            sortname: "fetch_time",
            sortorder: "desc",
            title: '竞品数据列表'
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
        window.location.href ='index.php?act=sale_analysis&op=export&id=' + id+'&'+$("#formSearch").serialize();
    }
    //展示搜索时间框
    function show_searchtime(){
        s_type = $("#search_type").val();
        $("[id^='searchtype_']").hide();
        $("#searchtype_"+s_type).show();
    }
    $(function () {
        //统计数据类型
        var s_type = $("#search_type").val();
        $('#search_time').datepicker({dateFormat: 'yy-mm-dd'});

        show_searchtime();
        $("#search_type").change(function(){
            show_searchtime();
        });

        //更新周数组
        $("[name='searchweek_month']").change(function(){
            var year = $("[name='searchweek_year']").val();
            var month = $("[name='searchweek_month']").val();
            $("[name='searchweek_week']").html('');
            $.getJSON('<?php echo ADMIN_SITE_URL?>/index.php?act=common&op=getweekofmonth',{y:year,m:month},function(data){
                if(data != null){
                    for(var i = 0; i < data.length; i++) {
                        $("[name='searchweek_week']").append('<option value="'+data[i].key+'">'+data[i].val+'</option>');
                    }
                }
            });
        });

        $('#searchBarOpen').click();

        update_flex();

        $('#ncsubmit').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=sale_analysis&op=getXml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
            //$("#flexigrid").flexOptions({url: 'index.php?act=stat_marketing&op=get_groupgoods_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
            update_flex();
        });
    });
</script>