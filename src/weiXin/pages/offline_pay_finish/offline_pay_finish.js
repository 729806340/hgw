// pages/offline_pay_finish/offline_pay_finish.js\
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    res_data: "",
    order_info: "",
    fin : null,
    time : 5,
    clicK : false,
    poass:false, //是否是自提订单
    tuan_id:'',//是否是参团
    is_pintuan: '',//是否是拼团订单
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    console.log(options);
    var fin = options.fin
    this.setData({
      order_info: JSON.parse(options.order_info),
      fin: fin,
      poass: options.poass,
      tuan_id: options.tuan_id,
      is_pintuan: options.is_pintuan
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
    if(this.data.fin!=1){
      this.timeOut(this,this.data.time);
      wx.setNavigationBarTitle({
        title: '支付失败'
      })
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
  timeOut: function (that, time){ //倒计时 5S 后重新支付
    if (time == 0){
      that.setData({
        time: time,
        clicK :true
      })
      return ;
    }
    that.setData({
      time: time,
      clicK : false,
    })
    setTimeout(() => {
      time --;
      this.timeOut(that, time)
    }, 1000);
  },
  goOrder(){
    if (this.data.poass==="false"){
      if (parseInt(this.data.is_pintuan)>=1){  //拼团订单
        if (this.data.tuan_id>0){ //参团订单
          wx.redirectTo({
            url: '../myOrder/myOrder?currentTab=2',
          })
          return;
        } else if (this.data.tuan_id ==0){
          wx.redirectTo({
            url: '../myOrder/myOrder?currentTab=5',
          })
          return;
        }else{
          wx.redirectTo({
            url: '../myOrder/myOrder?currentTab=0',
          })
          return;
        }
      } else {//不是拼单商品
        wx.redirectTo({
          url: '../myOrder/myOrder?currentTab=2',
        })
        return;
      }
    }else{
      wx.redirectTo({
        url: '../myOrder/myOrder?currentTab=3',
      })
    }   
  },
  gopay(e){
    var that =this;
    if (this.data.order_info.pay_sn==''){
      return;
    }
    if(this.data.clicK == true){
      var that = this;
      var pay_sn = that.data.order_info.pay_sn;
      request.postUrl("buy.prepay_id", { pay_sn: pay_sn, open_id: wx.getStorageSync("open_id") }, function (res) {
        if (res.data.code == 200) {
          wx.requestPayment({
            'timeStamp': res.data.datas.pay_info.timeStamp + "",
            'nonceStr': res.data.datas.pay_info.nonceStr,
            'package': res.data.datas.pay_info.package,
            'signType': 'MD5',
            'paySign': res.data.datas.pay_info.paySign,
            'success': function (res) {
              console.log("支付=", res);
              that.goOrder()//
            },
            'fail': function (res) {
            }
          })
        } else {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none',
          })
        }
      })
    } 
  },
  goHome: function() {
    wx.switchTab({
      url: '../index_she/index_she'
    })
  }
})