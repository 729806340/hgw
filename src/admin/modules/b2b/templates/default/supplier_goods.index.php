<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
      <div class="item-title"><a class="back" href="index.php?act=supplier&op=index" title="返回供应商列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>供应商商品管理</h3>
        <h5>商城所有商品索引及管理</h5>
      </div>
          <ul class="tab-base nc-row"><li><a  class="current"><span>现有商品</span></a></li><li><a href="index.php?act=supplier&op=all_goods_list&supplier_id=<?php echo $output['supplier_id'];?>" ><span>商品导入</span></a></li></ul> </div>

  </div>

  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo $lang['goods_index_help1'];?></li>
      <li><?php echo $lang['goods_index_help2'];?></li>
      <li>设置项中可以查看商品详细、查看商品SKU。查看商品详细，跳转到商品详细页。查看商品SKU，显示商品的SKU、图片、价格、库存信息。</li>
    </ul>
  </div>
  <div id="flexigrid"></div>
  <?php if ($output['type'] == '') {?>
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
            <dt>商品名称</dt>
            <dd>
              <label>
                <input type="text" value="" name="goods_name" id="goods_name" class="s-input-txt" placeholder="输入商品全称或关键字">
              </label>
            </dd>
          </dl>
          <dl>
            <dt>SPU</dt>
            <dd>
              <label>
                <input type="text" value="" name="goods_commonid" id="goods_commonid" class="s-input-txt" placeholder="输入商品平台货号">
              </label>
            </dd>
          </dl>
          <dl>
            <dt>所属店铺</dt>
            <dd>
              <label>
                <input type="text" value="" name="store_name" id="store_name" class="s-input-txt" placeholder="输入商品所属店铺名称">
              </label>
            </dd>
          </dl>
          <dl>
            <dt>所属品牌</dt>
            <dd>
              <label>
                <input type="text" value="" name="brand_name" id="brand_name" class="s-input-txt" placeholder="输入商品关联品牌关键字">
              </label>
            </dd>
          </dl>
          <dl>
            <dt>商品分类</dt>
            <dd id="gcategory">
              <input type="hidden" id="cate_id" name="cate_id" value="" class="mls_id" />
              <select class="class-select">
                <option value="0"><?php echo $lang['nc_please_choose'];?></option>
                <?php if(!empty($output['gc_list'])){ ?>
                <?php foreach($output['gc_list'] as $k => $v){ ?>
                <option value="<?php echo $v['gc_id'];?>"><?php echo $v['gc_name'];?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </dd>
          </dl>
          <dl>
            <dt>商品状态</dt>
            <dd>
              <label>
                <select name="goods_state" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="1">出售中</option>
                  <option value="0">仓库中</option>
                  <option value="10">违规下架</option>
                </select>
              </label>
            </dd>
          </dl>
          <dl>
            <dt>审核状态</dt>
            <dd>
              <label>
                <select name="goods_verify" class="s-select">
                  <option value=""><?php echo $lang['nc_please_choose'];?></option>
                  <option value="1">通过</option>
                  <option value="0">未通过</option>
                  <option value="10">审核中</option>
                </select>
              </label>
            </dd>
          </dl>
        </div>
      </div>
      <div class="bottom"><a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
    </form>
  </div>
  <script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
  <script type="text/javascript">
    gcategoryInit('gcategory');
    </script>
  <?php }?>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=supplier&op=get_xml&type=my&supplier_id=<?php echo $output['supplier_id'];?>',
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: 'SPU', name : 'goods_commonid', width : 60, sortable : true, align: 'center'},
            {display: '商品名称', name : 'goods_name', width : 150, sortable : false, align: 'left'},
            {display: '供应商', name : 'supplier_name', width : 100, sortable : true, align: 'center'},
            {display: '商品分类', name : 'gc_name', width : 150, sortable : true, align: 'center'},
            {display: 'B2C商品编号', name : 'b2c_goodsid', width : 180, sortable : true, align: 'center'},
            {display: '发布者', name : 'memberid', width : 60, sortable : true, align: 'center'},
            {display: '发布时间', name : 'addtime', width : 80, sortable : true, align: 'left'}
            ],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增商品', name: 'add', bclass: 'add', onpress: fg_operation}
            ],
        searchitems : [
            {display: 'SPU', name : 'goods_commonid'},
            {display: '商品名称', name : 'goods_name'}
            ],
        sortname: "goods_commonid",
        sortorder: "desc",
        title: '商品列表'
    });


    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=goods&op=get_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });

    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=goods&op=get_xml'}).flexReload();
        $("#formSearch")[0].reset();
    });
});

function fg_operation(name, bDiv) {
    if (name == 'add') {
        window.location.href = 'index.php?act=goods&op=goods_add';
    } else if (name == 'del') {
        if ($('.trSelected', bDiv).length == 0) {
            showError('请选择要操作的数据项！');
        }
        var itemids = new Array();
        $('.trSelected', bDiv).each(function (i) {
            itemids[i] = $(this).attr('data-id');
        });
        fg_del(itemids);
    } else if (name = 'csv') {
        window.location.href = 'index.php?act=goods_class&op=goods_class_export';
    }
}

function fg_operation1(name, bDiv) {
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

function fg_csv(ids) {
    id = ids.join(',');
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_csv&type=<?php echo $output['type'];?>&id=' + id;
}

//商品解绑
function  fg_unbind(ids){
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?act=supplier&op=goods_unbind",
            data: {"ids":ids},
            success: function(data){
                if (data.state){
                    $("#flexigrid").flexReload();
                } else {
                    alert(data.msg);
                }
            }
        });
}


//商品下架
function  fg_lonkup_off(ids){
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "index.php?act=goods&op=goods_lockup_off",
        data: {"ids":ids},
        success: function(data){
            if (data.state){
                $("#flexigrid").flexReload();
            } else {
                alert(data.msg);
            }
        }
    });
}


//商品下架
function fg_lonkupOld(ids) {
    _uri = "index.php?act=goods&op=goods_lockup&id=" + ids;
    CUR_DIALOG = ajax_form('goods_lockup', '违规下架理由', _uri, 640);
}



function fg_sku(commonid) {
    CUR_DIALOG = ajax_form('login','商品"' + commonid +'"的SKU列表','<?php echo urlAdminB2b('goods', 'get_goods_sku_list');?>&commonid=' + commonid, 480);
}

// 删除
function fg_del(ids) {

        $.getJSON('index.php?act=goods&op=goods_del', {ids:ids}, function(data){
            if (data.state) {
                $("#flexigrid").flexReload();
            } else {
                showError(data.msg)
            }
        });

}


// 商品审核
function fg_verify(ids) {
    _uri = "index.php?act=goods&op=goods_verify&id=" + ids;
    CUR_DIALOG = ajax_form('goods_verify', '审核商品', _uri, 640);
}
</script> 
