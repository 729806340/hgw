<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>专题商品模板管理</h3>
        <h5>专题页面商品模板新增与编辑查看</h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="专题商品模板管理">专题商品模板管理</h4>
      <span id="explanationZoom" title="专题商品模板"></span> </div>
    <ul>
      <li>专题商品模板用于生成专题页面的商品区块HTML代码</li>
    </ul>
  </div>
  <form id="list_form" method='post'>
    <input id="special_id" name="special_id" type="hidden" />
    <table class="flex-table">
      <thead>
        <tr>
          <th width="24" align="center" class="sign"><i class="ico-check"></i></th>
          <th width="150" align="center" class="handle"><?php echo $lang['nc_handle'];?></th>
          <th width="300">模板名称</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <?php foreach($output['list'] as $val){ ?>
        <tr>
          <td class="sign"><i class="ico-check"></i></td>
          <td class="handle"><a href="index.php?act=cms_special_widgetpl&op=widgetpl_drop&id=<?php echo $val['id'];?>" class="btn red confirm-del"><i class="fa fa-trash-o"></i><?php echo $lang['nc_del'];?></a> <span class="btn"><em><i class="fa fa-cog"></i><?php echo $lang['nc_set'];?><i class="arrow"></i></em>
            <ul>
              <li><a href="index.php?act=cms_special_widgetpl&op=widgetpl_edit&id=<?php echo $val['id'];?>">编辑模板内容</a></li>
            </ul>
            </span></td>
          <td class="name"><?php echo $val['name'];?></td>
          <td></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr>
          <td class="no-data" colspan="100"><i class="fa fa-exclamation-triangle"></i><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </form>
</div>
<script>
$(function(){
	$('.flex-table').flexigrid({
		height:'auto',// 高度自动
		usepager: false,// 不翻页
		striped:false,// 不使用斑马线
		resizable: false,// 不调节大小
		title: '模板列表',// 表格标题
		reload: false,// 不使用刷新
		columnControl: false,// 不使用列控制
        buttons : [
                   {display: '<i class="fa fa-plus"></i>新增模板', name : 'add', bclass : 'add', title : '新增模板', onpress : fg_operation }
               ]
		});

    $('a.confirm-del').live('click', function() {
        if (!confirm('确定删除？')) {
            return false;
        }
    });

});
function fg_operation(name, bDiv) {
    if (name == 'add') {
        window.location.href = 'index.php?act=cms_special_widgetpl&op=widgetpl_add';
    }
}
</script>