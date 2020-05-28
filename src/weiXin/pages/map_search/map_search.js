// pages/map_search/map_search.js
var amapFile = require('../../utils/amap-wx.js');
var util = require('../../utils/util.js');

var app = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    map_list: [],
    page: 1,
    offset: 20,
    search_txt: "",
    type:'',//判断从哪来  1=物流  空=其他
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    that.type = options.type
    var myAmapFun = new amapFile.AMapWX({
      key: app.map_key
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    console.log(app.s_area.ad_code)
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  },
  inputHandle: function(e) {
    var t = e.detail.value;
    console.log("ttttt", t);
    var that = this;
    that.setData({
      page: 1,
      search_txt: t,
    })
    if (t == '') {
      return;
    }
    that.getList();
  },
  getList: function(t) {
    var that = this;
    if(that.type == 1){
      var city = 420100
    }else{
      var city = app.s_area.ad_code
    }
    util.getUrl("https://restapi.amap.com/v3/place/text", {
      key: app.map_web_key,
      keywords: that.data.search_txt,
      city: city,
      citylimit: true,
      page: that.data.page,
      offset: that.data.offset,
    }, function(res) {
      console.log(res.pois)
      var map_list = that.data.map_list;
      if (that.data.page == 1) {
        map_list = res.pois;
      } else {
        map_list = map_list.concat(res.pois);
      }
      if (res.pois.length > 0) {
        that.setData({
          page: that.data.page + 1,
        })
      }
      that.setData({
        map_list: map_list,
      })
    })
  },
  chooseLocation: function(e) {
    var item = e.currentTarget.dataset.item;
    if (this.type == 1) {
      app.l_map = item;
    } else {
      app.s_map = item;
    }
    console.log(item)
    wx.navigateBack({

    })
  },

})