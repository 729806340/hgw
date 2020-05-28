
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    currentData: 0,
    flag: 0,
    totalData: [],//请求的数据数组
  },
// 团
  checkCurrent: function (e) {
    const that = this;

    if (that.data.currentData === e.target.dataset.current) {
      return false;
    } else {

      that.setData({
        currentData: e.target.dataset.current
      })
    }
  },
  switchNav: function (e) {
    console.log(e.currentTarget.id);
    this.setData({
      flag: e.currentTarget.id
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
     this.getIndex(this.data.curpage)
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;
    that.getIndex()
  },
  // 获取接口数据
  getIndex: function () {
    var that = this;
    request.postUrl('shequ_dinosaur_performance.index', {},
      function (res) {
        if(res.data.code=200){
            console.log(res)
          that.setData({
            totalData: res.data.datas
          })

        }
      })
  },

  

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

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