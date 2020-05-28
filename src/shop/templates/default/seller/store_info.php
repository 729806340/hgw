<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="tabmenu"><?php include template('layout/submenu');?></div>
<form id="joinin_form" enctype="multipart/form-data" method="post" action="index.php?act=store_info&op=edit_save_joinin">
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">公司及联系人信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">公司名称：</th>
            <td colspan="20"><input type="text" name="company_name" value="<?php echo $output['joinin_detail']['company_name'];?>"></td>
    </tr>
    <tr>
        <th>公司所在地：</th>
        <td><input type="hidden" name="company_address" id="company_address" value="<?php echo $output['joinin_detail']['company_address'];?>"></td>
        <th>公司详细地址：</th>
        <td colspan="20"><input type="text" name="company_address_detail" value="<?php echo $output['joinin_detail']['company_address_detail'];?>"></td>
    </tr>
    <tr>
        <th>公司电话：</th>
        <td><input type="text" name="company_phone" value="<?php echo $output['joinin_detail']['company_phone'];?>"></td>
        <th>员工总数：</th>
        <td><input type="text" name="company_employee_count" value="<?php echo $output['joinin_detail']['company_employee_count'];?>">&nbsp;人</td>
        <th>注册资金：</th>
        <td><input type="text" name="company_registered_capital" value="<?php echo $output['joinin_detail']['company_registered_capital'];?>">&nbsp;万元 </td>
    </tr>
    <tr>
        <th>联系人姓名：</th>
        <td><input type="text" name="contacts_name" value="<?php echo $output['joinin_detail']['contacts_name'];?>"></td>
        <th>联系人电话：</th>
        <td><input type="text" name="contacts_phone" value="<?php echo $output['joinin_detail']['contacts_phone'];?>"></td>
        <th>电子邮箱：</th>
        <td><input type="text" name="contacts_email" value="<?php echo $output['joinin_detail']['contacts_email'];?>"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">营业执照信息（副本）</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">营业执照号：</th>
            <td><input type="text" name="business_licence_number" value="<?php echo $output['joinin_detail']['business_licence_number'];?>"></td></tr><tr>

        <th>营业执照所在地：</th>
        <td><input type="hidden" name="business_licence_address" id="business_licence_address" value="<?php echo $output['joinin_detail']['business_licence_address'];?>"></td></tr><tr>

        <th>营业执照有效期：</th>
        <td><input type="text" class="input-txt" name="business_licence_start" id="business_licence_start" value="<?php echo $output['joinin_detail']['business_licence_start'];?>">
              -
              <input type="text" class="input-txt" name="business_licence_end" id="business_licence_end" value="<?php echo $output['joinin_detail']['business_licence_end'];?>"></td>
    </tr>
    <tr>
        <th>法定经营范围：</th>
        <td colspan="20"><input type="text" name="business_sphere" value="<?php echo $output['joinin_detail']['business_sphere'];?>"></td>
    </tr>
    <tr>
        <th>营业执照<br />
            电子版：</th>
        <td colspan="20"><a nctype="nyroModal"  href="<?php echo getStoreJoininImageUrl($output['joinin_detail']['business_licence_number_elc']);?>"> <img src="<?php echo getStoreJoininImageUrl($output['joinin_detail']['business_licence_number_elc']);?>" alt="" /> </a>
              <input class="w200" type="file" name="business_licence_number_elc"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">组织机构代码证</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>组织机构代码：</th>
            <td colspan="20"><input type="text" name="organization_code" value="<?php echo $output['joinin_detail']['organization_code'];?>"></td>
    </tr>
    <tr>
        <th>组织机构代码证<br/>          电子版：</th>
        <td colspan="20"><a nctype="nyroModal"  href="<?php echo getStoreJoininImageUrl($output['joinin_detail']['organization_code_electronic']);?>"> <img src="<?php echo getStoreJoininImageUrl($output['joinin_detail']['organization_code_electronic']);?>" alt="" /> </a>
              <input type="file" name="organization_code_electronic"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">一般纳税人证明：</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>一般纳税人证明：</th>
            <td colspan="20"><a nctype="nyroModal" href="<?php echo getStoreJoininImageUrl($output['joinin_detail']['general_taxpayer']);?>"> <img src="<?php echo getStoreJoininImageUrl($output['joinin_detail']['general_taxpayer']);?>" alt="" /> </a>
              <input type="file" name="general_taxpayer"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">开户银行信息：</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">银行开户名：</th>
            <td><input type="text" name="bank_account_name" value="<?php echo $output['joinin_detail']['bank_account_name'];?>"></td>
        </tr><tr>
        <th>公司银行账号：</th>
        <td><input type="text" name="bank_account_number" value="<?php echo $output['joinin_detail']['bank_account_number'];?>"></td></tr>
    <tr>
        <th>开户银行支行名称：</th>
        <td><input type="text" name="bank_name" value="<?php echo $output['joinin_detail']['bank_name'];?>"></td>
    </tr>
    <tr>
        <th>支行联行号：</th>
        <td><input type="text" name="bank_code" value="<?php echo $output['joinin_detail']['bank_code'];?>"></td>
        </tr><tr>
        <th>开户银行所在地：</th>
        <td colspan="20"><input type="hidden" name="bank_address" id="bank_address" value="<?php echo $output['joinin_detail']['bank_address'];?>"></td>
    </tr>
    <tr>
        <th>开户银行许可证<br/>电子版：</th>
        <td colspan="20"><a nctype="nyroModal"  href="<?php echo getStoreJoininImageUrl($output['joinin_detail']['bank_licence_electronic']);?>"> <img src="<?php echo getStoreJoininImageUrl($output['joinin_detail']['bank_licence_electronic']);?>" alt="" /> </a>
              <input type="file" name="bank_licence_electronic"></td>
        </tr>
    </tbody>

