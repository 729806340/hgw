/**
 * Created by Administrator on 2017/2/13 0013.
 */
$(function () {

    // var posturl = getSign('member_article.comment_add') + '&key='+key+'&client='+client;
    //type:1,回复文章;2:回复评论
    var type = _getUrlString('type');
    var id = _getUrlString('id');
    if(type == 2){
        var posturl = getSign('member_article.comment_reply') + '&key=' + key + '&client='+client;
    }else{
        var posturl = getSign('member_article.comment_add') + '&key=' + key + '&client='+client;
    }
//获取数据
    $("#send_comment").click(function () {
        var message = $("#message").val();
        if(message.length < 3){
            layer.open({
                content: '评论字数不能少于三个字'
                ,skin: 'msg'
                ,time: 2 //2秒后自动关闭n
            });
            return;
        }
        //文章id（应用级参数）
        var json = {
            id: id,
            message: message
        };
        getData(posturl, json, getPostData);
    })
//数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            layer.open({
                content: '添加评论成功'
                ,skin: 'msg'
                ,time: 2 //2秒后自动关闭n
            });
            setTimeout("_historyback()",2000);
            return;
        } else {
            layer.open({
                content: result.datas.error
                ,skin: 'msg'
                ,time: 2 //2秒后自动关闭
            });
            // console.log('error');
        }
    }
   
})
