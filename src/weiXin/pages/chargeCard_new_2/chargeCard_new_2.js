var request = require('../../utils/request.js');
Page({
  data: {
    available_rc_balance: 0,
    show_charge:2, // 1开  2关
    title:'',
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
    this.showInfo()
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
              title: '充值卡'
            })
          }
        }
      })
    },

})