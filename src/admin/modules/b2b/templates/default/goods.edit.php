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
          <input type="text" value="<?php echo $output['good_info']['goods_name']?>" name="goods_name" id="goods_name" maxlength="50" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">
                <label class="" for="s_sort">分类:</label>
            </dt>
            <input type="hidden" id="choose_gcid" name="choose_gcid" value="0"/>
            <?php if(!empty($output['b2c_goodsid'])){?>
            <input type="hidden" id="b2c_goodsid" name="b2c_goodsid" value="<?php echo $output['b2c_goodsid']?>"/>
            <?php } ?>
            <dd class="opt" id="searchgc_td">
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">
                <label class="" for="s_sort">选择供应商:</label>
            </dt>
            <dd class="opt">
                <select name="supplier_id">
                    <option value="0"><?php echo $lang['nc_please_choose'];?></option>
                    <?php foreach ($output['supplier_list'] as $k=>$v){?>
                        <?php if($output['good_info']['supplier_id'] == $v['supplier_id']){?>
                            <option value="<?php echo $v['supplier_id']?>" selected = "selected"><?php echo $v['company_name']?></option>
                        <?php }else{?>
                            <option value="<?php echo $v['supplier_id']?>" ><?php echo $v['company_name']?></option>
                        <?php }?>
                    <?php }?>
                </select>
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
                        <?php if(!empty($output['pic_list']) && is_array($output['pic_list'])){?>
                            <?php foreach($output['pic_list'] as $key => $val){ ?>
                                <li class="lc" nctype="<?php echo $val['upload_id'];?>" id="pic_<?php echo $val['upload_id'];?>">
                                    <input type="hidden" name="file_id[]" value="<?php echo $val['upload_id'];?>" />
                                    <input type="hidden" name="file_main[]" value="<?php echo $val['is_main'];?>" nctype="<?php echo $val['upload_id'];?>"/>
                                    <div class="thumb-list-pics"><a href="javascript:void(0);"><img src="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_B2B_GOODS.'/'.$val['file_name'];?>" alt="<?php echo $v['file_name'];?>"/></a></div>
                                    <a href="javascript:del_file_upload('<?php echo $val['upload_id'];?>');" class="del" title="<?php echo $lang['nc_del'];?>">X</a>
                                    <a href="javascript:void(0);" class="inset"><i class="fa fa-clipboard"></i>
                                        <?php if( $val['is_main']){?>
                                        主图
                                    <?php } else {?>
                                            设为主图
                                    <?php }?>
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>
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
                    <?php if (is_array($output['sku_list']) && !empty($output['sku_list'])){?>
                    <?php foreach ($output['sku_list'] as $aval){?>
                    <li>
                        <label class="w100 center" >
                            <input type="goods_calculate"  class="w50" name="at_value[<?php echo $aval['goods_id'];?>][calculate]" value="<?php echo $aval['goods_calculate'];?>" />
                        </label>
                        <label class="w100 center" >
                            <input type="goods_price" class="w50" name="at_value[<?php echo $aval['goods_id'];?>][price]" value="<?php echo $aval['goods_price'];?>" />
                        </label>
                        <label class="w100 center">
                            <input type="goods_storage" class="w50" name="at_value[<?php echo $aval['goods_id'];?>][storage]" value="<?php echo $aval['goods_storage'];?>" />
                        </label>
                        <label class="w100 center">
                            <input type="goods_cost" class="w50" name="at_value[<?php echo $aval['goods_id'];?>][cost]" value="<?php echo $aval['goods_cost'];?>" />
                        </label>
                        <label class="w100 center">
                            <input type="tax_input" class="w50" name="at_value[<?php echo $aval['goods_id'];?>][tax_input]" value="<?php echo $aval['tax_input'];?>" />
                        </label>
                        <label class="w100 center">
                            <input type="tax_output" class="w50" name="at_value[<?php echo $aval['goods_id'];?>][tax_output]" value="<?php echo $aval['tax_output'];?>"/>
                        </label>
                        <label class="w100 center"><a onclick="remove_attr($(this));" class="ncap-btn ncap-btn-red" href="JavaScript:void(0);">移除</a></label>
                    </li>
                        <?php }?>
                    <?php }?>
                </ul>
                <a id="add_type" class="ncap-btn" href="JavaScript:void(0);"><i class="fa fa-plus"></i>新增一条规格</a> </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label><em>*</em>商品详情</label>
            </dt>
            <dd class="opt">
                <?php showB2BEditor('goods_body',$output['good_info']['goods_body']);?>
                <span class="err"></span>
                <p class="notic"></p>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">上架状态</dt>
            <dd class="opt">
                <input id="show_type_1" type="radio" <?php echo $output['good_info']['goods_state']==1?'checked':'';?> value="1" style="margin-bottom:6px;" name="show_type" />
                <label for="show_type_1">上架</label>
                <input id="show_type_0" type="radio" <?php echo !$output['good_info']['goods_state']?'checked':'';?> value="0" style="margin-bottom:6px;" name="show_type" />
                <label for="show_type_0">下架</label>

                <span class="err"></span>
            </dd>
        </dl>


      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/b2b/common_b2b_select.js" charset="utf-8"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/b2b/b2b_goods_edit.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="/data/resource/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script>

var UPLOAD_ARTICLE_URL = "<?php echo UPLOAD_SITE_URL.'/'.ATTACH_B2B_GOODS.'/'; ?>";
var SITEURL = "<?php echo B2B_modules_URL;?>";


$(function(){


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


    //商品分类
    init_gcselect(<?php echo $output['gc_choose_json'];?>,<?php echo $output['gc_json']?>);


    $('#thumbnails > ul').find('.lc').click(function(){
        var nctype = $(this).attr('nctype');
        var main_img = $('li[nctype="' + nctype + '"]');
        main_img.find('.inset').text('主图');
        main_img.siblings().find('.inset').text('设为主图');

        main_img.find('input[nctype="' + nctype + '"]').val(1);
        main_img.siblings().find('input[name="file_main[]"]').val(0);
    });

});


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




</script> 
