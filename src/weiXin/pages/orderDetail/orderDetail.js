var request = require('../../utils/request.js');
var common = require('../../utils/common.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    status: null,
    order_info: {},
    show: false,
    store_phone: '',
    tuan_user_list:[],
    pgmarst_interval:'',
    inTime:'00:00:00',
    alsetime:false,
    tz_id:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    console.log(options)
    that.setData({
      order_id: options.order_id,
      tz_id:options.tz_id
    })
    this.getOrderInfo();
  },
  getOrderInfo() {
    var that = this;
    var tz_id = that.data.tz_id
    request.postUrl('member_order.order_info', {
      order_id: that.data.order_id,
      tz_id:tz_id
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
      if (res.data.datas.order_info.tuan_info != ''){ //当前时间 过期时间
        var info = res.data.datas.order_info.tuan_info
        if (res.data.datas.order_info.tuan_info.state == 0){ //拼团中 倒计时
          var pgmarst_interval = setInterval(function () {
            var pingo_end_time = parseInt(info.expires_time - common.getTimestamp())
            var time = common.getTime(pingo_end_time);
            var inTime = time[0] + ":" + time[1] + ":" + time[2]
            that.setData({
              inTime: inTime
            })
            if (time == 0) { //重新加载
              that.onLoad()
            }
            time = time - 1
            that.setData({
              inTime: inTime
            })
          }, 1000);
          that.data.pgmarst_interval = pgmarst_interval; 
        }
        that.setData({
          tuan_user_list: res.data.datas.order_info.tuan_user_list
        })
      }
      res.data.datas.order_info.reciver_addr = res.data.datas.order_info.reciver_addr.replace(/\s+/g, "");
      that.setData({
        order_info: res.data.datas.order_info,
        status: res.data.datas.order_info.order_state,
        store_phone: res.data.datas.order_info.store_phone,
      })
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
    clearInterval(this.data.pgmarst_interval)
  },
  cancelOrder: function () {
    var that = this;
    var item = that.data.order_info;
    var order_id = item.order_id;
    wx.showModal({
      title: '提示',
      content: '确认要取消该订单?',
      success: function (res) {
        if (res.confirm) {
          request.postUrl("member_order.order_cancel", {
            order_id: order_id,
          }, function (res) {
            if (res.data.code == 200) {
              wx.showToast({
                title: '取消成功',
                icon:'none',
              })
              wx.redirectTo({
                url: '../myOrder/myOrder?currentTab=' + 0,
              })
            }else{
              wx.showToast({
                title: res.data.datas.error,
                icon: 'none',
              })
            }
          })
        }
      }
    })
  },
  quitOrder: function (e) { //退款
    var refund = e.currentTarget.dataset.refund;
    var order_id = e.currentTarget.dataset.order_id;
    var order_goods_id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../getRefund/getRefund?order_id=' + order_id + '&refund=' + refund + '&order_goods_id=' + order_goods_id,
    })
  },
  makePhone: function () {
    var that = this;
    if (that.data.store_phone==''){
      return;
    }
    wx.makePhoneCall({
      phoneNumber: that.data.store_phone, //仅为示例，并非真实的电话号码
    })
  },
  goShopDetail: function () { //店铺详情
    return
    var that = this;
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id='+that.data.order_info.store_id,
    })
  },
  //拨打电话
  phoneCall(e){
    var that = this
    var phone = e.currentTarget.dataset.phone
    wx.makePhoneCall({
      phoneNumber: phone //仅为示例，并非真实的电话号码
    })
  },
  goGoodsDetail: function (e) { //商品详情
    return
    var item = e.currentTarget.dataset.item;
    var scene = e.currentTarget.dataset.scene;
    if(scene){
      wx.navigateTo({
        url: '../community/community?scene=' + scene,
      })
      return
    }
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id='+item.goods_id,
    })
  },
  //确认收货
  orderRec: function (e) {
    var that = this;
    var order_id = e.currentTarget.dataset.order_id;
    wx.showModal({
      title: '提示',
      content: '确认收货该订单?',
      success: function (res) {
        if (res.confirm) {
          request.postUrl("member_order.order_receive", {order_id:order_id}, function (res) {
            if (res.data.code == 200) {
              that.getOrderInfo();
              wx.showToast({
                title: '确认收货成功',
                icon: 'none'
              })
            }
          })
        }
      }
    })
  },
  //支付
  payReq: function (e) {
    var that = this;
    console.log(e);
    var item = that.data.order_info;
    var pay_sn = item.pay_sn;
    request.postUrl("buy.prepay_id", {
      pay_sn: pay_sn,
      open_id: wx.getStorageSync("open_id")
    }, function (pre_res) {
      if (pre_res.data.code == 200) {
        console.log("buy/prepay_id", pre_res);
        var reciver_info = pre_res.data.datas.reciver_info;
        reciver_info.area_info = reciver_info.area;
        reciver_info.address = reciver_info.street;
        reciver_info.true_name = pre_res.data.datas.reciver_name;
        that.setData({
          order_info: res.data.datas.order_info
        })
        wx.requestPayment({
          'timeStamp': pre_res.data.datas.pay_info.timeStamp + "",
          'nonceStr': pre_res.data.datas.pay_info.nonceStr,
          'package': pre_res.data.datas.pay_info.package,
          'signType': 'MD5',
          'paySign': pre_res.data.datas.pay_info.paySign,
          'success': function (res) {
            console.log("支付=", res);
            wx.redirectTo({
              url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 1,
            })
          },
          'fail': function (res) {
            wx.redirectTo({
              url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 2,
            })
           }
        })
      } else {
       wx.showToast({
         title : res.data.datas.error,
         icon : 'none'
       })
      }
    })
  },
  // 评价订单
  commentOrder:function(e){
    var order_id = e.currentTarget.dataset.order_id
    var id = this.data.order_info.extend_order_goods[0].rec_id
    var rec_id = []
    rec_id.push(id)
    wx.navigateTo({
      url: '../commentOrder/commentOrder?order_id=' + order_id + '&rec_id=' + rec_id,
    })
  },
  distributionInfo:function(e){ //物流
    var order_id = e.currentTarget.dataset.order_id;
    console.log(order_id)
    wx.navigateTo({
      url: '../distributionInfo/distributionInfo?order_id=' + JSON.stringify(order_id),
    })
  },
  /**
   * 自定义分享
   */
  onShareAppMessage: function (res) {
    var that = this;
    if (that.data.order_info.tuan_info!=''){
      return {
        title: '你的好朋友' + that.data.order_info.tuan_info.captain_name + "超值推荐" + that.data.order_info.goods_amount + "元拼团"+ that.data.order_info.extend_order_goods[0].goods_name,
        path: '/pages/goodsDetails/goodsDetails?tuan_id=' + that.data.order_info.tuan_info.tuan_id + '&goods_id=' + that.data.order_info.extend_order_goods[0].goods_id,
        imageUrl: that.data.order_info.extend_order_goods[0].image_url
      }
    }else{
      return {
        title: '你的好朋友超值推荐' + that.data.order_info.extend_order_goods[0].goods_name,
        path: '/pages/goodsDetails/goodsDetails?goods_id=' + that.data.order_info.extend_order_goods[0].goods_id,
        imageUrl: that.data.order_info.extend_order_goods[0].image_url
      }
    }
  }
})