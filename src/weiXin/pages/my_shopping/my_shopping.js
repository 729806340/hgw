var request = require('../../utils/request.js');
var util = require('../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    Istrue: true, //自动轮播
    banner_list:[],
    goods_class_list:[],
    nav_index:0,
    default_address:'',
    tuan_list:[],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
  },
  //获取数据
  getIndex(){
    var that = this
    request.postUrl("shequ_dinosaur_me.index", {
      
    }, function (res) {
        if(res.data.code == '200'){
          var tuan_list = res.data.datas.tuan_list
          console.log(res.data.datas.tuan_list)
          var timestamp = Date.parse(new Date());
          timestamp = timestamp / 1000;
          for(var i=0;i<tuan_list.length;i++){
            if(timestamp > tuan_list[i].end_time){
              tuan_list[i].if_end = 1  //结束
            }else{
              tuan_list[i].if_end = 2  //未结束
            }
          }
          that.setData({
            tuan_list: tuan_list
          })
          console.log(that.data.tuan_list)
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
    this.getIndex()
  },
  //跳团长页面
  goTuan(e){
    wx.navigateTo({
      url: '../community/community?scene=' + e.currentTarget.dataset.scene,
    })
  },
   //我的订单
 goorder_she(){
  wx.navigateTo({
    url: '../myOrder_s/myOrder_s?currentTab=' + 0
  })
 },
 //我参与的团购
 gohome(){
  wx.navigateTo({
    url: '../my_shopping/my_shopping'
  })
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
})