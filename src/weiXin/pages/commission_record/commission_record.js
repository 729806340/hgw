var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    start_date:'',
    // start_time:'',
    end_date:'',
    end:'',
    bill_type:'all',
    dataList:[],//get数据
    dat:[],//分页数据
    page_total: '',//总页数
    curpage: 1,//查询页数
    if_show: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options,'options') 
    var searchData = new Date(new Date() - 30 * 24 * 60 * 60 * 1000);
    var searchyear = searchData.getFullYear(), searchmonth = searchData.getMonth() + 1, searchday = searchData.getDate()

    var nowDate = new Date();
    var year = nowDate.getFullYear(), month = nowDate.getMonth() + 1, day = nowDate.getDate()
    this.setData({
      end_date: `${year}-${month}-${day}`,
      end:`${year}-${month}-${day}`,
      start_date: `${searchyear}-${searchmonth}-${searchday}`,
    })
    this.getTime()
    // this.getIndex()
  },

  // 选择开始时间
  bindDateChange_start(e){
    this.setData({
      start_date: e.detail.value
    })
  },
   // 选择结束时间
  bindDateChange_end(e) {
    this.setData({
      end_date: e.detail.value
    })
  },
// 点击切换
  tabClick(e){
    console.log('tabClick',e)
    this.setData({
      bill_type:e.currentTarget.dataset.tab,
      curpage: 1,
      dat: [],
      if_show: false
    })
    this.getIndex()
  },
  //查询
  searchclick(e){
    var that = this;
    that.setData({
      bill_type: 'all',
      curpage: 1,
      dat: [],
      if_show: false,
    })
    that.getIndex()
  },
  getTime(){
    var that  = this
    request.postUrl('member_index.get_tuanzhang_info',{ },
    function (res) {
      if(res.data.code == '200'){
        that.setData({
          start_time:res.data.datas.register_time
        })
        var start_date = that.data.start_date;
        if(res.data.datas.register_time < start_date){
          that.setData({
            start_date:res.data.datas.register_time,
            start_time:res.data.datas.register_time,
          })
          console.log(that.data.start_date,that.data.start_time,'--------------')
        }else{
          that.setData({
            start_date:that.data.start_date,
            start_time:that.data.start_date,
          })
          console.log(that.data.start_date,that.data.start_time,'111111111111')
        }
        console.log(that.data.start_time)
        that.getIndex()
      }
  })
  },
  getIndex: function (){
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    request.postUrl('shequ_dinosaur_bill.index', {
      start_time: that.data.start_time,
      end_time: that.data.end_date,
      page:15,
      curpage: that.data.curpage,
      bill_type: that.data.bill_type,
    },
      function (res) {
        console.log(res)
        if (res.data.code == '200') {
          var start_date = that.data.start_date;
          var goods_list = res.data.datas.goods_list;
          var dat = that.data.dat;
          for (var i = 0; i < goods_list.length; i++ ){
            dat.push(goods_list[i])
          }
          that.setData({
            dataList: res.data.datas,
            page_total: res.data.page_total,
            dat: dat
          })
         
          if(res.data.page_total < 1){
               that.setData({
                 if_show:true
               })
          }
           wx.hideLoading();
        }
        
      })
      
  },
  bindpushList() {
    var that = this
    var curpage = JSON.parse(that.data.curpage) + 1
    var page_total = that.data.page_total
    if (curpage > page_total) {
      that.setData({
        if_show: true
      })
      return
    } else {
      that.setData({
        curpage: curpage
      })
      that.getIndex()
    }
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