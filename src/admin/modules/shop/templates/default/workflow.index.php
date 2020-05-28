<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>审批管理</h3>
        <h5>系统所有审批流程管理</h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
        <li>请在2小时内处理当前节点属于您权限组的审核项</li> 
    </ul>
  </div>
  <div id="flexigrid"></div>
</div>
<script type="text/javascript">
$(function(){
	$("#flexigrid").flexigrid({
        url: 'index.php?act=workflow&op=get_xml&type=<?php echo $output['type'] ?>',
        colModel : [
            {display: '操作', name : 'operation', width : 50, sortable : false, align: 'center', className: 'handle'},
            {display: '编号', name : 'id', width : 50, sortable : true, align: 'center'},
            {display: '标题', name : 'title', width : 300, sortable : false, align: 'left'},
            {display: '类型', name : 'type', width : 250, sortable : true, align: 'center'},
            {display: '当前节点', name : 'stage', width : 60, sortable : true, align: 'center'},
            {display: '审核状态', name : 'status', width : 60, sortable : false, align: 'center'},
            {display: '发起人类型', name : 'role', width : 60, sortable : true, align: 'center'},
            {display: '发起人', name : 'user', width : 60, sortable : true, align: 'left'},
            {display: '发起时间', name : 'created_at', width : 150, sortable : true, align: 'left'}
            ],
        searchitems : [
           {display: '编号', name : 'id', isdefault: true},
           {display: '标题', name : 'title'},
           {display: '类型', name : 'type'},
           {display: '当前节点', name : 'stage'},
           {display: '发起人类型', name : 'role'},
           {display: '发起人', name : 'user'}
       ],    
        sortname: "id",
        sortorder: "desc",
        title: '审核列表'
    });
});
</script> 
