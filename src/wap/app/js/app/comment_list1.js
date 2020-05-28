var page = 1;
var postCommentUrl = getSign('article.comment') + '&key='+key+'&client='+client;
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
/**
 * 下拉刷新具体业务实现
 */
function pulldownRefresh() {
    setTimeout(function () {
          var json = {id:1,page:1};
        getCommentData(json,1);
        mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
    }, 1500);
}
var count = 0;
/**
 * 上拉加载具体业务实现
 */
function pullupRefresh() {
    var json = {id:1,page:page};
    setTimeout(function () {
        mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > 2)); //参数为true代表没有更多数据了。
        getCommentData(json,2);
        mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
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
function getCommentData(json,type){
    $.post(postCommentUrl,json, function (data) {
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            var commentHtml = "";
            // var list = result.datas.items;
            // if (list.length <= 0) {
            //     return;
            // }
            for (var i = 0; i < 1; i++) {
                commentHtml += '<li class="bgwhite paddinglr comment-li">';
                commentHtml += '    <div class="clearfix comment-view-top">';
                commentHtml += '        <img class="fl headimg" src="images/img-head00.png"/>';
                commentHtml += '        <div class="fl cob3 name font11">汉购网小粉丝</div>';
                commentHtml += '    </div>';
                commentHtml += '    <p class="cob6 comment-text block font12">很好，很厉害的一排那我在 </p>';
                commentHtml += '    <ul class="preview-list">';
                commentHtml += '        <li class="inb">';
                commentHtml += '            <img src="images/img_pj00.png" data-preview-src="" data-preview-group="1"/>';
                commentHtml += '        </li>';
                commentHtml += '    </ul>';
                commentHtml += '    <div class="clearfix comment-view-bottom">';
                commentHtml += '        <div class="cob font10 fl comment-time">2016-10-22</div>';
                commentHtml += '        <a class="fr cob9 btn-comment font10" href="">';
                commentHtml += '            <img src="images/icon-evaluate1.png"/>';
                commentHtml += '            <span class="cogreen">550</span>';
                commentHtml += '        </a>';
                commentHtml += '        <a class="fr cob9 font10" href="">';
                commentHtml += '           <img src="images/icon-zan2.png"/>';
                commentHtml += '           <span class="cogreen">150</span>';
                commentHtml += '        </a>';
                commentHtml += '    </div>';
                commentHtml += ' </li>';
            }
            if(type == 1){
                $("#commentList").html(commentHtml);
            }else{
                $("#commentList").append(commentHtml);
            }
        } else {
            mui('#pullrefresh').pullRefresh().endPullupToRefresh(); //参数为true代表没有更多数据了。
        }
    });
}