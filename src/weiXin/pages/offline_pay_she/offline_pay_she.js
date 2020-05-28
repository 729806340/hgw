// pages/offline_pay_she/offline_pay_she.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    pay:'',
    title:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      pay:options.pay || 1
    })
    if(this.data.pay == 1){
      this.setData({
        title:'支付成功'
      })
    }else{
      this.setData({
        title:'支付失败'
      })
    }
    wx.setNavigationBarTitle({
      title: this.data.title
    })
  },
  goindex(){
    wx.redirectTo({
      url: '../my_shopping/my_shopping'
    })
  },
  goorder(){
    wx.redirectTo({
      url: '../myOrder_s/myOrder_s?currentTab=' + 0
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

  }
})