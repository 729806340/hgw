<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="<?php echo B2B_SITE_URL;?>/index.php?act=member_purchase&op=register" id="address_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <input type="hidden" name="id" value="<?php echo $output['address_info']['address_id'];?>" />
      <input type="hidden" value="<?php echo $output['address_info']['city_id'];?>" name="city_id" id="_area_2">
      <input type="hidden" value="<?php echo $output['address_info']['area_id'];?>" name="area_id" id="_area">
      <dl>
        <dt><i class="required">*</i>公司名称</dt>
        <dd>
          <input type="text" class="text w100" name="company_name" value="<?php echo $output['address_info']['true_name'];?>"/>
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>地区</dt>
        <dd><input type="hidden" name="region" id="region" value="<?php echo $output['address_info']['area_info'];?>">
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>详细地址</dt>
        <dd>
          <input class="text w300" type="text" name="address" value="<?php echo $output['address_info']['address'];?>"/>
          <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>联系人</dt>
        <dd>
          <input type="text" class="text w200" name="contact_name" value="<?php echo $output['address_info']['tel_phone'];?>"/>
<!--          <p class="hint">--><?php //echo $lang['member_address_area_num'];?><!-- - --><?php //echo $lang['member_address_phone_num'];?><!-- - --><?php //echo $lang['member_address_sub_phone'];?><!--</p>-->
        </dd>
      </dl>
      <dl>
        <dt><i class="required">*</i>联系人手机</dt>
        <dd>
          <input type="text" class="text w200" name="mob_phone" value="<?php echo $output['address_info']['mob_phone'];?>"/>
        </dd>
      </dl>

      <div class="bottom">
        <label class="submit-border">
          <input type="submit" class="submit" value="<?php if($output['type'] == 'add'){?>注册采购商<?php }else{?><?php echo $lang['member_address_edit_address'];?><?php }?>" />
        </label>
        <a class="ncbtn ml5" href="javascript:DialogManager.close('my_address_edit');">取消</a> </div>
    </form>
  </div>
</div>
<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";
$(document).ready(function(){
	$("#region").nc_region();
	$('#address_form').validate({
    	submitHandler:function(form){
    		ajaxpost('address_form', '', '', 'onerror');
    	},
        errorLabelContainer: $('#warning'),
        invalidHandler: function(form, validator) {
           var errors = validator.numberOfInvalids();
           if(errors)
           {
               $('#warning').show();
           }
           else
           {
               $('#warning').hide();
           }
        },
        rules : {
            company_name : {
                required : true
            },
            contact_name : {
                required : true
            },
            address : {
                required : true
            },
            region : {
                required : true
            },
            mob_phone : {
                required : true
            }
        },
        messages : {
            company_name : {
                required : '请填写公司名称'
            },
            contact_name : {
                required : '请填写联系人'
            },
            address : {
                required : '请填写详细地址'
            },
            mob_phone : {
                required : '请填写电话'
            }
        },
        groups : {
            phone:'mob_phone'
        }
    });

});

</script>