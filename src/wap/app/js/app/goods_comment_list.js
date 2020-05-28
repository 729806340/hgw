/**
 * Created by Administrator on 2017/2/16 0016.
 */
$(function () {
    var goods_id = _getUrlString('goods_id');
    var postCommentUrl = getSign('goods.evaluate') + '&key=' + key + '&client='+client;
    var json = {
        type: 0,
        goods_id: goods_id
    };
    getData(postCommentUrl, json, getCommentData);
    function getCommentData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var list = result.datas.goods_eval_list;
            if(list.length > 0){
                $("#commentNum").html(list.length);
                if(list.length > 3){
                    list.length = 3;
                }
                var evalHtml = '';
                for(var i=0;i<list.length;i++){
                    evalHtml += '<li class="bgwhite paddinglr comment-li comment-view">';
                    evalHtml += '    <div class="clearfix comment-view-top"> <img class="fl headimg" src="'+list[i].member_avatar+'" />';
                    evalHtml += '   <div class="fl cob3 name font11">'+list[i].geval_frommembername+'</div>';
                    evalHtml += '   <div class="cob font10 fr comment-time">'+userDate(list[i].geval_addtime)+'</div>';
                    evalHtml += '    </div>';
                    evalHtml += '   <p class="cob6 comment-text block font12">'+list[i].geval_content+'</p>';
                    evalHtml += '    <ul class="preview-list">';
                    var geval_image = list[i].geval_image;
                    if(geval_image.length > 0){
                        for(var j=0;j<geval_image.length;j++){
                            evalHtml += '<li class="inb preview-img"> <img src="'+geval_image[j]+'" data-preview-src="" data-preview-group="1" /> </li>';
                        }
                    }
                    evalHtml += '    </ul>';
                    evalHtml += '</li>';
                }
                $("#comment_content").html(evalHtml);
            }


        } else {

        }
    }


})