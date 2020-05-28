<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<form method="post" enctype="multipart/form-data" name="form1" action="index.php?act=store_bill&op=batch_edit&ob_id=<?php echo $_GET['ob_id'];?>">
    <input type="hidden" name="form_submit" value="ok">
    <div class="eject_con">
        <dl>
            <dt>
                <label for="description">上传说明</label>
            </dt>
            <dd class="opt">
                <p style="color: red;">上传后将批量修改相关数据，请核对数据无误后再上传！每次批量修改建议不超过500条。</p>
                <p style="color: red;">若批量修改并非全部成功，会将操作结果以文件形式返回，请下载该文件查看详情。</p>
                <p><a href="/admin/public/batch_bill_modify_sample.csv">&gt;&gt;&gt;模板下载&lt;&lt;&lt;</a></p>
            </dd>
        </dl>
        <dl>
            <dt>
                <label for="attachment_file">附件上传</label>
            </dt>
            <dd class="opt">
                <div class="input-file-show"><span class="type-file-box">
            <input class="input-file" id="attachment_file" name="attachment_file" type="file" size="30" hidefocus="true" nc_type="change_attachment_file" title="请上传CSV文件">
            </span></div>
                <p class="notic">当前仅允许批量修改红包金额，上传文档格式为csv</p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="button" id="btn_add_certification" class="submit" value="确认提交" onclick="document.form1.submit();" /></label>
        </div>
    </div>
</form>


<script type="text/javascript">
    $(function(){
        // 上传图片类型
        $('input[class="input-file"]').change(function(){
            var filepath=$(this).val();
            var extStart=filepath.lastIndexOf(".");
            var ext=filepath.substring(extStart,filepath.length).toUpperCase();
            if(ext!=".CSV"){
                alert("文件格式错误！");
                $(this).attr('value','');
                return false;
            }
        });
    });
</script>

