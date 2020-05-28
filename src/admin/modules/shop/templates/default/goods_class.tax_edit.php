<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=goods_class&op=goods_class&gc_id=<?php echo $output['class_array']['gc_parent_id']?>" title="返回商品分类列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>类目税率修改 - <?php echo $lang['nc_edit'];?>“<?php echo $output['class_array']['gc_name'];?>”</h3>
        <h5>类目税率及子类目税率修改</h5>
      </div>
    </div>
  </div>
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>勾选关联到子分类后，该分类下的子分类税率也将按此继承设定。</li>
    </ul>
  </div>
  <form id="goods_class_form" name="goodsClassForm" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="gc_id" value="<?php echo $output['class_array']['gc_id'];?>" />
    <div class="ncap-form-default">
      <p style="margin: 10px; font-size: 16px;"><?php echo $output['class_array']['gc_name'];?></p>
	  <p style="margin: 10px; font-size: 14px;">
			当前进项税：<span style="color:red;"><?php echo $output['tax_arr'][ $output['class_array']['tax_input'] ];?></span>，
			当前销项税：<span style="color:red;"><?php echo $output['tax_arr'][ $output['class_array']['tax_output'] ];?></span>
		</p>
      
      <dl class="row">
        <dt class="tit">
          <label><em>*</em>类目税率</label>
        </dt>
        <dd class="opt">
          <label>进项税：</label>
		  <select name="tax_input" id="tax_input">
		  <?php foreach ( $output['tax_arr'] as $key => $value ) { ?>
			<option value="<?php echo $key?>"><?php echo $value?></option>
		  <?php } ?>
		  </select>
          ，
		  <label>销项税：</label>
		  <select name="tax_output" id="tax_output">
		  <?php foreach ( $output['tax_arr'] as $key => $value ) { ?>
			<option value="<?php echo $key?>"><?php echo $value?></option>
		  <?php } ?>
		  </select>
		  <span class="err"></span>
        </dd>
      </dl>

      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script>
$(function(){

	//按钮先执行验证再提交表单
	$("#submitBtn").click(function(){
	    if($("#goods_class_form").valid()){
	     $("#goods_class_form").submit();
		}
	});

	$('#goods_class_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            tax_input : {
            	required : true,
                remote   : {
                url :'index.php?act=goods_class&op=ajax&branch=check_tax',
                type:'get',
                data:{
                    tax : function(){
                        return $('#tax_input').val();
                    }
                  }
                }
            },
			tax_output : {
            	required : true,
                remote   : {
                url :'index.php?act=goods_class&op=ajax&branch=check_tax',
                type:'get',
                data:{
                    tax : function(){
                        return $('#tax_output').val();
                    }
                  }
                }
            },
        },
        messages : {
            tax_input : {
            	remote : '<i class="fa fa-exclamation-circle"></i>请正确填写分佣比例',
            },
			tax_output : {
            	remote : '<i class="fa fa-exclamation-circle"></i>请正确填写分佣比例',
            }
        }
    });

    // 类型搜索
    $("#gcategory > select").live('change',function(){
    	type_scroll($(this));
    });
});
</script>
