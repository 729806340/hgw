<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page"> 
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>模板设置</h3>
        <h5>手机客户端首页/专题页模板设置</h5>
      </div>
      <ul class="tab-base nc-row">
        <?php foreach($output['menu'] as $menu) {  if($menu['menu_key'] == $output['menu_key']) { ?>
        <li><a href="JavaScript:void(0);" class="current"><?php echo $menu['menu_name'];?></a></li>
        <?php }  else { ?>
        <li><a href="<?php echo $menu['menu_url'];?>" ><?php echo $menu['menu_name'];?></a></li>
        <?php  } }  ?>
      </ul>
    </div>
  </div>
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>点击添加专题按钮可以添加新的专题，专题描述可以点击后直接修改</li>
      <li>点击编辑按钮对专题内容进行修改</li>
      <li>点击删除按钮可以删除整个专题</li>
    </ul>
  </div>
  
  <!-- 列表 -->
  <form id="list_form" method="post">
    <table class="flex-table">
      <thead>
        <tr>
          <th width="24" align="center" class="sign"><i class="ico-check"></i></th>
          <th width="150" align="center" class="handle"><?php echo $lang['nc_handle'];?></th>
          <th width="60" align="center">专题编号</th>
          <th width="350">专题描述</th>
          <th width="350">专题背景色</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <?php foreach($output['list'] as $key => $value){ ?>
        <tr>
          <td class="sign"><i class="ico-check"></i></td>
          <td class="handle"><a href="javascript:;" nctype="btn_del" data-special-id="<?php echo $value['special_id'];?>" class="btn red"><i class="fa fa-trash-o"></i>删除</a><a href="<?php echo urlAdminMobile('mb_special', 'special_edit', array('special_id' => $value['special_id']));?>" class="btn blue"><i class="fa fa-pencil-square-o"></i>编辑</a></td>
          <td><?php echo $value['special_id'];?></td>
          <td class="name"><span nc_type="edit_special_desc" column_id="<?php echo $value['special_id'];?>" title="<?php echo $lang['nc_editable'];?>" class="editable tooltip w270"><?php echo $value['special_desc'];?></span></td>
          <td class="name"><span nc_type="edit_special_background" column_id="<?php echo $value['special_id'];?>" title="<?php echo $lang['nc_editable'];?>" class="editable tooltip w270"><?php echo $value['special_background'];?></span></td>
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
    <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
    <div class="pagination"><?php echo $output['page'];?> </div>
    <?php }?>
</div>
<form id="del_form" action="<?php echo urlAdminMobile('mb_special', 'special_del');?>" method="post">
  <input type="hidden" id="del_special_id" name="special_id">
</form>
<div id="dialog_add_mb_special" style="display:none;">
  <form id="add_form" method="post" action="<?php echo urlAdminMobile('mb_special', 'special_save');?>">
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="special_desc"><em>*</em>专题描述</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="special_desc" class="input-txt">
          <span class="err"></span>
          <p class="notic">专题描述，最多20个字符</p>
        </dd>
      </dl>
      <div class="bot"><a id="submit" href="javascript:void(0)" class="ncap-btn-big ncap-btn-green"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.edit.js"></script> 
<script type="text/javascript">
$(function(){
	$('.flex-table').flexigrid({
		height:'auto',// 高度自动
		usepager: false,// 不翻页
		striped:false,// 不使用斑马线
		resizable: false,// 不调节大小
		title: '移动客户端专题模板列表',// 表格标题
		reload: false,// 不使用刷新
		columnControl: false,// 不使用列控制
        buttons : [
            {
                display: '<i class="fa fa-plus"></i>新增专题',
                name : 'add',
                bclass : 'add',
                title : '新增专题',
                onpress : function() {
                    $('#dialog_add_mb_special').nc_show_dialog({title: '新增专题'});
                }
            }
        ]
    });
        //添加专题
        $('#btn_add_mb_special').on('click', function() {
            $('#dialog_add_mb_special').nc_show_dialog({title: '新增专题'});
        });

        //提交
        $("#submit").click(function(){
            $("#add_form").submit();
        });

        $('#add_form').validate({
            errorPlacement: function(error, element){
                var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
            },
            rules : {
                special_desc : {
                    required : true,
                    maxlength : 20
                }
            },
            messages : {
                special_desc : {
                    required : "<i class='fa fa-exclamation-circle'></i>专题描述不能为空",
                    maxlength : "<i class='fa fa-exclamation-circle'></i>专题描述最多20个字"
                }
            }
        });

        //删除专题
        $('[nctype="btn_del"]').on('click', function() {
            if(confirm('确认删除?')) {
                $('#del_special_id').val($(this).attr('data-special-id'));
                $('#del_form').submit();
            }
        });

        //编辑专题描述
        $('span[nc_type="edit_special_desc"]').inline_edit({act: 'mb_special',op: 'update_special_desc'});
        //专题背景色编辑
        $('span[nc_type="edit_special_background"]').inline_edit({act: 'mb_special',op: 'update_special_background'});
    });
	function fg_operation(name, bDiv) {
    if (name == 'add') {
        window.location.href = 'javascript:;';

    }
}
</script> 
