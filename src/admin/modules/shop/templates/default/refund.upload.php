<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .img_show li img{
        width:15%;
        float:left;
        border:solid 1px #cccccc;
        margin-right:2%;
        margin-bottom:2%;
        margin-top:5px;
    }
</style>
<form id="upload_img" method="post" action="index.php?act=refund&op=upload_img">
    <input name="imgs" id="imgs" type="hidden">
    <input type="hidden" name="refund_id" value="<?php echo $output['refund_id']?>">
<div class="page" style="min-width: 95%;width: 600px;">
            <div id="refund_all">
                <dl class="row">
                    <dd class="opt">
                        <div class="input-file-show"><span class="show">
              <a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>"/>
              <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>>')" onMouseOut="toolTip()"></i></a>
              </span><span class="type-file-box">
            <input class="type-file-file" id="pic" name="pic" type="file" size="30">
            <input type="text" name="textfield" id="textfield1" class="type-file-text" />
            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
            </span></div>
                        <p class="notic">1.支持一次性上传多张图片,但最多只能上传5张。</p>
                        <p class="notic">2.点击图片可以删除图片</p>
                        <ul class="img_show"></ul>
                    </dd>
                </dl>
            </div>
            <div class="bot" style="clear: both;"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script>
    $('#pic').change(uploadChange);
    function uploadChange(){
        var filepatd=$(this).val();
        var extStart=filepatd.lastIndexOf(".");
        var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();
        if($("ul.img_show>li").length==5){
            alert("最多只能上传5张图片");
            return false;
        }
        if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
            alert("file type error");
            $(this).attr('value','');
            return false;
        }
        if ($(this).val() == '') return false;
        ajaxFileUpload();
    }
    function ajaxFileUpload()
    {
        $.ajaxFileUpload
        ({
                url:'index.php?act=return&op=pic_upload&form_submit=ok&uploadpath=shop/refund',
                secureuri:false,
                fileElementId:'pic',
                dataType: 'json',
                success: function (data, status)
                {
                    if (data.status == 1){
                        if(data.pic_info==""){
                            alert("上传图片失败，请刷新页面重新上传");
                            $('#pic').bind('change',uploadChange);
                            return false;
                        }
                        $("ul.img_show").append("<li onclick='delimg(this)'><img data-url='"+data.pic_info+"' src='"+data.url+"'></li>");
                    }else{
                        alert(data.msg);
                    }
                    $('#pic').bind('change',uploadChange);
                },
                error: function (data, status, e)
                {
                    alert('上传失败');
                    $('#pic').bind('change',uploadChange);
                }
            }
        )
    }

    function delimg(obj){
        var file_name=$(obj).find("img").attr("src");
        if(confirm("你确定要删除图片吗？")) {
            $("img[src='"+file_name+"']").parent().remove();
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "index.php?act=return&op=delimg",
                data: {"file_name": file_name},
                success: function (data) {
                    if (data.state=="1") {
                        alert(data.msg);
                    }
                }
            });
        }
    }
    $("#submitBtn").click(function () {
        var imgNum = $("ul.img_show>li").length;
       if($("ul.img_show>li").length < 1){
           alert('请上传凭证！');
           return false;
       }
        var arr=new Array();
        $("ul.img_show>li").each(function(){
            var img_src=$(this).children("img").attr("data-url");
            arr.push(img_src);
        });

        for(var i=0;i<arr.length;i++) {
            for(var j=i+1;j<arr.length;j++) {
                if(arr[i]===arr[j]) {
                    arr.splice(j,1);
                    j--;
                }
            }
        }

        if(arr.length>0){
            $("#imgs").val(arr.join(","));
        }
        $("#upload_img").submit();
    })
</script>
