<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="eject_con">
  <div class="adds">
    <div id="warning"></div>
    <form method="post" action="<?php echo B2B_SITE_URL;?>/index.php?act=member_invoice&op=address" id="address_form" target="_parent">
      <input type="hidden" name="form_submit" value="ok" />
      <input type="hidden" name="id" value="<?php echo $output['invoice_info']['inv_id'];?>" />
      <input type="hidden" name="invoice_type" value="<?php echo $output['invoice_type'];?>" />
      <input type="hidden" value="<?php echo $output['address_info']['city_id'];?>" name="city_id" id="_area_2">
      <input type="hidden" value="<?php echo $output['address_info']['area_id'];?>" name="area_id" id="_area">

        <?php if($output['invoice_info']['inv_state'] == 1 || $output['invoice_type'] == 1){?>
        <dl>
        <dt><i class="required">*</i><?php echo '发票抬头'.$lang['nc_colon'];?></dt>
        <dd>
          <input type="text" class="text w100" name="inv_title" value="<?php echo $output['invoice_info']['inv_title'];?>"/>
          <p class="hint"><?php echo $lang['member_address_input_name'];?></p>
        </dd>
      </dl>
        <dl>
            <dt><i class="required">*</i><?php echo '发票内容'.$lang['nc_colon'];?></dt>
            <dd>
                <input class="text w300" type="text" name="inv_content" value="<?php echo $output['invoice_info']['inv_content'];?>"/>
                <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
            </dd>
        </dl>
        <?php }?>

        <?php if($output['invoice_info']['inv_state'] == 2 || $output['invoice_type'] == 2){?>
            <dl>
                <dt><i class="required">*</i><?php echo '单位名称'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_company" value="<?php echo $output['invoice_info']['inv_company'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>
            <dl>
                <dt><i class="required">*</i><?php echo '纳税人识别号'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_code" value="<?php echo $output['invoice_info']['inv_code'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '注册地址'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_phone" value="<?php echo $output['invoice_info']['inv_reg_addr'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '注册电话'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_phone" value="<?php echo $output['invoice_info']['inv_reg_phone'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '开户银行'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_reg_bname'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '银行账户'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_reg_baccount'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '收票人姓名'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_reg_baccount'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '收票人手机号'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_rec_mobphone'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '收票人省份'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_rec_province'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>

            <dl>
                <dt><i class="required">*</i><?php echo '送票地址'.$lang['nc_colon'];?></dt>
                <dd>
                    <input class="text w300" type="text" name="inv_reg_bname" value="<?php echo $output['invoice_info']['inv_goto_addr'];?>"/>
                    <p class="hint"><?php echo $lang['member_address_not_repeat'];?></p>
                </dd>
            </dl>
        <?php }?>

      <div class="bottom">
        <label class="submit-border">
          <input type="submit" class="submit" value="<?php if($output['type'] == 'add'){?><?php echo '新增发票信息';?><?php }else{?><?php echo '修改发票信息';?><?php }?>" />
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
            inv_content : {
                required : false
            }
        },
        messages : {
            inv_content : {
                required : '请填写发票抬头'
            }
        }
    });
    $('#zt').on('click',function(){
    	DialogManager.close('my_address_edit');
    	ajax_form('daisou','使用代收货（自提）', '<?php echo MEMBER_SITE_URL;?>/index.php?act=member_address&op=delivery_add', '900',0);
    });
});



</script>