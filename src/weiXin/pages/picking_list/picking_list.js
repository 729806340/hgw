// pages/picking_list/picking_list.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    member_id:'',
    infoList:'',

    dataList:[],
    page_total:'',//总页数
    curpage:1,//查询页数
    if_show:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      member_id:options.scene
    })
    this.getIndex()
    this.getList()
  },
  getIndex(){
    var that = this;
    request.postUrl("shequ_wait_delivery_son.info", {
      member_id:that.data.member_id
    }, function(res) {
      if (res.data.code == 200) {
        that.setData({
          infoList:res.data.datas.member_info
        })
      }
    })
  },
  getList(){
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_wait_delivery_son.delivery_order_list", {
      member_id:that.data.member_id,
      page:1,
      curpage:that.data.curpage
    }, function(res) {
      if (res.data.code == 200) {
        var list = res.data.datas
        var dataList = that.data.dataList
        for(var i=0;i<list.length;i++){
          dataList.push(list[i])
        }
        that.setData({
          dataList:dataList,
          page_total:res.data.page_total
        })
        if(res.data.page_total <= 1){
          that.setData({
            if_show: true
          })
        }
        wx.hideLoading()
      }
    })
  },
  bindpushList(){
    var that = this
    var curpage = JSON.parse(that.data.curpage) + 1
    var page_total = that.data.page_total
    if(curpage > page_total){
      that.setData({
        if_show:true
      })
      return
    }else{
      that.setData({
        curpage: curpage
      })
      that.getList()
    }
  },
  confirmClick(){
    var that = this;
    request.postUrl("shequ_wait_delivery_son.fetch_goods_out", {
      member_id:that.data.member_id
    }, function(res) {
      if (res.data.code == 200) {
        wx.showToast({
          title: '成功',
        })
        setTimeout(function(){
          wx.switchTab({
            url: '../me/me'
          })
        },300)
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