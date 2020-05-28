var request = require('../../utils/request.js');
var app = getApp();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        search_txt: "",
        detail: [],
        hot_list: [],
        local_search_list: [],
        is_home: true,
        cart_count: 0,
        sum: 0,
        store_id: "",
        cur_page: 1,
        page_num: 10,
        has_null: false,
        is_bottom: false,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        var that = this;
        if (options.name) {
            var e = {
                detail: {
                    value: options.name,
                }
            }
            that.inputHandle(e);
        }
        if (options.store_id) {
            that.setData({
                store_id: options.store_id,
            })
        } else {
            that.setData({
                store_id: "",
            })
        }

        var local_search_list = wx.getStorageSync('local_search_list');
        if (local_search_list == '') {
            local_search_list = [];
        }
        that.setData({
            local_search_list: local_search_list,
        })
        request.postUrl('index.search_hot', {}, function(res) {
            that.setData({
                hot_list: res.data.datas.list,
            })
        })
        // that.initRed();
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    },
    inputHandle: function(e) {
        var that = this;
        var t = that.data.search_txt;
        if (t == '') {
            return;
        }
        this.setData({
            search_txt: t,
            is_home: false,
            detail: [],
            cur_page: 1,

        })
        that.getList();
    },
    getList: function() {
        var that = this;
        that.setData({
            has_null: false,
        })
        var t = that.data.search_txt;
        var local_search_list = wx.getStorageSync('local_search_list');
        if (local_search_list == '') {
            local_search_list = [];
        }
        if (local_search_list.indexOf(t) == -1) {
            local_search_list.unshift(t);
        }
        if (local_search_list.length > 10) {
            local_search_list.pop();
        }
        wx.setStorageSync('local_search_list', local_search_list)
        request.postUrl("goods.list", {keyword: t, curpage:that.data.cur_page}, function(res) {
            var temp = res.data.datas.goods_list;
            if (res.data.hasmore !== "1") {
                that.setData({
                    is_bottom: true,
                })
            } else {
                that.setData({
                    is_bottom: false,
                })
            }
            if (temp.length == 0) {
                return;
            }
            if (that.data.cur_page == 1) {
                that.setData({
                    detail: [],
                })
            }
            if (temp.length > 0) {
                that.setData({
                    cur_page: parseInt(that.data.cur_page) + 1,
                })
            }

            var detail = that.data.detail;
            detail = detail.concat(temp);
            if (detail.length == 0) {
                that.setData({
                    has_null: true,
                })
            } else {
                that.setData({
                    has_null: false,
                })
            }
            that.setData({
                detail: detail,
            })
        })

    },
    handlescrolltolower: function() {
        this.getList();
    },

    goGoodsDetail: function(e) {
        var item = e.currentTarget.dataset.item;
        wx.navigateTo({
            url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
        })
    },
    handleinput: function(e) {
        var t = e.detail.value;
        if (t == '') {
            var local_search_list = wx.getStorageSync('local_search_list');
            if (local_search_list == '') {
                local_search_list = [];
            }
            this.setData({
                is_home: true,
                local_search_list: local_search_list
            })
        } else {
            this.setData({
                search_txt: t
            })
        }

    },
    click_search: function(e) {
        var that = this;
        var t = e.currentTarget.dataset.t;
        var e = {
            detail: {
                value: t,
            }
        }
        that.setData({
            search_txt: t,
        })
        this.inputHandle(e);
    },
    goCar: function() {
        wx.switchTab({
            url: '../shoppingCar/shoppingCar',
        })
    },
    //清除
    clean_t: function() {
        var that = this;
        wx.setStorageSync('local_search_list', "")
        that.setData({
            local_search_list: [],
        })
    }
})