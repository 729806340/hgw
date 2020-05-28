var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    page: 1,
    is_bottom: false,
    records: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getListData()
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  getListData() {
    let that = this;
    request.postUrl('pyramid_selling.crash_out_list', 
                    { curpage: that.data.page }, 
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
                          records: res.data.datas.log_list,
                          is_bottom: res.data.hasmore == "1",
                        })
                      }
                      else {
                        that.setData({
                          records: that.data.records.concat(res.data.datas.log_list),
                          is_bottom: res.data.hasmore == "1",
                        })
                      }
                    })
  }
})