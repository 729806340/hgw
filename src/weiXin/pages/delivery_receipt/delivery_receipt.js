// pages/delivery_receipt/delivery_receipt.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    data:[],
    dataList: [],//签收页详情
    tuan_id:'',
    page_total: '',//总页数
    curpage: 1,//查询页数
    if_show: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this; 
    that.setData({
      tuan_id: options.id
    })
    that.getIndex(that.data.curpage);
    
  },
  bindgetuserinfo(e){
    console.log(e)
  },
  //签收页详情接口
  getIndex: function (curpage) {
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl('shequ_tuan_signin.signInfo', {
      tuan_id: that.data.tuan_id,
      page: 15,
      curpage: curpage
    },
      function (res) {
        if (res.data.code = 200) {
          var list = res.data.datas
          var data = that.data.data
          for (var i = 0; i < list.length; i++) {
            data.push(list[i])
          }
          that.setData({
            data:data,
            dataList: res.data.datas,
            page_total: res.data.page_total
          })
          if (res.data.page_total <= 1) {
            that.setData({
              if_show: true
            })
          }
          wx.hideLoading()
        }
       
      })
  },
  bindpushList() {
    var that = this
    var curpage = JSON.parse(that.data.curpage) + 1
    var page_total = that.data.page_total
    if (curpage > page_total) {
      that.setData({
        if_show: true
      })
      return
    } else {
      that.setData({
        curpage: curpage
      })
      that.getList(curpage)
    }
  },
  goBtn(){
    var that = this; 
    that.getBtn()
  },
  getBtn: function () {
    var that = this;
    request.postUrl('shequ_tuan_signin.signChange', {
      tuan_id: that.data.tuan_id,
    },
      function (res) {
        console.log(res)
        if (res.data.code == '200') {
          wx.showToast({
            title: '签收成功',
          })
          wx.switchTab({
            url: '../me/me' 
          })
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
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
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;
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