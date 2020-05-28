var request = require('../../utils/request.js');

Page({
  data: {
    disabled: true,
    show_charge:2,  //1开  2关
  },
  onLoad: function () {
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
    }
    this.showInfo()
  },
    //展示用户数据
    showInfo: function() {
        var that = this;
        request.postUrl("member_index.index", {
            member_avatar: wx.getStorageSync("user_img")
        }, function(res) {
            if (res.data.code == 200) {
            if(res.data.datas.show_charge){
                that.setData({
                show_charge:res.data.datas.show_charge
                })
            }
            if(res.data.datas.show_charge == 1){
                wx.setNavigationBarTitle({
                title: '充值卡充值'
                })
            }
            }
        })
    },

    getCardNum: function (e) {
      var that = this;
        that.setData({
            card_num: e.detail.value
      });
      if (!this.data.card_num == '' && !this.data.card_pass == '') {
          that.setData({
              disabled: false
          })
      } else {
          that.setData({
              disabled: true
          })
      }
  },
    getCardPass: function (e) {
        var that = this;
        that.setData({
            card_pass: e.detail.value
        });
        if (!this.data.card_pass == '' && !this.data.card_num == '') {
            that.setData({
                disabled: false
            })
        } else {
            that.setData({
                disabled: true
            })
        }
    },

    chargeNow: function() {
        var that = this;
        var card_num = that.data.card_num;
        var card_pass = that.data.card_pass;
        if (!card_num || !card_pass) {
            wx.showToast({
                title: '充值卡号和密码不能为空',
                icon: 'none',
                duration: 1000
            })
            return false;
        }
        request.postUrl('member_recharge.card_add', {
            rc_sn: card_num,
            pwd: card_pass
        }, function(res) {
            if (!res.data.code) {
                wx.showToast({
                    title: '充值卡充值失败!'
                });
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }
            wx.showToast({
                title: '充值成功'
            })
            wx.navigateTo({
                url: '../chargeCard_new_2/chargeCard_new_2'
            })
        })

    }

})