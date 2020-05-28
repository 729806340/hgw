// pages/live/live.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    roomId:"",
    customParams:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let roomId = [5] // 填写具体的房间号，可通过下面【获取直播房间列表】 API 获取
    let customParams = encodeURIComponent(JSON.stringify({ path: 'pages/index/index' })) // 开发者在直播间页面路径上携带自定义参数（如示例中的path和pid参数），后续可以在分享卡片链接和跳转至商详页时获取，详见【获取自定义参数】、【直播间到商详页面携带参数】章节（上限600个字符，超过部分会被截断）
    this.setData({
        roomId:roomId,
        customParams:customParams
    })
  },
  testPay(){
    var that = this
    request.postUrl('pyramid_selling.test_small_change',
      { open_id:  wx.getStorageSync("open_id")},
      function (res) {
        
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