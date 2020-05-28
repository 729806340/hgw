<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=supplier&op=index" title="返回供应商列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>供应商管理 - <?php echo $lang['nc_new'];?></h3>
        <h5>供应商管理</h5>
      </div>
    </div>
  </div>
  <form id="supplier_add_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="gc_name"><em>*</em>公司名称</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="company_name" id="company_name" maxlength="20" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
      </dl>

      <dl class="row">
        <dt class="tit">
          <label>地址</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="address" id="address" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>

        <dl class="row">
            <dt class="tit">
                <label for="manage_type"> 店铺类型 </label>
            </dt>
            <dd class="opt">
                <select id="manage_type" name="manage_type">
                    <option  selected="selected" value="platform">请选择类型</option>
                    <option  value="platform">平台商家</option>
                    <option  value="co_construct">共建商家</option>
                </select>
                <span class="err"></span>
                <p class="notic">
                    店铺类型申请提交后，系统将邮件通知相关人员进行修改，请勿重复提交；
                </p>
            </dd>
        </dl>

      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script>
$(function(){
//自动加载滚动条
    $('#type_div').perfectScrollbar();
//按钮先执行验证再提交表单    
	$("#submitBtn").click(function(){
			$("#supplier_add_form").submit();
	});
	

//表单验证	
	$('#supplier_add_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            company_name : {
                required : true
            },
            address : {
            	required :true
            }
        },
        messages : {
            company_name : {
                required : '要求公司名称'
            },
            address : {
            	required : '要求填写地址'
            }
        }
    });


});

</script> 
