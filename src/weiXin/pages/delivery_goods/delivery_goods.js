// pages/delivery_goods/delivery_goods.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    array: ['微信昵称', '姓名', '手机号'],
    array_get: ['nick_name', 'name', 'phone'],
    index:0,
    currentData: 'wait_take',
    searchText:'',

    dataList:[],
    page_total:'',//总页数
    curpage:1,//查询页数
    if_show:false,
  },
  // picker
  bindPickerChange: function (e) {
    this.setData({
      index: e.detail.value
    })
  },
  //  < !--提（未）走-- >
  checkCurrent: function (e) {
    const that = this;
    if (that.data.currentData === e.target.dataset.current) {
      return false;
    } else {
      that.setData({
        currentData: e.target.dataset.current,
        curpage:1,
        dataList:[],
        if_show:false
      })
      that.getList()
    }
  },
  searchVal(e){
    this.setData({
      searchText:e.detail.value
    })
  },
  searchClick(){
    this.setData({
      currentData:'wait_take',
      curpage:1,
      dataList:[],
      if_show:false
    })
    this.getList()
  },

  getList(){
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_wait_delivery.index", {
      page:10,
      curpage:that.data.curpage,
      take_type:that.data.currentData,
      search_key:that.data.array_get[that.data.index],
      search_value:that.data.searchText
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
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getList()
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