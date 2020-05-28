// pages/my_commission/my_commission.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    dataList:'',//所有数据
  },
  goBank(){
    wx.navigateTo({
      url: '../my_banknumber/my_banknumber',
    })
  },
  goWithdrawal(){
    wx.navigateTo({
      url: '../withdrawal/withdrawal',
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getList()
  },
  getList(){
    var that = this;
    request.postUrl("shequ_cash_out.index", {}, function(res) {
      if (res.data.code == 200) {
        that.setData({
          dataList:res.data.datas
        })
      }
    })
  },
  cashOut(){
    var that = this;
    wx.showModal({
      title: '提示',
      content: '是否确定提现到零钱',
      success (res) {
        if (res.confirm) {
          console.log('用户点击确定')
          request.postUrl("shequ_cash_out.cash_out", {
            open_id:  wx.getStorageSync("open_id")
          }, function(res) {
            if (res.data.code == 200) {
              wx.showToast({
                title: '提现成功',
              })
            }else{
              wx.showToast({
                title: '提现失败',
                icon:'none'
              })
            }
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
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