// pages/scanning_code/scanning_code.js
var request = require('../../utils/request.js');
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    searchText:'',
  },
  searchVal(e){
    this.setData({
      searchText:e.detail.value
    })
  },
  searchClick: function () {
    var that = this;
    if(that.data.searchText == ''){
      return
    }
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_wait_delivery_son.search_son", {
      search_value:that.data.searchText
    }, function(res) {
      if (res.data.code == 200) {
        wx.hideLoading()
        wx.navigateTo({
          url: '../code_result/code_result?item=' + JSON.stringify(res.data.datas.member_info),
        })
      }else{
        wx.showToast({
          title: '查询不到相关信息',
          icon:'none'
        })
      }
    })
  },

  getScancode() {
    var that = this;
    // 允许从相机和相册扫码
    wx.scanCode({
      success: (res) => {
        console.log('扫一扫',res)
        wx.navigateTo({
          url: res.path,
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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