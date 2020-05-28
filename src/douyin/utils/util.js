var app = getApp();
const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()
  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}
const format = date => {
  const dates = new Date(date);
  const year = dates.getFullYear()
  const month = dates.getMonth() + 1
  const day = dates.getDate()
  return [year, month, day].map(formatNumber).join('/');
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}
//获取最近7天/30天
function getDay(day) {    
  var today = new Date();    
  var targetday_milliseconds = today.getTime() + 1000 * 60 * 60 * 24 * day;    
  today.setTime(targetday_milliseconds); //注意，这行是关键代码
      
  var tYear = today.getFullYear();    
  var tMonth = today.getMonth();    
  var tDate = today.getDate();    
  tMonth = doHandleMonth(tMonth + 1);    
  tDate = doHandleMonth(tDate);    
  return tYear + "-" + tMonth + "-" + tDate;
}

function doHandleMonth(month) {    
  var m = month;    
  if (month.toString().length == 1) {     
    m = "0" + month;    
  }    
  return m;
}

function getheader() {
  var header = {
    'content-type': 'application/x-www-form-urlencoded',
    'version': '1.0',
  };
  return header;
};
// 客服：
function getheaderCli() {
  var headerCli = {
    'content-type': 'text/xml',
    'version': '1.0',
  };
  return headerCli;
};

function postUrlCli(url, data, callback) {
  data.api_key = 'c1dca569396ba260fe6a7d552b6b7d74';
  data.user_token = wx.getStorageSync('token');
  url = getApp().clientUrl + url;
  console.log("请求的接口: " + url, data);
  wx.request({
    url: url,
    method: "POST",
    data: data,
    header: getheaderCli(),
    success: function(res) {
      console.log("返回的结果: " + url, res.data);
      callback(res);
    },
    fail: function(res) {
      /*console.log(res);*/
      callback(null);
    }
  })
}



function postUrl(url, data, callback) {
  // console.log("aaaaaa", url);
  // if (url != "cart/cart_add" && url != "cart/cart_count" && url != "cart/cart_count" && url != "cart/cart_add" && url != "cart/get_list_v2" && url !="cart/cart_remove") {
  //   wx.showLoading({
  //     title: '请求中',
  //     mask: true,
  //   })
  // }

  data.api_key = 'c1dca569396ba260fe6a7d552b6b7d74';
  data.user_token = wx.getStorageSync('token');
  data.verison = '1.1.4';
  url = getApp().clientUrl + url;
  // console.log("请求的接口: " + url, data);
  wx.request({
    url: url,
    method: "POST",
    data: data,
    header: getheader(),
    success: function(res) {
      // wx.hideLoading();
      // console.log("返回的结果: " + url, res.data);
      callback(res);
    },
    fail: function(res) {
      // wx.hideLoading();
      /*console.log(res);*/
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
      /* console.log("返回的结果: " + url, res.data);*/
      callback(res.data);
    },
    fail: function(res) {
      callback(null);
    }
  })
}
// 生成指定长度的字符串
function randomStr(a) {
  var d,
    e,
    b = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
    c = "";
  for (d = 0; a > d; d += 1)
    e = Math.random() * b.length, e = Math.floor(e), c += b.charAt(e);
  return c
}

function getTime(time) {
  var hour = parseInt(time / 3600);
  if (hour < 10) {
    hour = '0' + hour;
  }
  var fen = parseInt((time - hour * 3600) / 60);
  if (fen < 10) {
    fen = '0' + fen;
  }
  var second = time - hour * 3600 - fen * 60;
  if (second < 10) {
    second = '0' + second;
  }
  var aa = [];
  aa.push(hour);
  aa.push(fen);
  aa.push(second);
  // console.log(aa);
  return aa;
}
const wrapText = ({
  ctx,
  text,
  x,
  y,
  w,
  fontStyle: {
    lineHeight = 60,
    textAlign = 'left',
    textBaseline = 'top',
    font = 'normal 40px arial',
    fillStyle = '#000'
  }
}) => {
  ctx.save();
  ctx.font = font;
  ctx.fillStyle = fillStyle;
  ctx.textAlign = textAlign;
  ctx.textBaseline = textBaseline;
  const chr = text.split('');
  const row = [];
  let temp = '';

  for (let a = 0; a < chr.length; a++) {
    if (ctx.measureText(temp).width < w) { } else {
      if (/[，。！》]/im.test(chr[a])) {
        temp += ` ${chr[a]} `;
        a++;
      }

      if (/[《]/im.test(chr[a - 1])) {
        temp = temp.substr(0, temp.length - 1);
        a--;
      }

      row.push(temp);
      temp = '';
    }
    temp += chr[a] ? chr[a] : '';
  }
  row.push(temp);
  for (let b = 0; b < row.length; b++) {
    ctx.fillText(row[b], x, y + b * lineHeight)

  }

  ctx.restore();
  return y + (row.length - 1) * lineHeight
}


module.exports = {
  getUrl: getUrl,
  postUrl: postUrl,
  randomStr: randomStr,
  postUrlCli: postUrlCli,
  getTime: getTime,
  formatTime: formatTime,
  getDay: getDay,
  format: format,
  wrapText: wrapText
}