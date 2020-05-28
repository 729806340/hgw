/**
 * Created by Administrator on 2017/2/8 0008.
 */
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

    // setTimeout(function () {
        getData(postCommentUrl, {id: 1, page: page}, getCommentData);
        // for (var i = 0, len = i + 3; i < len; i++) {
        //     var li = document.createElement('li');
        //     li.className = 'mui-table-view-cell';
        //     li.innerHTML = '<li class="bgwhite paddinglr comment-li"> <div class="clearfix comment-view-top"> <img class="fl headimg" src="images/img-head00.png"/> <div class="fl cob3 name font11">汉购网小粉丝</div> </div> <p class="cob6 comment-text block font12"> 我觉得这个苹果蛮好吃，脆甜可口，值得大家去购买。我觉得这个苹果蛮好吃，脆甜可口，值得大家去购买。 </p> <ul class="preview-list"> <li class="inb preview-img"> <img src="images/img_pj00.png" data-preview-src="" data-preview-group="1"/> </li> <li class="inb preview-img"> <img src="images/img_pj01.png" data-preview-src="" data-preview-group="1"/> </li> <li class="inb preview-img"> <img src="images/img_pj02.png" data-preview-src="" data-preview-group="1"/> </li> </ul> <div class="clearfix comment-view-bottom"> <div class="cob font10 fl comment-time">2016-10-22</div> <a class="fr cob9 btn-comment font10" href=""> <img src="images/icon-evaluate1.png"/> <span class="cogreen">550</span> </a> <a class="fr cob9 font10" href=""> <img src="images/icon-zan2.png"/> <span class="cogreen">150</span> </a> </div> </li>';
        //     //下拉刷新，新纪录插到最前面；
        //     table.insertBefore(li, table.firstChild);
        // }
        // mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
    // }, 1500);
}
var count = 0;
/**
 * 上拉加载具体业务实现
 */
function pullupRefresh() {
    setTimeout(function () {
        getData(postCommentUrl, {id: 1, page: page}, getCommentData);
        // mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > 2)); //参数为true代表没有更多数据了。
        // var table = document.body.querySelector('.mui-table-view');
        // var cells = document.body.querySelectorAll('.mui-table-view-cell');
        // for (var i = cells.length, len = i + 3; i < len; i++) {
        //     var li = document.createElement('li');
        //     li.className = 'mui-table-view-cell';
        //     li.innerHTML = '<li class="bgwhite paddinglr comment-li"> <div class="clearfix comment-view-top"> <img class="fl headimg" src="images/img-head00.png"/> <div class="fl cob3 name font11">汉购网小粉丝</div> </div> <p class="cob6 comment-text block font12"> 我觉得这个苹果蛮好吃，脆甜可口，值得大家去购买。我觉得这个苹果蛮好吃，脆甜可口，值得大家去购买。 </p> <ul class="preview-list"> <li class="inb preview-img"> <img src="images/img_pj00.png" data-preview-src="" data-preview-group="1"/> </li> <li class="inb preview-img"> <img src="images/img_pj01.png" data-preview-src="" data-preview-group="1"/> </li> <li class="inb preview-img"> <img src="images/img_pj02.png" data-preview-src="" data-preview-group="1"/> </li> </ul> <div class="clearfix comment-view-bottom"> <div class="cob font10 fl comment-time">2016-10-22</div> <a class="fr cob9 btn-comment font10" href=""> <img src="images/icon-evaluate1.png"/> <span class="cogreen">550</span> </a> <a class="fr cob9 font10" href=""> <img src="images/icon-zan2.png"/> <span class="cogreen">150</span> </a> </div> </li>';
        //     table.appendChild(li);
        // }
    }, 1500);
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

//数据回调
function getCommentData(data) {
    var table = document.body.querySelector('.mui-table-view');
    var cells = document.body.querySelectorAll('.mui-table-view-cell');
    // var table = document.body.querySelector('.mui-table-view');
    // var cells = document.body.querySelectorAll('.mui-table-view-cell');
    var result = eval('(' + data + ')');
    if (result.code == '200') {
        var commentHtml = "";
        var list = result.datas.items;
        if (list.length <= 0) {
            return;
        }
        for (var i = 0; i < 1; i++) {
            commentHtml += '<li class="bgwhite paddinglr comment-li">';
            commentHtml += '    <div class="clearfix comment-view-top">';
            commentHtml += '        <img class="fl headimg" src="images/img-head00.png"/>';
            commentHtml += '        <div class="fl cob3 name font11">汉购网小粉丝</div>';
            commentHtml += '    </div>';
            commentHtml += '    <p class="cob6 comment-text block font12">很好，很厉害的一排那我在 </p>';
            commentHtml += '    <ul class="preview-list">';
            commentHtml += '        <li class="inb preview-img">';
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
        $("#commentList").append(commentHtml);
    } else {
        mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > 2)); //参数为true代表没有更多数据了。
    }
}

