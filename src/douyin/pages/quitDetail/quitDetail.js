var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    status: 0,
    refund: "", //退款信息
    pic_list: "",
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options);
    var that = this;
    request.postUrl("member_refund.get_refund_info", {
      refund_id: options.refund_id
    }, function (res) {
      that.setData({
        refund: res.data.datas.refund,
        pic_list: res.data.datas.pic_list,
        status: res.data.datas.refund.refund_show_state
      })
    })
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
  previewImage: function (e) {
    var that = this;
    var item = e.currentTarget.dataset.item
    wx.previewImage({
      current: item, // 当前显示图片的http链接
      urls: that.data.pic_list // 需要预览的图片http链接列表
    })
  }
})