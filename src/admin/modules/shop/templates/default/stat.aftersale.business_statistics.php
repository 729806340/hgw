<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>售后分析</h3>
        <h5>平台针对订单售后服务的各项数据统计</h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>

    <!-- 操作说明 -->
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span>
        </div>
        <ul>
            <li></li>
        </ul>
    </div>

    <div class="explanation mt10" id="result" style="display:none;"></div>
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
                        <dt>日期筛选-商家处理时间</dt>
                        <dd>
                            <label>
                                <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="<?php echo @date('Y-m-d',$output['query_start_date']);?>" type="text" class="s-input-txt" />
                            </label>
                            <label>
                                <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="<?php echo @date('Y-m-d',$output['query_end_date']);?>" type="text" class="s-input-txt" />
                            </label>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
        </form>
    </div>
</div>
<div id="mask" style="position: fixed;top: 0;bottom: 0;left: 0;right: 0;background: #333;opacity: .3;z-index: 9999; display: none;">
</div>
<div id="loading" style="z-index:9999;position: fixed; top: 100px; width: 100%; text-align: center;display: none;">
    <p style="background:#FFF;margin: 100px auto; width: 300px; padding: 20px 30px; font-size: 16px;">正在处理，请勿关闭页面...</p>
</div>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        $('#query_start_date').datepicker();
        $('#query_end_date').datepicker();
        // 高级搜索提交
        $('#ncsubmit').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=stat_aftersale&op=get_business_xml&'+$("#formSearch").serialize()}).flexReload();
        });
        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=stat_aftersale&op=get_business_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });
        $("#flexigrid").flexigrid({
            url: 'index.php?act=stat_aftersale&op=get_business_xml',
            colModel : [
                {display: '商家名称', name : 'store_name', width : 150, sortable : true, align: 'left'},
                {display: '退款金额统计', name : 'refund_amount_total', width : 200, sortable : true, align: 'center'},
                {display: '售前退款总数', name : 'before_sale_num', width : 150, sortable : true, align: 'center'},
                {display: '售后退款总数', name : 'after_sale_num', width : 150, sortable : true, align: 'center'},
                {display: '退单总数', name : 'order_num', width : 150, sortable : true, align: 'center'},
                {display: '订单总数', name : 'order_total', width : 150, sortable : true, align: 'center'},
                {display: '退单率', name : 'refund_rate', width : 150, sortable : true, align: 'center'},
                {display: '售前退款率', name : 'before_sale_rate', width : 150, sortable : true, align: 'center'},
                {display: '售后退款率', name : 'after_sale_rate', width : 150, sortable : true, align: 'center'},
            ],

            sortname: "refund_rate",
            sortorder: "desc",
            title: '售后率最高的商家列表',
            rp: 20,
            rpOptions: [20],
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
</script>