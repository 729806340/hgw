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
          <input type="text" value="" name="gc_name" id="gc_name" maxlength="20" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['nc_sort'];?></label>
        </dt>
        <dd class="opt">
          <input type="text" value="0" name="gc_sort" id="gc_sort" class="input-txt">
          <span class="err"></span>
          <p class="notic"><?php echo $lang['goods_class_add_update_sort'];?></p>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit"><label>栏目类型</label></dt>
            <dd class="opt">
                <input type="radio" name="wuliu_type" value="1" checked>&nbsp;自提
                <input type="radio" name="wuliu_type" value="0">&nbsp;物流
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">栏目显示图</dt>
            <dd class="opt">
                <div class="input-file-show"><span class="show"><a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['class_array']['app_img']);?>"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['class_array']['app_img']);?>>')" onMouseOut="toolTip()"/></i> </a></span>
                    <span class="type-file-box">
            <input type="text" name="textfield" id="textfield1" class="type-file-text" />
            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
            <input class="type-file-file" id="app_img" name="app_img" type="file" size="30" hidefocus="true" nc_type="change_site_logo" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
            </span></div>
                <span class="err"></span>
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
		if($("#goods_class_form").valid()){
			$("#goods_class_form").submit();
		}
	});
	
	$('input[type="radio"][name="t_id"]').click(function(){
		if($(this).val() == '0'){
			$('#t_name').val('');
		}else{
			$('#t_name').val($(this).next('span').html());
		}
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
                    gc_name : function(){
                        return $('#gc_name').val();
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
            wuliu_type : {
                number   : true
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
            gc_sort  : {
                number   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_sort_int'];?>'
            },
            wuliu_type  : {
                number   : '<i class="fa fa-exclamation-circle"></i><?php echo "请选择正确的物流类型";?>'
            }
        }
    });

	// 所属分类
    $("#gc_parent_id").live('change',function(){
    	type_scroll($(this));
    });
    // 类型搜索
    $("#gcategory > select").live('change',function(){
    	type_scroll($(this));
    });
});
var typeScroll = 0;
function type_scroll(o){
	var id = o.val();
	if(!$('#type_dt_'+id).is('dt')){
		return false;
	}
	$('#type_div').scrollTop(-typeScroll);
	var sp_top = $('#type_dt_'+id).offset().top;
	var div_top = $('#type_div').offset().top;
	$('#type_div').scrollTop(sp_top-div_top);
	typeScroll = sp_top-div_top;
}
gcategoryInit('gcategory');
</script> 
