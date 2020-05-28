// pages/my_tuanUser/my_tuanUser.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    dataList:[],
    numList:[],
    searchText:'',
    page_total:'',
    curpage:1,
    if_show:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getIndex()
    this.getList(this.data.curpage)
  },
  search(e){
    this.setData({
      searchText:e.detail.value
    })
  },
  getIndex(){
    var that = this
    request.postUrl("shequ_tuan_member.index", {
      
    }, function(res) {
      if(res.data.code == '200'){
        that.setData({
          numList:res.data.datas
        })
      }
    })
  },
  getList(curpage){
    var that = this
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_tuan_member.get_list", {
      member_name:that.data.searchText,
      page:15,
      curpage:curpage
    }, function(res) {
      if(res.data.code == '200'){
        var list = res.data.datas.list
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
  pushList(){
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
        curpage:curpage
      })
      that.getList(curpage)
    }
  },
  searchClick(){
    this.setData({
      curpage:1,
      dataList:[]
    })
    this.getList(1)
  },
  gousershoping(e){
    var member_id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../my_tuanUsershoping/my_tuanUsershoping?member_id=' + member_id,
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