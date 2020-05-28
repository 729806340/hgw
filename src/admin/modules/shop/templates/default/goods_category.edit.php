<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=goods_category&op=goods_class" title="返回自定义分类列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>自定义分类管理 - 修改</h3>
            </div>
        </div>
    </div>
    <form id="goods_category_form" enctype="multipart/form-data" method="post">
        <input type="hidden" name="form_submit" value="ok"/>
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="cat_name"><em>*</em>分类名称</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php echo $output['cat']['cat_name'];?>" name="cat_name" id="cat_name" maxlength="20" class="input-txt">
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <input type="hidden" name="cat_id" value="<?php echo $output['cat']['cat_id'];?>">
                <dt class="tit">
                    <label for="parent_id">上级分类</label>
                </dt>
                <dd class="opt">
                    <select name="parent_id" id="parent_id">
                        <option value="0">-请选择-</option>
                        <?php foreach ((array)$output['cat_list'] as $k=>$v) {?>
                        <option value="<?php echo $v['cat_id']?>" <?php if($output['cat']['parent_id'] == $v['cat_id']){ echo "selected='selected'";}?>><?php echo $v['cat_name']?></option>
                            <?php foreach ((array)$v['child'] as $ck=>$cv) {?>
                                <option value="<?php echo $cv['cat_id']?>" <?php if($output['cat']['parent_id'] == $cv['cat_id']){ echo "selected='selected'";}?>>&nbsp;&nbsp;<?php echo $cv['cat_name']?></option>
                                <?php foreach ((array)$cv['child'] as $ck2=>$cv2) {?>
                                    <option value="<?php echo $cv2['cat_id']?>"<?php if($output['cat']['parent_id'] == $cv2['cat_id']){ echo "selected='selected'";}?>>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $cv2['cat_name']?></option>
                                <?php }?>
                            <?php }?>
                        <?php }?>
                    </select>
                    <span class="err"></span>
                    <p class="notic"><?php echo $lang['goods_class_add_sup_class_notice']; ?></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="cat_link">分类链接</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php echo $output['cat']['cat_link'];?>" name="cat_link" id="cat_link" maxlength="255" class="input-txt">
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="wap_link">wap端分类链接</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php echo $output['cat']['wap_link'];?>" name="wap_link" id="wap_link" maxlength="255" class="input-txt">
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">是否隐藏</dt>
                <dd class="opt">
                    <div class="onoff">
                        <label for="brand_recommend1" class="cb-enable <?php if($output['cat']['disable'] === 'true'){echo 'selected';}?>" title="是">是</label>
                        <label for="brand_recommend0" class="cb-disable <?php if($output['cat']['disable'] === 'false'){echo 'selected';}?>" title="否">否</label>
                        <input id="brand_recommend1" name="disable" value="true" <?php if($output['cat']['disable'] === 'true'){echo 'checked="checked"';}?> type="radio">
                        <input id="brand_recommend0" name="disable" value="false" <?php if($output['cat']['disable'] === 'false'){echo 'checked="checked"';}?> type="radio">
                    </div>
                    <p class="notic">选择“是”将不会出现在前台</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label>排序</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php echo $output['cat']['cat_sort'];?>" name="cat_sort" id="cat_sort" class="input-txt">
                    <span class="err"></span>
                    <p class="notic"><?php echo $lang['goods_class_add_update_sort']; ?></p>
                </dd>
            </dl>
            <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit']; ?></a></div>
        </div>
    </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js" charset="utf-8"></script>
<script>
    $(function () {
//自动加载滚动条
        $('#type_div').perfectScrollbar();
//按钮先执行验证再提交表单
        $("#submitBtn").click(function () {
            if ($("#goods_category_form").valid()) {
                $("#goods_category_form").submit();
            }
        });

        $('input[type="radio"][name="t_id"]').click(function () {
            if ($(this).val() == '0') {
                $('#t_name').val('');
            } else {
                $('#t_name').val($(this).next('span').html());
            }
        });
//表单验证
        $('#goods_category_form').validate({
            errorPlacement: function (error, element) {
                var error_td = element.parent('dd').children('span.err');
                error_td.append(error);
            },
            rules: {
                cat_name: {
                    required: true,
                    remote: {
                        url: 'index.php?act=goods_class&op=ajax&branch=check_class_name',
                        type: 'get',
                        data: {
                            cat_name: function () {
                                return $('#cat_name').val();
                            },
                            parent_id: function () {
                                return $('#parent_id').val();
                            },
                            gc_id: ''
                        }
                    }
                },
                cat_sort: {
                    number: true,
                    min:0,
                    max:255
                },
                cat_link:{
                    url: true
                },
                wap_link:{
                    url: true
                }
            },
            messages: {
                cat_name: {
                    required: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_name_null'];?>',
                    remote: '<i class="fa fa-exclamation-circle"></i><?php echo $lang['goods_class_add_name_exists'];?>'
                },
                cat_sort: {
                    number: '<i class="fa fa-exclamation-circle"></i>排序值必须为数字',
                    min: '<i class="fa fa-exclamation-circle"></i>排序值最小为0',
                    max: '<i class="fa fa-exclamation-circle"></i>排序值最大为255'
                },
                cat_link:{
                    url: '<i class="fa fa-exclamation-circle"></i>必须为url'
                },
                wap_link:{
                    url: '<i class="fa fa-exclamation-circle"></i>必须为url'
                }
            }
        });

        // 所属分类
        $("#parent_id").live('change', function () {
            type_scroll($(this));
        });
        // 类型搜索
        $("#gcategory > select").live('change', function () {
            type_scroll($(this));
        });
    });
    var typeScroll = 0;
    function type_scroll(o) {
        var id = o.val();
        if (!$('#type_dt_' + id).is('dt')) {
            return false;
        }
        $('#type_div').scrollTop(-typeScroll);
        var sp_top = $('#type_dt_' + id).offset().top;
        var div_top = $('#type_div').offset().top;
        $('#type_div').scrollTop(sp_top - div_top);
        typeScroll = sp_top - div_top;
    }
    gcategoryInit('gcategory');
</script> 
