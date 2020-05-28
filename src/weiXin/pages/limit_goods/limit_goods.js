var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    kill_list: [], //秒杀对象列表
    goods_list: [], //商品列表
    interval: "", //倒计时对象
    current_xianshi_id: 0, //选中的限时描述id
    xian_shi_ids: '', //秒杀列表中秒杀id集合
    current_Status: 1, //选中限时秒杀 默认状态 1已结束 2未开始或者开始
    current_my_text: '', //选中限时秒杀开始未开始文字说明
    rest_time: '', //剩余时间
    start_time: 0, //选中倒计时对象的开始时间
    end_time: 0,//选中倒计时对象的结束时间
    quantities:0,//购车从数量总数
    top_img:'',//头部图片
    last_xianshi: [] //限时数量小于3时
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    var xian_shi_ids_arr = decodeURIComponent(options.xian_shi_ids)
    var current_xianshi_id = options.current_id;
    if (!xian_shi_ids_arr || !current_xianshi_id) {
      wx.switchTab({
         url: '../index/index'
      });
    }
    var xian_shi_ids = [];
    xian_shi_ids_arr = xian_shi_ids_arr.split(",");
    for (let n in xian_shi_ids_arr) {
      xian_shi_ids.push(parseInt(xian_shi_ids_arr[n]));
    }
    that.cart_Ount()//购物车气泡
    that.setData({
        current_xianshi_id: current_xianshi_id,
        xian_shi_ids: JSON.stringify(xian_shi_ids)
    });
    console.log(xian_shi_ids_arr)
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function(e) {
    //获取秒杀列表
    this.getSecondKillList();
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

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

  cart_Ount(){
    var that = this;
    request.postUrl('cart.count', {}, function (res) {
      if (res) {
        if (res.data.code != 200 || res.data.datas.count == 0) {
          that.setData({
            quantities: 0
          })
        } else {
          that.setData({
            quantities: res.data.datas.count,
          })
        }
      }
    })
  },
  //点击减号
  bindMinus: function(e) {
    if (!wx.getStorageSync('user_token')) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    var that = this;
    var item = e.currentTarget.dataset.item;
    var index = e.currentTarget.dataset.index;
    var goods_list = that.data.goods_list;
    if (item.cart_num - 1 == 0) {
      request.postUrl("cart.delete", {
        goods_id: item.goods_id,
      }, function(res) {
        if (res && res.data.code && res.data.code == 200) {
          goods_list[index].cart_num = 0;
          that.setData({
            goods_list: goods_list,
            quantities: parseInt(that.data.quantities) - 1 ,
          });
        }
      })
    } else {
      request.postUrl('cart.add', {
        goods_id: item.goods_id,
        quantity: item.cart_num - 1,
      }, function(res) {
        if (res && res.data.code && res.data.code == 200) {
          goods_list[index].cart_num = parseInt(item.cart_num) - 1;
          that.setData({
            goods_list: goods_list,
          });
          that.cart_Ount()//购物车气泡
        }
      })
    }

  },

  /* 点击加号 */
  bindPlus: function(e) {
    if (!wx.getStorageSync('user_token')) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    var that = this;
    var item = e.currentTarget.dataset.item;
    var index = e.currentTarget.dataset.index;
    var goods_list = that.data.goods_list;
    if (parseInt(item.cart_num) + 1 > parseInt(that.data.goods_storage)) {
      wx.showToast({
        title: '商品所剩无几了'
      });
      return;
    }
    request.postUrl('cart.add', {
      goods_id: item.goods_id,
      quantity: parseInt(item.cart_num) + 1
    }, function(res) {
      if (res && res.data.code && res.data.code == 200) {
        goods_list[index].cart_num = parseInt(item.cart_num) + 1;
        that.setData({
          goods_list: goods_list,
        });
        that.cart_Ount()//购物车气泡
      }else{
        that.setData({
          goods_list: that.data.goods_list,
        });
        wx.showToast({
          title: res.data.datas.error,
          icon: 'none'
        })
      }
    })
  },
  /* 输入框事件 */
  bindManual: function(e) {
    if (!wx.getStorageSync('user_token')) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    var that = this;
    var item = e.currentTarget.dataset.item;
    var index = e.currentTarget.dataset.index;
    var goods_list = that.data.goods_list;
    // 将数值与状态写回
    if (e.detail.value <= 0) {
      e.detail.value = 1;
    }
    if (e.detail.value == '' || e.detail.value == 0) {
      e.detail.value = 1;
    }
    if (e.detail.value > item.goods_storage) {
      wx.showToast({
        title: '商品所剩无几了'
      });
      that.setData({
        goods_list: goods_list
      })
      return;
    }
    item.cart_num = parseInt(e.detail.value);
    request.postUrl('cart.add', {
      goods_id: item.goods_id,
      quantity: parseInt(e.detail.value),
    }, function(res) {
      if (res && res.data.code == 200) {
        goods_list[index].cart_num = parseInt(e.detail.value);
        that.setData({
          goods_list: goods_list,
        });
        that.cart_Ount()//购物车气泡
      } else {
        that.setData({
          goods_list: that.data.goods_list,
        });
        wx.showToast({
          title: res.data.datas.error,
          icon: 'none'
        })
      }
    })


  },
  /**
   * 跳转商品详情页面
   * @param e
   */
  goGoodsDetail: function(e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id
    })
  },
  goCart: function() {
    wx.switchTab({
      url: '../shoppingCar/shoppingCar',
    })
  },
  /**
   * 获取秒杀列表
   */
  getSecondKillList: function() {
    var that = this;
    request.postUrl('index.second_kill', {
      config_ids: that.data.xian_shi_ids
    }, function(res) {
      console.log(res)
      if (res.data.code == 200) {
        var killList = res.data.datas.kill_list;
        if (killList.length < 3) {
          that.data.last_xianshi = [];
          var name = {
            na: '敬请期待'
          }
          var num = 3 - killList.length;
          for (var l = 0; l < num; l++) {
            that.data.last_xianshi.push(name)
          }
          console.log(that.data.last_xianshi)
          that.setData({
            last_xianshi: that.data.last_xianshi
          })
        }
        that.setData({
          kill_list: killList
        });
        var current_xianshi_data = '';
        for (let i in killList) {
          if (killList[i]['config_xianshi_id'] == that.data.current_xianshi_id) {
            current_xianshi_data = killList[i];
          }
        }
        if (!current_xianshi_data) {
          return;
        }
        that.setData({
          start_time: parseInt(current_xianshi_data.start_time),
          end_time: parseInt(current_xianshi_data.end_time),
          top_img:res.data.datas.top_img,
        });
        //获取商品列表
        that.getGoodsList();
      }
    })
  },
  /**
   * 获取选中秒杀的商品列表
   */
  getGoodsList: function() {
    var that = this;
    request.postUrl('index.second_goods_list', {
      current_config_id: that.data.current_xianshi_id
    }, function(res) {
      if (res.data.code == 200) {
        var goodsList = res.data.datas.goods_list;
        var now_time = parseInt(res.data.datas.now_time);
        that.listInterval(that.data.start_time, that.data.end_time, now_time);
        that.setData({
          goods_list: goodsList
        });
      }
    })
  },
  /**
   * 切换选中不同描述
   * @param e
   */
  statusChange: function(e) {
    var that = this;
    var current_xianshi_data = e.currentTarget.dataset.item;
    that.setData({
      current_xianshi_id: current_xianshi_data.config_xianshi_id,
      start_time: parseInt(current_xianshi_data.start_time),
      end_time: parseInt(current_xianshi_data.end_time)
    });
    that.getGoodsList();
  },
  /**
   * 处理倒计时
   * @param start_time
   * @param end_time
   * @param now_time
   */
  listInterval: function(start_time, end_time, now_time) {
    var that = this;
    var rest_time = 0;
    if (now_time < start_time) {
      that.setData({
        current_Status: 3,
        current_my_text: "距开场还剩"
      });
      rest_time = parseInt((start_time - now_time));
    }
    if (now_time >= start_time && now_time <= end_time) {
      that.setData({
        current_Status: 2,
        current_my_text: "距结束还剩"
      });
      rest_time = parseInt((end_time - now_time));
    }
    if (now_time > end_time) {
      that.setData({
        current_Status: 1,
        current_my_text: "已结束，快去抢购下一场吧"
      });
      rest_time = 0;
    }
    that.setData({
      rest_time: util.getTime(rest_time)
    });
    clearInterval(that.data.interval);
    if (rest_time <= 0) {
        return;
    }
    var interval = setInterval(function() {
      if (rest_time <= 0) {
        that.getSecondKillList();
        clearInterval(that.data.interval);
        return;
      }
      rest_time = rest_time - 1;
      that.setData({
        rest_time: util.getTime(rest_time),
      })
    }, 1000);
    that.data.interval = interval;
  }

})