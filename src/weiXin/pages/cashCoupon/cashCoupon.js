var request = require('../../utils/request.js');

Page({
  data: {
    tabs: ["未使用", "已使用", "已过期"],
    has_coupon: false,
    coupon_list: [],
    cur_page: 1,
    has_more: 0,
    voucher_state: 1, //未使用优惠券状态
    currentTab: 0,//
  },
  onLoad: function() {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
    }
    this.getVoucherList();
  },

  onShow: function() {
    //
  },
  refresh: function() {
    this.setData({
      cur_page: 1
    });
    this.getVoucherList();
  },
  getMore: function() {
    var that = this;
    if (that.data.has_more > 0) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getVoucherList();
    }
  },

  //优惠券列表
  getVoucherList: function() {
    var that = this;
    request.postUrl('member_voucher.voucher_list', {
      curpage: that.data.cur_page,
      voucher_state: that.data.voucher_state
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
      // if (res.data.datas.voucher_list.length==0) {
      //   that.setData({
      //     has_coupon: true

      //   })
      // }

      if (that.data.cur_page === 1) {
        that.setData({
          coupon_list: res.data.datas.voucher_list,
          has_more: res.data.hasmore
        })
      } else {
        that.setData({
          coupon_list: that.data.coupon_list.concat(res.data.datas.voucher_list),
          has_more: res.data.hasmore
        })
      }
    })
  },
  bindChange(e) {
    var that = this;
    var current = e.detail.current;
    that.setData({
      currentTab: e.detail.current
    });
    if (current == 0) {
      that.setData({
        voucher_state: 1, //未使用
      })
    }
    if (current == 1) {
      that.setData({
        voucher_state: 2, //已使用
      })
    }
    if (current == 2) {
      that.setData({
        voucher_state: 3,   //已过期
      })
    }
    this.setData({
      cur_page: 1,
    })
    that.getVoucherList();
  },
  /** 
     * 点击tab切换 
     */
  swichNav: function (e) {
    var that = this;
    if (this.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
      var scrollLeftNumber = 0;
      var current = e.currentTarget.dataset.current;
      if (current > 5) {
        scrollLeftNumber = 5 * 130;
        scrollLeftNumber = scrollLeftNumber + 130 * (current - 5);
      }
      that.setData({
        currentTab: e.target.dataset.current,
        scrollLeftNumber: scrollLeftNumber
      })
    }
  },
  //使用
  goUse: function(e) {
    console.log(e)
    var store_id = e.currentTarget.dataset.id;
    if (parseInt(store_id) <= 0) {
      return;
    }
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + store_id
    })
  },
  //领取优惠券
  getCoupon: function() {
    wx.navigateTo({
      url: '../getCoupon/getCoupon'
    });
  }
})