//index.js
var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
var common = require('../../utils/common.js');
const app = getApp()
Page({
  data: {
    bannerImg: '', //banner图片
    indexList: [],
    cur_time: "",
    xs_time: "",
    interval: "",
    xs_list_time: "",
    xianshi_more: "",
    xs_cur_time: "",
    xs_more_time: "",
    cur_index: "", //限时秒杀当前显示样式
    Atatus: 0,
    Istrue: true, //自动轮播
    xs_m_time: '',
    layer_data: [],// 弹出层传入数据
  },
  onLoad: function() {
    
  },
  onUnload: function () {
    clearInterval(this.data.interval)
  },
  onShow() {
    // if (app.Atatus == 1 || wx.getStorageSync("user_token")) {
    //   this.setData({
    //     Atatus: 1
    //   })
    // }
    var that = this;
    that.getIndex();
    that.initRed() //气泡
  },
  getIndex: function() {
    var that = this;
    request.postUrl("index.index2", {}, function(res) {
      if (res.data.code == 200) {
        // 停止下拉动作
        wx.stopPullDownRefresh();
        var indexList = res.data.datas;
        console.log(indexList)
        that.setData({
          indexList: indexList
        })
        for (let item of indexList) {
          if (item.type == 'adv_list') {
            if (item.list.length <= 1) {
              that.setData({
                Istrue: false,
              })
            }
            that.setData({
              bannerImg: item.list
            })
          } else {}


          // 新版限时秒杀
          if (item.type == "home6" && item.xian_shi.hasOwnProperty("xianshi_id")) { 
            var temp =  item.xian_shi
            if (parseInt(item.current_time) > parseInt(temp.end_time)) {
              that.setData({
                xs_m_time: common.XsgetTime(0),
                cur_time: 0,
              })
              continue;
            }
            clearInterval(that.data.interval);
            var xianshi_end_time = parseInt(temp.end_time) - parseInt(item.current_time)
            var xs_m_time = common.XsgetTime(xianshi_end_time);
            that.setData({
              xs_m_time: xs_m_time,
              cur_time: xianshi_end_time
            })
            console.log(xs_m_time)
            var interval = setInterval(function () {
              var xs_m_time = common.XsgetTime(that.data.cur_time);
              if (that.data.cur_time == 0) { //重新加载
                that.onLoad()
              }
              that.setData({
                xs_m_time: xs_m_time,
                cur_time: that.data.cur_time - 1
              })
            }, 1000);
            that.data.interval = interval;
          }

          if (item.type == "miaosha_more" && item.list.current_xianshi_data.hasOwnProperty("xianshi_id")) {
            var temp = item.list.current_xianshi_data;
            var end_time = parseInt(temp.end_time);
            var start_time = parseInt(temp.start_time);
            var now_time = parseInt(item.list.now_time);
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
                xs_more_time: util.getTime(temp.my_time),
                xs_cur_time: temp.my_time,
              })
              continue;
            }
            that.setData({
              xs_more_time: util.getTime(temp.my_time),
              xs_cur_time: temp.my_time,
            })
            clearInterval(that.data.interval);
            var interval = setInterval(function() {
              if (that.data.xs_cur_time <= 0) {
                that.getIndex();
                clearInterval(that.data.interval);
                return;
              }
              var xs_more_time = util.getTime(that.data.xs_cur_time);
              that.setData({
                xs_more_time: xs_more_time,
                xs_cur_time: that.data.xs_cur_time - 1,
              })
            }, 1000);
            that.data.interval = interval;
          }

          if (item.type == "miaosha" && item.list.item.info.hasOwnProperty("xianshi_id")) {
            var temp = item.list.item.info;
            var end_time = parseInt(temp.end_time);
            var start_time = parseInt(temp.start_time);
            var now_time = common.getTimestamp();
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
                xs_time: common.getTime(temp.my_time),
                cur_time: temp.my_time,
              })
              continue;
            }
            that.setData({
              xs_time: common.getTime(temp.my_time),
              cur_time: temp.my_time,
            })
            clearInterval(that.data.interval);
            var interval = setInterval(function() {
              if (that.data.cur_time <= 0) {
                that.getIndex();
                clearInterval(that.data.interval);
                return;
              }
              var xs_time = common.getTime(that.data.cur_time);
              that.setData({
                xs_time: xs_time,
                cur_time: that.data.cur_time - 1,
              })
            }, 1000);
            that.data.interval = interval;
          }
          
          if (item.type == "layer") {//弹出层
            var layer_data = that.data.layer_data;
            //var layer_type = that.data.layer_type;
            //layer_data[item.layer_type] = item.item;
            layer_data = []
            layer_data.push(item)
            that.setData({
              layer_data: layer_data,
              //layer_type: layer_type
            })
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
  stopTouchMove: function() {
    var that = this;
    if (that.data.bannerImg.length <= 1) {
      return false
    }
  },

  goSearch: function() {
    wx.navigateTo({
      url: '../search/search',
    })
  },
  //更新购物车气泡数
  initRed: function() {
    var that = this;
    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
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
    } else {
      request.postUrl('cart.count', {}, function(res) {
        if (res) {
          if (res.data.code != 200 || res.data.datas.count == 0) {
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
    }
  },
  scanf: function() {
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
  // 更多秒杀
  moreLimit: function(e) {
    var xian_shi_ids = e.currentTarget.dataset.ids;
    var current_id = e.currentTarget.dataset.current_id;
    wx.navigateTo({
      url: '../limit_goods/limit_goods?xian_shi_ids=' + xian_shi_ids + '&current_id=' + current_id
    })
  },
  goGoodsDetail: function(e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  },
  goClass: function(e) {
    console.log(e)
    var item = e.currentTarget.dataset.class;
    var calssType = e.currentTarget.dataset.type;
    if (calssType == 'cate') {
      if (item == '') {
        app.class_id = 0;
        wx.switchTab({
          url: '../classify/classify'
        })
        return;
      } else {
        request.postUrl('goods_class.class_info', {
          cate_id: item
        }, function(res) {
          if (res.data.code != 200) {
            wx.switchTab({
              url: '../classify/classify'
            })
            return;
          }
          if (res.data.datas.parent_class == 1) {
            app.class_id = item;
            wx.switchTab({
              url: '../classify/classify'
            })
          } else if (res.data.datas.parent_class == 2) { //二级分类全部展示
            wx.navigateTo({
              url: '../classified/classified?gc_id_1=' + res.data.datas.gc_parent_id + '&gc_id_2=' + item,
            })
          } else if (res.data.datas.parent_class == 3) { //三级分类展示全部 选中展示的三级分类商品
            wx.navigateTo({
              url: '../classified/classified?gc_id_3=' + item + '&gc_id_2=' + res.data.datas.gc_parent_id,
            })
          }

        })
      }
    } else if (calssType == 'wei_msg') {
      wx.showToast({
        title: item,
        icon: 'none'
      })
      return false;

    } else if (calssType == 'wei_url') {
      wx.navigateTo({
        url: item,
      })
    } else if (calssType == 'wei_xianshi') {
        var xian_shi_ids_arr = item;
        if (xian_shi_ids_arr.length <= 0) {
            return false;
        }
        xian_shi_ids_arr = xian_shi_ids_arr.split(",");
        wx.navigateTo({
            url: '../limit_goods/limit_goods?xian_shi_ids=' + item + '&current_id=' + xian_shi_ids_arr[0]
        })
    }
    else if (calssType == 'wei_pyramid') {
      if (wx.getStorageSync('user_token') == '') {
        wx.showToast({
          title: '请登录'
        })
        return
      }

      wx.navigateTo({
        url: '../distributionCenter/distributionCenter',
      })
    }

  },
  GoSome: function(e) {
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

    if (mytype == 'wei_url') {
      if (data == "") {
        return;
      }
      
      wx.navigateTo({
        url: data,
      })
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
        tid: data
      }, function(res) {
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
      }, function(res) {
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
  //下拉刷新
  onPullDownRefresh: function (e) {
    var that = this;
    that.getIndex();
  },
})