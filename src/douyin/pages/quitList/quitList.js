// pages/quit_list/quit_list.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    refund_list: [],
    cur_page: 1,
    page_num: 10,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.getList();
  },
  getList: function () {
    var that = this;

    request.postUrl('member_refund.get_refund_list', { curpage:that.data.cur_page}, function (res) {
      var temp = res.data.datas.refund_list;
      if (that.data.cur_page == 1) {
        that.data.refund_list = [];
      }
      if (temp.length > 0) {
        that.setData({
          cur_page: parseInt(that.data.cur_page) + 1,
        })
      }
      var refund_list = that.data.refund_list;
      refund_list = refund_list.concat(temp);
      that.setData({
        refund_list: refund_list,
      })
    })

  },
  handlescrolltolower: function () {
    this.getList();
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  goQuitDetail: function (e) {
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../quitDetail/quitDetail?refund_id=' + id,
    })
  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  }
})