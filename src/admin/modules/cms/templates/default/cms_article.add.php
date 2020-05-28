<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=cms_article" title="返回列表"><i
                    class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>文章管理 - <?php echo !empty($output['article_detail'])? '修改':'新增';?>文章</h3>
                <h5><?php echo !empty($output['article_detail'])? '修改':'添加';?>文章内容</h5>
            </div>
        </div>
    </div>
    <form id="article_form" method="post" name="articleForm" enctype="multipart/form-data">
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden"  id="article_id" name="article_id" value="<?php echo $output['article_detail']['article_id'];?>"/>
        <input type="hidden" id="new_class_id" name="new_class_id" value="<?php echo  !empty($output['article_detail']['article_class_id']) ? $output['article_detail']['article_class_id']:'1' ?>">
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit"><em>*</em>选择分类</dt>
                <dd class="opt">
                    <select id="article_class" name="article_class">
                        <?php if (!empty($output['article_class_list']) && is_array($output['article_class_list'])) { ?>
                            <?php foreach ($output['article_class_list'] as $value) { ?>
                                <option  <?php if(!empty($output['article_detail']['article_class_id']) && $output['article_detail']['article_class_id']==$value['class_id']) echo 'selected="true"';?>"
                                    value="<?php echo $value['class_id']; ?>"><?php echo $value['class_name']; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label><em>*</em>文章标题</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php if(!empty($output['article_detail']['article_title'])) echo $output['article_detail']['article_title']; ?>" name="article_title" id="article_title" class="input-txt">
                    <span class="err"></span>
                    <p class="notic">标题限定在50个字以内。</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit"><em>*</em>文章封面</dt>
                <dd class="opt">
                    <div class="input-file-show"><span class="show"><a class="nyroModal" rel="gal" href="<?php echo !empty($output['article_detail']['article_image']) ? getCMSArticleImageUrl($output['article_detail']['article_attachment_path'], $output['article_detail']['article_image']):UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['list_setting']['site_logo']);?>"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo  !empty($output['article_detail']['article_image']) ? getCMSArticleImageUrl($output['article_detail']['article_attachment_path'], $output['article_detail']['article_image']):UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['list_setting']['site_logo']);?>>')" onMouseOut="toolTip()"/></i> </a></span><span class="type-file-box">
            <input type="text" name="textfield" id="textfield1" class="type-file-text" />
            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
            <input class="type-file-file" id="article_image" name="article_image" type="file" size="30" hidefocus="true" nc_type="change_site_logo" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
            </span></div>
                    <span class="err"></span>
                    <p class="notic">图片尺寸以APP运营手册为准</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label><em>*</em>用户名</label>
                </dt>
                <dd class="opt">
                    <input type="text" value="<?php if(!empty($output['article_detail']['article_publisher_name'])) echo $output['article_detail']['article_publisher_name']; ?>" name="article_publisher_name" id="article_publisher_name" class="input-txt">
                    <span class="err"></span>

                    <p class="notic">用户名限定在24个字以内。</p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit"><em>*</em>用户头像</dt>
                <dd class="opt">
                    <div class="input-file-show"><span class="show"><a class="nyroModal" rel="gal" href="<?php echo !empty($output['article_detail']['article_publisher_avatar']) ? getCMSArticleImageUrl($output['article_detail']['article_attachment_path'], $output['article_detail']['article_publisher_avatar']):UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['list_setting']['site_logo']);?>"> <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo  !empty($output['article_detail']['article_publisher_avatar']) ? getCMSArticleImageUrl($output['article_detail']['article_attachment_path'], $output['article_detail']['article_publisher_avatar']):UPLOAD_SITE_URL.'/'.(ATTACH_COMMON.DS.$output['list_setting']['site_logo']);?>>')" onMouseOut="toolTip()"/></i> </a></span><span class="type-file-box">
            <input type="text" name="textfield2" id="textfield2" class="type-file-text" />
            <input type="button" name="button2" id="button2" value="选择上传..." class="type-file-button" />
            <input class="type-file-file" id="article_publisher_avatar" name="article_publisher_avatar" type="file" size="30" hidefocus="true" nc_type="change_site_logo" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
            </span></div>
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label><em>*</em>文章正文</label>
                </dt>
                <dd class="opt">
                    <?php !empty($output['article_detail']['article_content']) ? showEditor('article_content',$output['article_detail']['article_content']) :showEditor('article_content');?>
                    <span class="err"></span>
                    <p class="notic"></p>
                </dd>
            </dl>
            <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn">确认提交</a>
            </div>
        </div>

    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script>
    //按钮先执行验证再提交表单
    $(function () {
        $("#submitBtn").click(function () {
            if ($("#article_form").valid()) {
                var article_id="<?php echo !empty($output['article_detail']['article_id']) ? :'';?>";
                if(article_id!=''){
                    $("#article_form").attr("action","index.php?act=cms_article&op=cms_article_save");
                    $("#article_form").submit();
                    return false;
                }
                $("#article_form").submit();
            }
        });

        //控制标题的字数
        $("#article_title").keyup(function(){
            var content=$(this).val();
            var num=content.replace(/[^\x00-\xff]/g,"01").length;
            if(num>50){
               $(this).val(content.substring(0,50));
            }
        })
    });

    $(document).ready(function () {
        $('#article_form').validate({
            errorPlacement: function (error, element) {
                var error_td = element.parent('dd').children('span.err');
                error_td.append(error);
            },
            rules: {
                article_title: {
                    required: true
                },
                article_content: {
                    required: function () {
                        return $('#article_url').val() == '';
                    }
                }
            },
            messages: {
                article_title: {
                    required: '<i class="fa fa-exclamation-circle"></i>标题不能为空'
                },
                article_content: {
                    required: '<i class="fa fa-exclamation-circle"></i>内容不能为空'
                }
            }
        });
    });

    function insert_editor(file_path) {
        KE.appendHtml('article_content', '<img src="' + file_path + '" alt="' + file_path + '">');
    }

    // 模拟网站LOGO上传input type='file'样式
    $(function(){
        $("#article_image").change(function(){
            $("#textfield1").val($(this).val());
        });
        $("#article_publisher_avatar").change(function(){
            $("#textfield2").val($(this).val());
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
            //判断浏览器是否兼容html5
            if (window.applicationCache) {
                var file_length = this.files.length;
                var html = '';
                for (var i = 0; i < file_length; i++) {
                    html += getObjectURL(this.files[i]);
                }
                $("i.fa-picture-o").attr("onmouseover", "toolTip('<img src=" + html + ">')")
                $("a.nyroModal").attr("href", html);
            }
        });
       // 点击查看图片
        $('.nyroModal').nyroModal();
    });

    //建立一個可存取到該file的url
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }
</script>