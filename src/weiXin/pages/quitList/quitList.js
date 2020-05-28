// pages/quit_list/quit_list.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    refund_list: [],
    cur_page: 1,
    page_num: 10,
    tab:1,
    page_total:'',
    tz_id:0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    console.log(options,'options')
    if(options.tz_id){
      that.setData({
        tz_id:options.tz_id
      })
    }
    that.getList();
  },
  getList: function () {
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl('member_refund.get_refund_list', { 
      curpage:that.data.cur_page,
      refund_state:that.data.tab,
      tz_id:that.data.tz_id
    }, function (res) {
      var temp = res.data.datas.refund_list;
      var refund_list = that.data.refund_list;
      refund_list = refund_list.concat(temp);
      that.setData({
        refund_list: refund_list,
        page_total:res.data.page_total
      })
      wx.hideLoading()
    })

  },
  handlescrolltolower: function () {
    var that = this
    var cur_page = JSON.parse(that.data.cur_page) + 1
    var page_total = that.data.page_total
    if(cur_page > page_total){
      return
    }else{
      that.setData({
        cur_page: cur_page
      })
      this.getList();
    }
  },
  tabClick(e){
    this.setData({
      tab:e.currentTarget.dataset.tab,
      cur_page:1,
      refund_list: []
    })
    this.getList();
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

  },
  goQuitDetail: function (e) {
    var that = this;
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../quitDetail/quitDetail?refund_id=' + id + '&tz_id=' + that.data.tz_id,
    })
  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  }
})