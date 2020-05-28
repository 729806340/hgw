<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>分销商品SKU</h3>
      </div>
        <?php echo $output['top_link'];?>
    </div>
  </div>

  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
        <li>分销商品SKU</li>
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>

<script>
$(function(){
    var flexUrl = 'index.php?act=fenxiao_map&op=index_xml&source=<?php echo $output['source'];?>';
    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '商品名称', name: 'goods_name', width: 600, sortable: false, align: 'left'},
            {display: '分销商品sku', name: 'sku_id', width: 200, sortable: false, align: 'left'},
            {display: '汉购商品id', name: 'spu_id', width: 200, sortable: true, align: 'left'}
        ],
        searchitems: [
            {display: '商品名称', name: 'goods_name'},
            {display: '分销商品sku', name: 'sku_id'},
            {display: '汉购商品id', name: 'spu_id'}
        ],
        sortname: "spu_id",
        sortorder: "asc",
        title: '分销商品SKU列表'
    });

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl + '&' + $("#formSearch").serialize(),query:'',qtype:'advance'}).flexReload();
    });

    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl}).flexReload();
        $("#formSearch")[0].reset();
    });

    $('[data-dp]').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>