<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page"> 
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <?php if($output['item_info']['special_id'] > 0) { ?>
      <a id="btn_back" href="<?php echo urlAdminShop('pc_special', 'special_edit', array('special_id' => $output['item_info']['special_id']));?>" class="back"  title="返回上一级"><i class="fa fa-arrow-circle-o-left"></i></a>
      <?php } else { ?>
      <a id="btn_back" href="<?php echo urlAdminShop('pc_special', 'index_edit');?>" class="back" title="返回上一级"><i class="fa fa-arrow-circle-o-left"></i></a>
      <?php } ?>
      <div class="subject">
        <h3><?php echo $output['special_info']['special_title'] . '-编辑悬浮导航：【' . $output['module_info']['desc'] . '&nbsp;&nbsp;' . $output['item_info']['item_type'];?>】 </h3>
        <h5>PC客户端首页/专题页模板设置</h5>
      </div>
    </div>
  </div>
  <form id="form_item" action="<?php echo urlAdminShop('pc_special', 'special_navi_edit',['item_id'=>$output['item_info']['item_id']]);?>" method="post">
    <input type="hidden" name="special_id" value="<?php echo $output['item_info']['special_id'];?>">
    <input type="hidden" name="item_id" value="<?php echo $output['item_info']['item_id'];?>">

    <?php 
		$item_data = $output['item_info']['item_data'];
		$item_info = $output['item_info'];
		$item_edit_flag = true;
	?>
    <div id="item_edit_content" class="pc-item-edit-content">
<!--      --><?php //require(BASE_RESOURCE_PATH . '/pc_special/widgets/' . $output['item_info']['item_type'] . '/edit.data.php');?>
        <div class="explanation" id="explanation">
            <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
                <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            </div>
            <ul>
                <li>从右侧筛选按钮，点击添加按钮完成添加</li>
                <li>鼠标移动到已有商品上，会出现删除按钮可以对商品进行删除</li>
                <li>操作完成后点击保存编辑按钮进行保存</li>
            </ul>
        </div>

        <div class="index_block adv_list">
            <h3><?php echo $output['module_info']['desc']; ?></h3>
            <div nctype="item_content" class="content" id="item_content">
<!--                ID：-->
<!--                <input type="text" name="navi_id" value="--><?php //echo $item_info['navi_id']; ?><!--" >-->
<!--                <br/><br/>-->
                导航名称：
                <input type="text" name="navi_title"  value="<?php echo $item_info['navi_title']; ?>">

            </div>
        </div>
    </div>
    <div class="bot"><a id="btn_save" class="ncap-btn-big ncap-btn-green" href="javascript:;">保存编辑</a> </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js" charset="utf-8"></script> 
<script type="text/javascript">
    var url_upload_image = '<?php echo urlAdminShop('pc_special', 'special_image_upload');?>';

    $(document).ready(function(){

        var $current_content = null;
        var $current_image = null;
        var $current_image_name = null;
        var $current_image_type = null;
        var $current_image_data = null;
        var $current_image_titlebelow = null;
        var old_image = '';
        var $dialog_item_image = $('#dialog_item_image');
        var $dialog_item_image_name = $('#dialog_item_image_name');
        var special_id = <?php echo $output['item_info']['special_id'];?>;
        var item_id = <?php echo $_GET['item_id'];?>;

        //保存
        $('#btn_save').on('click', function() {
            $('#form_item').submit();
        });

        $('#dialog_item_image_type').on('change', function() {
            change_image_type_desc($(this).val());
        });

        function change_image_type_desc(type) {
            var desc_array = {};
            var desc = '操作类型一共四种，对应点击以后的操作。';
            if(type != '') {
                desc_array['keyword'] = '关键字类型会根据搜索关键字跳转到商品搜索页面，输入框填写搜索关键字。';
                desc_array['special'] = '专题编号会跳转到指定的专题，输入框填写专题编号。';
                desc_array['goods'] = '商品编号会跳转到指定的商品详细页面，输入框填写商品编号。';
                desc_array['url'] = '链接会跳转到指定链接，输入框填写完整的URL。';
                desc = desc_array[type];
            }
            $('#dialog_item_image_desc').text(desc);
        }
    });
    </script> 
