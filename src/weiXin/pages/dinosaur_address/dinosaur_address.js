var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
var amapFile = require('../../utils/amap-wx.js');
var app = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    city_name: "定位中",
    default_address:'',
    tuan_list:'',
    array: ['武汉'],
    index: 0,

    dataList:'',//数据
    lay_x:"",
    lay_y:"",
  },
  // picker
  bindPickerChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      index: e.detail.value
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    app.s_map = ''
    that.getLocation()  //定位/附近团长
  },
  getLocation: function () {
    var that = this;
    wx.getLocation({
      type: "gcj02",
      success: function (res) {
        var longitude = res.longitude;
        var latitude = res.latitude;
        that.setData({
          lay_x:longitude,
          lay_y:latitude
        })
        var location = longitude + "," + latitude;
        var myAmapFun = new amapFile.AMapWX({
          key: app.map_key
        });
        myAmapFun.getRegeo({
          location: location,
          success: function(r) {
            that.setData({
              city_name:r[0].name
            })
            // if(r[0].regeocodeData.addressComponent.city != '武汉市'){
            //   that.setData({
            //     index:1
            //   })
            // }
            that.getIndex()
          },
          fail: function(r) {
            that.setData({
              city_name:'获取地址失败'
            })
          }
        })
      },
      fail: function(res){
        wx.navigateBack()
      }
    })
  },
  //附近团长
  getIndex(){
    var that = this
    request.postUrl("shequ_captial_near.get_near", {
      lay_x: that.data.lay_x,
      lay_y: that.data.lay_y,
    }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            dataList:res.data.datas
          })
        }
    })
  },
  //重新定位
  again(){
    this.setData({
      city_name: "定位中"
    })
    this.getLocation()
  },
  //设置默认团长
  setTuanzhang(e){
    var that = this
    var tz_id = e.currentTarget.dataset.id
    request.postUrl("member_index.set_default_tuanzhang", {
      tz_id:tz_id
    }, function (res) {
        if(res.data.code == '200'){
          that.getIndex()
          wx.setStorageSync('tuanzhang_id', tz_id);
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
    })
  },

  //搜索提货地址
  gomap(){
    var s_area = {
      'ad_code': "420100",
      'area_id': "258",
      'area_name': "武汉市",
      'location': "114.304569,30.593354"
    }
    app.s_area = s_area
    wx.navigateTo({
      url: '../map/map',
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
    var that = this
    if(app.s_map != ''){
      var ll = app.s_map.location.split(',')
      that.setData({
        lay_x: ll[0],
        lay_y: ll[1],
      })
      that.getIndex()
      console.log(app.s_map)
    }
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