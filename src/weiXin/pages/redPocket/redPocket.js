var request = require('../../utils/request.js');
Page({
  data: {
    tabs: ["未使用", "已使用", "已过期"],
    zeroPocket: false, //没有红包
    rpList: [],
    cur_page: 1,
    has_more: 0,
    rp_state_select:1, //未使用红包状态
    currentTab:0,//
  },
  onLoad: function() {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
    }
    this.getRedpacketList();
  },
  //红包列表
  getRedpacketList: function() {
    var that = this;
    request.postUrl("member_redpacket.redpacket_list", {
      curpage: that.data.cur_page,
      rp_state_select: that.data.rp_state_select
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
      if (res.data.datas.redpacket_list.length > 0) {
        that.setData({
          zeroPocket: true
        })
      }

      if (that.data.cur_page === 1) {
        that.setData({
          rpList: res.data.datas.redpacket_list,
          has_more: res.data.hasmore
        })
      } else {
        that.setData({
          rpList: that.data.rpList.concat(res.data.datas.redpacket_list),
          has_more: res.data.hasmore
        })
      }
    })
  },
  bindChange(e){
    var that = this;
    var current = e.detail.current;
    that.setData({
      currentTab: e.detail.current
    });
    if (current == 0) {
      that.setData({
        rp_state_select: 1, //未使用
      })
    }
    if (current == 1) {
      that.setData({
        rp_state_select: 2, //已使用
      })
    }
    if (current == 2) {
      that.setData({
        rp_state_select: 3,   //已过期
      })
    }
    this.setData({
      cur_page: 1,
    })
    that.getRedpacketList();
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
  
  //刷新
  refresh: function() {
    this.setData({
      cur_page: 1
    });
    this.getRedpacketList();
  },
  //获取更多
  getMore: function() {
    var that = this;
    console.log(that.data.has_more)
    if (that.data.has_more > 0) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getRedpacketList();
    }
  },
  //领取红包
  getPocket: function() {
    wx.navigateTo({
      url: '../getPocket/getPocket'
    })
  }
})