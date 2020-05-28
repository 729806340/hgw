<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
#gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
<!--  <form method="post" id="importorder">-->
      <ul style="padding: 30px 50px;">
        <li><input name="file" id="uploadsfile" type="file"></li>
      </ul>
    <div class="bottom"><label class="submit-border">
            <button class="submit" style="background-image: linear-gradient(#48CFAE, #48CFAE);" id="importorder"><?php echo $lang['nc_submit'];?></button>
    </div>
<!--  </form>-->
</div>
<script>
    $(function () {
        $("#importorder").click(function() {
            var uploadsfile = $('#uploadsfile');
            if ($.trim(uploadsfile.val()) == '') {
                alert("请选择文件");
                return false;
            }
            var formdata = new FormData();
            var fileObj = document.getElementById("uploadsfile").files;
            for (var i = 0; i < fileObj.length; i++)
                formdata.append("file" + i, fileObj[i]);
            formdata.append('action', 'importorder');
            $.ajax({
                type: "POST",
                url: "index.php?act=fenxiao_order&op=index&action=importorder",
                data: formdata,
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function(XMLHttpRequest) {
                    // $('body').showLoading();
                },
                success: function(data)
                {
                    if (data.status == '1') {
                        $(".import-wrap").fadeOut();
                        var answer = confirm(data.msg);
                        if(answer) {
                            location.href="index.php?act=fenxiao_order&op=export_excel_result&key_name=" + data.data.key_name;
                        }
                        // getData(1, 0);
                    } else {
                        alert(data.msg);
                    }
                },
                complete: function(XMLHttpRequest, textStatus) {
                    $('body').hideLoading();
                },
                error: function() {
                    $('body').hideLoading();
                    alert('请求出错，请稍候再试！');
                }
            });
            return false;
        });
    })
</script>
