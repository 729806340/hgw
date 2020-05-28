var request = require('../../utils/request.js');

Page({
  data: {
    member_points: 0,
    available_predeposit: 0,
    available_rc_balance: 0,
    rpt_num: 0,
    voucher_num: 0
  },

  onLoad: function() {
      if (!wx.getStorageSync("user_token")) {
          wx.switchTab({
              url: '../me/me'
          });
      }
    var that = this;
    request.postUrl("member_index.index", {}, function(res) {
      if (res.data.code == 200) {
        that.setData({
          member_points: res.data.datas.member_info.point,
          available_predeposit: res.data.datas.member_info.predepoit,
          available_rc_balance: res.data.datas.member_info.available_rc_balance,
          rpt_num: res.data.datas.member_info.rpt_num,
          voucher_num: res.data.datas.member_info.voucher_num
        })
      }
    })
  },
  //预存款
  preSave: function() {
    wx.navigateTo({
      url: '../preSave/preSave'
    });
  },
  //充值卡余额
  chargeCard: function() {
    wx.navigateTo({
      url: '../chargeCard/chargeCard'
    });
  },
  //代金券
  cashCoupon: function() {
    wx.navigateTo({
      url: '../cashCoupon/cashCoupon'
    });

  },
  //红包
  redPocket: function() {
    wx.navigateTo({
      url: '../redPocket/redPocket'
    });
  },
  //积分
  integration: function() {
    wx.navigateTo({
      url: '../integration/integration'
    });
  }

})