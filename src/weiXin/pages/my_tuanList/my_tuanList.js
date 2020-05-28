// pages/my_tuanList/my_tuanList.js
var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page_total:'',
    curpage:1,
    dataList:[],
    if_show:false,
    cur_time:'',
    interval:'',
    time:'',
    if_timeShow:false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getIndex(this.data.curpage)
  },
  getIndex(curpage){
    var that = this
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl("shequ_captial_tuan.tuan_list", {
      page:15,
      curpage:curpage
    }, function(res) {
      if(res.data.code == '200'){
        var list = res.data.datas.list
        if(list.length==0){
          that.setData({
            if_show: true
          })
          clearInterval(that.data.interval);
          wx.hideLoading()
          return
        }
        var dataList = that.data.dataList
        for(var i=0;i<list.length;i++){
          dataList.push(list[i])
        }
        that.setData({
          dataList:dataList,
          page_total:res.data.page_total
        })
        if(res.data.page_total <= 1){
          that.setData({
            if_show: true
          })
        }
        if(curpage == 1){
          clearInterval(that.data.interval);
          var end_time = dataList[0].tuan_info.config_end_time
          var timestamp = Date.parse(new Date());
          timestamp = timestamp / 1000;
          var cur_time = parseInt(end_time) - timestamp
          that.setData({
            cur_time: cur_time
          })
          var interval = setInterval(function() {
            if(that.data.cur_time <= 0){
              clearInterval(that.data.interval);
              that.setData({
                if_timeShow:false
              })
              return
            }
            that.setData({
              if_timeShow:true
            })
            var time = util.GetRTime_t(that.data.cur_time);
            that.setData({
              time: time,
              cur_time: that.data.cur_time - 1,
            })
            // console.log(that.data.time)
          }, 1000);
          that.data.interval = interval;
        }
        wx.hideLoading()
      }
    })
  },
  pushList(){
    var that = this
    var curpage = JSON.parse(that.data.curpage) + 1
    var page_total = that.data.page_total
    if(curpage > page_total){
      that.setData({
        if_show:true
      })
      return
    }else{
      that.setData({
        curpage: curpage
      })
      that.getIndex(curpage)
    }
  },
  goDetail(e){
    wx.navigateTo({
      url: '../goodsDetail_tuan/goodsDetail_tuan?id=' + e.currentTarget.dataset.id,
    })
  },


  goOrder(e){
    wx.navigateTo({
      url: '../my_tuanOrder/my_tuanOrder?id=' + e.currentTarget.dataset.id,
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
    clearInterval(this.data.interval);
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