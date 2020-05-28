var request = require('../../utils/request.js');
var Mcaptcha = require('../../utils/mcaptcha.js');
Page({
  data: {
    code: '', //输入的验证码
    num: '', // 获取的验证码
    card: '', // 红包卡号
    disabled: true,
  },
  onLoad: function(option) {

  },
  //红包卡密号
  getCardValue: function(e) {
    var that = this;
    var cardNum = e.detail.value.replace(/\s+/g, "");
    this.setData({
      card: cardNum
    })
    if (that.data.card !== '') {
      that.setData({
        disabled: false
      })
    } else {
      that.setData({
        disabled: true
      })
    }
  },
  //验证码
  getCodeValue: function(e) {
    var that = this;
    var textNum = e.detail.value.replace(/\s+/g, "");
    this.setData({
      code: textNum
    })
    if (that.data.code !== '') {
      that.setData({
        disabled: false
      })
    } else {
      that.setData({
        disabled: true
      })
    }
  
  },
  // 确认
  confirm: function() {
    var that = this;
    if (that.data.code.toLowerCase() == that.data.num.toLowerCase()) {
      var cardNum = that.data.card;
      request.postUrl("member_redpacket.rp_pwex", {
        pwd_code: cardNum
      }, function(res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '领取成功',
            icon: 'succes',
            duration: 1000,
            mask: true
          })
          that.setData({
            code: '',
            card: '',
            disabled: true
          })
          wx.redirectTo({
            url: '../redPocket/redPocket',
          })
        }else if(res.data.code == '400'){
          var error = res.data.datas.error
          wx.showToast({
            title: error,
            icon: 'none',
            duration: 1500,
            mask: true
          })
          that.setData({
            card: '',
            disabled: true
          })
        }

      })
    } else {
      wx.showToast({
        title: '验证码错误,请重新输入',
        icon: 'none',
        duration: 1500,
        mask: true
      })
      that.setData({
        disabled: true
      })
    }

  },
  onReady: function() {
    var that = this;
    var num = that.getRanNum();
    this.setData({
      num: num
    })
    new Mcaptcha({
      el: 'canvas',
      width: 80, //对图形的宽高进行控制      
      height: 30,
      code: num
    });
  },
  getRanNum: function() {
    var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
    var pwd = '';
    for (var i = 0; i < 4; i++) {
      if (Math.random() < 48) {
        pwd += chars.charAt(Math.random() * 48 - 1);
      }
    }
    return pwd;
  }
})