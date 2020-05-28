var request = require('../../utils/request.js');
var app = getApp();

Page({
  data: {
    has_address: false,
    address_list: [], //地址列表
    flag: 1, //1是我的过来的，2是确认订单过来的  9是社区团购过来的
    wx_address:'',
  },
  onLoad: function(e) {
      if (!wx.getStorageSync("user_token")) {
          wx.navigateTo({
              url: '../me/me'
          });
      }
      this.setData({
          flag: e.flag
      });
  },
  onShow: function (e) {
      this.getAddress();
  },
    //地址列表
  getAddress: function(e) {
    var that = this;
    request.postUrl('member_address.address_list', {}, function(res) {
        if (!res.data.code) {
            return;
        }
        if (res.data.code != 200) {
            wx.showToast({
                title: res.data.datas.error
            });
            return;
        }
        if (res.data.datas.address_list.length > 0){
            that.setData({
                has_address: true
            })
        }
        that.setData({
            address_list: res.data.datas.address_list
        })
    })
  },

  //编辑地址
  addressEdite: function (e) {
    let item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../addAddress/addAddress?item=' + JSON.stringify(item),
    })
  },
    //删除地址
    addressDetele: function (e) {
        var that = this;
        var address_id = e.currentTarget.dataset.item.address_id;
        request.postUrl('member_address.address_del', {
            address_id: address_id
        }, function(res) {
            if (!res.data.code) {
                wx.showToast({
                    title: '删除地址失败!'
                });
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }

            if (app.address_info && app.address_info.address_id == address_id) {
                app.address_info = '';
            }

            wx.showToast({
                title: '成功'
            });
            let address_list = that.data.address_list;
            address_list.splice(e.currentTarget.dataset.index, 1);
            that.setData({
                address_list: address_list
            })
        })
    },
    //选择默认地址
    click: function(e) {
        var that = this;
        let address_info = e.currentTarget.dataset.item;
        if (address_info.is_default != 0) {
            return;
        }
        request.postUrl('member_address.address_edit', {
            address_id: address_info.address_id,
            true_name: address_info.true_name,
            area_info: address_info.area_info,
            area_id: address_info.area_id,
            city_id: address_info.city_id,
            address: address_info.address,
            mob_phone: address_info.mob_phone,
            is_default: 1
        },function(res) {
            if (!res.data.code) {
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }
            var index = e.currentTarget.dataset.index;
            var addressList = that.data.address_list;
            for (let it in addressList) {
                addressList[it].is_default = 0;
            }
            addressList[index].is_default = 1;
            that.setData({
                address_list: addressList
            })
            wx.showToast({
                title: '默认地址设置成功',
                icon: 'none',
            })
        })
    },
    //确认订单 选择地址
    chooseAddress: function (e) {
      if (this.data.flag == 2 || this.data.flag == 9) {
        let item = e.currentTarget.dataset.item;
        app.address_info = item;
        wx.navigateBack({})
      }
    },
    //新增地址链接
    addAddress: function() {
        wx.navigateTo({
            url: '../addAddress/addAddress'
        });
    },
    //获取微信地址
    wx_addAddress(){
        var that = this
        wx.getSetting({
            success(res) {
              if (res.authSetting['scope.address']) {
                wx.chooseAddress({
                  success(res) {
                    console.log(res)
                    that.wx_postaddress(res.userName,res.telNumber,res.provinceName,res.cityName,res.countyName,res.detailInfo)
                  }
                })
              } else {
                if (res.authSetting['scope.address'] == false) {
                  console.log("222")
                  wx.openSetting({
                    success(res) {
                      console.log(res.authSetting)
                    }
                  })
                } else {
                  console.log("eee")
                  wx.chooseAddress({
                    success(res) {
                        that.wx_postaddress(res.userName,res.telNumber,res.provinceName,res.cityName,res.countyName,res.detailInfo)
                    }
                  })
                }
              }
            }
          })
    },
    //新增微信地址
    wx_postaddress(true_name,mob_phone,province_name,city_name,area_name,address){
        var that = this
        request.postUrl('member_address.address_add_wei', {
            true_name: true_name, //名称
            mob_phone: mob_phone, //联系电话
            province_name: province_name, //省id
            city_name: city_name, //市id
            area_name: area_name, //区id
            address: address,  //详细地址
        }, function(res) {
            if (res.data.code == 200) {
                that.getAddress()
            }else{
                wx.showToast({
                    title: res.data.datas.error,
                    icon: 'none',
                    duration: 2000
                  })
            }
        })
    }
})