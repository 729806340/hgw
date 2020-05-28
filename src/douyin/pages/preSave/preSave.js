var request = require('../../utils/request.js');

Page({
  data: {
    available_predeposit: 0,
    chosen: 1,//选中状态 1余额 2充值明细
    rechargeList: [], //充值明细列表
    logList: [], //余额变更列表
    recharge_cur_page: 1,
    recharge_has_more: 0,
    log_cur_page: 1,
    log_has_more: 0
  },

  onLoad: function(option) {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
  },
  onShow: function (e) {
      this.getLogList();
  },
  //余额明显
  getLogList:function () {
      var that = this;
      request.postUrl("member_recharge.log_list", {
          curpage: that.data.log_cur_page
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
          if (that.data.log_cur_page === 1) {
              that.setData({
                  logList: res.data.datas.log_list,
                  log_has_more: res.data.hasmore,
                  available_predeposit: res.data.datas.predepoit
              })
          } else {
              that.setData({
                  logList: that.data.logList.concat(res.data.datas.log_list),
                  log_has_more: res.data.hasmore,
                  available_predeposit: res.data.datas.predepoit
              })
          }
      })
  },
  //刷新
  refreshLog: function() {
    this.setData({
      log_cur_page: 1
    });
    this.getLogList();
  },
  //获取更多
  getLogMore: function() {
    var that = this;
    if (that.data.log_has_more > 0) {
      that.setData({
        log_cur_page: that.data.log_cur_page + 1
      });
      that.getLogList();
    }
  },
  //充值明细
  getRechargeList: function () {
      var that = this;
      request.postUrl("member_recharge.recharge_list", {
          curpage: that.data.recharge_cur_page
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
          if (that.data.recharge_cur_page === 1) {
              that.setData({
                  rechargeList: res.data.datas.recharge_list,
                  recharge_has_more: res.data.hasmore
              })
          } else {
              that.setData({
                  rechargeList: that.data.rechargeList.concat(res.data.datas.recharge_list),
                  recharge_has_more: res.data.hasmore
              })
          }
      })
  },
  //刷新
  refreshRecharge: function() {
    this.setData({
        recharge_cur_page: 1
    });
    this.getRechargeList();
  },
  //获取更多
  getRechargeMore: function() {
    var that = this;
    if (that.data.recharge_has_more > 0) {
      that.setData({
        recharge_cur_page: that.data.recharge_cur_page + 1
      });
      that.getRechargeList();
    }
  },
  //切换选中
  chosen: function(e) {
    if (e.target.dataset.name == 1) {
       this.setData({
        chosen: 1
      })
    }
  },
  //切换选中
  chosenDetail: function(e) {
    if (this.data.rechargeList.length == 0) {
      this.getRechargeList();
    }
    if (e.target.dataset.name == 2) {
      this.setData({
        chosen: 2
      })
    }
  },
  //充值
  chargeBtn: function() {
    wx.navigateTo({
      url: '../chargeBtn/chargeBtn'
    });
  }
})