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
          <dt><i class="required">*</i>分销平台商品ID：</dt>
          <dd>
              <input name="fxpid" id="fxpid" type="text" class="text w210" value="<?php echo $output['category']['fxpid'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">请填写分销平台商品的ID号。</p>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>分销供价：</dt>
          <dd>
              <input name="fxprice" id="fxprice" type="text" class="text w210" value="<?php echo $output['category']['fxprice'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">价格必须是0.01~9999999之间的数字，请根据该实际情况认真填写。</p>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>分销成本价</dt>
          <dd>
              <input name="fxcost" id="fxcost" type="text" class="text w210" value="<?php echo $output['category']['fxcost'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">价格必须是0.01~9999999之间的数字，请根据该实际情况认真填写。</p>
          </dd>
      </dl>
      <dl>
          <dt><i class="required">*</i>倍数</dt>
          <dd>
              <input name="multiple_goods" id="multiple_goods" type="text" class="text w210" value="<?php echo $output['category']['multiple_goods'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
              <span></span>
              <p class="hint">倍数必须是1~9999999之间的数字，请根据该实际情况认真填写。</p>
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
        var url = 'index.php?act=fenxiao_goods&op=save_fenxiao';
        var id = $('#id').val();
        var fxpid = $('#fxpid').val();
        var fxprice = $('#fxprice').val();
        var fxcost = $('#fxcost').val();
        var multiple_goods = $('#multiple_goods').val();
        var uid = $('#uid').val();

        showDialog('确认要设置吗？', 'confirm', '', function(){
            $.post(url, {id:id,fxpid:fxpid,fxprice:fxprice,fxcost:fxcost,multiple_goods:multiple_goods,uid:uid}, function (data) {
                alert(data.msg);
                location.href = data.url;
            }, 'json');
        	// ajaxpost('store_certification_form', '', '', 'onerror')
        });
    });

    $('input[nc_type="logo"]').change(function(){
        var src = getFullPath($(this)[0]);
        $('img[nc_type="logo1"]').attr('src', src);
    });
});
</script>
