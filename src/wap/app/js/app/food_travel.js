/**
 * Created by Administrator on 2017/2/10 0010.
 */
$(function () {
    var special_id = _getUrlString('special_id');
    var posturl = getSign('index.shilv') + '&key=' + key + '&client=' + client;
//文章id（应用级参数）
    var json = {special_id: special_id};
//获取数据
    getData(posturl, json, getPostData);
//数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var item = result.datas.list[0].article.item;
            var itemHtml = '';
            for (var i = 0; i < item.length; i++) {
                itemHtml += '<li class="bgwhite paddinglr comment-li food_article" data-id="' + item[i].article_id + '">';
                itemHtml += '      <div class="clearfix comment-view-top1 jumping" data-id="'+item[i].article_id+'">';
                itemHtml += '         <img class="fl headimg" src="' + item[i].article_publisher_avatar + '"  onerror="javascript:this.src=\'images/img-head00.png\';"/>';
                if (!item[i].article_publisher_name) {
                    itemHtml += '       <div class="fl cob3 name font12">农谷鲜</div>';
                } else {
                    itemHtml += '       <div class="fl cob3 name font12">' + item[i].article_publisher_name + '</div>';
                }
                itemHtml += '       </div>';
                itemHtml += '       <p class="cob6 comment-text block font12 jumping" data-id="'+item[i].article_id+'">' + item[i].article_abstract + '</p>';
                itemHtml += '       <img class="w jumping" data-id="'+item[i].article_id+'" src="' + item[i].article_image + '" />';
                itemHtml += '       <div class="clearfix comment-view-bottom">';
                itemHtml += '       <a class="fl db cob9 font10 tc w33 click_assist" data-id="'+item[i].article_id+'" href="javascript:void(0);">';
                itemHtml += '       <img src="images/icon-zan1.png"/>';
                itemHtml += '       <span class="cob9" id="article_num'+item[i].article_id+'">' + item[i].article_up + '</span>';
                itemHtml += '       </a>';
                itemHtml += '       <a class="fl db cob9 font10 tc w33 to_comment" data-id="'+item[i].article_id+'"  href="javascript:void(0);">';
                itemHtml += '       <img src="images/icon-evaluate.png"/>';
                itemHtml += '       <span class="cob9">' + item[i].article_comment_count + '</span>';
                itemHtml += '       </a>';
                itemHtml += '       <a class="fl db cob9 font10 tc w33 " href="javascript:void(0);">';
                itemHtml += '       <img class="icon-see" src="images/icon-liulan.png"/>';
                itemHtml += '       <span class="cob9">' + item[i].article_click + '</span>';
                itemHtml += '       </a>';
                itemHtml += '       </div>';
                itemHtml += '</li>';
            }
            $('#itemHtml').html(itemHtml);
        } else {
            console.log('error');
        }
    }
    //页面跳转
    $(document).on('click', '.jumping', function () {
        // var id = $(this).parent('li').attr('data-id');
        var id = $(this).attr('data-id');
        window.location.href = "food_article.html?type=travel&key=" + key + "&id=" + id;
    })
    //评论
    $(document).on('click', '.to_comment', function () {
        var id = $(this).attr('data-id');
        window.location.href = "comment_send.html?type=1&key=" + key + "&id=" + id;
    })


    //点赞
    $(document).on('click',".click_assist",function () {
        var article_id = $(this).attr('data-id');
        var clickAssistUrl = getSign('member_article.up') + '&key=' + key + '&client=' + client;
        var json = {id: article_id};
        getDataValue(clickAssistUrl, json, clickAssist,article_id);
    })
//文章点赞数据回调
    function clickAssist(data,typeValue) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            //信息框
            layer.open({
                content: '点赞成功'
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
            var article_up = $("#article_num"+typeValue).html();
            article_up = parseInt(article_up) + 1;
            $("#article_num"+typeValue).html(article_up);
        } else {
            //信息框
            layer.open({
                content: result.datas.error
                , skin: 'msg'
                , time: 2 //2秒后自动关闭
            });
        }
    }
})