// pages/order/order.js
var app = getApp();
var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabs: ["全部", "待付款", "待收货", "已完成", "已取消"],
    currentTab: 0,
    order_list: [
      {},
      {},
      {},
      {},
      {}
    ], //订单列表
    is_show: false,
    flag: false,
    click_flag: false, //防止重复点击
    cur_page: 1,
    state_type: "",
    hasOrder: true, //是否有相关订单
    is_bottom: false,
    order_info: {},
    form_id:'',
    is_pintuan:0,
    tuan_id:'',
    touched:false, //触摸时间限制
    hasmore:1, //后续是否还有订单避免多次请求空数据
    inval_touch:true,
    change:true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    wx.hideShareMenu();
    console.log(options);
    var that = this;
    this.setData({
      currentTab: options.currentTab,
    })
    var current = options.currentTab;
    if (current == 0) {
      that.setData({
        state_type: "",
      })
    }
    if (current == 1) {
      that.setData({
        state_type: "state_new",//待付款
      })
    }
    if (current == 2) {
      that.setData({
        state_type: "state_wait",//待收货
      })
    }
    if (current == 3) {
      that.setData({
        state_type: "state_noeval",//已完成
      })
    }
    if (current == 4) {
      that.setData({
        state_type: "state_cancel",//已取消
      })
    }
    
    this.setData({
      cur_page: 1,
    })
    this.initData();
  },

  getMore(e){
    console.log(this.data.inval_touch)
    if (this.data.inval_touch){
      return;
    }
    if (this.data.cur_page != 1 && this.data.hasmore != 1) {
      return;
    }
    this.initData();
  },
  initData: function() {
    var that = this;
    if (that.data.touched){
      return;
    }
    if (that.data.cur_page!=1){
      that.setData({
        touched:true,
      })
    }
    wx.showLoading({
      title: '加载中...',
    })
    that.setData({
      hasOrder: true,
      inval_touch:true,
      change:true,
    });
    request.postUrl("member_order.order_list", {
      // shequ_tuan_id:100,
      curpage: that.data.cur_page,
      state_type: that.data.state_type,
    }, function(res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      that.setData({
        inval_touch:false,
        change:false,
      })
      wx.hideLoading();
      var res_list = res.data.datas.order_list
      var l = Object.keys(res_list).length;
      if (l < 6) {
        that.setData({
          is_bottom: true,
        })
      }
      if (l > 0) {
        var order_list = that.data.order_list;
        var temp_list = order_list[that.data.currentTab];
        for (var key in res_list) {
          temp_list[key] = res_list[key];
        }
        that.setData({
          order_list: order_list,
          cur_page: that.data.cur_page + 1,
        })
      } else {
        if (that.data.cur_page == 1) {
          that.setData({
            hasOrder: false,
          })
        } else {
          that.setData({
            hasmore: 0,
            is_bottom:true
          })
          wx.showToast({
            title: '没有更多订单',
            icon: 'none'
          })
        }
      }
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
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

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
  onShareAppMessage: function(res) {
    var that = this;
    var item = res.target.dataset.item;
    if (item.pin_share_member_name==undefined){
      item.pin_share_member_name = ''
    }
    return {
      title: '你的好朋友' + item.pin_share_member_name + "超值推荐" + item.extend_order_goods[0].goods_price + "元拼团" + item.extend_order_goods[0].goods_name,
      path: '/pages/goodsDetails/goodsDetails?tuan_id=' + item.tuan_id + '&goods_id=' + item.extend_order_goods[0].goods_id,
      imageUrl: item.extend_order_goods[0].goods_image
    }
  },
  /**
   * 滑动切换tab
   */
  bindChange: function(e) {
    this.setData({
      order_list: [
      {},
      {},
      {},
      {},
      {}
      ]
    })
    if(this.data.change){
      return;
    }
    var that = this;
    var current = e.detail.current;
    if (e.detail.source == 'touch') {
      if (e.detail.current == 0 && that.data.currentTab > 1) {
        that.setData({
          currentTab: 0,
        });
      } else {
        that.setData({
          currentTab: current,
        })
      }
    }
    if (current == 0) {
      that.setData({
        state_type: "",
      })
    }
    if (current == 1) {
      that.setData({
        state_type: "state_new",//待付款
      })
    }
    if (current == 2) {
      that.setData({
        state_type: "state_wait",//待收货
      })
    }
    if (current == 3) {
      that.setData({
        state_type: "state_noeval",//已完成
      })
    }
    if (current == 4) {
      that.setData({
        state_type: "state_cancel",//已取消
      })
    }
    this.setData({
      cur_page: 1,
      hasmore: 1,
      is_bottom: false
    })
    that.initData();
  },

  tach_fas:function(){  //加载触摸时间限制
    this.setData({
      touched:false,
    })
  },
  /**
   * 点击tab切换
   */
  swichNav: function(e) {
    var that = this;
    that.setData({
      order_list: [
      {},
      {},
      {},
      {},
      {}
      ]
    })
    if (this.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
      var scrollLeftNumber = 0;
      var current = e.currentTarget.dataset.current;
      if (current > 6) {
        scrollLeftNumber = 6 * 130;
        scrollLeftNumber = scrollLeftNumber + 130 * (current - 6);
      }
      that.setData({
        currentTab: e.target.dataset.current,
        scrollLeftNumber: scrollLeftNumber
      })
    }
  },
  //跳转订单详情
  goOrderDetail: function(e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../orderDetail/orderDetail?order_id=' + item.order_id + '&tz_id=0',
    })
  },
  formSubmit(e){
    var that = this;
    console.log('formid', e.detail.formId);
    that.setData({
      form_id: e.detail.formId
    })
  },
  //支付
  payReq: function(e) {
    var that = this;
    var poass = false;
    console.log(e);
    var pay_sn = e.currentTarget.dataset.index;
    var item = e.currentTarget.dataset.item;
    console.log(item)
    if(item.list[0].order_type==3){ //自提订单
      poass = true;
    }
    if (item.list[0].tuan_id>0){ //是拼购的商品
      that.setData({
        is_pintuan :1,
        tuan_id:-1
      })
    }
    request.postUrl("buy.prepay_id", {
      pay_sn: pay_sn,
      open_id: wx.getStorageSync("open_id"),
      form_id: that.data.form_id,
    }, function(res) {
      if (res.data.code == 200) {
        that.setData({
          order_info: res.data.datas.order_info
        })
        wx.requestPayment({
          'timeStamp': res.data.datas.pay_info.timeStamp + "",
          'nonceStr': res.data.datas.pay_info.nonceStr,
          'package': res.data.datas.pay_info.package,
          'signType': 'MD5',
          'paySign': res.data.datas.pay_info.paySign,
          'success': function(res) {
            console.log("支付=", res);
            wx.redirectTo({
              url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 1 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan,
            })
          },
          'fail': function(res) {
            wx.redirectTo({
              url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 2 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan,
            })
          }
        })
      } else {
        wx.showToast({
          title: res.data.datas.error,
          icon: 'none',
        })
      }
    })
  },
  cancelOrder: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var item = order_list[order_index].list[index];
    var pay_sn = item.pay_sn;
    wx.showModal({
      title: '提示',
      content: '确认要取消该订单?',
      success: function(res) {
        if (res.confirm) {
          request.postUrl("member_order.order_cancel", {
            order_id: item.order_id,
          }, function(res) {
            if (res.data.code == 200) {
              request.postUrl("member_order.order_list", {
                // shequ_tuan_id:100,
                page: that.data.cur_page,
                state_type: that.data.state_type,
              }, function(res) {
                var temp_list = res.data.datas.order_list;
                console.log(real_list[that.data.currentTab][order_index], temp_list[order_index]);
                real_list[that.data.currentTab][order_index] = temp_list[order_index];
                if (res.data.datas.order_list.length == 0) {
                  that.setData({
                    hasOrder: false,
                  })
                }
                that.setData({
                  order_list: real_list,
                })
                wx.showToast({
                  title: '取消成功',
                  icon: 'none'
                })
              })
            } else {
              wx.showToast({
                title: res.data.datas.error,
                icon: 'none'
              })
            }
          })
        }
      }
    })
  },
  delOrder: function(e) {
    var that = this;
    var index = e.currentTarget.dataset.index;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var item = order_list[order_index].list[index];
    console.log(item);
    var pay_sn = item.pay_sn;
    wx.showModal({
      title: '提示',
      content: '确认要删除该订单?',
      success: function(res) {
        if (res.confirm) {
          request.postUrl("member_order.order_delete", {
            order_id: item.order_id,
          }, function(res) {
            if (res.data.code == 200) {
              request.postUrl("member_order.order_list", {
                // shequ_tuan_id:100,
                page: that.data.cur_page,
                state_type: that.data.state_type,
              }, function(res) {
                var temp_list = res.data.datas.order_list;
                console.log(real_list[that.data.currentTab][order_index], temp_list[order_index]);
                real_list[that.data.currentTab][order_index] = temp_list[order_index];
                that.setData({
                  order_list: real_list,
                })
                wx.showToast({
                  title: '删除成功',
                  icon: 'none'
                })
              })
            }else{
              wx.showToast({
                title: res.data.datas.error,
                icon: 'none'
              })
            }
          })
        }
      }
    })
  },
  evaluate: function(e) { // 评价订单
    var that = this;
    var index = e.currentTarget.dataset.index;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var item = order_list[order_index].list[index];
    var list_recid = item.extend_order_goods;
    var rec_id = [];
    for (var l of list_recid) {
      rec_id.push(l.rec_id)
    }
    wx.navigateTo({
      url: '../commentOrder/commentOrder?order_id=' + JSON.stringify(item.order_id) + '&rec_id=' + rec_id,
    })
  },
  quitOrder: function(e) { //查看物流
    var that = this;
    var index = e.currentTarget.dataset.index;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var item = order_list[order_index].list[index];
    console.log(item);
    wx.navigateTo({
      url: '../distributionInfo/distributionInfo?order_id=' + JSON.stringify(item.order_id),
    })
  },
  refund: function(e) { //退款
    var order_id = e.currentTarget.dataset.index;
    var refund = e.currentTarget.dataset.refund;
    var order_goods_id = e.currentTarget.dataset.orderid;
    console.log(order_goods_id)
    wx.navigateTo({
      url: '../getRefund/getRefund?order_id=' + order_id + '&refund=' + refund + '&order_goods_id=' + order_goods_id,
    })
  },
  chain: function(e) { //自提
    var that = this;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var order_id = e.currentTarget.dataset.index;
    wx.showModal({
      title: '提示',
      content: '是否确认自提收货',
      success: function(res) {
        if (res.confirm) {
          request.postUrl('member_order.pickup_parcel', {
            order_id: order_id
          }, function(res) {
            if (res.data.code != 200) {
              wx.showToast({
                title: res.data.error,
              })
            } else {
              request.postUrl("member_order.order_list", {
                // shequ_tuan_id:100,
                page: that.data.cur_page,
                state_type: that.data.state_type,
              }, function (res) {
                var temp_list = res.data.datas.order_list;
                real_list[that.data.currentTab][order_index] = temp_list[order_index];
                if (res.data.datas.order_list.length == 0) {
                  that.setData({
                    hasOrder: false,
                  })
                }
                that.setData({
                  order_list: real_list,
                })
                wx.showToast({
                  title: '提货成功',
                })
              })
            }
          })
        } else if (res.cancel) {}
      }
    })

  },
  orderRec: function(e) { //确认收货
    var that = this;
    var index = e.currentTarget.dataset.index;
    var order_index = e.currentTarget.dataset.order_index;
    var real_list = that.data.order_list;
    var order_list = that.data.order_list[that.data.currentTab];
    var item = order_list[order_index].list[index];
    console.log(item);
    var pay_sn = item.pay_sn;
    wx.showModal({
      title: '提示',
      content: '确认收货该订单?',
      success: function(res) {
        if (res.confirm) {
          request.postUrl("member_order.order_receive", {
            order_id: item.order_id,
          }, function(res) {
            if (res.data.code == 200) {
              request.postUrl("member_order.order_list", {
                // shequ_tuan_id:100,
                pay_sn: pay_sn,
              }, function(res) {
                var temp_list = res.data.datas.order_list;
                console.log(real_list[that.data.currentTab][order_index], temp_list[order_index]);
                real_list[that.data.currentTab][order_index] = temp_list[order_index];
                that.setData({
                  order_list: real_list,
                })
                wx.showToast({
                  title: '确认收货成功',
                  icon: 'none'
                })
              })
            }else{
              wx.showToast({
                title: res.data.datas.error,
                icon: 'none'
              })
            }
          })
        }
      }
    })


  },
  goShopDetail: function(e) { //店铺详情
    var item = e.currentTarget.dataset.item;
    console.log(item);

    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + item.store_id,
    })

  },
  goClass: function() { //去购买
    console.log("111");
    wx.switchTab({
      url: '../classify/classify',
    })
  },
})