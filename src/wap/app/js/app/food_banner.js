/**
 * Created by Administrator on 2017/2/10 0010.
 */
//index.special
$(function () {
    var posturl = getSign('index.special') + '&key=' + key + '&client='+client;
//文章id（应用级参数）
    var json = {special_id: 16};
//获取数据
    getData(posturl, json, getPostData);
//数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var image_url = result.datas.list[0].home1.image;
            $("#body_image").attr('src',image_url);
        } else {
            console.log('error');
        }
    }
})