</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">结算账号信息：</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">银行开户名：</th>
            <td><input type="text" name="settlement_bank_account_name" value="<?php echo $output['joinin_detail']['settlement_bank_account_name'];?>"></td>
    </tr>
    <tr>
        <th>公司银行账号：</th>
        <td><input type="text" name="settlement_bank_account_number" value="<?php echo $output['joinin_detail']['settlement_bank_account_number'];?>"></td>
    </tr>
    <tr>
        <th>开户银行支行名称：</th>
        <td><input type="text" name="settlement_bank_name" value="<?php echo $output['joinin_detail']['settlement_bank_name'];?>"></td>
    </tr>
    <tr>
        <th>支行联行号：</th>
        <td><input type="text" name="settlement_bank_code" value="<?php echo $output['joinin_detail']['settlement_bank_code'];?>"></td>
    </tr>
    <tr>
        <th>开户银行所在地：</th>
        <td><input type="hidden" name="settlement_bank_address" id="settlement_bank_address" value="<?php echo $output['joinin_detail']['settlement_bank_address'];?>"></td>
        </tr>
    </tbody>

</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">税务登记证</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">税务登记证号：</th>
            <td><input type="text" name="tax_registration_certificate" value="<?php echo $output['joinin_detail']['tax_registration_certificate'];?>"></td>
    </tr>
    <tr>
        <th>纳税人识别号：</th>
        <td><input type="text" name="taxpayer_id" value="<?php echo $output['joinin_detail']['taxpayer_id'];?>"></td>
    </tr>
    <tr>
        <th>税务登记证号<br />
            电子版：</th>
        <td><a nctype="nyroModal"  href="<?php echo getStoreJoininImageUrl($output['joinin_detail']['tax_registration_certif_elc']);?>"> <img src="<?php echo getStoreJoininImageUrl($output['joinin_detail']['tax_registration_certif_elc']);?>" alt="" /> </a>
              <input type="file" name="tax_registration_certif_elc"></td>
        </tr>
    </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
        <tr>
            <th colspan="20">店铺经营信息</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="w150">卖家账号：</th>
            <td><?php echo $output['joinin_detail']['seller_name'];?></td>
    </tr>
    <tr>
        <th class="w150">店铺名称：</th>
        <td><?php echo $output['joinin_detail']['store_name'];?></td>
    </tr>
    <tr>
        <th class="w150">店铺等级：</th>
        <td><?php echo $output['store_grade_name'];?></td>
    </tr>
    <tr>
        <th class="w150">店铺分类：</th>
        <td><?php echo $output['store_class_name'];?></td>
    </tr>
    <tr>
        <th>经营类目：</th>
        <td colspan="2"><table border="0" cellpadding="0" cellspacing="0" id="table_category" class="type">
                <thead>
                    <tr>
                        <th>分类1</th>
                        <th>分类2</th>
                        <th>分类3</th>
                        <th>比例</th>
                    </tr>
                </thead>
                <?php if(!empty($output['store_bind_class_list']) && is_array($output['store_bind_class_list'])) {?>
                <?php foreach($output['store_bind_class_list'] as $key=>$value) {?>
                    <tr>
                        <td><?php echo $value['class_1_name'];?></td>
                        <td><?php echo $value['class_2_name'];?></td>
                        <td><?php echo $value['class_3_name'];?></td>
                        <td><?php echo $value['commis_rate'];?>%</td>
                    </tr>
                <?php } ?>
                <?php } ?>
                </tbody>
        </table></td>
    </tr>
    <tr>
        <th>审核意见：</th>
        <td colspan="2"><?php echo $output['joinin_detail']['joinin_message'];?></td>
        </tr>
    </tbody>
</table>
<?php if($output['show_button'] == 1){?>
<div><label class="submit-border" id="btn_fail" ><input type="button" class="submit" value="提交"></label></div>
<?php }?>
</form>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript" src="<?php echo ADMIN_SITE_URL;?>/resource/js/jquery.nyroModal.js"></script>
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