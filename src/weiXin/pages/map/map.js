// pages/map/map.js
var amapFile = require('../../utils/amap-wx.js');
var app = getApp();
var util = require('../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    longitude: "",
    latitude: "",
    map_list: [],
    map_now: "",
    markers: [],
    sp: -1,
    s_map: "",
  },
  getLocation: function(location) {
    var that = this;
    var myAmapFun = new amapFile.AMapWX({
      key: app.map_key
    });
    var ll = location.split(',');
    console.log(ll);
    var markers = [{
      iconPath: "/src/images/blue.png",
      id: 0,
      longitude: ll[0],
      latitude: ll[1],
      width: 30,
      height: 30
    }]
    console.log(markers);
    that.setData({
      longitude: ll[0],
      latitude: ll[1],
      markers: markers,
    })
    myAmapFun.getRegeo({
      location: location,
      success: function(res) {
        var map_now = "";
        if (app.s_map == "") {
          map_now = {
            address: res[0].name,
            name: res[0].desc,
            location: location,
          }
        } else {
          map_now = app.s_map;
        }
        that.data.s_map = map_now;
        console.log('r.r.r..r.r.r.rr.r', res[0].regeocodeData.pois)
        that.setData({
          map_list: res[0].regeocodeData.pois,
          map_now: map_now,
        })
      },
      fail: function(res) {
        console.log(res);
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    if (app.s_map != "") {
      that.getLocation(app.s_map.location);
      that.data.s_map = app.s_map;
      return;
    }
    wx.getLocation({
      type: 'gcj02',
      success: function(res) {
        var longitude = res.longitude;
        var latitude = res.latitude;
        var location = longitude + "," + latitude;
        var myAmapFun = new amapFile.AMapWX({
          key: app.map_key
        });
        myAmapFun.getRegeo({
          location: location,
          success: function(res) {
            if (app.s_area != "") {
              if (res.length <= 0 || res[0].regeocodeData.addressComponent.adcode != app.s_area.ad_code) {
                location = app.s_area.location;
              }
            }
            that.getLocation(location);
          },
          fail: function(res) {}
        })
        return;
      }
    })
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
    var that = this;
    if (app.s_map != "") {
      that.setData({
        sp: -1,
        s_map: app.s_map,
      })
      that.getLocation(app.s_map.location);
    }
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
  goSearch: function() {
    wx.navigateTo({
      url: '../map_search/map_search',
    })
  },
  chooseLocation: function(e) {
    console.log(e);
    var that = this;
    var index = e.currentTarget.dataset.index;
    var item = "";
    if (index == -1) {
      item = that.data.map_now;
    } else {
      item = e.currentTarget.dataset.item;
    }
    that.data.s_map = item;
    var ll = item.location.split(',');
    var markers = [{
      iconPath: "/src/images/blue.png",
      id: 0,
      longitude: ll[0],
      latitude: ll[1],
      width: 30,
      height: 30
    }]
    console.log(markers);
    that.setData({
      longitude: ll[0],
      latitude: ll[1],
      markers: markers,
      sp: index,
    })
  },
  btn_sure: function() {
    var that = this;
    app.s_map = that.data.s_map;
    wx.navigateBack({})
  }

})