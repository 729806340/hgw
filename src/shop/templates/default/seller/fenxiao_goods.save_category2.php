<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
#gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
  <form method="post" action="index.php?act=fenxiao_goods&op=index&action=add" target="_parent" name="store_certification_form" id="store_certification_form" enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <input name="id" id="id" type="hidden" value="<?php echo $output['id'];?>">
    <input name="uid" id="uid" type="hidden" value="<?php echo $output['category']['uid'];?>">
      <dl>
          <dt><i class="required">*</i>一个包裹几个商品：</dt>
          <dd>
              <input name="package_count" id="package_count" type="text" class="text w210" value="<?php echo $output['category']['package_count'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">必须是1~9999999之间的数字，请根据该实际情况认真填写。</p>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>运费：</dt>
          <dd>
              <input name="freight_cost" id="freight_cost" type="text" class="text w210" value="<?php echo $output['category']['freight_cost'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">运费必须是0.01~9999999之间的数字，请根据该实际情况认真填写。</p>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>快递公司：</dt>
          <dd>
              <select name="express_id" id="express_id">
                  <?php foreach ($output['express'] as $v) {?>
                  <option <?php if ($output['category']['express_id'] == $v['id']) echo 'selected';?> value="<?php echo $v['id'];?>"><?php echo $v['e_name'];?></option>
                  <?php }?>
              </select>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>供应商：</dt>
          <dd>
              <select name="store_supplier_id" id="store_supplier_id">
                  <?php foreach ($output['store_supplier'] as $v) {?>
                      <option <?php if ($output['category']['store_supplier_id'] == $v['sup_id']) echo 'selected';?> value="<?php echo $v['sup_id'];?>"><?php echo $v['sup_name'];?></option>
                  <?php }?>
              </select>
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
        var url = 'index.php?act=fenxiao_goods2&op=save_good_category';
        var id = $('#id').val();
        var package_count = $('#package_count').val();
        var freight_cost = $('#freight_cost').val();
        var express_id = $('#express_id').val();
        var store_supplier_id = $('#store_supplier_id').val();
        var uid = $('#uid').val();

        showDialog('确认要设置吗？', 'confirm', '', function(){
            $.post(url, {id:id,package_count:package_count,freight_cost:freight_cost,express_id:express_id,store_supplier_id:store_supplier_id,uid:uid}, function (data) {
                alert(data.msg);
                location.reload();
            }, 'json');
        });
    });

    $('input[nc_type="logo"]').change(function(){
        var src = getFullPath($(this)[0]);
        $('img[nc_type="logo1"]').attr('src', src);
    });
});
</script>
