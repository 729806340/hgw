var md5 = require('./md5.js');
var common = require('./common.js');


function postUrl(method, data, callback) {
    data.key = wx.getStorageSync("user_token");
    wx.request({
        url: getTrueUrl(method),
        method: "POST",
        data: data,
        header: setHeader(),
        success: function(res) {
            callback(res);
        },
        fail: function(res) {
            callback(null);
        }
    })
}

function getUrl(url, data, callback) {
    wx.request({
        url: url,
        data: data,
        method: "GET",
        header: getheader(),
        success: function(res) {
            callback(res.data);
        },
        fail: function(res) {
            callback(null);
        }
    })
}

//拼接url
function getTrueUrl(method) {
    var client_url = getApp().client_url;
    var api_key    = getApp().api_key;
    var api_secret    = getApp().api_secret;
    var timestamp  = common.getTimestamp();
    var sign = getSign(api_secret, method, timestamp, api_key);
    return client_url + '?method=' + method + '&timestamp=' + timestamp + '&apikey=' + api_key + '&sign=' + sign;
}

//生成sign
function getSign(api_secret, method, timestamp, apikey) {
    return md5.hex_md5(api_secret + method + timestamp + apikey + api_secret)
}

//设置header
function setHeader() {
    return {
        'content-type': 'application/x-www-form-urlencoded',
        'version': '1.0'
    };
};

module.exports = {
    getUrl: getUrl,
    postUrl: postUrl,
    getTrueUrl: getTrueUrl,
}

