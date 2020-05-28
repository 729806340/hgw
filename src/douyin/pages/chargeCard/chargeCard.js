Page({
  data: {
    available_rc_balance: 0
  },

  onLoad: function(option) {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    if (option.available_rc_balance) {
      this.setData({
        available_rc_balance: option.available_rc_balance
      })
    }
  },
  //立即充值
  chargeNow: function() {
    wx.navigateTo({
      url: '../chargeNow/chargeNow'
    });
  },
  //交易明细
  cardDetail: function() {
    wx.navigateTo({
      url: '../cardDetail/cardDetail'
    });
  }

})