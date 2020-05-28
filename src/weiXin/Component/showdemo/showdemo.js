var get = getApp();
var request = require('../../utils/request.js')
Component({
    options: {
        multipleSlots: true // 在组件定义时的选项中启用多slot支持 
    },
    /** 
     * 组件的属性列表 
     * 用于组件自定义设置 
    */
    properties: {
        callTel: {
            type: Number,     // 类型（必填），目前接受的类型包括：String, Number, Boolean, Object, Array, null（表示任意类型）
            value: 0     // 属性初始值（可选），如果未指定则会根据类型选择一个
        },
        showModal: {
            type: Boolean,     // 类型（必填），目前接受的类型包括：String, Number, Boolean, Object, Array, null（表示任意类型）
            value: false     // 属性初始值（可选），如果未指定则会根据类型选择一个
        }
    },
    /** 
     * 私有数据,组件的初始数据 
     * 可用于模版渲染 
     */
    data: { // 弹窗显示控制 
        showModal :true,
    },
    /**
     * 组件的方法列表 
     * 更新属性和数据的方法与更新页面数据的方法类似 
    */
    methods: {
        /** 
        * 公有方法 
        */
        // showModal: function (e) {
        //     var that = this;
        //     // 显示遮罩层
        //     var animation = wx.createAnimation({
        //         duration: 200,
        //         timingFunction: "linear",
        //         delay: 0
        //     })
        //     that.animation = animation
        //     animation.translateY(300).step()
        //     if (e.currentTarget.dataset.type == 1) {
        //         if (e.currentTarget.dataset.refund != 1) {
        //             that.setData({
        //                 animationData: animation.export(),
        //                 showModalStatus: true
        //             })
        //         } else {
        //             that.setData({
        //                 showModalStatus: false
        //             })
        //         }
        //     } else {
        //         that.setData({
        //             animationData: animation.export(),
        //         })
        //     }

        //     setTimeout(function () {
        //         animation.translateY(0).step()
        //         that.setData({
        //             animationData: animation.export()
        //         })
        //     }.bind(that), 200)
        // },
        hideModal: function () {
            var that = this;
            // 隐藏遮罩层
            var animation = wx.createAnimation({
                duration: 200,
                timingFunction: "linear",
                delay: 0
            })
            that.animation = animation
            animation.translateY(300).step()
            that.setData({
                animationData: animation.export(),
            })
            setTimeout(function () {
                animation.translateY(0).step()
                that.setData({
                    animationData: animation.export(),
                    showModal: false,
                })
            }.bind(that), 200)
        },
        bindGetUserInfo: function (res) {  //登陆
            this.hideModal(); //隐藏弹框
            var that = this;
            wx.showLoading({
                title: '正在授权中..'
            });
            var temp_goods = wx.getStorageSync('temp_goods');
            var temp = [];
            for (var l = 0; l < temp_goods.length; l++) {
                var goods_id = temp_goods[l].goods_id
                var goods_num = temp_goods[l].goods_num
                var lf = { goods_id, goods_num }
                temp.push(lf)
            }
            wx.login({
                success: function (login_res) {
                    if (login_res.code) {
                        // 已经授权，可以直接调用 getUserInfo 获取头像昵称
                        wx.getUserInfo({
                            success: function (res) {
                                wx.setStorageSync("user_img", res.userInfo.avatarUrl);
                                request.postUrl("connect_weixin.login", {
                                    user_code: login_res.code,
                                    user_cookie: JSON.stringify(temp),
                                    callTel: that.properties.callTel
                                }, function (result) {
                                    if (!result.data.code) {
                                        wx.showToast({
                                            title: '登陆失败!'
                                        });
                                        return;
                                    }
                                    if (result.data.code != 200) {
                                        wx.showToast({
                                            title: result.data.datas.error
                                        });
                                        return;
                                    }
                                    wx.setStorageSync("open_id", result.data.datas.open_id);
                                    get.showModal = false; ///
                                    get.Atatus = 1;
                                    if (result.data.datas.user_token) {
                                        wx.setStorageSync("user_token", result.data.datas.user_token);
                                        wx.hideLoading();
                                        wx.setStorageSync("temp_goods", "");
                                        /*wx.switchTab({
                                                   url: '../me/me'
                                               });*/
                                        that.showInfo();
                                        return;
                                    }

                                    if (result.data.datas.union_id) {
                                        wx.setStorageSync("union_id", result.data.datas.union_id);
                                        wx.hideLoading();
                                        wx.navigateTo({
                                            url: '../bindTelephone/bindTelephone'
                                        });
                                        return;
                                    } else {
                                        request.postUrl("connect_weixin.weixin_iv_login", {
                                            session_key: result.data.datas.session_key,
                                            encrypted_data: res.encryptedData,
                                            iv: res.iv,
                                            open_id: result.data.datas.open_id,
                                            user_cookie: JSON.stringify(temp),
                                            callTel: that.properties.callTel
                                        }, function (res1) {
                                            if (!res1.data.code) {
                                                wx.showToast({
                                                    title: '登陆失败!!'
                                                });
                                                return;
                                            }
                                            if (res1.data.code != 200) {
                                                wx.showToast({
                                                    title: res1.data.datas.error
                                                });
                                                return;
                                            }

                                            if (res1.data.datas.user_token) {
                                                wx.setStorageSync("user_token", res1.data.datas.user_token);
                                                wx.hideLoading();
                                                wx.setStorageSync("temp_goods", "");
                                                /*wx.switchTab({
                                                    url: '../me/me'
                                                });*/
                                                return;
                                            }
                                            wx.setStorageSync("union_id", res1.data.datas.union_id);
                                            wx.hideLoading();
                                            wx.navigateTo({
                                                url: '../bindTelephone/bindTelephone'
                                            });
                                            return;
                                        })
                                    }
                                })
                            },
                            fail: function (res) {
                                wx.showToast({
                                    title: '授权失败',
                                    icon: "none",
                                })
                                get.Atatus = 3;
                                get.showModal = true;
                                wx.hideLoading();
                            }
                        })
                    }
                }
            });
        },
    }
})