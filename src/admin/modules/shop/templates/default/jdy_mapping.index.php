<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">

  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>精斗云商品映射</h3>
      </div>
    </div>
  </div>

  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
        <li>映射后可取消映射</li>
    </ul>
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
                    <dt>关键字搜索</dt>
                    <dd>
                        <label>
                            <select class="s-select" name="qtype_key">
                                <option value="goods_name">商品名称</option>
                                <option value="goods_id">商品id</option>
                            </select>
                        </label>
                        <label>
                            <input type="text" value="" placeholder="请输入关键字" name="query_key" class="s-input-txt">
                        </label>
                    </dd>
                </dl>
                <dl>
                    <dt>是否映射</dt>
                    <dd>
                        <label>
                            <select name="mapping_state" class="s-select">
                                <option value="0">全部</option>
                                <option value="1">已映射</option>
                                <option value="2">未映射</option>
                            </select>
                        </label>
                    </dd>
                </dl>
            </div>
        </div>
        <div class="bottom"><a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
    </form>
</div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/layer/layer.js"></script>
<script>
$(function(){
    var flexUrl = 'index.php?act=jdy_mapping&op=index_xml';
    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '商品id'		, name: 'goods_id'			, width: 60	, sortable: false, align: 'left'},
            {display: '商品名称'		, name: 'goods_name'		, width: 300, sortable: false, align: 'left'},
            {display: '商品价格'		, name: 'goods_price'		, width: 60	, sortable: false, align: 'left'},
            {display: '库存'			, name: 'goods_storage'		, width: 60	, sortable: false, align: 'left'},
            {display: '映射'			, name: 'is_mapping'		, width: 60	, sortable: true, align: 'left'},
            {display: 'jdy映射相关 商品名称|供应商'	, name: 'mapping_info'	, width: 300, sortable: false, align: 'left'},
            {display: '操作'			, name: 'operation'			, width: 80, sortable: false, align: 'center'}
        ],
        searchitems: [
            {display: '商品名称', name: 'goods_name'},
            {display: '商品id', name: 'goods_id'}
        ],
        sortname: "goods_id",
        sortorder: "desc",
        title: '精斗云商品映射列表'
    });

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl+'&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });
    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl}).flexReload();
        $("#formSearch")[0].reset();
    });

    $("#flexigrid").on('click', ".un-mapping" , function () {
        var re_back=confirm("确定取消映射？");
        if (re_back == true) {
            var goods_id = $(this).attr('data-goods-id');
            $.post("index.php?act=jdy_mapping&op=mapping_back",{
                goods_id: goods_id
            }, function(data) {
                if(data.result) {
                    //window.location.reload();
                    $("#flexigrid").flexReload();
                    //$("#flexigrid").flexOptions({url: flexUrl+'&'+$("#formSearch").serialize(),query:'',qtype:'',curpage:''}).flexReload();
                } else {
                    showError(data.message);
                }
            }, 'json');
        }
    });

    $("#flexigrid").on('click', ".do-mapping", function () {
        var url = $(this).attr('data-uri');
        layer.ready(function(){
            layer.open({
                type: 2,
                title: '映射商品',
                maxmin: true,
                area: ['80%', '80%'],
                content: url,
                end: function(){
                    $("#flexigrid").flexReload();
                    //$("#flexigrid").flexOptions({url: flexUrl+'&'+$("#formSearch").serialize(),query:'',qtype:'',curpage:''}).flexReload();
                    //window.location.reload();
                }
            });
        });
        return false;
    })
});
</script>