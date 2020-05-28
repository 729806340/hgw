var request = require('../../utils/request.js');

Page({
    data: {
        has_log: false,
        log_list: [],
        points: 0,
        cur_page: 1,
        has_more: 0
    },
    onLoad: function () {
        if (!wx.getStorageSync("user_token")) {
            wx.switchTab({
                url: '../me/me'
            });
        }
        this.getPointsLog();
    },
    onShow: function(e) {
        //
    },
    refresh: function () {
        this.setData({
            cur_page: 1
        });
        this.getPointsLog();
    },
    getMore: function () {
        var that = this;
        if (that.data.has_more > 0) {
            that.setData({
                cur_page: that.data.cur_page + 1
            });
            that.getPointsLog();
        }
    },
    getPointsLog: function () {
        var that = this;
        request.postUrl('member_points.pointslog', {
            curpage: that.data.cur_page
        }, function(res) {
            if (!res.data.code) {
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }
            if (res.data.datas.log_list.length) {
                that.setData({
                    has_log: true
                })
            }

            if (that.data.cur_page === 1) {
                that.setData({
                    log_list: res.data.datas.log_list,
                    points: res.data.datas.points,
                    has_more: res.data.hasmore,
                })
            } else {
                that.setData({
                    log_list: that.data.log_list.concat(res.data.datas.log_list),
                    points: res.data.datas.points,
                    has_more: res.data.hasmore,
                })
            }
        })
    }
})