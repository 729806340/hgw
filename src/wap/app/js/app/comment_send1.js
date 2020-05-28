/**
 * Created by Administrator on 2017/2/13 0013.
 */
$(function () {
    var post_upload_url = getSign('member_index.upload') + '&key=' + key + '&type=0&client='+client;
    var id = 1;
//        $("#image_list").append('<li class="fl img-show"> <img src="images/img_pj00.png"/> </li>');
    //图片上传
    $("#add_images").on('click', function () {
        var chkmodify = $("#image_list").children("li").children('img').length;//判断上传了多少张图片
        if (chkmodify < 4) {
            select_image(chkmodify + 1);

        } else {
            alert('最多只能上传3张图片');
        }
    });
    //触发图片选择
    function select_image(num) {
        $("#ict_imgbox" + num).click();
        $("#ict_imgbox" + num).change(function () {
            var src = getfilefull('ict_imgbox' + num);
            $("#image_list").append('<li data-id="'+num+'" class="fl img-show "> <img src="' + src + '"/> </li>');
            upload_img(num);
            if (num == 3) {
                $("#add_images").css('display', 'none');
            }
        });
    }
    //上传图片
    function upload_img(num) {
        $("#myForm").ajaxSubmit({
            type: "POST",//提交类型
            dataType: "json",//返回结果格式
            url: post_upload_url,//请求地址
            data: {"action": "TemporaryImage"},//请求数据
            success: function (data) {//请求成功后的函数
                console.log(num);
                if (data.code == '200') {
                    $("#image" + num).val(data.datas.src);
                } else {
                    alert('上传失败');
                }
            },
            error: function (data) {
                console.log(data);
            },//请求失败的函数
            async: true
        });
    }

    $("#send_comment").click(function () {
        var posturl = getSign('member_article.comment_add') + '&key=' + key + '&client='+client;
        var message = $("#message").val();
        var image1 = $("#image1").val();
        var image2 = $("#image2").val();
        var image3 = $("#image3").val();
        if(!message){
            alert('请填写评论后再提交');return;
        }
        var json = {
            id:id,
            message: message,
            image1: image1,
            image2: image2,
            image3: image3,
        };
        getData(posturl, json, getPostData);

    })
    //数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            alert('添加评论成功');
        } else {
            console.log('error');
        }
    }
})