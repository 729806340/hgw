// pages/my_tuanOrder/my_tuanOrder.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tab:0,
    is_show:false,
    numList:'',
    dataList:[],
    id:'',
    searchText:'',
    page_total:'',
    curpage:1,
    if_show:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      id:options.id
    })
    this.getIndex(options.id)//收益明细数据
    this.getList(options.id,this.data.tab,this.data.curpage,this.data.searchText)//订单列表数据
  },
  //收益明细数据
  getIndex(id){
    var that = this
    request.postUrl("shequ_captial_tuan.tuan_info_commis", {
      shequ_tuan_id:id
    }, function(res) {
      if(res.data.code == '200'){
        that.setData({
          numList:res.data.datas
        })
      }
    })
  },
  //订单列表数据
  getList(id,type,curpage,searchText){
    var that = this
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_captial_tuan.tuan_info", {
      shequ_tuan_id:id,
      order_type:type,
      search_key:searchText,
      page:10,
      curpage:curpage
    }, function(res) {
      if(res.data.code == '200'){
        var list = res.data.datas.list
        var dataList = that.data.dataList
        for(var i=0;i<list.length;i++){
          list[i].zhankai = false
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
  //下拉加载（分页）
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
      that.getList(that.data.id,that.data.tab,that.data.curpage,that.data.searchText)
    }
  },
  //默认展示两个商品，点击展开全部商品
  zhankai(e){
    var that = this
    var index = e.currentTarget.dataset.index
    var dataList = that.data.dataList
    dataList[index].zhankai = true
    that.setData({
      dataList:dataList
    })
  },
  //Tab切换事件
  tabClick(e){
    this.setData({
      tab:e.currentTarget.dataset.tab,
      curpage:1,
      dataList:[],
      if_show:false
    })
    this.getList(this.data.id,this.data.tab,this.data.curpage,this.data.searchText)
  },
  //跳转明细
  goProfit(){
    wx.navigateTo({
      url: '../my_tuanProfit/my_tuanProfit?id=' + this.data.id,
    })
  },
  //搜索框value
  searachipt(e){
    this.setData({
      searchText:e.detail.value
    })
  },
  //搜索事件
  searchClick(){
    this.setData({
      tab:0,
      curpage:1,
      dataList:[],
      if_show:false
    })
    this.getList(this.data.id,this.data.tab,this.data.curpage,this.data.searchText)
  },
  //拨打电话
  phoneCall(e){
    var that = this
    wx.makePhoneCall({
      phoneNumber: e.currentTarget.dataset.phone //仅为示例，并非真实的电话号码
    })
  },
  goorderDetail(e){
    var order_id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../orderDetail/orderDetail?order_id=' + order_id + '&tz_id=' + wx.getStorageSync('tuanzhang_id'),
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