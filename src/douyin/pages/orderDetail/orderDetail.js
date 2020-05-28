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
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    console.log(options)
    that.setData({
      order_id: options.order_id,
    })
    this.getOrderInfo();
  },
  getOrderInfo() {
    var that = this;
    request.postUrl('member_order.order_info', {
      order_id: that.data.order_id,
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
    var that = this;
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id='+that.data.order_info.store_id,
    })
  },
  goGoodsDetail: function (e) { //商品详情
    var item = e.currentTarget.dataset.item;
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
    request.postUrl("buy.douyin_ali_prepay_id", {
      pay_sn: pay_sn,
      open_id: wx.getStorageSync("open_id")
    }, function (pre_res) {
      if (pre_res.data.code == 200) {
        console.log("buy/prepay_id", pre_res);
        if(pre_res.data.datas.reciver_info){
          var reciver_info = pre_res.data.datas.reciver_info;
          reciver_info.area_info = reciver_info.area;
          reciver_info.address = reciver_info.street;
          reciver_info.true_name = pre_res.data.datas.reciver_name;
        }
        //
        var pay_info = pre_res.data.datas.pay_info
        console.log("开始支付")
        tt.pay({
          orderInfo: {
            app_id: pay_info.app_id,//ok
            sign_type: pay_info.sign_type,//ok
            out_order_no: pay_info.out_order_no,
            merchant_id: pay_info.merchant_id, //ok
            timestamp: pay_info.timestamp,
            product_code: pay_info.product_code, //ok
            payment_type: pay_info.payment_type,  //ok
            total_amount: pay_info.total_amount,  
            trade_type: pay_info.trade_type, //ok
            uid: pay_info.uid,
            version: pay_info.version, //ok
            currency: pay_info.currency,  //ok
            subject: pay_info.subject,
            body: pay_info.body,
            trade_time: pay_info.trade_time,
            valid_time: pay_info.valid_time,
            notify_url: pay_info.notify_url,
            alipay_url: pay_info.alipay_url,
            wx_url: pay_info.wx_url,
            wx_type: pay_info.wx_type,
            sign: pay_info.sign,
            risk_info: pay_info.risk_info
          },
          service: 1,
          //  getOrderStatus(res) {
          //   let { out_order_no } = res;
          //   return new Promise(function(resolve, reject) {
          //     // 商户前端根据 out_order_no 请求商户后端查询微信支付订单状态
          //     tt.request({
          //       url: "<your-backend-url>",
          //       success(res) {
          //         // 商户后端查询的微信支付状态，通知收银台支付结果
          //         resolve({ code: 0 | 1 | 2 | 3 | 9 });
          //       },
          //       fail(err) {
          //         reject(err);
          //       }
          //     });
          //   });
          // },
          'success': function(res) {
            console.log("调用支付=", res);
            if(res.code == 0){
              console.log("支付成功", res);
              wx.redirectTo({
                url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 1,
              })
            }else{
              console.log("支付失败", res);
              wx.redirectTo({
                url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 2,
              })
            }
          },
          'fail': function(res) {
            console.log("调用支付失败", res);
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