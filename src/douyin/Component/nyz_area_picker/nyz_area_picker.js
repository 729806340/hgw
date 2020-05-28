var areaTool = require('../../utils/area.js');
var index = [0, 0, 0]
var app = getApp();

Component({
  /**
   * 组件的属性列表
   */
  properties: {
    show: { //控制area_select显示隐藏
      type: Boolean,
      value: false
    },
    maskShow: { //是否显示蒙层
      type: Boolean,
      value: true
    },
    provinces: {
      type: Array,
      value: []
    },
    citys: {
      type: Array,
      value: []
    },
    areas: {
      type: Array,
      value: []
    },
    multiArray: {
      type: Array,
      value: []
    },
    value: {
      type: Array,
      value: [0, 0, 0]
    },
    defaultText: {
      type: String,
      value: "请选择区域"
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    province: '湖北省',
    city: '武汉市',
    area: '江汉区',
    flag: true,
    pick_text: "请选择城市",
  },

  /**
   * 组件的方法列表
   */
  methods: {
    bindMultiPickerChange: function(e) {
      return true;
    },
    bindMultiPickerColumnChange: function(e) {
      var that = this;
      console.log('修改的列为', e.detail.column, '，值为', e.detail.value);
      var column = e.detail.column;
      var i = e.detail.value;
      var value = that.data.value;
      var multiArray = that.data.multiArray;

      //省份变化
      if (column == 0) {
        value = [i, 0, 0];
        var citys = areaTool.getCitys(value[0]);
        var city_names = [];
        for (let item of citys) {
          city_names.push(item.area_name);
        }
        var areas = areaTool.getAreas(value[0], value[1]);
        var area_names = [];

        for (let item of areas) {
          area_names.push(item.area_name);
        }
        multiArray[1] = city_names;
        multiArray[2] = area_names;
        that.setData({
          multiArray: multiArray,
          value: value,
          citys: citys,
        })
        console.log(that.data.provinces);
        app.province_id = that.data.provinces[i].area_id;
        app.city_id = 0;
        app.area_id = 0;
        app.s_map = "";
        app.s_area = "";
      }
      //城市变化
      else if (column == 1) {
        value = [value[0], i, 0];
        var areas = areaTool.getAreas(value[0], value[1]);
        var area_names = [];
        for (let item of areas) {
          area_names.push(item.area_name);
        }
        multiArray[2] = area_names;
        that.setData({
          multiArray: multiArray,
          value: value,
          areas: areas,
        })

        app.city_id = that.data.citys[i].area_id;
        app.area_id = 0;
        app.s_map = "";
        app.s_area = "";
      }
      //地区变化 
      else if (column == 2) {
        value = [value[0], value[1], i];
        that.setData({
          value: value,
        })
        if (i == 0) {
          app.s_map = "";
          app.s_area = "";
          return;
        }
        console.log(that.data.areas[i]);
        app.area_id = that.data.areas[i].area_id;
        var area = that.data.areas[i];

        app.s_map = "";
        app.s_area = area;
        app.s_area.location = area.lat_x + "," + area.lat_y;
        that.triggerEvent('DeliverGoods', {city: app.city_id}, {})
      }
      console.log("area_id=", app.area_id)
    }
  }
})