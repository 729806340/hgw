var request = require('../../utils/request.js');

Page({

  data: {
    favorites_list: [],
    is_bottom: false,
    cur_page: 1
  },

  onLoad: function() {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    this.getCollectShops();
  },

  onShow: function() {
    if (this.data.favorites_list.length <= 0) {
      this.getCollectShops();
    }
  },

  //获取收藏店铺列表
  getCollectShops: function() {
    var that = this;
    request.postUrl('member_favorites_store.favorites_list', {
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
      if (that.data.cur_page === 1) {
        that.setData({
          favorites_list: res.data.datas.favorites_list,
          is_bottom: res.data.hasmore > 0 ? false : true
        })
      } else {
        that.setData({
          favorites_list: that.data.favorites_list.concat(res.data.datas.favorites_list),
          is_bottom: res.data.hasmore > 0 ? false : true
        })
      }
    })
  },

  //上拉获取更多
  getMore: function() {
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getCollectShops();
    }
  },
  // 进店铺
  goShop: function(e) {
    var store_id = e.currentTarget.dataset.id;
    if (parseInt(store_id) <= 0) {
      return;
    }
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + store_id
    })
  },

  //删除收藏店铺
  deleteStore: function(e) {
    var that = this;
    var storeIndex = e.currentTarget.dataset.index;
    var favoritesList = that.data.favorites_list;
    var store_id = favoritesList[storeIndex].store_id;

    request.postUrl('member_favorites_store.favorites_del', {
      store_id_list: JSON.stringify([store_id])
    }, function(res) {
      if (res.data.code == 200) {
        wx.showToast({
          title: '删除成功',
          icon: 'none'
        })
      }
    });
    favoritesList.splice(storeIndex, 1);
    that.setData({
      favorites_list: favoritesList
    });
  }
})