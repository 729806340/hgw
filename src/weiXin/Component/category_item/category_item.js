var request = require('../../utils/request.js');

// Component/category/category.js
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
    tabClick:function(e){
      
    }

  }
})