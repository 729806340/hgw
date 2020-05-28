// pages/signIn/signIn.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    base64ImgUrl:'',
    time:'',
    dataList:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.getIndex();
  },

  getIndex: function () {
    var that = this;
    request.postUrl('shequ_tuan_signin.index', {},
      function (res) {
        if (res.data.code = 200) {

          that.setData({
            dataList: res.data.datas,
            base64ImgUrl:res.data.datas.tuan_qr_code.replace(/[\r\n]/g,"")
          })

        }
      })
  },
  getList: function () {
    var that = this;
    request.postUrl('shequ_tuan_signin.polling', {},
      function (res) {
        if (res.data.code = '200') {
          if (res.data.datas.state === 'true'){
            wx.navigateTo({
              url: '../delivery_receipt/delivery_receipt?tuan_id=' + res.data.datas.tuan_id,
            })
            clearInterval(that.data.time)
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
    var that = this
    var time = setInterval(function(){
      that.getList()
      console.log(1)
    },2000)
    that.data.time = time
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    clearInterval(this.data.time)
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    clearInterval(this.data.time)
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