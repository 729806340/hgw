var request = require('../../utils/request.js');
var areaTool = require('../../utils/area.js');
var app = getApp();

Page({
  data: {
    true_name: '',
    mob_phone: '',
    province_id: '', //省id
    city_id: '', //市id
    area_id: '', //区id
    addressDetail: '', //详细地址
    province: '', //省
    city: '', //市
    area: '', //区
    provinces: [],
    citys: [],
    areas: [],
    AreaJson: [],
    is_default: 0, //默认地址
    flag: 0, //0地址注册,1地址编辑
    item: {}, //编辑的回填数据
    multiArray: "",
    value: [0, 0, 0],
    hasInvite: false,
    token: "",
  },
  onLoad: function(option) {
    var that = this;
    if (option.item) {
      that.data.flag = 1;
      wx.setNavigationBarTitle({
        title: '地址编辑',
      })
      var item = JSON.parse(option.item);
      var mylocation = item.area_info.split(' ');
      that.setData({
        true_name: item.true_name,
        mob_phone: item.mob_phone,
        province: mylocation[0],
        city: mylocation[1],
        area: mylocation[2],
        addressDetail: item.address,
        is_default: item.is_default,
        item: item,
      })
      if (item.is_default == 1) {
        that.setData({
          switch_chekced: true,
        })
      } else {
        that.setData({
          switch_chekced: false,
        })
      }
      app.province_id = item.province_id;
      app.city_id = item.city_id;
      app.area_id = item.area_id;
    }
    request.postUrl('area.wei_area_list', {}, function(res) {
      if (res.data.code == 200) {
        var AreaJson = res.data.datas;
        console.log("AreaJson = ", AreaJson);
        areaTool.setAreaJson(AreaJson);
        var provinces = [{
          area_id: 0,
          area_name: "选择省份"
        }];
        var value = that.data.value;
        for (var i = 0; i < AreaJson.length; i++) {
          provinces.push(AreaJson[i]);
        }
        if (that.data.flag == 0) {
          app.province_id = 0;
          app.city_id = 0;
          app.area_id = 0;
        }
        var multiArray = [];
        var provinces_name = [];
        var city_name = [];
        var area_name = [];
        for (let key in provinces) {
          provinces_name.push(provinces[key].area_name);
          if (provinces[key].area_id == app.province_id) {
            value[0] = key;
          }
        }
        var citys = areaTool.getCitys(value[0]);
        for (let key in citys) {
          city_name.push(citys[key].area_name);
          if (citys[key].area_id == app.city_id) {
            value[1] = key;
          }
        }
        var areas = areaTool.getAreas(value[0], value[1]);
        for (let key in areas) {
          area_name.push(areas[key].area_name);
          if (areas[key].area_id == app.area_id) {
            value[2] = key;
          }
        }
        multiArray.push(provinces_name);
        multiArray.push(city_name);
        multiArray.push(area_name);
        that.setData({
          provinces: provinces,
          citys: citys,
          areas: areas,
          multiArray: multiArray,
          value: value,
        })
      }
    })
  },
  onShow: function() {

  },

  // 表单提交：
  formSubmit: function(e) {
    var that = this;
    let bossName = e.detail.value.addressName;
    let bossPhone = e.detail.value.bossPhone;
    let myreg = /^(13[0-9]|14[5-9]|15[012356789]|166|17[0-8]|18[0-9]|19[8-9])[0-9]{8}$/;

    if (bossName == '') {
      wx.showToast({
        title: '负责人为空！',
        icon: 'none'
      })
      return false;
    }
    if (bossPhone == '') {
      wx.showToast({
        title: '手机号码为空！',
        icon: 'none'
      })
      return false;
    }
    if (!myreg.test(bossPhone)) {
      wx.showToast({
        title: '请填写正确手机号码！',
        icon: 'none'
      })
      return false;
    }
    if (app.area_id == 0) {
      wx.showToast({
        title: '请选择所属区域',
        icon: 'none'
      })
      return false;
    }

    if (that.data.addressDetail == "") {
      wx.showToast({
        title: '请填写详细地址',
        icon: 'none'
      })
      return false;
    }

    wx.showLoading({
      title: '正在提交',
      mask: true,
    })
    if (that.data.flag == 1) {
      request.postUrl('member_address.address_edit', {
        address_id: that.data.item.address_id, //编辑的地址id
        true_name: bossName, //名称
        mob_phone: bossPhone, //联系电话
        province_id: app.province_id, //省id
        city_id: app.city_id, //市id
        area_id: app.area_id, //区id
        is_default: that.data.is_default, //是否为默认
        address: that.data.addressDetail,  //详细地址
      }, function(res) {
        wx.hideLoading();
        if (res.data.code == 200) {
          wx.navigateBack({
              url: '../address/address'
          })
        }
      })
      return;
    } else if (that.data.flag == 0) {
      // 新增地址
      request.postUrl('member_address.address_add', {
        true_name: bossName, //名称
        mob_phone: bossPhone, //联系电话
        province_id: app.province_id, //省id
        city_id: app.city_id, //市id
        area_id: app.area_id, //区id
        is_default: that.data.is_default,
        address: that.data.addressDetail,  //详细地址
      }, function(res) {
        wx.hideLoading();
        if (res.data.code == 200) {
            wx.navigateBack({
              url: '../address/address'
            })
        }
      })
    }


  },

  //默认地址选项
  switchChange: function(e) {
    var that = this;
    var value = e.detail.value;
    let is_default = value ? 1 : 0;
    that.setData({
      is_default: is_default
    })
  },

  handleinput: function(e) {
    this.setData({
      addressDetail: e.detail.value
    })
  }
})