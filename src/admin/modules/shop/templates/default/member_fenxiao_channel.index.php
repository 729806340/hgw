<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['member_fenxiao_index_manage']?></h3>
        <h5><?php echo $lang['member_fenxiao_manage_subhead']?></h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo $lang['member_fenxiao_index_help1'];?></li>
      <li><?php echo $lang['member_fenxiao_index_help2'];?></li>
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=member_fenxiao&op=get_member_fenxiao_channel_xml',
        colModel : [
            {display: '操作', name : 'operation', width : 60, sortable : false, align: 'center', className: 'handle-s'},
            {display: '分销渠道用户ID', name : 'member_id', width : 150, sortable : true, align: 'center'},
            {display: '分销渠道名称', name : 'member_cn_code', width : 150, sortable : true, align: 'center'},
            {display: '是否贴标', name : 'is_sign', width : 150, sortable : true, align: 'center'},
            {display: '结算主体', name : 'store_name', width : 150, sortable : true, align: 'center'},
            ],
        buttons : [
            {display: '<i class="fa fa-plus"></i>新增渠道', name : 'add', bclass : 'add', title : '新增渠道', onpress : fg_operation },
            ],
        searchitems : [
            {display: '分销渠道名称', name : 'member_cn_code'}
            ],
        sortname: "member_id",
        sortorder: "desc",
        title: '分销渠道表'
    });
	
});

function fg_operation(name, bDiv) {
    if (name == 'add') {
        window.location.href = 'index.php?act=member_fenxiao&op=member_add';
    }
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
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_csv&id=' + id;
}
</script> 

