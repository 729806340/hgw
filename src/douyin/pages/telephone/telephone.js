var request = require('../../utils/request.js');
Page({
    data: {
        phone: '', //手机号
        code: '', //验证码
        iscode: null, //用于存放验证码接口里获取到的code
        codename: '获取验证码',
        disabled: true,
        no_phone: true,
        phone_number: '',
        binded:false,
    },
    onLoad: function(option) {
        if (option.phone && option.phone.length == 11) {
            this.setData({
                no_phone: false,
                phone_number: option.phone
            })
        }
    },
    getVerificationCode: function() {
        this.getCode();
    },
    getCodeValue: function(e) {
        this.setData({
            code: e.detail.value
        })
    },
    getPhoneValue: function(e) {
        var _this = this;
        var myreg = /^1[3-9]\d{9}$/;
        _this.setData({
            phone: e.detail.value,
        });
        if (!this.data.phone == '' && myreg.test(this.data.phone)) {
            _this.setData({
                disabled: false
            })
        } else {
            _this.setData({
                disabled: true
            })
        }
    },
    getCode: function() {
        var that = this;
        var myreg = /^1[3-9]\d{9}$/;
        if (this.data.phone == '') {
            wx.showToast({
                title: '手机号不能为空！',
                icon: 'none',
                duration: 1000
            })
            return false;
        }
        if (!myreg.test(this.data.phone)) {
            wx.showToast({
                title: '请输入正确的手机号！',
                icon: 'none',
                duration: 1000
            })
            return false;
        } else {
            request.postUrl('member_account.bind_mobile_step1', {
                phone: that.data.phone
            }, function(res) {
                if (!res.data.code) {
                    wx.showToast({
                        title: '获取验证码失败!'
                    });
                    return;
                }
                if (res.data.code != 200) {
                    wx.showToast({
                        title: res.data.datas.error
                    });
                    return;
                }

                var num = 30;
                var timer = setInterval(function() {
                    num--;
                    if (num <= 0) {
                        clearInterval(timer);
                        that.setData({
                            codename: '重新发送',
                            disabled: false
                        })
                    } else {
                        that.setData({
                            codename: num + 's后重发',
                            disabled: true
                        })
                    }
                }, 1000)
            })
        }
    },
    // 立即绑定
    register: function() {
        var user_token = wx.getStorageSync("user_token");
        if (!user_token) {
            wx.switchTab({
                url: '../me/me'
            })
            return;
        }

        var that = this;
        var myreg = /^1[3-9]\d{9}$/;

        if (this.data.phone == '') {
            wx.showToast({
                title: '手机号不能为空！',
                icon: 'none',
                duration: 1000
            })
            that.setData({
                binded:false
            })
            return false;
        }
        if (!myreg.test(this.data.phone)) {
            wx.showToast({
                title: '请输入正确的手机号！',
                icon: 'none',
                duration: 1000
            })
            return false;
        }
        if (this.data.code == '') {
            wx.showToast({
                title: '请输入获取验证码！',
                icon: 'none'
            })
            return false
        }
        request.postUrl('member_account.bind_mobile_step2', {
            phone: that.data.phone,
            auth_code: that.data.code
        }, function(res) {
            if (!res.data.code) {
                wx.showToast({
                    title: '绑定手机失败!'
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
                title: '绑定成功'
            })
            wx.switchTab({
                url: '../me/me'
            })

        })
    }
})