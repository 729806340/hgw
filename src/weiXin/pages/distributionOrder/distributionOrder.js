var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    state_type: 'state_new',
    page: 1,
    is_bottom: false,
    records: null,
    able_amount: 0,
    total_amount: 0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      page: 1
    })

    this.getRecordData()
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },

  getRecordData() {
    let that = this;
    request.postUrl('pyramid_selling.sell_orders', { 
                    state_type: that.data.state_type, 
                    curpage: that.data.page 
                    }, 
                    function(res) {
                      if (!res.data.code) {
                        return;
                      }
                      if (res.data.code != 200) {
                        wx.showToast({
                          title: res.data.datas.error
                        });

                        setTimeout(function () {
                          wx.navigateBack({})
                        }, 1000);

                        return;
                      }

                      if (that.data.page == 1) {
                        that.setData({
                          records: res.data.datas.order_list,
                          is_bottom: res.data.hasmore == "1",
                          able_amount: res.data.datas.able_invite_amount,
                          total_amount: res.data.datas.total_invite_amount
                        })
                      }
                      else {
                        that.setData({
                          records: that.data.records.concat(res.data.datas.order_list),
                          is_bottom: res.data.hasmore == "1",
                          able_amount: res.data.datas.able_invite_amount,
                          total_amount: res.data.datas.total_invite_amount
                        })
                      }
                      
                    })
  },

  unfinished() {
    this.setData({
      state_type: 'state_new',
      page: 1,
    })

    this.getRecordData()
  },

  finished() {
    this.setData({
      state_type: 'state_finish',
      page: 1,
    })

    this.getRecordData()
  },

  refunding() {
    this.setData({
      state_type: 'state_refund',
      page: 1,
    })

    this.getRecordData()
  },

  gotoWithdraw() {
    if (parseFloat(this.data.able_amount) <= 0.0) {
      wx.showToast({
        title: "没有佣金可提现"
      })

      return;
    }

    wx.navigateTo({
      url: '../withdraw/withdraw?total_amount=' + this.data.able_amount
    });
  },

})