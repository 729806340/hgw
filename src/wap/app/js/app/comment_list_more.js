/**
 * comment_list_more.js
 * @type {string}
 */
$(function () {
    var id = _getUrlString('id');
    var postCommentUrl = getSign('article.comment_view') + '&key=' + key + '&client='+client;
    var json = {id: id};
    getData(postCommentUrl, json, getCommentData);
    function getCommentData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var mainComment = result.datas;
            //评论基本信息
            $("#member_name").html(mainComment.member_name);
            $("#member_avatar").attr('src', mainComment.member_avatar);
            $("#comment_message").html(mainComment.comment_message);
            $("#comment_up").html(mainComment.comment_up);
            var comment_images = mainComment.comment_images;
            //评论图片
            if (comment_images.length >= 1) {
                var commentHtml = '';
                for (var i = 0; i < comment_images.length; i++) {
                    commentHtml += '<li class="inb preview-img">';
                    commentHtml += '    <img src="' + comment_images[i] + '" data-preview-src="" data-preview-group="1" />';
                    commentHtml += '</li>';
                }
                $("#comment_images").html(commentHtml);
            }
            //评论的回复
            var comment_quotes = mainComment.comment_quotes;
            if (comment_quotes.length > 0) {
                var quotesHtml = '';
                for (var i = 0; i < comment_quotes.length; i++) {
                    quotesHtml += '<li class="comment-view-cell line">';
                    quotesHtml += '    <div class="clearfix comment-view-top">';
                    quotesHtml += '<img class="fl headimg" src="' + comment_quotes[i].member_avatar + '"  onerror="javascript:this.src=\'images/img-head00.png\';"/>';
                    quotesHtml += '<div class="fl cob3 name font11">' + comment_quotes[i].member_name + '</div>';
                    quotesHtml += '<a class="fr cob9 font10 click_assist"  data-id="' + comment_quotes[i].comment_id + '">';
                    quotesHtml += '<img src="images/icon-zan2.png"/>';
                    quotesHtml += '<span class="cogreen" id="comment_assist' + comment_quotes[i].comment_id + '" data-id="' + comment_quotes[i].comment_id + '">' + comment_quotes[i].comment_up + '</span>';
                    quotesHtml += '</a>';
                    quotesHtml += '</div>';
                    quotesHtml += '<p class="cob6 comment-text block font12 ctl">' + comment_quotes[i].comment_message + ' </p>';
                    quotesHtml += '</li>';
                }
                $("#comment_quotes").html(quotesHtml);
            }else{
                $("#comment_quotes").css('display','none');
            }
        } else {

        }
    }

//评论点赞
    $("#click_assist").click(function () {
        var comment_up = $("#comment_up").html();
        var clickAssistUrl = getSign('member_article.comment_up') + '&key=' + key + '&client='+client;
        getData(clickAssistUrl, json, clickAssist);
    })
//文章点赞数据回调
    function clickAssist(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            layer.open({
                content: '点赞成功'
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
            var comment_up = $("#comment_up").html();
            comment_up = parseInt(comment_up) + 1;
            $("#comment_up").html(comment_up);
        } else {
            layer.open({
                content: result.datas.error
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
        }
    }

//回复点赞
    $(document).on('click', '.click_assist', function () {
        var comment_id = $(this).attr('data-id');
        var clickAssistUrl = getSign('member_article.comment_up') + '&key=' + key + '&client='+client;
        var commentJson = {id: comment_id};
        getDataValue(clickAssistUrl, commentJson, clickCommentAssist, comment_id);
    })
//评论点赞的回调
    function clickCommentAssist(data, typeValue) {
        var result = eval('(' + data + ')');
        console.log(data);
        if (result.code == '200') {
            layer.open({
                content: '点赞成功'
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
            var article_up = $("#comment_assist" + typeValue).html();
            article_up = parseInt(article_up) + 1;
            $("#comment_assist" + typeValue).html(article_up);
        } else {
            layer.open({
                content: result.datas.error
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
        }
    }
    $("#comment_add").click(function () {
        window.location.href = "comment_send.html?type=2&key="+key+"&id="+id;
    })
    $("#jump_history").bind('click',function(){
        history.go(-1);
    })
})