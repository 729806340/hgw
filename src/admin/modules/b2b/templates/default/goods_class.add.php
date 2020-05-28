<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=goods_class&op=goods_class" title="返回商品分类列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $lang['goods_class_index_class'];?> - <?php echo $lang['nc_new'];?></h3>
        <h5><?php echo $lang['goods_class_index_class_subhead'];?></h5>
      </div>
    </div>
  </div>
  <form id="goods_class_form" enctype="multipart/form-data" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="gc_name"><em>*</em><?php echo $lang['goods_class_index_name'];?></label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="bc_name" id="bc_name" maxlength="20" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>

      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['nc_sort'];?></label>
        </dt>
        <dd class="opt">
          <input type="text" value="0" name="bc_sort" id="bc_sort" class="input-txt">
          <span class="err"></span>
          <p class="notic"><?php echo $lang['goods_class_add_update_sort'];?></p>
        </dd>
      </dl>

        <dl class="row">
            <dt class="tit">
                <label><em>*</em>分佣比例</label>
            </dt>
            <dd class="opt">
                <input id="commis_rate" class="w60" type="text" value="<?php echo $output['class_info']['commis_rate'];?>" name="commis_rate">
                <i>%</i> <span class="err"></span>
                <p class="notic mb10">分佣比例必须为0-100的整数。</p>
                <!--                <label for="t_commis_rate">-->
                <!--                    <input id="t_commis_rate" class="checkbox" type="checkbox" value="1" name="t_commis_rate">-->
                <!--                    关联到子分类</label>-->
                <!--                <p class="notic">勾选关联到子分类后，该分类下的子分类分佣比利也将按此继承设定。</p>-->
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
			$("#goods_class_form").submit();
	});
	

//表单验证	
	$('#goods_class_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            gc_name : {
                required : true,
                remote   : {                
                url :'index.php?act=goods_class&op=ajax&branch=check_class_name',
                type:'get',
                data:{
                    bc_name : function(){
                        return $('#bc_name').val();
                    },
                    gc_parent_id : function() {
                        return $('#gc_parent_id').val();
                    },
                    gc_id : ''
                  }
                }
            },
            commis_rate : {
            	required :true,
                max :100,
                min :0,
                digits :true
            },
            gc_sort : {
                number   : true
            }
        },
        messages : {
            gc_name : {
                required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_name_null'];?>',
                remote   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_name_exists'];?>'
            },
            commis_rate : {
            	required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_commis_rate_error'];?>',
                minlength :'<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_commis_rate_error'];?>',
                maxlength :'<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_commis_rate_error'];?>',
                digits :'<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_commis_rate_error'];?>'
            },
            gc_sort  : {
                number   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_sort_int'];?>'
            }
        }
    });


});

</script> 
