<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
#gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
  <form method="post" action="index.php?act=store_info&op=certification_save" target="_parent" name="store_certification_form" id="store_certification_form" enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <input id="goods_class" name="goods_class" type="hidden" value="">
    <dl>
      <dt>认证名称<?php echo $lang['nc_colon'];?></dt>
      <dd id="gcategory">
          <input type="text" value="" name="name">
      </dd>
    </dl>
    <dl>
      <dt>认证说明<?php echo $lang['nc_colon'];?></dt>
      <dd id="gcategory">
          <textarea name="description" id="" cols="30" rows="10"></textarea>
      </dd>
    </dl>
      <dl>
          <dt><i class="required">*</i>资质图片<?php echo $lang['nc_colon'];?></dt>
          <dd>
              <div class=""><span class="sign"><img src="<?php echo brandImage($output['brand_array']['brand_pic']);?>" onload="javascript:DrawImage(this,150,50)" nc_type="logo1"/></span></div>
              <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
          <input type="file" hidefocus="true" size="1" class="input-file" name="content" id="brand_pic" nc_type="logo"/>
          </span>
                      <p><i class="icon-upload-alt"></i>图片上传</p>
                  </a> </div>
              <p class="hint">请上传清晰的资质图片，分辨率不得低于800x1280，图片文字必须清晰，否则将影响商品发布审核。</p>
          </dd>
      </dl>

    <div class="bottom">
      <label class="submit-border"><input type="button" id="btn_add_certification" class="submit" value="<?php echo $lang['nc_submit'];?>" /></label>
    </div>
  </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
	//页面输入内容验证
    $('#btn_add_certification').on('click', function() {
        $('#error_message').hide();
        var category_id = '';
        var validation = true;
        if(!validation) {
            $('#error_message').show();
            return false;
        }
        $('#goods_class').val(category_id);

        var rate = $('#gcategory').find('select').last().find('option:selected').attr('data-explain') + '%';
        showDialog('确认要确认申请吗？', 'confirm', '', function(){
        	ajaxpost('store_certification_form', '', '', 'onerror')
        });
    });

    $('input[nc_type="logo"]').change(function(){
        var src = getFullPath($(this)[0]);
        $('img[nc_type="logo1"]').attr('src', src);
    });
});
</script> 
