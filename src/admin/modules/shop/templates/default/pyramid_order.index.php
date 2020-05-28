<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>分销订单管理</h3>
        <h5>分销交易订单查询及管理</h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul></ul>
  </div>
    <div id="result"></div>
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
                  <option value="order_sn">订单编号</option>
                  <option value="buyer_name">买家账号</option>
                  <option value="buyer_phone">买家手机</option>
                  <option value="store_name">店铺名称</option>
                  <option value="pay_sn">支付单号</option>
                  <option value="shipping_code">发货单号</option>
                </select>
              </label>
              <label>
                <input type="text" value="" placeholder="请输入关键字" name="keyword" class="s-input-txt">
              </label>
              <label>
                <input type="checkbox" id="jq_query" value="1" name="jq_query">精确
              </label>
            </dd>
          </dl>
          <dl>
            <dt>日期筛选</dt>
            <dd>
              <label>
                <select class="s-select" name="qtype_time">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="add_time">下单时间</option>
                  <option value="payment_time">支付时间</option>
                  <option value="finnshed_time">完成时间 </option>
                  <option value="import_time">导单时间 </option>
                </select>
              </label>
              <label>
                <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
          <dl>
            <dt>金额筛选</dt>
            <dd>
              <label>
                <select class="s-select" name="query_amount">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="order_amount">订单金额</option>
                  <option value="shipping_fee">运费金额</option>
                  <option value="refund_amount">退款金额</option>
                </select>
              </label>
              <label>
                <input placeholder="请输入起始金额" name=query_start_amount value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input placeholder="请输入结束金额" name="query_end_amount" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
          <dl>
            <dt>支付方式</dt>
            <dd>
              <label>
                <select name="payment_code" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <?php foreach($output['payment_list'] as $val) { ?>
                  <option <?php if($_GET['payment_code'] == $val['payment_code']){?>selected<?php }?> value="<?php echo $val['payment_code']; ?>"><?php echo $val['payment_name']; ?></option>
                  <?php } ?>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>订单状态</dt>
            <dd>
              <label>
                <select name="order_state" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="10">待付款</option>
                  <option value="15">拼团中</option>
                  <option value="20">已支付(待发货)</option>
                  <option value="21">备货中</option>
                  <option value="30">待收货</option>
                  <option value="40">已完成</option>
                  <option value="0">已取消</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>订单来源</dt>
            <dd>
              <select name="order_from" class="s-select">
                <option value=""><?php echo $lang['nc_please_choose'];?></option>
                <option value="1">网站</option>
                <option value="2">移动端</option>
                <option value="3">分销</option>
                <option value="4">集采</option>
                <option value="6">小程序</option>
              </select>
            </dd>
            </dd>
          </dl>
          <dl>
            <dt>是否退款</dt>
            <dd>
              <label>
                <select name="refund_state" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="0">无退款</option>
                  <option value="1">部分退款</option>
                  <option value="2">全部退款</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>是否导出商品明细</dt>
            <dd>
              <select name="export_goods" class="s-select">
                <option value=""><?php echo $lang['nc_please_choose'];?></option>
                <option value="">否</option>
                <option value="1">是</option>
              </select>
            </dd>
            </dd>
          </dl>
        </div>
      </div>
      <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
    </form>
  </div>
</div>
<script type="text/javascript">
$(function(){
	$('#query_start_date').datepicker();
    $('#query_end_date').datepicker();

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=pyramid_order&op=get_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });
    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=pyramid_order&op=get_xml'}).flexReload();
        $("#formSearch")[0].reset();
    });

    //判断是否是站外请求
    var query_state_date = '<?php echo $_GET['query_start_date']?>';
    var url = 'index.php?act=pyramid_order&op=get_xml';
    if(query_state_date){
        $("#query_start_date").val(query_state_date);
        $("#query_end_date").val('<?php echo $_GET['query_end_date']?>');
        $("input[name='keyword']").val('<?php echo $_GET['keyword']?>');
        $("#jq_query").attr("checked","checked");;
        $("select[name='qtype_time']").val('<?php echo $_GET['qtype_time']?>');
        $("select[name='keyword_type']").val('<?php echo $_GET['keyword_type']?>');
        $("select[name='order_from']").val('<?php echo $_GET['order_from']?>');
        url = url+"&"+$("#formSearch").serialize();
    }

    $("#flexigrid").flexigrid({
        url: url,
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: 'own会员id', name : 'lg_member_id', width : 80, sortable : false, align: 'left'},
            {display: '级别', name : 'invite_level', width : 30, sortable : false, align: 'left'},
            {display: '可提现金额', name : 'real_return_money', width : 50, sortable : false, align: 'left'},
            {display: '冻结金额', name : 'return_money', width : 50, sortable : false, align: 'left'},
            {display: '订单编号', name : 'order_sn', width : 150, sortable : false, align: 'left'},
			{display: '订单来源', name : 'order_from', width : 50, sortable : true, align : 'center'},
			{display: '下单时间', name : 'order_id', width : 140, sortable : true, align: 'left'},
			{display: '订单金额(元)', name : 'order_amount', width : 100, sortable : true, align: 'left'},
			{display: '订单状态', name : 'order_state', width: 120, sortable : true, align : 'center'},                                           
            {display: '支付单号', name : 'pay_sn', width : 140, sortable : false, align: 'left'},
			{display: '支付方式', name : 'payment_code', width: 60, sortable : true, align : 'center'},
			{display: '支付时间', name : 'payment_time', width: 140, sortable : true, align : 'left'}, 
            {display: '充值卡支付(元)', name : 'rcb_amount', width : 70, sortable : true, align: 'center'},
            {display: '预存款支付(元)', name : 'pd_amount', width : 70, sortable : true, align: 'center'},
			{display: '退款金额(元)', name : 'refund_amount', width : 80, sortable : true, align: 'center'},
			{display: '订单完成时间', name : 'finnshed_time', width: 120, sortable : true, align : 'left'},
            {display: '是否评价', name : 'evaluation_state', width : 80, sortable : true, align: 'center'},            
            {display: '店铺ID', name : 'store_id', width : 40, sortable : true, align: 'center'},
			{display: '店铺名称', name : 'store_name', width : 200, sortable : true, align: 'left'}, 
			{display: '买家ID', name : 'buyer_id', width : 40, sortable : true, align: 'center'},
			{display: '买家账号', name : 'buyer_name', width : 150, sortable : true, align: 'left'}
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出excel文件,如果不选中行，将导出列表所有数据', onpress : fg_operate }
        ],
        searchitems : [
            {display: '订单编号', name : 'order_sn', isdefault: true},
            {display: '订单序号', name : 'order_id'},
            {display: '买家账号', name : 'buyer_name'},
            {display: '买家手机', name : 'buyer_phone'},
            {display: '店铺名称', name : 'store_name'},
            {display: '支付单号', name : 'pay_sn'}
            ],
        sortname: "order_id",
        sortorder: "desc",
        title: '分销交易实物订单明细'
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
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_step1&order_id=' + id;
}
</script>
