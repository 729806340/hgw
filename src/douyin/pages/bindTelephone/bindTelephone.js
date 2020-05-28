var request = require('../../utils/request.js');
var router = getApp().router;
Page({
  data: {
    phone: '', //手机号
    code: '', //验证码
    iscode: null, //用于存放验证码接口里获取到的code
    codename: '获取验证码',
    disabled: true,
    second:0,
    sec :false,
  },
  onLoad: function(option) {
    //
  },
  getVerificationCode: function() {
    this.getCode();
  },
  getCodeValue: function(e) {
    this.setData({
      code: e.detail.value
    })
  },
  getPhoneValue: function(e) {
    var _this = this;
    var myreg = /^1[3-9]\d{9}$/;
    _this.setData({
      phone: e.detail.value,
    });
    if (!this.data.phone == '' && myreg.test(this.data.phone)) {
      _this.setData({
        disabled: false
      })
    } else {
      _this.setData({
        disabled: true
      })
    }
  },
  getCode: function() {
      var that = this;
      var myreg = /^1[3-9]\d{9}$/;
      if (this.data.phone == '') {
        wx.showToast({
          title: '手机号不能为空！',
          icon: 'none',
          duration: 1000
        })
        return false;
      }
      if (!myreg.test(this.data.phone)) {
        wx.showToast({
          title: '请输入正确的手机号！',
          icon: 'none',
          duration: 1000
        })
        return false;
      }
    if (this.data.disabled ==true){
      return false;
    }
      else {
        this.setData({
          disabled : true
        })
        request.postUrl('connect_weixin.get_sms_captcha', {
          phone: that.data.phone
        }, function (res) {
          if (!res.data.code) {
            wx.showToast({
              title: '获取验证码失败!'
            });
            return;
          }
          if (res.data.code != 200) {
            wx.showToast({
              title: res.data.datas.error
            });
            return;
          }

          var num = 30;
          var timer = setInterval(function () {
            num--;
            if (num <= 0) {
              clearInterval(timer);
              that.setData({
                codename: '重新发送',
                disabled: false,
                sec: false,
              })
            } else {
              that.setData({
                codename: '后重新发送',
                disabled: true,
                second: num,
                sec: true,
              })
            }
          }, 1000)
        })
      }
  },
  // 立即注册 提交
  register: function() {
    var union_id = wx.getStorageSync("union_id");
    var open_id = wx.getStorageSync("open_id");
    var user_token = wx.getStorageSync("user_token");
    var temp_goods = wx.getStorageSync('temp_goods');
    var temp = [];
    for (var lp of temp_goods) {
      temp.push(lp.goods_id)
    }
    if (!union_id || !open_id || user_token) {
      wx.switchTab({
        url: '../me/me'
      })
      return;
    }

    var that = this;
    var myreg = /^1[3-9]\d{9}$/;

    if (this.data.phone == '') {
      wx.showToast({
        title: '手机号不能为空！',
        icon: 'none',
        duration: 1000
      })
      return false;
    }
    if (!myreg.test(this.data.phone)) {
      wx.showToast({
        title: '请输入正确的手机号！',
        icon: 'none',
        duration: 1000
      })
      return false;
    }
    if (this.data.code == '') {
      wx.showToast({
        title: '请输入获取验证码！',
        icon: 'none'
      })
      return false
    }
    let dealer_id = ''
    if (wx.getStorageSync('dealer_id')) {
      dealer_id = wx.getStorageSync('dealer_id')
    }

    request.postUrl('connect_weixin.sms_register', {
      phone: that.data.phone,
      captcha: that.data.code,
      union_id: union_id,
      open_id: open_id,
      member_avatar: wx.getStorageSync('user_img'),
      goods_id: JSON.stringify(temp),
      dealer_id: dealer_id
    }, function(res) {
      if (!res.data.code) {
        wx.showToast({
          title: '绑定手机失败!'
        });
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      wx.setStorageSync("user_token", res.data.datas.user_token);
       wx.setStorageSync("temp_goods", "");
      wx.showToast({
        title: '绑定成功'
      })
      wx.navigateBack()  //回退到上页面
    })
  }
})