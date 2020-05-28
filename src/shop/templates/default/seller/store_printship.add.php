<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <form id="add_form" action="<?php echo urlShop('store_printship', 'add');?>" method="post" enctype="multipart/form-data">
        <input type="hidden" value="<?php echo $output['template_info']['area_id']?>" name="area_id" id="_area">
        <?php
        if($output['template_info']['id']){
        ?>
            <input type="hidden" value="<?php echo $output['template_info']['id']?>" name="id" id="id">
        <?php }?>
        <dl>
            <dt><i class="required">*</i>模板名称<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text" value="<?php echo $output['template_info']['template_name']?>" name="template_name" size="30" id="template_name" class="text">
                <span></span>
                <p class="hint">电子面单名称，最多10个字</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>物流公司<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <select name="express_code" id="express_code">
                    <?php if(!empty($output['express']) && is_array($output['express'])) {?>
                        <?php foreach($output['express'] as $value) {?>
                            <option value="<?php echo $value['kdncode'];?>" <?php if($output['template_info']['express_code']==$value['kdncode']) { echo 'selected'; }?> ><?php echo $value['hgwname'];?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <span></span>
                <p class="hint">电子面单对应的物流公司</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>是否通知上门取货<?php echo $lang['nc_colon'];?></dt>
            <dd>
               <input type="radio" <?php if(!$output['template_info']['is_notify'] || $output['template_info']['is_notify']=='0'){?>checked<?php }?> name="is_notify" id="is_notify" value="0">否&nbsp;&nbsp;<input type="radio" <?php if($output['template_info']['is_notify']=='1'){?>checked<?php }?> name="is_notify" id="is_notify" value="1">是
                <span></span>
                <p class="hint">是否通知上门取货</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>所属区域<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="hidden" name="region" id="region" value="<?php echo $output['template_info']['region'];?>"/>
                <p class="hint">请选择发货区域</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>详细地址<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text" value="<?php echo $output['template_info']['address']?>" id="address" name="address" size="50" class="text">
                <span></span>
                <p class="hint">请输入发货的详细地址</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>联系人<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text" value="<?php echo $output['template_info']['sender']?>" name="sender" id="sender" class="w12 text">
                <span></span>
                <p class="hint">请输入发货人</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>手机号码<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text" value="<?php echo $output['template_info']['mobile']?>" name="mobile" id="mobile" class="w12 text">
                <span></span>
                <p class="hint">请输入发货人手机号码</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>邮政编码<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text" value="<?php echo $output['template_info']['shipcode']?>" name="shipcode" id="mobile" class="w12 text">
                <span></span>
                <p class="hint">请输入发货地址的邮政编码</p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border">
                <input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>">
            </label>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#region").nc_region();

        $('input[nctype="address_form" ]').click(function(){
            if ($('#address_form').valid()) {
                ajaxpost('address_form', '', '', 'onerror');
            }
        });
        $('#add_form').validate({
            rules : {
                template_name: {
                    required:true,
                    maxlength:10
                },
                express_code:{
                    required: true,
                },
                region:{
                    checklast: true
                },
                address: {
                    required : true,
                },
                sender: {
                    required : true
                },
                mobile:{
                    required : true,
                    number:true,
                    minlength:11,
                    maxlength:11,
                },

                shipcode: {
                    required : true,
                    number:true,
                    minlength:6,
                    maxlength:6,
                }
            },
            messages : {
                template_name: {
                    required : "<i class=\"icon-exclamation-sign\"></i>模板名称不能为空",
                    maxlength : "<i class=\"icon-exclamation-sign\"></i>模板名称最多10个字"
                },
                express_code: {
                    required : "<i class=\"icon-exclamation-sign\"></i>请选择快递公司",
                },
                region:{
                    checklast : "<i class=\"icon-exclamation-sign\"></i>发货区域不能为空",
                },
                address: {
                    required : "<i class=\"icon-exclamation-sign\"></i>收货地址不能为空",
                },
                sender: {
                    required : "<i class=\"icon-exclamation-sign\"></i>请输入发货人",
                },
                mobile: {
                    required : "<i class=\"icon-exclamation-sign\"></i>发货人手机号码不能为空",
                    number: "<i class=\"icon-exclamation-sign\"></i>手机号码格式不正确",
                    minlength: "<i class=\"icon-exclamation-sign\"></i>手机号码格式不正确",
                    maxlength:"<i class=\"icon-exclamation-sign\"></i>手机号码格式不正确",
                },
                shipcode: {
                    required : "<i class=\"icon-exclamation-sign\"></i>邮政编码不能为空",
                    number: "<i class=\"icon-exclamation-sign\"></i>邮政编码格式不正确",
                    minlength: "<i class=\"icon-exclamation-sign\"></i>邮政编码格式正确",
                    maxlength:"<i class=\"icon-exclamation-sign\"></i>邮政编码格式不正确",
                }
            }
        });
    });
</script>
