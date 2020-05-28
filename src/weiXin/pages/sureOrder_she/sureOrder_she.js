var app = getApp();
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    info:"",
    datalist:"",
    address_info:'',
    cartList:'',
    pay_message:'',
    link_name:'',
    link_phone:'',
    pd_pay : 0, // 实时预存款
    rcb_pay :0 ,//实时卡余额
    rcb_pays : 0,
    pd_pays  : 0,
    yuesdsdwd : false,
    yuesdsdwds :false,
    dinosaur_id:'',
    cart_id:'',
    if_phone:true,

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.login({
      success: function(login_res) {
        console.log('刷新session_key')
      }
    })
    this.setData({
      cart_id:options.cart_id,
      ifcart:options.ifcart
    })
    if(wx.getStorageSync("pay_name")){
      this.setData({
        link_name:wx.getStorageSync("pay_name"),
        link_phone:wx.getStorageSync("pay_phone")
      })
    }
    this.gosureorder(options.cart_id,options.ifcart)
  },
  gosureorder(cart_id,ifcart){
    var that = this
    request.postUrl('shequ_dinosaur_buy.index', {
      cart_id:cart_id,
      ifcart:ifcart,
      tuanzhang_id:wx.getStorageSync('tuanzhang_id')
    }, function(res) {
      if (res.data.code == 200) {
        that.setData({
          cartList:res.data.datas.store_cart_list,
          address_info:res.data.datas.address_info,
          datalist:res.data.datas
        })
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
    var that = this
    if (app.address_info) {
      that.setData({
        address_info: app.address_info
      })
    }else {
      that.setData({
        address_info: that.data.datalist.address_info
      })
    }
    console.log(app.address_info)
  },
  goaddress(){
    wx.navigateTo({
      url: '../address/address?flag=' + 9
    })
  },
  change_pay_type: function(e){
    if (e.currentTarget.dataset.type == 1) { //预存款
      if (this.data.yuesdsdwds){
        this.setData({
          yuesdsdwds :false,
          pd_pay : 0,
          pd_pays : 0,
        })
      }else{
        this.setData({
          yuesdsdwds: true,
          pd_pay: this.data.datalist.available_predeposit,
          pd_pays: 1,
        });
      }
    } else {  //卡支付
      if (this.data.yuesdsdwd) {
        this.setData({
          yuesdsdwd: false,
          rcb_pay :0,
          rcb_pays :0,
        })
      } else {
        this.setData({
          yuesdsdwd: true,
          rcb_pay: this.data.datalist.available_rc_balance,
          rcb_pays: 1,
        })
      }
    }
  },
  handleinput: function(e) {
    this.setData({
      pay_message: e.detail.value
    })
  },
  handleinput_name: function(e) {
    this.setData({
      link_name: e.detail.value
    })
  },
  handleinput_phone: function(e) {
    this.setData({
      link_phone: e.detail.value
    })
  },
  // 支付
  payto(){
    var that = this;
    if(that.data.ifcart == 1){
      if(that.data.link_name == ''){
        wx.showToast({
          title: '请填写购买人',
          icon:'none'
        });
        return
      }
      if(that.data.link_phone == ''){
        wx.showToast({
          title: '请填写手机号',
          icon:'none'
        });
        return
      }
    }else if(that.data.ifcart == 0){
      if(that.data.address_info.address_id == 0){
        wx.showToast({
          title: '请填写收货地址',
          icon:'none'
        });
        return
      }
    }
    wx.showLoading({
      title: '订单提交中',
      mask: "none",
    })
    var pay_message = []
    var datalist = that.data.datalist
    if(that.data.pay_message){
      for(var i=0;i<datalist.store_cart_list.length;i++){
        var item = datalist.store_cart_list[i].store_id + '|' + that.data.pay_message
        pay_message.push(item)
      }
      pay_message = pay_message.join(',')
    }else{
      pay_message = ''
    }
    var list = {
      cart_id:that.data.cart_id,
      address_id:that.data.address_info.address_id,
      dinosaur_id:that.data.dinosaur_id,
      link_name:that.data.link_name,
      link_phone:that.data.link_phone,
      pay_message:pay_message,
      vat_hash:that.data.datalist.vat_hash,
      offpay_hash:that.data.datalist.offpay_hash,
      offpay_hash_batch:that.data.datalist.offpay_hash_batch,
      ifcart:that.data.ifcart,
      tuanzhang_id:wx.getStorageSync('tuanzhang_id')
    }
    request.postUrl('shequ_dinosaur_buy.buy', list, function (res) {
      wx.hideLoading();
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      request.postUrl('buy.prepay_id', { 
        pay_sn: res.data.datas.pay_sn,
        open_id: wx.getStorageSync("open_id"),
        rcb_pay: that.data.rcb_pays,
        pd_pay: that.data.pd_pays,
      }, function (res) {
        if (res.data.code == 200) {
          wx.setStorageSync("pay_name", that.data.link_name);
          wx.setStorageSync("pay_phone", that.data.link_phone);
          if (res.data.datas.pay_ok) {
            wx.redirectTo({
              url: '../offline_pay_she/offline_pay_she?pay=' + 1
            })
            return;
          }else{
            wx.requestPayment({
              'timeStamp': res.data.datas.pay_info.timeStamp + "",
              'nonceStr': res.data.datas.pay_info.nonceStr,
              'package': res.data.datas.pay_info.package,
              'signType': 'MD5',
              'paySign': res.data.datas.pay_info.paySign,
              'success': function (res) {
                console.log("支付=", res);
                wx.redirectTo({
                  url: '../offline_pay_she/offline_pay_she?pay=' + 1
                })
              },
              'fail': function (res) {
                wx.redirectTo({
                  url: '../offline_pay_she/offline_pay_she?pay=' + 2
                })
              }
            })
          }
        } else {
          wx.showToast({
            title: res.data.datas.error
          });
        }
      })
    })
  },
  //获取微信手机号




  getPhoneNumber(e){
    console.log(e)
    var that = this
    wx.login({
      success: function(login_res) {
        if (login_res.code) {
          request.postUrl("connect_weixin.get_session_key", {
            user_code:login_res.code
          }, function(result) {
            if(result.data.code == '200'){
              request.postUrl("connect_weixin.decrypt_iv", {
                encrypted_data:e.detail.encryptedData,
                iv:e.detail.iv,
                session_key:result.data.datas.session_key
              }, function(res) {
                if(res.data.code == '200'){
                  that.setData({
                    link_phone:res.data.datas.phoneNumber,
                    if_phone:false
                  })
                }
              })
            }
          })
        }
      }
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
    app.address_info = ''
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
  // onShareAppMessage: function () {

  // }
})