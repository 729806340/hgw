<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=config_goods_class&config_tuan_id=<?php echo $output['class_array']['tuan_config_id']?>" title="返回商品分类列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>商品分类修改</h3>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
        <ul>
            <li><?php echo $lang['goods_class_edit_prompts_one'];?></li>
            <li><?php echo $lang['goods_class_edit_prompts_two'];?></li>
        </ul>
    </div>
    <form id="goods_class_form" name="goodsClassForm" enctype="multipart/form-data" method="post">
        <input type="hidden" name="form_submit" value="ok" />
        <input type="hidden" name="gc_id" value="<?php echo $output['class_array']['gc_id'];?>" />
        <input type="hidden" name="gc_parent_id" id="gc_parent_id" value="<?php echo $output['class_array']['gc_parent_id'];?>" />
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label class="gc_name" for="gc_name"><em>*</em>分类名称</label>
                </dt>
                <dd class="opt">
                    <input type="text" maxlength="20" value="<?php echo $output['class_array']['gc_name'];?>" name="gc_name" id="gc_name" class="input-txt">
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="gc_sort"><?php echo $lang['nc_sort'];?></label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php echo $output['class_array']['gc_sort'] == ''?0:$output['class_array']['gc_sort'];?>" name="gc_sort" id="gc_sort" class="txt">
                    <span class="err"></span>
                    <p class="notic"><?php echo $lang['goods_class_add_update_sort'];?></p>
                </dd>
            </dl>
            <?php /*if(empty($output['class_array']['gc_parent_id'])):*/?>
            <dl class="row">
                <dt class="tit"><label>栏目类型</label></dt>
                <dd class="opt">
                    <input type="radio" disabled="disabled"  name="wuliu_type" value="1" <?php echo $output['class_array']['type_id']=='1'?"checked":"" ?>>&nbsp;自提
                    <input type="radio" disabled="disabled"  name="wuliu_type" value="0" <?php echo $output['class_array']['type_id']=='0'?"checked":"" ?>>&nbsp;物流
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
            <?php /*endif;*/?>
            <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script>
    $(function(){
//自动加载滚动条
        $('#type_div').perfectScrollbar();

// 点击查看图片
        $('.nyroModal').nyroModal();
        //按钮先执行验证再提交表单
        $("#submitBtn").click(function(){
            if($("#goods_class_form").valid()){
                $("#goods_class_form").submit();
            }
        });

        $('input[type="radio"][name="t_id"]').change(function(){
            // 标记类型时候修改 修改为ok
            var t_id = <?php echo $output['class_array']['type_id'];?>;
            if(t_id != $(this).val()){
                $('#t_sign').val('ok');
            }else{
                $('#t_sign').val('');
            }

            if($(this).val() == '0'){
                $('#t_name').val('');
            }else{
                $('#t_name').val($(this).next('span').html());
            }
        });
        // 上传图片类型
        $('input[class="type-file-file"]').change(function(){
            var filepath=$(this).val();
            var extStart=filepath.lastIndexOf(".");
            var ext=filepath.substring(extStart,filepath.length).toUpperCase();
            if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
                alert("图片类型错误");
                $(this).attr('value','');
                return false;
            }
        });
        //$('#goods_class_form').validate({
        //    errorPlacement: function(error, element){
        //        var error_td = element.parent('dd').children('span.err');
        //        error_td.append(error);
        //    },
        //    rules : {
        //        gc_name : {
        //            required : true,
        //            remote   : {
        //                url :'index.php?act=tuan_config&op=goods_class_ajax&branch=check_class_name',
        //                type:'get',
        //                data:{
        //                    gc_name : function(){
        //                        return $('#gc_name').val();
        //                    },
        //                    gc_parent_id : function() {
        //                        return $('#gc_parent_id').val();
        //                    },
        //                    config_gc_id : '<?php //echo $output['class_array']['config_gc_id'];?>//'
        //                }
        //            }
        //        },
        //        gc_sort : {
        //            number   : true
        //        }
        //    },
        //    messages : {
        //        gc_name : {
        //            required : '<i class="fa fa-exclamation-circle"></i><?php //echo $lang['goods_class_add_name_null'];?>//',
        //            remote   : '<i class="fa fa-exclamation-circle"></i><?php //echo $lang['goods_class_add_name_exists'];?>//'
        //        },
        //        gc_sort  : {
        //            number   : '<i class="fa fa-exclamation-circle"></i><?php //echo $lang['goods_class_add_sort_int'];?>//'
        //        }
        //    }
        //});

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
