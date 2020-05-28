var request = require('../../utils/request.js');

Page({
  data: {
    chosen: 100,
    pdr_sn: '',
    money: 100,
    show_charge:2, //1开 2关
  },

  onLoad: function () {
      if (!wx.getStorageSync("user_token") || !wx.getStorageSync("open_id")) {
          wx.switchTab({
              url: '../me/me'
          });
      }
  },
   //展示用户数据
   showInfo: function() {
        var that = this;
        request.postUrl("member_index.index", {
            member_avatar: wx.getStorageSync("user_img")
        }, function(res) {
            if (res.data.code == 200) {
            if(res.data.datas.show_charge){
                that.setData({
                show_charge:res.data.datas.show_charge
                })
            }
            if(res.data.datas.show_charge == 1){
                wx.setNavigationBarTitle({
                title: '预存款充值'
                })
            }
            }
        })
    },
  itemClick: function(e){
    var that = this;
    var chosen = parseInt(e.target.dataset.value);
    var arr = [1,10,20,30,50,100];
    if (chosen <= 0 || arr.indexOf(chosen) < 0) {
      return;
    }
    that.setData({
      chosen: chosen,
      money:chosen
    });
    // request.postUrl('member_recharge.get_sn', {
    //     amount: chosen
    // }, function(res) {
    //     if (!res.data.code) {
    //         return;
    //     }
    //     if (res.data.code != 200) {
    //         wx.showToast({
    //             title: res.data.datas.error
    //         });
    //         return;
    //     }
    //     that.setData({
    //         pdr_sn: res.data.datas.pdr_sn
    //     })
    //     that.pay();
    // })
  },
  getMoney: function (e) {
      var val = e.detail.value;
      this.setData({
          money: val
      });
  },
  chargeNow: function (e) {
      var that = this;
      var money = that.data.money;
      var reg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/;
      if (!reg.test(money) || money <= 0.01) {
          wx.showToast({
            title: '您输入充值金额不合法',
            icon:'none'
          })
          return;
      }
      request.postUrl('member_recharge.get_sn', {
          amount: money
      }, function(res) {
          if (!res.data.code) {
              return;
          }
          if (res.data.code != 200) {
              wx.showToast({
                  title: res.data.datas.error
              });
              return;
          }
          that.setData({
              pdr_sn: res.data.datas.pdr_sn
          })
          that.pay();
      })
  },
  pay: function () {
    var that = this;
    var pdr_sn = that.data.pdr_sn;
    console.log(pdr_sn);
    if (!pdr_sn) {
      return;
    }
    request.postUrl('member_payment.pd_order', {
        pdr_sn: pdr_sn, payment_code: 'wxpay_jsapi', open_id: wx.getStorageSync("open_id")
    }, function(res) {
        if (!res.data.code) {
            return;
        }
        console.log("buy/prepay_id", res);
        if (res.data.code != 200) {
            wx.showToast({
                title: res.data.datas.error,
                icon: 'none'
            });
            return;
        }
        wx.requestPayment({
            'timeStamp': res.data.datas.pay_info.timeStamp + "",
            'nonceStr': res.data.datas.pay_info.nonceStr,
            'package': res.data.datas.pay_info.package,
            'signType': 'MD5',
            'paySign': res.data.datas.pay_info.paySign,
            'success': function(res1) {
                wx.navigateTo({
                    url: '../preSave_new/preSave_new'
                });
                return;
            },
            'fail': function(res) {
                wx.showToast({
                    title: '支付失败',
                    icon: 'none'
                });
                return;
            }
        })
    })
  }

})