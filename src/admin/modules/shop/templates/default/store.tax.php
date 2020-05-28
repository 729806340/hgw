<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.d_inline {
	display: inline;
}
</style>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=store&op=store" title="返回<?php echo $lang['manage'];?>列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['nc_store_manage'];?> - 编辑店铺“<?php echo $output['store_array']['store_name'];?>”的税率</h3>
        <h5><?php echo $lang['nc_store_manage_subhead'];?></h5>
      </div>
    </div>
  </div>
  <div class="homepage-focus" nctype="editStoreContent">
  <div class="title">
  <h3>编辑店铺税率</h3>
    </div>
    <form id="store_form" method="post">
      <input type="hidden" name="form_submit" value="ok" />
      <input type="hidden" name="store_id" value="<?php echo $output['store_array']['store_id'];?>" />
      <div class="ncap-form-default">
        <dl class="row">
          <dt class="tit">
            <label><?php echo $lang['store_user_name'];?></label>
          </dt>
          <dd class="opt"><?php echo $output['store_array']['member_name'];?><span class="err"></span>
            <p class="notic"></p>
          </dd>
        </dl>
        <dl class="row">
          <dt class="tit">
            <label for="store_name"><em>*</em>店铺名称</label>
          </dt>
          <dd class="opt">
            <?php echo $output['store_array']['store_name'];?>
          </dd>
        </dl>
        <dl class="row">
          <dt class="tit">
            <label for="store_name">开店时间</label>
          </dt>
          <dd class="opt"><?php echo ($t = $output['store_array']['store_time'])?@date('Y-m-d',$t):'';?><span class="err"></span>
            <p class="notic"> </p>
          </dd>
        </dl>
        <dl class="row">
          <dt class="tit">
            <label for="tax_input"> 进项税率 </label>
          </dt>
          <dd class="opt">
            <input type="number" name="tax_input" id="tax_input" value="<?php echo $output['store_array']['tax_input'];?>" >%
            <span class="err"></span>
            <p class="notic"></p>
          </dd>
        </dl>
        <dl class="row">
          <dt class="tit">
            <label for="tax_output"> 销项税率 </label>
          </dt>
          <dd class="opt">
            <input type="number" name="tax_output" id="tax_output" value="<?php echo $output['store_array']['tax_output'];?>" >%
            <span class="err"></span>
            <p class="notic"></p>
          </dd>
        </dl>
        <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">
var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
$(function(){
    $("#company_address").nc_region();
    $("#business_licence_address").nc_region();
    $("#bank_address").nc_region();
    $("#settlement_bank_address").nc_region();
    $('#end_time').datepicker();
    $('#business_licence_start').datepicker();
    $('#business_licence_end').datepicker();
    $('a[nctype="nyroModal"]').nyroModal();
    $('input[name=store_state][value=<?php echo $output['store_array']['store_state'];?>]').trigger('click');

    //按钮先执行验证再提交表单
    $("#submitBtn").click(function(){
        if($("#store_form").valid()){
            $("#store_form").submit();
        }
    });

    $("#btn_fail").click(function(){
        $("#joinin_form").submit();
    });

    $('#store_form').validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
             store_name: {
                 required : true,
                 remote   : '<?php echo urlAdminShop('store', 'ckeck_store_name', array('store_id' => $output['store_array']['store_id']))?>'
              }
        },
        messages : {
            store_name: {
                required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['please_input_store_name'];?>',
                remote   : '<i class="fa fa-exclamation-circle"></i>店铺名称已存在'
            }
        }
    });

    $('div[nctype="editStoreContent"] > .title').find('li').click(function(){
        $(this).children().addClass('current').end().siblings().children().removeClass('current');
        var _index = $(this).index();
        var _form = $('div[nctype="editStoreContent"]').find('form');
        _form.hide();
        _form.eq(_index).show();
    });
});
</script>