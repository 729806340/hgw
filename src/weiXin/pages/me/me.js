var request = require('../../utils/request.js');

Page({
  data: {
    show_charge:2, // 1开  2关
    has_login: '',
    member_name: '',
    phone: '',
    sortList: [
      {
        sortName: '待付款',
        sortUrl: '/weixinImg/assets/daizhifu.png',
        tab: '1',
        orderCount:0
      },
      {
        sortName: '待收货',
        sortUrl: '/weixinImg/assets/daishouhuo.png',
        tab: '2',
        orderCount:0
      },
      {
        sortName: '已完成',
        sortUrl: '/weixinImg/assets/yiwancheng.png',
        tab: '3',
        orderCount:0
      },
      {
        sortName: '已取消',
        sortUrl: '/weixinImg/assets/yiquxiao.png',
        tab: '4',
        orderCount:0
      },
      {
        sortName: '退款/售后',
        sortUrl: '/weixinImg/assets/Shape.png',
        tab: '9',
        orderCount:0
      }
    ],
    member_points: 0,
    available_predeposit: 0,
    available_rc_balance: 0,
    rpt_num: 0,
    voucher_num: 0,
    level_name: 'v1',
    member_avatar: 'http://www.hangowa.com/data/upload/shop/common/default_user_portrait.gif',
    tid: 0,
    is_pyramid: '',
    fnList:[
      {
        fnName:'我的开团',
        fnUrl:'../../weixinImg/assets/lishidingdan.png',
        fnPath:'../my_tuanList/my_tuanList'
      },
      {
        fnName:'签收码',
        fnUrl:'../../weixinImg/assets/icon1.png',
        fnPath:'../signIn/signIn'
      },
      {
        fnName:'到货提醒',
        fnUrl:'../../weixinImg/assets/tixing.png',
        fnPath:'../arrival_reminder/arrival_reminder'
      },
      {
        fnName:'扫码提货',
        fnUrl:'../../weixinImg/assets/saoma.png',
        fnPath:'../scanning_code/scanning_code'
      },
      // {
      //   fnName:'配送跟踪',
      //   fnUrl:'../../weixinImg/assets/distribution.png',
      //   fnPath:'../distribution/distribution'
      // },
      {
        fnName:'待提货订单',
        fnUrl:'../../weixinImg/assets/dingdan.png',
        fnPath:'../delivery_goods/delivery_goods'
      },
      {
        fnName:'售后管理',
        fnUrl:'../../weixinImg/assets/shouhoufuwu-zidongpingjia.png',
        fnPath:'../quitList/quitList'
      },
      {
        fnName:'送货预告',
        fnUrl:'../../weixinImg/assets/yckbg_img@2x.png',
        fnPath:'../delivery_notice/delivery_notice'
      },
      {
        fnName:'佣金记录',
        fnUrl:'../../weixinImg/assets/345.png',
        fnPath:'../commission_record/commission_record'
      },
      {
        fnName:'团员管理',
        fnUrl:'../../weixinImg/assets/345634534.png',
        fnPath:'../my_tuanUser/my_tuanUser'
      },
      {
        fnName:'活动海报',
        fnUrl:'../../weixinImg/assets/123.png',
        fnPath:'../my_tuanPoster/my_tuanPoster'
      },
    ],
    member_info:'',
    order_count_num:'',
    dataList:[],//金额数据

    achievement:true,
    commission:true,
  },
  onLoad: function (options) {
    var that = this
    if (options.tid) {
      that.setData({
        tid: options.tid
      })

      if (wx.getStorageSync('user_token')) {
        that.scanQRCode(options.tid)
      }
    }
  },

  onShow: function() {
    var that = this;
    var user_token = wx.getStorageSync("user_token");
    that.initRed() //气泡
    if (user_token) {
      that.showInfo();
      that.getLeader();
    } else {
      wx.showToast({
        title: "请授权登陆~"
      })
    }
  },
//展示团长中心信息统计
  getLeader:function(){
    var that = this;
    request.postUrl('shequ_tuan_info.bill_info', {},
      function (res) {
        if (res.data.code == '200') {
          console.log(res)
          that.setData({
            dataList:res.data.datas
          })
        }
      })
  },


  scanQRCode: function(e) {
    request.postUrl('member_redpacket.rpt_free',
      { tid: e },
      function (res) {
        if (res.data.code != 200) {
          wx.showToast({
            title: res.data.datas.error,
            duration: 1000
          });

          return;
        }

        wx.showToast({
          title: '领券成功',
          duration: 1000
        })

        setTimeout(function () {
          wx.navigateTo({
            url: '../redPocket/redPocket'
          });
        }, 1000)
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
                  wx.setStorageSync("temp_goods", "");
                  /*wx.switchTab({
                             url: '../me/me'
                         });*/
                  that.showInfo();
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
                    dealer_id: dealer_id
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
                      wx.setStorageSync("user_token", res1.data.datas.user_token);
                      wx.hideLoading();
                      wx.setStorageSync("temp_goods", "");
                      /*wx.switchTab({
                          url: '../me/me'
                      });*/
                      that.showInfo();
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

  //展示用户数据
  showInfo: function() {
    var that = this;
    request.postUrl("member_index.index", {
      wx_user_avatar: wx.getStorageSync("user_img"),
      wx_nick_name: wx.getStorageSync("nick_name"),
    }, function(res) {
      if(res.data.code == 10001){ //登陆已过期
        wx.setStorageSync("user_token",'');
        wx.showToast({
          title: '登陆已过期,请重新登陆',
          icon: "none",
        })
        that.setData({
          has_login:'',
        })
        return;
      }
      if (res.data.code == 200) {
        if(res.data.datas.show_charge){
          that.setData({
            show_charge:res.data.datas.show_charge
          })
        }
        var sortList = that.data.sortList;
        var order_count_num = res.data.datas.member_info.order_count_num;
        sortList[0].orderCount = order_count_num.new_count;
        sortList[1].orderCount = order_count_num.wait_count;
        sortList[2].orderCount = order_count_num.completed_count;
        sortList[3].orderCount = order_count_num.cancel_count;
        sortList[4].orderCount = order_count_num.after_count
        that.setData({
          has_login: true,
          member_name: res.data.datas.member_info.user_name,
          phone: res.data.datas.member_info.phone,
          member_points: res.data.datas.member_info.point,
          available_predeposit: res.data.datas.member_info.predepoit,
          available_rc_balance: res.data.datas.member_info.available_rc_balance,
          // rpt_num: res.data.datas.member_info.rpt_num,
          // voucher_num: res.data.datas.member_info.voucher_num,
          level_name: res.data.datas.member_info.level_name,
          member_avatar: wx.getStorageSync("user_img"),
          sortList: sortList,
          // is_pyramid: res.data.datas.member_info.is_pyramid,

          member_info:res.data.datas.member_info
        })
        wx.setStorageSync("is_shequ_tuanzhang", res.data.datas.member_info.is_shequ_tuanzhang);
        if(res.data.datas.member_info.is_shequ_tuanzhang == 2){
          wx.setStorageSync("tuanzhang_id", res.data.datas.member_info.tuanzhang_id);
        }else{
          if(res.data.datas.member_info.default_shequ_tuanzhang_id != 0){
            wx.setStorageSync("tuanzhang_id", res.data.datas.member_info.default_shequ_tuanzhang_id)
          }
        }
        that.initRed() //气泡
      }
    })
  },
  //我的财产
  goMyProperty: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../myProperty/myProperty'
      });
    }
    return;
  },
  //收货地址
  address: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../address/address?flag=' + 1
      });
    }
  },
  //绑定手机
  bindTelephone: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../telephone/telephone?phone=' + this.data.phone
      });
    }
  },

  //预存款
  preSave: function() {
    // if (wx.getStorageSync("user_token")) {
    //   wx.navigateTo({
    //     url: '../preSave_new/preSave_new'
    //   });
    // }
  },
  //充值卡余额
  chargeCard: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../chargeCard_new_2/chargeCard_new_2?available_rc_balance=' + this.data.available_rc_balance
      });
    }
  },
  //代金券
  cashCoupon: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../cashCoupon/cashCoupon'
      });
    }
  },
  //红包
  redPocket: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../redPocket/redPocket'
      });
    }
  },
  //积分
  integration: function() {
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../integration/integration'
      });
    }
  },
  // 商品收藏
  goodsCollect: function() {
    var that = this;
    wx.navigateTo({
      url: '../goodsCollect2/goodsCollect2',
    })
  },
  // 店铺收藏
  storeCollect: function() {
    var that = this;
    wx.navigateTo({
      url: '../storeCollect2/storeCollect2',
    })
  },
  // 分销中心
  distributionCenter: function () {
    if (wx.getStorageSync('user_token') == '') {
      wx.showToast({
        title: '请登录'
      })
      return
    }

    wx.navigateTo({
      url: '../distributionCenter/distributionCenter',
    })
  },

  gotoOrder() {
    if (wx.getStorageSync('user_token') == '') {
      wx.showToast({
        title: '请登录'
      })
      return
    }

    if (this.data.is_pyramid == '0') {
      wx.navigateTo({
        url: '../distributionCenter/distributionCenter',
      })
      return
    }
    
    wx.navigateTo({
      url: '../distributionOrder/distributionOrder'
    });
  },

  gotoRecord() {
    if (wx.getStorageSync('user_token') == '') {
      wx.showToast({
        title: '请登录'
      })
      return
    }

    if (this.data.is_pyramid == '0') {
      wx.navigateTo({
        url: '../distributionCenter/distributionCenter',
      })
      return
    }

    wx.navigateTo({
      url: '../withdrawRecord/withdrawRecord'
    });
  },

  // 我的足迹
  viewHistory() {
    wx.navigateTo({
      url: '../viewHistory/viewHistory',
    })
  },
  // 系统消息
  systemInfo: function() {
    var that = this;
    wx.navigateTo({
      url: '../systemInfo/systemInfo',
    })
  },
  goMyOrder: function(e) {
    // console.log((parseInt(e.currentTarget.dataset.index) + 1), e.currentTarget.dataset.index)
    var index = e.currentTarget.dataset.index;
    var sortList = this.data.sortList
    if (wx.getStorageSync("user_token")) {
      if (index == -1) {
        wx.navigateTo({
          url: '../myOrder_s/myOrder_s?currentTab=' + 0,
        })
        return;
      }
      if (index != 4) {
        wx.navigateTo({
          url: '../myOrder_s/myOrder_s?currentTab=' + sortList[index].tab,
        })
        return;
      }
      if (index == 4) {
        wx.navigateTo({
          url: '../quitList/quitList',
        })
        return;
      }
      wx.navigateTo({
        // url: '../myOrder/myOrder?currentTab=' + (parseInt(index) + 1),
        url: '../myOrder/myOrder?currentTab=' + index,
      })
    } else {
      wx.showToast({
        title: '请登录'
      })
    }
  },
  //更新购物车气泡数
  initRed: function() {
    var that = this;
    if (wx.getStorageSync("user_token")) {
      request.postUrl('cart.count_shequ', {}, function(res) {
        if (res.data.code == 200) {
          if (res.data.datas.count == 0) {
            wx.hideTabBarRedDot({
              index: 2,
            })
          } else {
            wx.setTabBarBadge({
              index: 2,
              text: (res.data.datas.count + ""),
            })
          }
        }
      })
    } else {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
      var flag = true;
      console.log("temp_goods", temp_goods);
      if (temp_goods != '') {
        sum = temp_goods.length;
        wx.setTabBarBadge({
          index: 2,
          text: sum + "",
        })
      } else {
        wx.hideTabBarRedDot({
          index: 2,
        })
      }
    }
  },
    // //跳团长页面
    // goTuan(){
    //   wx.navigateTo({
    //     url: '../community/community',
    //   })
    // },


  goCommission(){
    wx.navigateTo({
      url: '../my_commission/my_commission',
    })
  },
  goSales(){
    wx.navigateTo({
      url: '../my_sales/my_sales',
    })
  },
  taunFnpath(e){
    var that = this;
    var path = e.currentTarget.dataset.path
    if(path == '../quitList/quitList'){
      wx.navigateTo({
        url: '../quitList/quitList?tz_id=' + that.data.member_info.tuanzhang_id,
      })
      return
    }
    wx.navigateTo({
      url: path,
    })
  },
  goApply(){
    if(this.data.member_info.is_shequ_tuanzhang == 0){
      wx.navigateTo({
        url: '../apply_TZ/apply_TZ',
      })
    }
  },
  previewImage(){//src="{{member_info.fetch_qr}}"
    var imgUrl = this.data.member_info.fetch_qr;
    wx.previewImage({
      current: imgUrl, // 当前显示图片的http链接
      urls: [imgUrl] // 需要预览的图片http链接列表
    })
  },
  yanjing(e){
    var icon = e.currentTarget.dataset.icon
    if(icon == 0){
      this.setData({
        commission:!this.data.commission
      })
    }
    if(icon == 1){
      this.setData({
        achievement:!this.data.achievement
      })
    }
  },
});