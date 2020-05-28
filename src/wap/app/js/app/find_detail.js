/**
 * Created by Administrator on 2017/2/6 0006.
 * find_detail.js
 */

$(function () {
    // var id = 1;
    var id = _getUrlString('id');
//url
    var posturl = getSign('article.view') + '&key='+key+'&client='+client;
//文章id（应用级参数）
    var json = {id: id};
//获取数据
    getData(posturl, json, getPostData);
//数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            $("#main_img").attr('src', result.datas.article_image);
            $("#numbers").html(result.datas.article_click);
            console.log('success');
            console.log(result);
        } else {
            console.log('error');
        }
    }

//评论
    var postCommentUrl = getSign('article.comment') + '&key='+key+'&client='+client;
//获取数据
    getData(postCommentUrl, json, getCommentData);
//数据回调
    function getCommentData(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var commentHtml = "";
            var list = result.datas.items;
            if (list.length <= 0) {
                return;
            }
            for (var i = 0; i < 3; i++) {
                commentHtml += ' <li class="comment-view-cell">';
                commentHtml += '     <div class="clearfix comment-view-top">';
                commentHtml += '    <img class="fl headimg" src="' + list[i].member_avatar + '" onerror="javascript:this.src=\'images/img-head00.png\';"/>';
                commentHtml += '    <div class="fl cob3 name font11">' + list[i].member_name + '</div>';
                commentHtml += '    <a class="fr cob9 btn-comment font10" href="">';
                commentHtml += '     <img src="images/icon-evaluate1.png"/>';
                commentHtml += '     <span class="cogreen">' + list[i].comment_quote + '</span>';
                commentHtml += '     </a>';
                commentHtml += '    <a class="fr cob9 font10 click_assist" data-id="' + list[i].comment_id + '">';
                commentHtml += '    <img src="images/icon-zan2.png"/>';
                commentHtml += '     <span class="cogreen" id="comment_assist' + list[i].comment_id + '">' + list[i].comment_up + '</span>';
                commentHtml += '    </a>';
                commentHtml += '    </div>';
                commentHtml += '    <p class="cob6 comment-text block font12">' + list[i].comment_message + '</p>';
                var comment_quotes = list[i].comment_quotes;
                if (comment_quotes.length > 0) {
                    if(comment_quotes.length > 3){
                        comment_quotes.length = 3;
                    }
                    commentHtml += ' <dl class="comment-text-other cob6 line font12">';
                    for (var j = 0; j < comment_quotes.length; j++) {
                        commentHtml += '    <dd><span class="cogreen">' + comment_quotes[j].member_name + '：</span>' + comment_quotes[j].comment_message + '</dd>';
                    }
                    if (comment_quotes.length > 3) {
                        commentHtml += ' <dt><a class="cogreen">共' + comment_quotes.length + '条回复&gt;</a></dt>';
                    }
                    commentHtml += '     </dl>';
                }
                commentHtml += '</li>';
            }
            $("#commentContent").html(commentHtml);
        } else {
            console.log('error');
        }
    }

//点赞
    $("#click_assist").click(function () {
        var clickAssistUrl = getSign('member_article.up') + '&key='+key+'&client='+client;
        var json = {id: 1};
        getData(clickAssistUrl, json, clickAssist);
    })
//文章点赞数据回调
    function clickAssist(data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var article_up = $("#numbers").html();
            article_up = parseInt(article_up) + 1;
            $("#numbers").html(article_up);
        } else {
            alert(result.datas.error);
        }
    }

//回复点赞
    $(document).on('click', '.click_assist', function () {
        var comment_id = $(this).attr('data-id');
        var clickAssistUrl = getSign('member_article.comment_up') + '&key='+key+'&client='+client;
        var json = {id: comment_id};
        getDataValue(clickAssistUrl, json, clickCommentAssist, comment_id);
    })
//评论点赞的回调
    function clickCommentAssist(data, typeValue) {
        var result = eval('(' + data + ')');
        console.log(data);
        if (result.code == '200') {
            console.log(typeValue);
            var article_up = $("#comment_assist" + typeValue).html();
            article_up = parseInt(article_up) + 1;
            $("#comment_assist" + typeValue).html(article_up);
        } else {
            alert(result.datas.error);
        }
    }
    //回复评论
    $("#comment_send").click(function () {
        //type:1,回复文章;2:回复评论
        window.location.href = "comment_send.html?type=1&id="+id;
    })
    //更多评论
    $("#more_comment").click(function () {
        window.location.href = "comment_list.html?id="+id;
    })
})