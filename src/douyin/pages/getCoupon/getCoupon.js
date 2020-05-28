var request = require('../../utils/request.js');
var Mcaptcha = require('../../utils/mcaptcha.js');

Page({
    data: {
        disabled: true,
        code_num: ''
    },
    onLoad: function () {
        if (!wx.getStorageSync("user_token")) {
            wx.switchTab({
                url: '../me/me'
            });
        }
    },
    onReady: function() {
        var code_num = this.getRanNum();
        this.setData({
            code_num: code_num
        })
        new Mcaptcha({
            el: 'canvas',
            width: 80, //对图形的宽高进行控制
            height: 30,
            code: code_num
        });
    },
    getCouponPass: function (e) {
        var that = this;
        that.setData({
            coupon_pass: e.detail.value
        });
        if (!this.data.coupon_pass == '' && !this.data.secret_code == '') {
            that.setData({
                disabled: false
            })
        } else {
            that.setData({
                disabled: true
            })
        }
    },
    getSecretCode: function (e) {
        var that = this;
        that.setData({
            secret_code: e.detail.value
        });
        if (!this.data.coupon_pass == '' && !this.data.secret_code == '') {
            that.setData({
                disabled: false
            })
        } else {
            that.setData({
                disabled: true
            })
        }
    },

    getCoupon: function() {
        var that = this;
        var coupon_pass = that.data.coupon_pass;
        var secret_code = that.data.secret_code;
        if (!coupon_pass) {
            wx.showToast({
                title: '代金券卡密不能为空',
                icon: 'none',
                duration: 1000
            })
            return false;
        }
        if (secret_code.toLowerCase() != this.data.code_num.toLowerCase()) {
            wx.showToast({
                title: '验证码错误,请重新输入',
                icon: 'none',
                duration: 1500,
                mask: true
            });
            that.setData({
                disabled: true
            });
            return;
        }
        request.postUrl('member_voucher.voucher_pwex', {
            pwd_code: coupon_pass
        }, function(res) {
            if (!res.data.code) {
                wx.showToast({
                    title: '领取代金券失败!'
                });
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }
            wx.redirectTo({
                url: '../cashCoupon/cashCoupon'
            })
        })
    },

    getRanNum: function() {
        var chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
        var pwd = '';
        for (var i = 0; i < 4; i++) {
            if (Math.random() < 48) {
                pwd += chars.charAt(Math.random() * 48 - 1);
            }
        }
        return pwd;
    }
})