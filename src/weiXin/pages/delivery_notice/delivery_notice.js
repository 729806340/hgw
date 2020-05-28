// pages/arrival_reminder/arrival_reminder.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tab:'today_notice',
    numList:'',
    dataList:[],
    goodsList:[],
    page_total:'',//总页数
    curpage:1,//查询页数
    if_show:false,
    searchText:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getIndex()
    this.getList()
  },
  getIndex(){
    var that = this;
    request.postUrl("shequ_goods_send.get_notice_data", {
      
    }, function(res) {
      if (res.data.code == 200) {
        that.setData({
          numList:res.data.datas
        })
      }
    })
  },
  getList(){
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_goods_send.index", {
      page:15,
      curpage:that.data.curpage,
      goods_name:that.data.searchText,
      notice_type:that.data.tab
    }, function(res) {
      if (res.data.code == 200) {
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
      that.getList()
    }
  },
  tabClick(e){
    var that = this
    that.setData({
      tab:e.currentTarget.dataset.tab,
      dataList:[],
      goodsList:[],
      if_show:false,
      curpage:1
    })
    that.getList()
  },
  searchVal(e){
    this.setData({
      searchText:e.detail.value
    })
  },
  //搜索事件
  searchClick(){
    var that = this
    that.setData({
      tab:'today_notice',
      curpage:1,
      dataList:[],
      goodsList:[],
      if_show:false
    })
    that.getList()
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