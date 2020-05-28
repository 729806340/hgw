// pages/undex_line/undex_line.js
var request = require('../../utils/request.js');
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    chain_list :[],//线下门店列表
    lay_x :0, //坐标
    lay_y: 0,//坐标
    cur_page : 1,//页数
    is_bottom:false, //后续是否还有数据
    info_dress : false,//是否获取地址信息
    Xclass:false, //长屏手机适配
    Atatus:1,
    showModal:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // this.getPermission()
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
    var that = this;
    //调用定位方法
    that.getUserLocation();
  },
  initData: function () {
    var that = this;
    request.postUrl("chain.chain_list", {
      curpage: that.data.cur_page,
      lay_x: that.data.lay_x,
      lay_y: that.data.lay_y,
      store_id: 223
    }, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      if (that.data.cur_page === 1) {
        that.setData({
          Xclass: false,
          chain_list: res.data.datas.chain_list,
          is_bottom: res.data.hasmore >= 0 ? false : true
        })
      } else {
        that.setData({
          Xclass : true,
          chain_list: that.data.chain_list.concat(res.data.datas.chain_list),
          is_bottom: res.data.hasmore > 0 ? false : true
        })
      }
    })
  },
  getMore: function () {
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.initData();
    }
  },
  getUserLocation(){
    var _this = this;
    wx.getSetting({
      success: (res) => {
        console.log(res)
        // res.authSetting['scope.userLocation'] == undefined    表示 初始化进入该页面
        // res.authSetting['scope.userLocation'] == false    表示 非初始化进入该页面,且未授权
        // res.authSetting['scope.userLocation'] == true    表示 地理位置授权
        if (res.authSetting['scope.userLocation'] != undefined && res.authSetting['scope.userLocation'] != true) {
          //未授权
          wx.showModal({
            title: '请求授权当前位置',
            content: '需要获取您的地理位置，请确认授权',
            success: function (res) {
              if (res.cancel) {
                //取消授权
                wx.showToast({
                  title: '拒绝授权',
                  icon: 'none',
                  duration: 1000
                })
                _this.setData({
                  info_dress:true
                })
              } else if (res.confirm) {
                //确定授权，通过wx.openSetting发起授权请求
                wx.openSetting({
                  success: function (res) {
                    if (res.authSetting["scope.userLocation"] == true) {
                      wx.showToast({
                        title: '授权成功',
                        icon: 'success',
                        duration: 1000
                      })
                      //再次授权，调用wx.getLocation的API
                      _this.getPermission();
                    } else {
                      wx.showToast({
                        title: '授权失败',
                        icon: 'none',
                        duration: 1000
                      })
                      _this.setData({
                        info_dress: true
                      })
                    }
                  }
                })
              }
            }
          })
        } else if (res.authSetting['scope.userLocation'] == undefined) {
          //用户首次进入页面,调用wx.getLocation的API
          console.log("初始化进入")
          _this.getPermission();
        }
        else {
          console.log('授权成功')
          //调用wx.getLocation的API
          _this.getPermission();
        }
      }
    })
  },

  //获取用户地理位置权限
    getPermission: function() {
      var that = this;
      wx.getLocation({
      type: 'wgs84',//默认为 wgs84 返回 gps 坐标，gcj02 返回可用于wx.openLocation的坐标
      success: function (res) {
        var latitude = res.latitude   //授权成功
        var longitude = res.longitude
        that.setData({
          lay_x: longitude,
          lay_y: latitude,
          info_dress :false,
        })
        that.initData();
      },
      fail:function(res){
        wx.showToast({
          title: '拒绝授权',
          icon: 'none',
          duration: 1000
        })
        that.setData({
          info_dress: true
        })
      }
    })
  },
  goshop:function(e){ //跳转线下门店详情
    if (!wx.getStorageSync("user_token")) {
      this.setData({
        Atatus: app.Atatus,
        showModal: app.showModal
      })
      return;
    }
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../store_details/store_details?chain_id=' + id,
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
    console.log("下拉了")
    this.getMore()
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    console.log("上拉了")
    this.getMore()
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})