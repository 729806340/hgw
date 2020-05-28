var request = require('../../utils/request.js');
var app = getApp();
// Component/goods_item/goods_item.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    goods: {
      type: Array,
      value: []
    },
    is_bottom: {
      type: Boolean,
      value: false,
    },
    enter_shop: {
      type: Boolean,
      value: true,
    },
    fav_goods: {
      type: Boolean,
      value: false,
    },
    has_deleteicon: {
      type: Boolean,
      value: false,
    },
    goods_list: {
      type: Array,
      value: []
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    alarmStatus: 0, //到货提醒状态
  },

  /**
   * 组件的方法列表
   */
  methods: {
    goGoodsDetail: function(e) {
      var item = e.currentTarget.dataset.item;
      wx.navigateTo({
        url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id
      })
    },
    goShop: function(e) {
      var store_id = e.currentTarget.dataset.id;
      if (parseInt(store_id) <= 0) {
        return;
      }
      wx.navigateTo({
        url: '../shopDetails/shopDetails?store_id=' + store_id
      })
    }
  }
})