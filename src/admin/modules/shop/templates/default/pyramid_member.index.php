<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>分销会员管理</h3>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li></li>
      <li></li>
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
    $("#flexigrid").flexigrid({
        url: 'index.php?act=pyramid_member&op=get_xml',
        colModel : [
            {display: '操作', name : 'operation', width : 60, sortable : false, align: 'center'},
            {display: '会员ID', name : 'member_id', width : 60, sortable : false, align: 'center'},
            {display: '会员名称', name : 'member_name', width : 150, sortable : false, align: 'left'},
            {display: '会员手机', name : 'member_mobile', width : 80, sortable : false, align: 'center'},
            {display: '注册时间', name : 'member_time', width : 100, sortable : false, align: 'center'},
            {display: '最后登录时间', name : 'member_login_time', width : 100, sortable : false, align: 'center'},
            {display: '最后登录IP', name : 'member_login_ip', width : 100, sortable : false, align: 'center'},
            {display: '会员积分', name : 'member_points', width : 60, sortable : false, align: 'center'},
            {display: '分销一级会员id', name : 'invite_one', width : 120, sortable : false, align: 'center'},
            {display: '分销二级会员id', name : 'invite_two', width : 120, sortable : false, align: 'center'},
            {display: '分销三级会员id', name : 'member_points', width : 120, sortable : false, align: 'center'}
            ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operation }
            ],
        searchitems : [
            {display: '会员ID', name : 'member_id'},
            {display: '会员名称', name : 'member_name'},
            {display: '会员邮箱', name : 'member_email'},
            {display: '会员手机', name : 'member_mobile'},
            ],
        sortname: "member_id",
        sortorder: "desc",
        title: '商城会员列表'
    });
	
});

function fg_operation(name, bDiv) {
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

