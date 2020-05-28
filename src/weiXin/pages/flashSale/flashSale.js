// pages/flashSale/flashSale.js
var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    cur_page: 1,
    goods_list: [], //列表
    is_bottom: false,// 是否还有数据（分页之后）
    no_img: '', //没有数据是显示图片
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.initDetail()
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

  initDetail() { //数据
    var that = this;
    request.postUrl("index.current_second_goods_list", {
      curpage: that.data.cur_page,
    }, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      if (res.data.code == 200) {
        if (that.data.cur_page === 1) {
          that.setData({
            goods_list: res.data.datas.goods_list,
            is_bottom: res.data.hasmore > 0 ? false : true,
            no_img: res.data.datas.bottom_image,
          })
        } else {
          that.setData({
            goods_list: that.data.goods_list.concat(res.data.datas.goods_list),
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        }
      }
    })
  },
  getMore: function () {
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.initDetail();
    }
  },
  goGoodsDetail(e) {
    var goods_id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + goods_id
    })
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

  }
})