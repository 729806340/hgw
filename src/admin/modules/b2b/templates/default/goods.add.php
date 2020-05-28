<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=goods&op=goods" title="返回商品列表"><i class="fa fa-arrow-circle-o-left"></i></a>
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
          <label for="gc_name"><em>*</em>商品名称</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="goods_name" id="goods_name" maxlength="20" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>


        <dl class="row">
            <dt class="tit">
                <label class="" for="s_sort">分类:</label>
            </dt>
            <dd class="opt">
                <div id="gcategory">
                    <input type="hidden" value="" class="mls_id" name="class_id" />
                    <input type="hidden" value="" class="mls_name" name="class_name" />
                    <select class="class-select">
                        <option value="0">请选择</option>
                        <?php if(!empty($output['bc_list'])){ ?>
                            <?php foreach($output['bc_list'] as $k => $v){ ?>
                                <?php if ($v['bc_pid'] == 0) {?>
                                    <option value="<?php echo $v['bc_id'];?>"><?php echo $v['bc_name'];?></option>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <p class="notic"><?php echo $lang['type_common_belong_class_tips'];?></p>
            </dd>
        </dl>



        <dl class="row">
            <dt class="tit">图片上传</dt>
            <dd class="opt">
                <div class="input-file-show" id="divComUploadContainer"><span class="type-file-box">
            <input class="type-file-file" id="fileupload" name="fileupload" type="file" size="30" multiple hidefocus="true" title="点击按钮选择文件上传">
            <input type="text" name="text" id="text" class="type-file-text" />
            <input type="button" name="button" id="button" value="选择上传..." class="type-file-button" />
            </span></div>
                <div id="thumbnails" class="ncap-thumb-list">
                    <ul nctype="pic_list">
                    </ul>
                </div>
            </dd>
        </dl>


        <dl class="row">
            <dt class="tit">规格:</dt>
            <dd class="opt">
                <ul class="ncap-ajax-add" id="ul_attr">
                    <li>
                        <label class="w100 center">计量单位

                        </label>
                        <label class="w100 center">单价

                        </label>
                        <label class="w100 center" >库存

                        </label>
                        <label class="w100 center" >成本价

                        </label>
                        <label class="w100 center" >进项税率

                        </label>
                        <label class="w100 center" >进项税率

                        </label>
                    </li>

                    <li>
                        <label class="w100 center" >
                            <input type="goods_calculate"  class="w50" name="at_value[key][calculate]" value="" />
                        </label>
                        <label class="w100 center" >
                            <input type="goods_price" class="w50" name="at_value[key][price]" value="" />
                        </label>
                        <label class="w100 center">
                            <input type="goods_storage" class="w50" name="at_value[key][storage]" value="" />
                        </label>
                        <label class="w100 center">
                            <input type="goods_cost" class="w50" name="at_value[key][cost]" value="" />
                        </label>
                        <label class="w100 center">
                            <input type="tax_input" class="w50" name="at_value[key][tax_input]" value="" />
                        </label>
                        <label class="w100 center">
                            <input type="tax_output" class="w50" name="at_value[key][tax_output]" />
                        </label>
                    </li>
                </ul>
                <a id="add_type" class="ncap-btn" href="JavaScript:void(0);"><i class="fa fa-plus"></i>新增一条规格</a> </dd>
        </dl>


        <dl class="row">
            <dt class="tit">
                <label><em>*</em>商品详情</label>
            </dt>
            <dd class="opt">
                <?php showB2BEditor('goods_body',$output['brand_array']['brand_introduction']);?>
                <span class="err"></span>
                <p class="notic"></p>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">上架状态</dt>
            <dd class="opt">
                <input id="show_type_0" type="radio" <?php echo !$output['brand_array']['show_type']?'checked':'';?> value="1" style="margin-bottom:6px;" name="show_type" />
                <label for="show_type_0">上架</label>
                <input id="show_type_1" type="radio" <?php echo $output['brand_array']['show_type']==1?'checked':'';?> value="0" style="margin-bottom:6px;" name="show_type" />
                <label for="show_type_1">下架</label>
                <span class="err"></span>
            </dd>
        </dl>


      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script>

var UPLOAD_ARTICLE_URL = "<?php echo UPLOAD_SITE_URL.'/'.ATTACH_B2B_GOODS.'/'; ?>";
var SITEURL = "<?php echo B2B_modules_URL;?>";

$(function(){

//自动加载滚动条
    $('#type_div').perfectScrollbar();
//按钮先执行验证再提交表单    
	$("#submitBtn").click(function(){
			$("#goods_class_form").submit();
	});

    // 添加属性
    var i = 0;

    var ul1_attr = '<li>' +
        '<label class="w100 center"><input type="goods_calculate" class="w50" name="at_value[key][calculate]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_price" class="w50" name="at_value[key][price]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_storage" class="w50" name="at_value[key][storage]" value="0" /></label>' +
        '<label class="w100 center"><input type="goods_cost" class="w50" name="at_value[key][cost]" value="0" /></label>' +
        '<label class="w100 center"><input type="tax_input" class="w50" name="at_value[key][tax_input]" value="0" /></label>' +
        '<label class="w100 center"><input type="tax_output" class="w50" name="at_value[key][tax_output]" value="0" /></label>' +
        '<label class="w100 center"><a onclick="remove_attr($(this));" class="ncap-btn ncap-btn-red" href="JavaScript:void(0);">移除</a></label>' +
        '</li>';

    $("#add_type").click(function(){
        $('#ul_attr > li:last').after(ul1_attr.replace(/key/g, i));
        i++;
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

    // 图片上传
    $('#fileupload').each(function(){
//        console.log('fileupload');
        $(this).fileupload({
            dataType: 'json',
            url: 'index.php?act=goods&op=upload_pic',
            done: function (e,data) {
                if(data != 'error'){
                    add_uploadedfile(data.result);
                }
            }
        });
    });

});

function add_uploadedfile(file){
    var newImg = '<li nctype="' + file.file_id + '" id="pic_' + file.file_id + '">' +
        '<input type="hidden" name="file_id[]" value="' + file.file_id + '" />' +
        '<input type="hidden" name="file_main[]" value="0" nctype="' + file.file_id + '"/>' +
        '<div class="thumb-list-pics">' +
        '<a href="javascript:void(0);"><img src="'+UPLOAD_ARTICLE_URL + file.file_name + '" /></a>' +
        '</div>' +
        '<a href="javascript:del_file_upload(' + file.file_id + ');" class="del" title="删除">X</a>' +
        '<a href="javascript:void(0);" class="inset"><i class="fa fa-clipboard"></i>设为主图</a>' +
        '</li>';
    $('#thumbnails > ul').prepend(newImg);
    $('#thumbnails > ul').find('li[nctype="' + file.file_id + '"]').click(function(){
        var nctype = $(this).attr('nctype');
        var main_img = $('li[nctype="' + nctype + '"]');
        main_img.find('.inset').text('主图');
        main_img.siblings().find('.inset').text('设为主图');

        main_img.find('input[nctype="' + nctype + '"]').val(1);
        main_img.siblings().find('input[name="file_main[]"]').val(0);
    });
}

function del_file_upload(file_id){
    if(!window.confirm('<?php echo $lang['nc_ensure_del'];?>')){
        return;
    }
    $.getJSON('index.php?act=goods&op=del_pic&file_id=' + file_id, function(result){
        if(result){
            $('#pic_' + file_id).remove();
        }
    });
}

function remove_attr(o){
    o.parents('li:first').remove();
}


// 所属分类
gcategoryInitB2B('gcategory');


</script> 
