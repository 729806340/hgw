var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    infoItems: [],
    page_num: 10, //返回数据个数
    cur_page: 1, //设置加载页数，默认第1页
    is_bottom: false,
    mess_id: '', //每条消息id
    jump_type: '', //信息类型
    jump_data: '' //跳转数据
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.getMsgList();
  },

  getMsgList: function (e) {
      var that = this;
      request.postUrl('member_msg.msg_list', {
          cur_page: that.data.cur_page
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
          if (that.data.cur_page === 1) {
              that.setData({
                  infoItems: res.data.datas.msg_list,
                  is_bottom: res.data.datas.has_more > 0 ? false : true
              })
          } else {
              that.setData({
                  infoItems: that.data.infoItems.concat(res.data.datas.msg_list),
                  is_bottom: res.data.datas.has_more > 0 ? false : true
              })
          }
      })
  },

  handlescrolltolower: function (e) {
      var that = this;
      if (!that.data.is_bottom) {
          that.setData({
              cur_page: that.data.cur_page + 1
          });
          that.getMsgList();
      }
  },
  // 跳转相应信息链接：
  inToInfo: function(e) {
      var jump_type = e.currentTarget.dataset.jump_type;
      var jump_data = e.currentTarget.dataset.jump_data;
    if ((jump_type == 'order_payment_success' || jump_type == 'order_deliver_success' || jump_type == 'order_book_end_pay')  && jump_data.order_id > 0) {
      wx.navigateTo({
        url: '../orderDetail/orderDetail?order_id=' + jump_data.order_id
      })
    } else if ((jump_type == 'consult_goods_reply' || jump_type == 'arrival_notice') && jump_data.goods_id > 0) {
      wx.navigateTo({
        url: '../goodsDetails/goodsDetails?goods_id=' + jump_data.goods_id
      })
    }
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

  }
})