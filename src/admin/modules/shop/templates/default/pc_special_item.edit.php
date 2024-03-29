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
        <h3><?php echo $output['special_info']['special_title'] . '-编辑挂件类型：【' . $output['module_info']['desc'] . '&nbsp;&nbsp;' . $output['item_info']['item_type'];?>】 </h3>
        <h5>PC客户端首页/专题页模板设置</h5>
      </div>
    </div>
  </div>
  <form id="form_item" action="<?php echo urlAdminShop('pc_special', 'special_item_save');?>" method="post">
    <input type="hidden" name="special_id" value="<?php echo $output['item_info']['special_id'];?>">
    <input type="hidden" name="item_id" value="<?php echo $output['item_info']['item_id'];?>">

    <?php 
		$item_data = $output['item_info']['item_data'];
		$item_info = $output['item_info'];
		$item_edit_flag = true;
	?>
    <div id="item_edit_content" class="pc-item-edit-content">
      <?php require(BASE_RESOURCE_PATH . '/pc_special/widgets/' . $output['item_info']['item_type'] . '/edit.data.php');?>
    </div>
    <div class="bot"><a id="btn_save" class="ncap-btn-big ncap-btn-green" href="javascript:;">保存编辑</a> </div>
  </form>
</div>
<div id="dialog_item_edit_image" style="display:none;">
  <div class="s-tips"><i class="fa fa-lightbulb-o"></i>请按提示尺寸制作上传图片，已达到PC客户端及WapPC商城最佳显示效果。</div>
  <div class="upload-thumb"> <img id="dialog_item_image" src="" alt=""></div>
  <input id="dialog_item_image_name" type="hidden">
  <input id="dialog_type" type="hidden">
  <form id="form_image" action="">
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">选择要上传的图片：</dt>
        <dd class="opt">
          <div class="input-file-show"><span class="type-file-box">
            <input type='text' name='textfield' id='textfield' class='type-file-text' />
            <input type='button' name='button' id='button' value='选择上传...' class='type-file-button' />
            <input id="btn_upload_image" type="file" name="special_image" class="type-file-file" size="30" hidefocus="true" >
            </span> </div>
          <p id="dialog_image_desc" class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">图片下方显示文字：</dt>
        <dd class="opt">
          <input id="dialog_item_image_titlebelow" type="text" class="txt w200 marginright marginbot vatop">
          <p id="dialog_image_desc" class="notic">一般请不要填写，否则会在图片下方将该内容输出</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">操作类型：</dt>
        <dd class="opt">
          <select id="dialog_item_image_type" name="" class="vatop">
            <option value="">-请选择-</option>
            <option value="keyword">关键字</option>
            <option value="special">专题编号</option>
            <option value="goods">商品编号</option>
            <option value="url">链接</option>
          </select>
          <input id="dialog_item_image_data" type="text" class="txt w200 marginright marginbot vatop">
          <p id="dialog_item_image_desc" class="notic"></p>
        </dd>
      </dl>
      <div class="bot"><a id="btn_save_item" class="ncap-btn-big ncap-btn-green" href="javascript:;">保存</a></div>
    </div>
  </form>
