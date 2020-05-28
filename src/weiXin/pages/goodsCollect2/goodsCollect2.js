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
    this.getCollectGoods();
  },

  onShow: function() {
    if (this.data.favorites_list.length <= 0) {
      this.getCollectGoods();
    }
  },

  //获取收藏商品列表
  getCollectGoods: function() {
    var that = this;
    request.postUrl('member_favorites.favorites_list', {
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
            favorites_list: res.data.datas.favorites_list,
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        } else {
          that.setData({
            favorites_list: that.data.favorites_list.concat(res.data.datas.favorites_list),
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        }
      }
    })
  },
  //下拉刷新
  refresh: function() {
    this.setData({
      cur_page: 1
    });
    this.getCollectGoods();
  },
  //上拉获取更多
  getMore: function() {
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getCollectGoods();
    }
  },
  
  // 取消商品收藏：
  deleteStore: function(e) {
    var that = this;
    var goodsIndex = e.currentTarget.dataset.index;
    var favoritesList = that.data.favorites_list;
    var goodsSelectedId = favoritesList[goodsIndex].goods_id;

    request.postUrl('member_favorites.favorites_del', {
      goods_id_list: JSON.stringify(Array(goodsSelectedId))
    }, function(res) {
      if (res.data.code == 200) {
        wx.showToast({
          title: '删除成功',
          icon: 'none'
        })
      }
    })
    favoritesList.splice(goodsIndex, 1);
    that.setData({
      favorites_list: favoritesList
    });
  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  }
})