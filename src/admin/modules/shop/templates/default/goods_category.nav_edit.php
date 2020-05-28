<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=goods_category&op=goods_class" title="返回自定义分类列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>自定义分类导航管理</h3>
                <h5><?php echo $lang['goods_class_index_class_subhead']; ?></h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span']; ?>"></span></div>
        <ul>
            <li>设置前台左上侧商品分类导航的相关信息，可以设置分类前图标、推荐分类、推荐品牌以及两张广告图片。</li>
            <li>对分类作任何更改后，都需要到 平台 -> 设置 -> 清理缓存 清理 <a href="/admin/modules/system/index.php?act=cache"><span style="color: red;">自定义分类</span></a>，新的设置才会生效</li>
        </ul>
    </div>
    <form id="goods_class_form" name="goodsClassForm" enctype="multipart/form-data" method="post">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="cat_id" value="<?php echo $output['cat_id']; ?>"/>
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="pic">自定义分类LOGO</label>
                </dt>
                <dd class="opt">
                    <div class="input-file-show">
                        <span class="show">
                            <a class="nyroModal" rel="gal"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CATEGORY . '/' . $output['nav_info']['logo']; ?>>')" onMouseOut="toolTip()"></i></a>
                        </span>
                        <span class="type-file-box">
                            <input class="type-file-file" id="clogo" name="clogo" type="file" size="30" nc_type="change_pic" hidefocus="true" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
                            <input type="text" name="logo" id="logo" class="type-file-text" value="<?php echo $output['nav_info']['logo']; ?>"/>
                            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button"/>
                        </span>
                    </div>
                    <p class="notic">建议使用16*16像素PNG透明背景图片</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="pic">wap端自定义分类LOGO</label>
                </dt>
                <dd class="opt">
                    <div class="input-file-show">
                        <span class="show">
                            <a class="nyroModal" rel="gal"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CATEGORY . '/' . $output['nav_info']['wap_logo']; ?>>')" onMouseOut="toolTip()"></i></a>
                        </span>
                        <span class="type-file-box">
                            <input class="type-file-file" id="wlogo" name="wlogo" type="file" size="30" nc_type="change_pic" hidefocus="true" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
                            <input type="text" name="wap_logo" id="wap_logo" class="type-file-text" value="<?php echo $output['nav_info']['wap_logo']; ?>"/>
                            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button"/>
                        </span>
                    </div>
                    <p class="notic">建议使用22*22像素PNG透明背景图片</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label><em>*</em>推荐分类</label>
                </dt>
                <dd class="opt">
                    <div> 分类下的三级分类 <a class="ncap-btn" nctype="class_hide" href="javascript:void(0);">隐藏未选项</a></div>
                    <div id="class_div" class="scrollbar-box">
                        <div class="ncap-type-spec-list">
                            <?php if (!empty($output['third_category'])) { ?>
                                <?php foreach ($output['third_category'] as $key => $val) { ?>
                                    <dl>
                                        <dt id="class_dt_<?php echo $key; ?>"><?php echo $val['cat_name']; ?></dt>
                                        <?php if (!empty($val['child'])) { ?>
                                            <dd>
                                                <?php foreach ($val['child'] as $k => $v) { ?>
                                                    <label for="class_<?php echo $k; ?>">
                                                        <input type="checkbox" name="recommend_catids[]" value="<?php echo $v['cat_id']; ?>" <?php
                                                        if (count($output['nav_info']['recommend_catids']) > 0) {
                                                            if (in_array($v['cat_id'], $output['nav_info']['recommend_catids'])) {
                                                                echo 'checked="checked"';
                                                            }
                                                        }
                                                        ?>>
                                                        <?php echo $v['cat_name']; ?> </label>
                                                <?php } ?>
                                            </dd>
                                        <?php } ?>
                                    </dl>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <p class="notic">推荐分类将在展开后的二、三级导航列表上方突出显示，建议根据分类名称长度控制选择数量不超过8个以确保展示效果。</p>
                </dd>
            </dl>
            <dl class="row" id="add">
                <dt class="tit">
                    <label for="pic"></label>
                </dt>
                <dd class="opt">
                    <a class="ncap-btn" href="javascript:addAd();">新增广告图</a>
                </dd>
            </dl>
            <?php if(!$output['nav_info']['nav']){?>
            <dl class="row ad" id="pic0">
                <dt class="tit">
                    <label for="pic">广告图</label>
                </dt>
                <dd class="opt">
                    <div class="input-file-show">
                        <span class="show">
                            <a class="nyroModal" rel="gal"><i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CATEGORY . '/' . $bv['nav_url']; ?>>')" onMouseOut="toolTip()"></i> </a>
                        </span>
                        <span class="type-file-box">
                            <input class="type-file-file" id="f_ad0" name="f_ad0" type="file" size="30" nc_type="change_pic" onchange="adChange(0)" hidefocus="true">
                            <input type="text" name="nav_url0" id="nav_url0" class="type-file-text" value=""/>
                            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button"/>
                        </span>
                    </div>
                    <label title="分类导航广告图1-跳转链接" class="ml5"><i class="fa fa-link"></i>
                        <input type="text" value="" name="nav_link0" id="nav_link0" class="input-txt ml5">
                        <input type="checkbox" value="1" name="is_large0">是否大图
                    </label>
                    <a class="ncap-btn" href="javascript:delAd(0);" style="margin-left: 52px;">删除</a>
                </dd>
            </dl>
            <?php }else{?>
                <?php foreach ($output['nav_info']['nav'] as $k => $v) {?>
                    <dl class="row ad" id="pic<?php echo $k;?>">
                        <dt class="tit">
                            <label for="pic">广告图</label>
                        </dt>
                        <dd class="opt">
                            <div class="input-file-show">
                        <span class="show">
                            <a class="nyroModal" rel="gal"><i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL . '/' . ATTACH_GOODS_CATEGORY . '/' . $v['nav_url']; ?>>')" onMouseOut="toolTip()"></i> </a>
                        </span>
                        <span class="type-file-box">
                            <input class="type-file-file" id="f_ad<?php echo $k;?>" name="f_ad<?php echo $k;?>" type="file" size="30" nc_type="change_pic" onchange="adChange(<?php echo $k;?>)" hidefocus="true">
                            <input type="text" name="nav_url<?php echo $k;?>" id="nav_url<?php echo $k;?>" class="type-file-text" value="<?php echo $v['nav_url'];?>"/>
                            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button"/>
                        </span>
                            </div>
                            <label title="分类导航广告图1-跳转链接" class="ml5"><i class="fa fa-link"></i>
                                <input type="text" value="<?php echo $v['nav_link'];?>" name="nav_link<?php echo $k;?>" id="nav_link<?php echo $k;?>" class="input-txt ml5">
                                <input type="checkbox" value="1" <?php if($v['is_large'] == 1){echo 'checked="checked" ';}?> name="is_large<?php echo $k;?>">是否大图
                            </label>　　　　
                            <a class="ncap-btn" href="javascript:delAd(<?php echo $k;?>);">删除</a>
                        </dd>
                    </dl>
                <?php }?>
            <?php }?>
            <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit']; ?></a></div>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL; ?>/js/jquery.nyroModal.js"></script>
<script>
    function addAd() {
        var adHtml = "";
        var adLen = $('.ad').length;
        adHtml += '<dl class="row ad" id="pic'+adLen+'">';
        adHtml += '<dt class="tit">';
            adHtml += '<label for="pic">广告图</label>';
            adHtml += '</dt>';
            adHtml += '<dd class="opt">';
            adHtml += '<div class="input-file-show">';
            adHtml += '<span class="show">';
            adHtml += '<a class="nyroModal" rel="gal"><i class="fa fa-picture-o" onMouseOver="toolTip()" onMouseOut="toolTip()"></i> </a>';
            adHtml += '</span>';
            adHtml += '<span class="type-file-box">';
            adHtml += '<input class="type-file-file" id="f_ad'+adLen+'" name="f_ad'+adLen+'" type="file" size="30" nc_type="change_pic" onchange="adChange('+adLen+')" hidefocus="true">';
            adHtml += '<input type="text" name="nav_url'+adLen+'" id="nav_url'+adLen+'" class="type-file-text" value=""/>';
            adHtml += '<input type="button" name="button" id="button1" value="选择上传..." class="type-file-button"/>';
            adHtml += '</span>';
            adHtml += '</div>';
            adHtml += '<label title="分类导航广告图1-跳转链接" class="ml5">&nbsp;&nbsp;<i class="fa fa-link"></i>';
            adHtml += '<input type="text" value="" name="nav_link'+adLen+'" id="nav_link'+adLen+'" class="input-txt ml5">&nbsp;';
            adHtml += '<input type="checkbox" value="1" name="is_large'+adLen+'">是否大图';
            adHtml += '</label>';
            adHtml += '<a class="ncap-btn" href="javascript:delAd('+adLen+');" style="margin-left: 55px;">删除</a>';
            adHtml += '</dd>';
            adHtml += '</dl>';
        if(adLen != 0){
            $('.ad').eq(adLen - 1).after(adHtml);
        }else{
            $('#add').after(adHtml);
        }

    }

    function delAd(index){
        $('#pic'+index).remove();
    }

    function adChange(index) {
        $('#nav_url' + index).val($('#f_ad' + index).val());
    }
    $(function () {
        $("#clogo").change(function () {
            $("#logo").val($(this).val());
        });
        $("#wlogo").change(function () {
            $("#wap_logo").val($(this).val());
        });
        //自动加载滚动条
        $('#class_div').perfectScrollbar();
        $('#brand_div').perfectScrollbar();

        // 点击查看图片
        $('.nyroModal').nyroModal();
        //按钮先执行验证再提交表单
        $("#submitBtn").click(function () {
            if ($("#goods_class_form").valid()) {
                $("#goods_class_form").submit();
            }
        });

        // 子级分类隐藏未选项
        $('a[nctype="class_hide"]').live('click', function () {
            checked_hide('class');
        });
        // 子级分类全部显示
        $('a[nctype="class_show"]').live('click', function () {
            checked_show('class');
        });
    });
    var brandScroll = 0;
    function brand_scroll(o) {
        var id = o.val();
        if (!$('#brand_dt_' + id).is('dt')) {
            return false;
        }
        $('#brand_div').scrollTop(-brandScroll);
        var sp_top = $('#brand_dt_' + id).offset().top;
        var div_top = $('#brand_div').offset().top;
        $('#brand_div').scrollTop(sp_top - div_top);
        brandScroll = sp_top - div_top;
    }


    //隐藏未选项
    function checked_show(str) {
        $('#' + str + '_div').find('dt').show().end().find('label').show();
        $('#' + str + '_div').find('dl').show();
        $('a[nctype="' + str + '_show"]').attr('nctype', str + '_hide').html('隐藏未选项');
        $('#' + str + '_div').perfectScrollbar('destroy').perfectScrollbar();
    }

    //显示全部选项
    function checked_hide(str) {
        $('#' + str + '_div').find('dt').hide();
        $('#' + str + '_div').find('input[type="checkbox"]').parents('label').hide();
        $('#' + str + '_div').find('input[type="checkbox"]:checked').parents('label').show();
        $('#' + str + '_div').find('dl').each(function () {
            if ($(this).find('input[type="checkbox"]:checked').length == 0) $(this).hide();
        });
        $('a[nctype="' + str + '_hide"]').attr('nctype', str + '_show').html('显示未选项');
        $('#' + str + '_div').perfectScrollbar('destroy').perfectScrollbar();
    }
    gcategoryInit('brandcategory');

    /*function addBrandLogo(){
     alert(1)
     }*/
</script> 