</div>
<script id="item_image_template" type="text/html">
    <div nctype="item_image" class="item">
        <img nctype="image" src="<%=image%>" alt="">
        <input nctype="image_name" name="item_data[item][<%=image_name%>][image]" type="hidden" value="<%=image_name%>">
        <input nctype="image_type" name="item_data[item][<%=image_name%>][type]" type="hidden" value="<%=image_type%>">
        <input nctype="image_data" name="item_data[item][<%=image_name%>][data]" type="hidden" value="<%=image_data%>">
        <input nctype="image_titlebelow" name="item_data[item][<%=image_name%>][titlebelow]" type="hidden" value="<%=image_titlebelow%>">
        <a nctype="btn_del_item_image" href="javascript:;">删除</a>
    </div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script> 
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

        //上移
        $('#item_content').on('click', '[nctype="btn_move_up"]', function () {
            var $current = $(this).parents('[nctype="item_image"]');
            $prev = $current.prev('[nctype="item_image"]');
            if ($prev.length > 0) {
                $prev.before($current);
                update_item_sort();
            } else {
                showError('已经是第一个了');
            }
        });

        //下移
        $('#item_content').on('click', '[nctype="btn_move_down"]', function () {
            var $current = $(this).parents('[nctype="item_image"]');
            $next = $current.next('[nctype="item_image"]');
            if ($next.length > 0) {
                $next.after($current);
                update_item_sort();
            } else {
                showError('已经是最后一个了');
            }
        });

        var update_item_sort = function () {
            var goods_id_string = '';
            $item_list = $('#item_content').find('[nctype="item_image"]');
            $item_list.each(function (index, item) {
                goods_id_string += $(item).attr('good-item-id') + ',';
            });
            $.post("index.php?act=pc_special&op=update_goods_sort", {
                item_id: item_id,
                goods_id_string: goods_id_string,
                special_id:special_id
            }, function (data) {
                if (typeof data.error != 'undefined') {
                    showError(data.message);
                }
            }, 'json');
        };

        //保存
        $('#btn_save').on('click', function() {
            $('#form_item').submit();
        });

        //编辑图片
        $('[nctype="btn_edit_item_image"]').on('click', function() {
            //初始化当前图片对象
            $item_image = $(this).parents('[nctype="item_image"]');
            $current_image = $item_image.find('[nctype="image"]');
            $current_image_name = $item_image.find('[nctype="image_name"]');
            $current_image_type = $item_image.find('[nctype="image_type"]');
            $current_image_data = $item_image.find('[nctype="image_data"]');
            $current_image_titlebelow = $item_image.find('[nctype="image_titlebelow"]');

            $('#dialog_item_image').attr('src', $current_image.attr('src'));
            $('#dialog_item_image_name').val($current_image_name.val());
            $('#dialog_item_image_type').val($current_image_type.val());
            $('#dialog_item_image_data').val($current_image_data.val());
            $('#dialog_item_image_titlebelow').val($current_image_titlebelow.val());
            $('#dialog_image_desc').text('推荐图片尺寸' + $(this).attr('data-desc'));
            $('#dialog_type').val('edit');
            change_image_type_desc($('#dialog_item_image_type').val());
            $('#dialog_item_edit_image').nc_show_dialog({
                width: 600,
                title: '编辑'
            });
        });

        //添加图片
        $('[nctype="btn_add_item_image"]').on('click', function() {
            $dialog_item_image.hide();
            $dialog_item_image_name.val('');
            $current_content = $(this).parent().find('[nctype="item_content"]');
            $('#dialog_image_desc').text('推荐图片尺寸' + $(this).attr('data-desc'));
            $('#dialog_type').val('add');
            change_image_type_desc($('#dialog_item_image_type').val());
            $('#dialog_item_edit_image').nc_show_dialog({
                width: 600,
                title: '添加'
            });
        });

        //删除图片
        $('#item_edit_content').on('click', '[nctype="btn_del_item_image"]', function() {
            $(this).parents('[nctype="item_image"]').remove();
        });

        //图片上传
        $("#btn_upload_image").fileupload({
            dataType: 'json',
            url: url_upload_image,
            formData: {special_id: special_id},
            add: function(e, data) {
                old_image = $dialog_item_image.attr('src');
                $dialog_item_image.attr('src', LOADING_IMAGE);
                data.submit();
            },
            done: function (e, data) {
                var result = data.result;
                if(typeof result.error === 'undefined') {
                    $dialog_item_image.attr('src', result.image_url);
                    $dialog_item_image.show();
                    $dialog_item_image_name.val(result.image_name);
                } else {
                    // $dialog_item_image.attr('src') = old_image;
                    showError(result.error);
                }
            }
        });

        $('#btn_save_item').on('click', function() {
            var type = $('#dialog_type').val();
            if(type == 'edit') {
                edit_item_image_save();
            } else {
                if($dialog_item_image_name.val() == '') {
                    showError('请上传图片');
                    return false;
                }
                add_item_image_save();
            }
            $('#dialog_item_edit_image').hide();
        });

        function edit_item_image_save() {
            $current_image.attr('src', $('#dialog_item_image').attr('src'));
            $current_image_name.val($('#dialog_item_image_name').val());
            $current_image_type.val($('#dialog_item_image_type').val());
            $current_image_data.val($('#dialog_item_image_data').val());
            $current_image_titlebelow.val($('#dialog_item_image_titlebelow').val());
        }

        function add_item_image_save() {
            var $html_item_image = $('#html_item_image');
            var item = {};
            item.image = $('#dialog_item_image').attr('src');
            item.image_name = $('#dialog_item_image_name').val();
            item.image_type = $('#dialog_item_image_type').val();
            item.image_data = $('#dialog_item_image_data').val();
            item.image_titlebelow = $('#dialog_item_image_titlebelow').val();
            $current_content.append(template.render('item_image_template', item));
        }


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
