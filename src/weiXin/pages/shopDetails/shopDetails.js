var request = require('../../utils/request.js');
const app = getApp()

Page({
  data: {
    imgUrls: [], //焦点图
    indicatorDots: true,
    autoplay: true,
    interval: 3000,
    duration: 500,
    indicatorActiveColor: '#68D465',
    store_info: "",
    scrollLeftNumber: 0,
    store_id: 0,
    store_banner: '', //店铺banner
    store_name: '', //店铺名称
    store_avatar: '', //店铺头像
    is_favorate: false, //店铺是否收藏
    tab_index: 1, //选中栏
    voucher_list: [], //店铺优惠券
    voucher_more: false, //是否有更多优惠券
    goods_sales: [], //点评推荐商品
    gc_list: [], //店铺分类列表
    shop_gc_id: 0, //店铺默认选中的分类
    cur_page: 1, //全部商品列表 默认第一页
    shop_all_goods_list: [], //店铺全部商品列表
    all_is_bottom: false, //全部商品是否到底
    new_goods_list: [], //店铺新品
    store_z: '',
    top: 0,
  },

  onLoad: function(option) {
    var that = this;
    var store_id = 0;
    if (option.store_id) {
      store_id = option.store_id;
    }
    if (!store_id) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    that.setData({
      store_id: store_id
    });

    that.goShop();
  },
  //菜单滑动固定
  scrollTopFun: function(e) {
    var that = this;
    // console.log(e.detail.scrollTop)
    that.setData({
      top: e.detail.scrollTop
    })


  },
  //店铺首页
  goShop: function() {
    var that = this;
    that.setData({
      tab_index: 1
    });
    if (that.data.goods_sales.length > 0) {
      return;
    }
    that.getStoreInfo();
  },
  //全部商品
  goAllGoods: function() {
    var that = this;
    that.setData({
      tab_index: 2
    });
    if (that.data.shop_all_goods_list.length > 0) {
      return;
    }
    this.getAllGoodsList();
  },
  //新品上架
  goNewGoods: function() {
    var that = this;
    that.setData({
      tab_index: 3
    });
    that.getNewGoosList();
  },
  //获取店铺详情
  getStoreInfo: function() {
    var that = this;
    var store_id = that.data.store_id;
    request.postUrl('store.index', {
      store_id: store_id
    }, function(res) {
      var storeId = res.data.datas.store_info.store_id;
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
        store_id: storeId,
        store_banner: res.data.datas.store_info.store_banner,
        store_avatar: res.data.datas.store_info.store_avatar,
        store_name: res.data.datas.store_info.store_name,
        is_favorate: res.data.datas.store_info.is_favorate,
        voucher_list: res.data.datas.voucher_list,
        voucher_more: res.data.datas.voucher_more,
        goods_sales: res.data.datas.goods_sales,
        gc_list: res.data.datas.gc_list,
        shop_gc_id: res.data.datas.gc_list.length > 0 ? res.data.datas.gc_list[0]['gc_id'] : 0
      });
      console.log(that.data.goods_sales);
    })
  },

  tabClick: function(e) {
    var that = this;
    var shop_gc_id = e.currentTarget.dataset.index;
    that.setData({
      shop_gc_id: shop_gc_id,
      cur_page: 1
    })
    this.getAllGoodsList();
  },
  //全部商品列表
  getAllGoodsList: function() {
    var that = this;
    var gc_id = that.data.shop_gc_id;
    if (gc_id <= 0) {
      return;
    }
    request.postUrl('goods.list', {
      gc_id: gc_id,
      store_id: that.data.store_id,
      curpage: that.data.cur_page
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
      if (res.data.code == 200) {
        if (that.data.cur_page === 1) {
          that.setData({
            shop_all_goods_list: res.data.datas.goods_list,
            all_is_bottom: res.data.hasmore > 0 ? false : true
          })
        } else {
          that.setData({
            shop_all_goods_list: that.data.shop_all_goods_list.concat(res.data.datas.goods_list),
            all_is_bottom: res.data.hasmore > 0 ? false : true
          })
        }
      }
    });
  },
  refresh: function() {
    this.setData({
      cur_page: 1
    });
    this.getAllGoodsList();
  },
  getMore: function() {
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getAllGoodsList();
    }
  },
  //店铺新品上架
  getNewGoosList: function() {
    //new_goods_list
    var that = this;
    var store_id = that.data.store_id;
    request.postUrl('store.store_new_goods', {
      store_id: store_id
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
      console.log(res.data.datas.goods_list);
      that.setData({
        new_goods_list: res.data.datas.goods_list
      });
    })
  },

  goSearch: function() {
    var that = this;
    wx.navigateTo({
      url: '../search/search?store_id=' + that.data.store_id,
    })
  },
  // 收藏店铺
  collectStore: function() {
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    request.postUrl('member_favorites_store.favorites_add', {
      store_id: that.data.store_id
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
        is_favorate: true
      })
      wx.showToast({
        title: '店铺收藏成功',
        icon: 'none'
      })
    })
  },
  //取消店铺收藏
  store_del: function() {
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    request.postUrl('member_favorites_store.favorites_del', {
      store_id_list: JSON.stringify([that.data.store_id])
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
        is_favorate: false
      })
      wx.showToast({
        title: '店铺取消收藏',
        icon: 'none'
      })
    })

  },
  moreCoupon: function() {
    wx.navigateTo({
      url: '../getShopCoupons/getShopCoupons',
    })
  },
  /**
   * 领取优惠券
   */
  getCoupon: function (e) {
      if (!wx.getStorageSync("user_token")) {
          wx.switchTab({
              url: '../me/me'
          })
          return;
      }
      var voucher_id = e.currentTarget.dataset.id;
      request.postUrl("member_voucher.voucher_freeex", {tid: voucher_id}, function(res) {
          if (!res.data.code) {
              return;
          }
          if (res.data.code != 200) {
              wx.showToast({
                  title: res.data.datas.error
              });
              return;
          }
          wx.showToast({
              title: '领取成功',
              icon: 'none'
          })
      })
  }
})