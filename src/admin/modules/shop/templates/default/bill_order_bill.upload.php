<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<form method="post" enctype="multipart/form-data" name="form1" action="index.php?act=bill&op=add_attachment&ob_id=<?php echo $_GET['ob_id'];?>">
    <input type="hidden" name="form_submit" value="ok">
    <div class="ncap-form-default">
        <dl class="row">
            <dt class="tit">
                <label for="description">附件说明</label>
            </dt>
            <dd class="opt">
                <textarea name="description" rows="6" class="tarea" id="description"></textarea>
                <p class="notic">请输入附件说明。</p>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label for="attachment_file">附件上传</label>
            </dt>
            <dd class="opt">
                <div class="input-file-show"><span class="type-file-box">
            <input class="type-file-file" id="attachment_file" name="attachment_file" type="file" size="30" hidefocus="true" nc_type="change_attachment_file" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
            </span></div>
                <p class="notic">上传后其他审核人员可以查看并下载该附件。</p>
            </dd>
        </dl>
        <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" onclick="document.form1.submit()">确认提交</a></div>
    </div>
</form>


<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">
    $(function(){
// 模拟默认用户图片上传input type='file'样式
        var textButton="<input type='text' name='textfield' id='textfield4' class='type-file-text' /><input type='button' name='button' id='button4' value='选择上传...' class='type-file-button' />"
        $(textButton).insertBefore("#attachment_file");
        $("#attachment_file").change(function(){
            $("#textfield4").val($("#attachment_file").val());
        });
// 上传图片类型
        $('input[class="type-file-file"]').change(function(){
            var filepath=$(this).val();
            var extStart=filepath.lastIndexOf(".");
            var ext=filepath.substring(extStart,filepath.length).toUpperCase();
            if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"
                &&ext!=".CSV"&&ext!=".XLS"&&ext!=".XLSX"&&ext!=".RAR"
                &&ext!=".ZIP"&&ext!=".DOC"&&ext!=".DOCX"
            ){
                //gif,jpg,jpeg,bmp,png,swf,tbi,xls,xlsx,rar,zip,csv
                alert("<?php echo '仅支持下列格式：gif,jpg,jpeg,bmp,png,doc,docx,xls,xlsx,rar,zip,csv';?>");
                $(this).attr('value','');
                return false;
            }
        });
// 点击查看图片
        $('.nyroModal').nyroModal();
    });
</script>

