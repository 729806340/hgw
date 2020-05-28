var request = require('../../utils/request.js');

Page({
  data: {
    has_log: false,
    card_list: [],
    cur_page: 1,
    has_more: 0,
    redPlus: false,
  },
  onLoad: function() {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    this.getCardList();
  },
  //交易明细列表
  getCardList: function() {
    var that = this;
    request.postUrl("member_recharge.card_list", {
      curpage: that.data.cur_page
    }, function(res) {
      var cardList = res.data.datas.card_list
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      if (cardList.length > 0) {
        that.setData({
          has_log: true
        })
      }

      if (that.data.cur_page === 1) {
        that.setData({
          card_list: res.data.datas.card_list,
          has_more: res.data.hasmore
        })
      } else {
        that.setData({
          card_list: that.data.card_list.concat(res.data.datas.card_list),
          has_more: res.data.hasmore
        })
      }
    })
  },
  //刷新
  refresh: function() {
    this.setData({
      cur_page: 1
    });
    this.getCardList();
  },
  //获取更多
  getMore: function() {
    var that = this;
    if (that.data.has_more > 0) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getCardList();
    }
  },

})