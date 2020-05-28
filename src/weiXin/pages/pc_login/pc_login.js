// pages/pc_login/pc_login.js
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    wx_code:'',
    login_show: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      wx_code:options.scene
    })
  },
  login(){
    var that = this
    if(!wx.getStorageSync('user_token')){
      that.setData({
        login_show:true
      })
      return
    }
    wx.showLoading({
      title: '正在登录..'
    });
    request.postUrl('member_index.wx_pc_login', {
      wx_code:that.data.wx_code,
      wx_nick_name:wx.getStorageSync('nick_name'),
      wx_user_avatar:wx.getStorageSync('user_img')
    }, function(res) {
      if (res.data.code == 200) {
        wx.hideLoading();
        wx.switchTab({
          url: '../index/index'
        })
      }else{
        
      }
    })
  },
  cancel(){
    wx.switchTab({
      url: '../index/index'
    })
  },

  bindGetUserInfo: function(res) {
    var that = this;
    wx.showLoading({
      title: '正在授权中..'
    });
    var temp_goods = wx.getStorageSync('temp_goods');
    var temp = [];
    for (var l = 0; l < temp_goods.length; l++) {
      var goods_id = temp_goods[l].goods_id
      var goods_num = temp_goods[l].goods_num
      var lf = {
        goods_id,
        goods_num
      }
      temp.push(lf)
    }
    wx.login({
      success: function(login_res) {
        if (login_res.code) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称
          wx.getUserInfo({
            success: function(res) {
              wx.setStorageSync("user_img", res.userInfo.avatarUrl);
              wx.setStorageSync("nick_name", res.userInfo.nickName);
              request.postUrl("connect_weixin.login", {
                user_code: login_res.code,
                user_cookie: JSON.stringify(temp),
                tid: that.data.tid
              }, function(result) {
                if (!result.data.code) {
                  wx.showToast({
                    title: '登陆失败!'
                  });
                  return;
                }
                if (result.data.code != 200) {
                  wx.showToast({
                    title: result.data.datas.error
                  });
                  return;
                }
                wx.setStorageSync("open_id", result.data.datas.open_id);

                if (result.data.datas.user_token) {
                  wx.setStorageSync("user_token", result.data.datas.user_token);
                  wx.hideLoading();
                  that.login()
                  wx.setStorageSync("temp_goods", "");
                  /*wx.switchTab({
                             url: '../me/me'
                         });*/
                  // that.showInfo();
                  return;
                }

                if (result.data.datas.union_id) {
                  wx.setStorageSync("union_id", result.data.datas.union_id);
                  wx.hideLoading();
                  wx.navigateTo({
                    url: '../bindTelephone/bindTelephone'
                  });
                  return;
                } else {
                  let dealer_id = ''
                  if (wx.getStorageSync('dealer_id')) {
                    dealer_id = wx.getStorageSync('dealer_id')
                  }
                  
                  request.postUrl("connect_weixin.weixin_iv_login", {
                    session_key: result.data.datas.session_key,
                    encrypted_data: res.encryptedData,
                    iv: res.iv,
                    open_id: result.data.datas.open_id,
                    user_cookie: JSON.stringify(temp),
                    tid: that.data.tid,
                    dealer_id: dealer_id,
                    callTel:1
                  }, function(res1) {
                    if (!res1.data.code) {
                      wx.showToast({
                        title: '登陆失败!!'
                      });
                      return;
                    }
                    if (res1.data.code != 200) {
                      wx.showToast({
                        title: res1.data.datas.error
                      });
                      return;
                    }

                    if (res1.data.datas.user_token) {
                      that.setData({
                        login_show: false
                      })
                      wx.showToast({
                        title: '授权成功',
                        icon: 'success',
                        duration: 2000
                      })
                      wx.setStorageSync("user_token", res1.data.datas.user_token);
                      wx.hideLoading();
                      that.login()
                      wx.setStorageSync("temp_goods", "");
                      /*wx.switchTab({
                          url: '../me/me'
                      });*/
                      // that.showInfo();
                      return;
                    }
                    wx.setStorageSync("union_id", res1.data.datas.union_id);
                    wx.hideLoading();
                    wx.navigateTo({
                      url: '../bindTelephone/bindTelephone'
                    });
                    return;
                  })
                }
              })
            },
            fail: function(res) {
              wx.showToast({
                title: '授权失败',
                icon: "none",
              })
              wx.hideLoading();
            }
          })
        }
      }
    });
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