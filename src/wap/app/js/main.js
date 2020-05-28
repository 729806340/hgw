/**
 * Created by Administrator on 2017/2/6 0006.
 * main.js
 */
//h5配置
// var httphost = 'http://www3.hangowa.com/appApi/';
var httphost = 'http://app.hangowa.com/appApi/';
var apikey = 'testhgwapi';
var secrect = 'c1dca569396ba260fe6a7d552b6b7d75';
// var key = '79acc05de8f232914a5df2b5288074ee';
var key = _getUrlString('key');
// alert(key);
var client = _getUrlString('client');
// var client = 'ios';
//专题配置
// var _host = 'www3.hangowa.com';
var _host = 'app.hangowa.com';
var SiteUrl = "http://" + _host + "/shop";
var ApiUrl = "http://" + _host + "/mo_bile";
var pagesize = 10;
var WapSiteUrl = "http://" + _host + "/wap";
var buildAppSite = 'www.fuhao.com';
var IOSSiteUrl = "https://itunes.apple.com/us/app/shopnc-b2b2c/id879996267?l=zh&ls=1&mt=8";
var AndroidSiteUrl = "http://" + _host + "/download/app/AndroidShopNCMoblie.apk";
var WeiXinOauth = false;
var downloadAndroid = 'http://www.hangowa.com/wap/app/apk/nongguxian0001.apk';

//时间戳
var timestamp = Date.parse(new Date()) / 1000;

/*
 * 获取签名
 */
function getSign(method) {
    var charSign = secrect + 'apikey' + apikey + 'method' + method + 'timestamp' + timestamp + secrect;
    //获取md5加密
    var md5Sign = $.md5(charSign);
    //将小写转化为大写
    var sign = md5Sign.toUpperCase();
    //拼接url
    var url = httphost + '?method=' + method + '&apikey=' + apikey + '&sign=' + sign + '&timeStamp=' + timestamp;
    return url;
}
//请求公共方法
function getData(posturl,json,getPost){
    $.post(posturl,json, function (data) {
        getPost(data);
    });
}
//带参数的请求公共方法
function getDataValue(posturl,json,getPost,typeValue){
    $.post(posturl,json, function (data) {
        getPost(data,typeValue);
    });
}
//格式化时间戳
function userDate(uData){
    var myDate = new Date(uData*1000);
    var year = myDate.getFullYear();
    var month = myDate.getMonth() + 1;
    var day = myDate.getDate();
    return year + '-' + month + '-' + day;
}
//图片上传获取图片路径
function getfilefull(fileid) {
    /*File对象可以用来获取某个文件的信息,还可以用来读取这个文件的内容.
     通常情况下,File对象是来自用户在一个input元素上选择文件后返回的FileList对象
     */
    var f = document.getElementById(fileid).files[0];
    //创建一个新的对象URL,该对象URL可以代表某一个指定的File对象或Blob对象.
    var src = '';
    if(window.createObjectURL != undefined) {
        src = window.createObjectURL(f)
    } else if(window.URL != undefined) {
        src = window.URL.createObjectURL(f)
    } else if(window.webkitURL != undefined) {
        src = window.webkitURL.createObjectURL(f)
    }
    return src;
}
//获取url参数
function _getUrlString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if(r != null) return unescape(r[2]);
    return null;
}
//返回上一层
function _historyback(){
    history.go(-1);
    return;
}
//判断ios、Android
function is_client(){
    if(navigator.userAgent.match(/android/i)){
        return 1;
    }else if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)){
        //ios
        return 2;
    }
}
