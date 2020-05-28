// pages/my_tuanProfit/my_tuanProfit.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    dataList:[],
    if_show:false,
    goodsList:[],
    page_total:'',
    curpage:1,
    id:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      id:options.id
    })
    this.getIndex(options.id)
  },
  getIndex(id){
    var that = this
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_captial_tuan.tuan_info_goods_commis", {
      shequ_tuan_id:id,
      page:15,
      curpage:that.data.curpage
    }, function(res) {
      if(res.data.code == '200'){
        var list = res.data.datas.goods_list
        var dataList = res.data.datas
        var goodsList = that.data.goodsList
        for(var i=0;i<list.length;i++){
          goodsList.push(list[i])
        }
        that.setData({
          dataList:dataList,
          goodsList:goodsList,
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
      that.getIndex(that.data.id)
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