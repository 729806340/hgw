// pages/offline_pay_finish/offline_pay_finish.js
var request = require('../../utils/request.js');
Page({

    /**
     * 页面的初始数据
     */
    data: {
        order_id:'',
        express_name : '', //快递公司
        shipping_code : '',//运单号码
        deliver_info : [],//运单信息列表
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        if (options.order_id ==''){
            return
        }else{
            this.setData({
                order_id: JSON.parse(options.order_id) 
            })
        }
    },
    reRoad:function(){
        var that = this;
        request.postUrl('member_order.search_deliver', { order_id: that.data.order_id},function(res){
            if (!res.data.code) {
                return;
            }
            if (res.data.code != 200) {
                wx.showToast({
                    title: res.data.datas.error
                });
                return;
            }
            var time = res.data.datas.deliver_info;
            for(var i=0; i<time.length;i++){
                var y= time[i].time.split(" ")[0];
                var h = time[i].time.split(" ")[1];
                time[i].y = y
                time[i].h =h
            }
            that.setData({
                express_name: res.data.datas.express_name,
                shipping_code: res.data.datas.shipping_code,
                deliver_info: time,
            })
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
        this.reRoad();
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

    },
})