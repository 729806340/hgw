$(function () {
    var page = 1;
    var postCommentUrl = getSign('article.comment') + '&key=' + key + '&client=' + client;
    var id = _getUrlString('id');
    //ios头部+20px
    var is_ios = is_client();
    if (is_ios == 2) {
        $("#aaa").addClass('addlineios');
        // $("#aaa").removeClass('bar-nav');
    }
    mui.init({
        pullRefresh: {
            container: '#pullrefresh',
            down: {
                callback: pulldownRefresh
            },
            up: {
                contentrefresh: '正在加载...',
                callback: pullupRefresh
            }
        }
    });
    //下拉刷新具体业务实现
    function pulldownRefresh() {
        setTimeout(function () {
            // count = 0;
            var json = {id: id, curpage: 1};
            getCommentData(json, 1);

            // mui('#pullrefresh').pullRefresh().scrollTo(0,0,100); //滚动置顶
            // mui('#pullrefresh').pullRefresh().refresh(true);
            mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
        }, 1500);
    }

    var count = 0;

    //上拉加载具体业务实现
    function pullupRefresh() {
        var json = {id: id, curpage: page};
        setTimeout(function () {
            getCommentData(json, 2);
            // mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
        }, 1500);
        page++;
    }

    if (mui.os.plus) {
        mui.plusReady(function () {
            setTimeout(function () {
                mui('#pullrefresh').pullRefresh().pullupLoading();
            }, 1000);
        });
    } else {
        mui.ready(function () {
            mui('#pullrefresh').pullRefresh().pullupLoading();
        });
    }
    function getCommentData(json, type) {
        $.post(postCommentUrl, json, function (data) {
            var result = eval('(' + data + ')');
            if (result.code == '200') {
                var commentHtml = "";
                var list = result.datas.items;
                if (list.length <= 0) {
                    // mui('#pullrefresh').pullRefresh().endPullupToRefresh(); //参数为true代表没有更多数据了。
                    return;
                }
                for (var i = 0; i < list.length; i++) {
                    commentHtml += '<li class="bgwhite paddinglr comment-li">';
                    commentHtml += '    <div class="clearfix comment-view-top">';
                    commentHtml += '        <img class="fl headimg" src="' + list[i].member_avatar + '" onerror="javascript:this.src=\'images/img-head00.png\';"/>';
                    commentHtml += '        <div class="fl cob3 name font11">' + list[i].member_name + '</div>';
                    commentHtml += '    </div>';
                    commentHtml += '    <p class="cob6 comment-text block font12">' + list[i].comment_message + '</p>';
                    commentHtml += '    <ul class="preview-list">';
                    var comment_images = list[i].comment_images;
                    if (comment_images.length > 0) {
                        for (var j = 0; j < comment_images.length; j++) {
                            if (comment_images[j]) {
                                commentHtml += '<li class="inb preview-img">';
                                commentHtml += '    <img src="' + comment_images[j] + '" data-preview-src="" data-preview-group="1"/>';
                                commentHtml += '</li>';
                            }
                        }
                    }
                    commentHtml += '    </ul>';
                    commentHtml += '    <div class="clearfix comment-view-bottom">';
                    var unix = list[i].comment_time;
                    var datetime = userDate(unix);
                    commentHtml += '        <div class="cob font10 fl comment-time">' + datetime + '</div>';
                    commentHtml += '        <a class="fr cob9 btn-comment font10 comment_send" href="comment_list_more.html?key=' + key + 'id=' + list[i].comment_id + '">';
                    commentHtml += '            <img src="images/icon-evaluate1.png"/>';
                    commentHtml += '            <span class="cogreen">' + list[i].comment_quote + '</span>';
                    commentHtml += '        </a>';
                    commentHtml += '        <a class="fr cob9 font10 click_assist" on-click="click_zan(' + list[i].comment_id + ')"  data-id="' + list[i].comment_id + '">';
                    commentHtml += '           <img src="images/icon-zan2.png"/>';
                    commentHtml += '           <span class="cogreen"  id="comment_assist' + list[i].comment_id + '">' + list[i].comment_up + '</span>';
                    commentHtml += '        </a>';
                    commentHtml += '    </div>';
                    commentHtml += ' </li>';
                }
                console.log(page);
                //type,1:下拉刷新；2:上拉加载
                if (type == 1) {
                    $("#commentList").html(commentHtml);
                    page = 2;
                    count = 0;
                    mui('#pullrefresh').pullRefresh().refresh(true);     //恢复滚动
                } else {
                    $("#commentList").append(commentHtml);
                    mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > result.datas.totalPage - 1)); //参数为true代表没有更多数据了。
                }
            } else {
                mui('#pullrefresh').pullRefresh().endPullupToRefresh(); //参数为true代表没有更多数据了。
            }
        });
    }

    $("#commentList").on('tap', 'a', '.click_assist', function (event) {
        this.click();
    });

    //回复点赞
    $(document).on('click', '.click_assist', function () {
        var comment_id = $(this).attr('data-id');
        var clickAssistUrl = getSign('member_article.comment_up') + '&key=' + key + '&client=' + client;
        var json = {id: comment_id};
        getDataValue(clickAssistUrl, json, clickCommentAssist, comment_id);
    })
    //评论点赞的回调
    function clickCommentAssist(data, typeValue) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            console.log(typeValue);
            var article_up = $("#comment_assist" + typeValue).html();
            article_up = parseInt(article_up) + 1;
            $("#comment_assist" + typeValue).html(article_up);
            // alert('点赞成功');
            layer.open({
                content: '点赞成功'
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
        } else {
            layer.open({
                content: result.datas.error
                , skin: 'msg'
                , time: 1 //2秒后自动关闭
            });
        }
    }

    //查看评论
    $("#commentList").on('tap', 'a', '.comment_send', function (event) {
        this.click();
    });
    $(document).on('click', '.comment_send', function () {
        var comment_send = $(this).attr('href');
        window.location.href = comment_send;
    });
    $("#urlback").click(function () {
        history.go(-1);
    })
})