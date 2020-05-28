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
                        <dt>关键字搜索</dt>
                        <dd>
                            <label>
                                <select class="s-select" name="keyword_type">
                                    <option selected="selected" value="">-请选择-</option>
                                    <option value="refund_sn">退单编号</option>
                                    <option value="goods_name">商品名称</option>
                                    <option value="buyer_name">买家账号</option>
                                    <option value="store_name">店铺名称</option>
                                    <option value="order_sn">订单编号</option>
                                </select>
                            </label>
                            <label>
                                <input type="text" value="" placeholder="请输入关键字" name="keyword" class="s-input-txt">
                            </label>
                            <label>
                                <input type="checkbox" value="1" name="jq_query">精确
                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>日期筛选</dt>
                        <dd>
                            <label>
                                <select class="s-select" name="qtype_time">
                                    <option <?php if(!$output['qtype_time']) {?> selected="selected" <?php }?> value="">-请选择-</option>
                                    <option <?php if($output['qtype_time'] == 'add_time') {?> selected="selected" <?php }?> value="add_time">买家申请时间</option>
                                    <option value="seller_time">商家处理时间</option>
                                    <option value="admin_time">平台处理时间 </option>
                                    <option value="order_add_time">用户下单时间 </option>
                                </select>
                            </label>
                            <label>
                                <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="<?php echo @date('Y-m-d',$output['query_start_date']);?>" type="text" class="s-input-txt" />
                            </label>
                            <label>
                                <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="<?php echo @date('Y-m-d',$output['query_end_date']);?>" type="text" class="s-input-txt" />
                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>退款金额</dt>
                        <dd>
                            <label>
                                <input placeholder="请输入起始金额" name=query_start_amount value="" type="text" class="s-input-txt" />
                            </label>
                            <label>
                                <input placeholder="请输入结束金额" name="query_end_amount" value="" type="text" class="s-input-txt" />
                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>平台处理状态</dt>
                        <dd>
                            <label>
                                <select class="s-select" name="refund_state">
                                    <option selected="selected" value="">-请选择-</option>
                                    <option value="1">商家处理中</option>
                                    <option value="2">待平台处理</option>
                                    <option value="3">已完成</option>
                                </select>
                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>渠道</dt>
                        <dd>
                            <label>
                                <select name="order_channel">
                                    <option selected="selected" value="">-请选择-</option>
                                    <?php foreach ($output['fx_member'] as $key=>$value) {?>
                                        <option value="<?php echo $key?>"><?php echo $value?></option>
                                    <?php }?>
                                </select>
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
            $("#flexigrid").flexOptions({url: 'index.php?act=stat_aftersale&op=get_order_refund_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
        });
        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=stat_aftersale&op=get_order_refund_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });
        $("#flexigrid").flexigrid({
            url: 'index.php?act=stat_aftersale&op=get_order_refund_xml&'+$("#formSearch").serialize(),
            colModel : [
                {display: '订单编号', name : 'refund_sn', width : 130, sortable : false, align: 'center'},
                {display: '退单编号', name : 'order_sn', width : 130, sortable : false, align: 'center'},
                {display: '商家名称', name : 'store_name', width : 100, sortable : false, align: 'left'},
                {display: '商品名称', name : 'goods_name', width : 120, sortable : false, align: 'left'},
                {display: '买家会员名', name : 'buyer_name', width : 60, sortable : false, align: 'left'},
                {display: '申请时间', name : 'add_time', width: 120, sortable : false, align : 'center'},
                {display: '退款金额', name : 'refund_amount', width : 60, sortable : false, align: 'center'},
                {display: '商家审核', name : 'seller_state',  width : 60, sortable : false, align: 'center'},
                {display: '平台确认', name : 'refund_state',  width : 60, sortable : false, align: 'center'},
                {display: '商家处理备注信息', name : 'seller_message',  width : 120, sortable : false, align: 'center'},
                {display: '平台处理备注信息', name : 'admin_message',  width : 120, sortable : false, align: 'center'},
                {display: '操作来源', name : 'operation_type', width : 200, sortable : true, align: 'center'},
                {display: '渠道', name : 'fx_name', width : 130, sortable : false, align: 'center'},
                {display: '申请图片', name : 'pic_info', width : 70, sortable : false, align : 'left'},
            ],

            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将导出csv文件', onpress : fg_operate }
            ],
            sortname: "refund_id",
            sortorder: "desc",
            title: '线上实物交易订单退款列表'
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
        window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_step1&refund_id=' + id;
    }
</script>