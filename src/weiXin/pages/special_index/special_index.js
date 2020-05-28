//index.js
var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
const app = getApp()

Page({
  data: {
    bannerImg: '', //banner图片
    indexList: [],
    cur_time: "",
    xs_time: "",
    interval: "",
    special_id: 0,
    xs_list_time: "",
    xianshi_more: "",
    cur_index: "", //限时秒杀当前显示样式
    special_background :'', //背景色
    special_desc : '', //标题头部
  },

  onLoad: function (options) {
    var that = this;
    var special_id = parseInt(options.special_id);
    if (special_id <= 0) {
        wx.switchTab({
            url: '../index/index'
        });
    }
    that.setData({
        special_id: special_id
    });
    that.getIndex();
  },
  getIndex: function () {
    var that = this;
    request.postUrl("index.index_special2", {
        special_id: that.data.special_id
    }, function (res) {
      if (res.data.code == 200) {
        var indexList = res.data.datas.list;
        var special_background = res.data.datas.special_background;
        var special_desc = res.data.datas.special_desc;
        console.log(indexList)
        if (special_desc !=''){
          wx.setNavigationBarTitle({ //设置头部标题
            title: special_desc,
          })
        }
        that.setData({
          indexList: indexList,
          special_background: special_background,
        })
        for (let item of indexList) {
          if (item.type == 'adv_list') {
            that.setData({
              bannerImg: item.list
            })
          } else { }
          if (item.type == "miaosha" && item.list.item.info.hasOwnProperty("xianshi_id")) {
            var temp = item.list.item.info;
            var end_time = parseInt(temp.end_time);
            var start_time = parseInt(temp.start_time);
            var now_time = parseInt(temp.now_time);
            if (now_time < start_time) {
              temp.my_text = "距开场还剩";
              temp.my_time = parseInt((start_time - now_time));
            }
            if (now_time >= start_time && now_time <= end_time) {
              temp.my_text = "距结束还剩"
              temp.my_time = parseInt((end_time - now_time));
            }
            if (now_time > end_time) {
              temp.my_text = "活动已结束"
              temp.my_time = 0;
              that.setData({
                xs_time: util.getTime(temp.my_time),
                cur_time: temp.my_time,
              })
              continue;
            }
            that.setData({
              xs_time: util.getTime(temp.my_time),
              cur_time: temp.my_time,
            })
            clearInterval(that.data.interval);
            var interval = setInterval(function () {
              if (that.data.cur_time <= 0) {
                that.getIndex();
                clearInterval(that.data.interval);
                return;
              }
              var xs_time = util.getTime(that.data.cur_time);
              that.setData({
                xs_time: xs_time,
                cur_time: that.data.cur_time - 1,
              })
            }, 1000);
            that.data.interval = interval;
          }
        }
        that.setData({
          indexList: indexList,
          current: 0,
        })
      }
    })
    // that.initRed();
  },

  // 轮播图禁止左右滑动
  stopTouchMove:function(){
    var that = this;
    if (that.data.bannerImg<=1){
      console.log('false')
      return false
    }
  },
  goSearch: function () {
    wx.navigateTo({
      url: '../search/search',
    })
  },
  //更新购物车气泡数
  initRed: function () {
    var that = this;
    if (wx.getStorageSync('token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
      if (temp_goods != '') {
        for (var i = 0; i < temp_goods.length; i++) {
          sum = sum + temp_goods[i].quantity;
        }
      }

      if (sum == 0) {
        wx.hideTabBarRedDot({
          index: 2,
        })
      } else {
        wx.setTabBarBadge({
          index: 2,
          text: sum + "",
        })
      }

    } else {
      request.postUrl('', {}, function (res) {
        if (res) {
          if (res.data.code != 200 || res.data.datas.count.goods_total_num == 0) {
            wx.hideTabBarRedDot({
              index: 2,
            })
          } else {
            wx.setTabBarBadge({
              index: 2,
              text: (res.data.datas.count.goods_total_num + ""),
            })
          }
        }
      })
    }

  },
  scanf: function () {
    // 允许从相机和相册扫码
    wx.scanCode({
      success: (res) => {
        if (res.path) {
          wx.navigateTo({
            url: '/' + res.path,
          })
        }

      }
    })

  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  },
  goClass: function (e) {
    var item = e.currentTarget.dataset.class;
    app.class_id = item;
    wx.switchTab({
      url: '../classify/classify'
    })
  },
  GoSome: function (e) {
    console.log(e)
    var mytype = e.currentTarget.dataset.type;
    var data = e.currentTarget.dataset.data;

    //跳搜索
    if (mytype == "keyword") {
      if (data == "") {
        return;
      }
      wx.navigateTo({
        url: '../search/search?name=' + data,
      })
      return;

    }

    //跳分销中心
    if (mytype == "wei_pyramid") {
      if (wx.getStorageSync('user_token') == '') {
        wx.showToast({
          title: '请登录'
        })
        return
      }

      wx.navigateTo({
        url: '../distributionCenter/distributionCenter',
      })

      return;
    }

    //跳分类
    if (mytype == "category") {
      if (data == "") {
        return;
      }
      app.class_id = data;
      wx.switchTab({
        url: '../classify/classify'
      })
      return;

    }
    //跳专题
    if (mytype == "special") {
      if (data == "") {
        return;
      }
      
      var that = this
      if (data == that.data.special_id) {
        if (wx.pageScrollTo) {
          wx.pageScrollTo({
            scrollTop: 0,
            duration: 300
          })
        }

        return
      }

      wx.navigateTo({
        url: '../special_index/special_index?special_id=' + data,
      })
      return;
    }
    //跳商品
    if (mytype == "goods") {
      if (data == "") {
        return;
      }
      var item = {
        goods_id: data,
      };
      wx.navigateTo({
        url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
      })
      return;

    }
    //跳店铺
    if (mytype == "store") {
      if (data == "") {
        return;
      }
      wx.navigateTo({
        url: '../shopDetails/shopDetails?store_id=' + data
      })
      return;

    }
    //优惠券
    if (mytype == "voucher") {
      if (data == "") {
        return;
      }
      if (wx.getStorageSync('user_token') == '') {
        wx.switchTab({
          url: '../me/me',
        })
        return;
      }
      request.postUrl('member_voucher.voucher_freeex', {
        packet_id: data
      }, function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '领取优惠券成功！',
            icon: 'none'
          })
        } else {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none'
          })
        }

      })
      return;

    }
    //红包
    if (mytype == "red_packet") {
      if (data == "") {
        return;
      }
      if (wx.getStorageSync('user_token') == '') {
        wx.switchTab({
          url: '../me/me',
        })
        return;
      }
      request.postUrl('member_redpacket.rpt_free', {
        tid: data
      }, function (res) {
        if (res.data.code == 200) {
          wx.showToast({
            title: '领取红包成功！',
            icon: 'none'
          })
        } else {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none'
          })
        }

      })
      return;

    }
  },
})