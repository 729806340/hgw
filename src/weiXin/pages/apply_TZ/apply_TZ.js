// pages/apply_TZ/apply_TZ.js
var request = require('../../utils/request.js');
var areaTool = require('../../utils/area.js');
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    name:'',
    phone:'',
    city:['武汉','非武汉'],
    city_val:'',
    region:[
      {ad_code: "420106",area_id: "2814",area_name: "武昌区",lat_x: "114.307344",lat_y: "30.546536",location: "114.307344,30.546536"},
      {ad_code: "420105",area_id: "2816",area_name: "汉阳区",lat_x: "114.265807",lat_y: "30.549326",location: "114.265807,30.549326"}
    ],
    region_val:'',
    s_map:'',
    addressDetail:'',

    multiArray: "",
    value: [0, 0, 0],
    provinces: [],
    citys: [],
    areas: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.s_area = ''
    app.s_map = ''
    wx.login({
      success: function(login_res) {
        
      }
    })
  },
  bindname(e){
    this.setData({
      name: e.detail.value
    })
  },
  bindphone(e){
    this.setData({
      phone: e.detail.value
    })
  },
  binddetail(e){
    this.setData({
      addressDetail: e.detail.value
    })
  },
  pickercity: function(e) {
    this.setData({
      city_val: e.detail.value
    })
  },
  pickerregion(e){
    this.setData({
      region_val: e.detail.value
    })
    app.s_area = this.data.region[this.data.region_val]
  },
  //获取微信手机号
  getPhoneNumber(e){
    console.log(e)
    var that = this
    wx.login({
      success: function(login_res) {
        if (login_res.code) {
          request.postUrl("connect_weixin.get_session_key", {
            user_code:login_res.code
          }, function(result) {
            if(result.data.code == '200'){
              request.postUrl("connect_weixin.decrypt_iv", {
                encrypted_data:e.detail.encryptedData,
                iv:e.detail.iv,
                session_key:result.data.datas.session_key
              }, function(res) {
                if(res.data.code == '200'){
                  that.setData({
                    phone:res.data.datas.phoneNumber
                  })
                }
              })
            }
          })
        }
      }
    })
  },

  goMap: function() {
    if (app.s_area == "") {
      wx.showToast({
        title: '请先选择区域',
        icon: "none"
      })
      return;
    }
    var that = this;
    wx.getSetting({
      success: (res) => {
        if (!res.authSetting['scope.userLocation']) {
          wx.openSetting({
            success: (res) => {
              wx.navigateTo({
                url: '../map/map',
              })
            }
          })
        } else {
          wx.navigateTo({
            url: '../map/map',
          })
        }
      }
    })
  },

  areaList(){
    var that = this
    request.postUrl("shequ_common.get_area_list", {
      area_id: '258'
    }, function(res) {
      if (res.data.code == 200) {
        var AreaJson = res.data.datas;
        console.log("AreaJson = ", AreaJson);
        areaTool.setAreaJson(AreaJson);
        var provinces = [{
          area_id: 0,
          area_name: "选择区域"
        }];
        var value = that.data.value;
        for (var i = 0; i < AreaJson.length; i++) {
          provinces.push(AreaJson[i]);
        }
        // if (that.data.flag == 0) {
        //   app.province_id = 0;
        //   app.city_id = 0;
        //   app.area_id = 0;
        // }
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
        var citys = areaTool.getCitys_2(value[0]);
        for (let key in citys) {
          city_name.push(citys[key].area_name);
          if (citys[key].area_id == app.city_id) {
            value[1] = key;
          }
        }
        var areas = areaTool.getAreas_2(value[0], value[1]);
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

  //提交
  submit(){
    var that = this
    if(!that.data.name){wx.showToast({title: '请填写姓名',icon:'none'});return}
    if(!(/^1[3456789]\d{9}$/.test(that.data.phone))){wx.showToast({title: '请填写正确的手机号码',icon:'none'});return}
    if(!that.data.city_val){wx.showToast({title: '请选择所在城市',icon:'none'});return}
    var ll = ''
    if(that.data.city_val == 0){
      if(app.s_area == ""){wx.showToast({title: '请选择所在区域',icon:'none'});return}
      if(!that.data.s_map.name){wx.showToast({title: '请选择所在地址',icon:'none'});return}
      ll = that.data.s_map.location.split(',');
    }
    
    request.postUrl("shequ_join_tuan.join", {
      name: that.data.name,
      phone: that.data.phone,
      city_name: that.data.city[that.data.city_val],
      area_id: app.s_area.area_id,
      longitude: ll[0],
      latitude: ll[1],
      building: that.data.addressDetail,
      address: that.data.s_map.address + that.data.s_map.name
    }, function(res) {
      if(res.data.code == '200'){
        wx.showToast({
          title: '提交成功'
        })
        setTimeout(function(){
          wx.navigateBack()
        },500)
      }else{
        wx.showToast({
          title: res.data.datas.error,
          icon:'none'
        })
      }
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      s_map:app.s_map
    })
    this.areaList()
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})