// pages/classify_tuan/classify_tuan.js
var request = require('../../utils/request.js');
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    goods_class:[],//分类数据
    goods_list:[],//商品数据
    gc_id:'',
    searchText:'',//搜索
    page_total:'',//总页数
    curpage:1,//查询页数
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
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
    var that = this
    that.setData({
      goods_list:[]
    })
    if(that.data.searchText != ''){
      this.goodsList()
      return
    }
    that.categroy()
  },
  categroy(){
    var that = this
    request.postUrl('shequ_categroy.index', {
      tz_id:wx.getStorageSync('tuanzhang_id')
    },function (res) {
      if(res.data.code == '200'){
        that.setData({
          goods_class:res.data.datas.goods_class
        })
        if(app.gc_id !=''){
          that.setData({
            gc_id:app.gc_id
          })
        }else{
          that.setData({
            gc_id:that.data.goods_class[0].gc_id
          })
        }
        console.log(that.data.gc_id)
        that.goodsList()
      }
    })
  },
  goodsList(){
    var that = this
    request.postUrl('shequ_categroy.get_goods_list', {
      goods_name:that.data.searchText,
      goods_class_id:that.data.gc_id,
      page:10,
      curpage:that.data.curpage
    },function (res) {
      if(res.data.code == '200'){
        var list = res.data.datas.goods_list
        var goods_list = that.data.goods_list
        for(var i=0;i<list.length;i++){
          list[i].specShow = false
          goods_list.push(list[i])
        }
        that.setData({
          goods_list:goods_list,
          page_total:res.data.page_total
        })
        console.log(goods_list)
      }
    })
  },
  bindpushList(){
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
      that.goodsList()
    }
  },
  
  bindGC(e){
    var that = this
    var index = e.currentTarget.dataset.index
    // if(index == '-1'){
    //   //重新加载分类数据、商品数据
    //   console.log('重新加载分类数据、商品数据')
    //   // that.setData({
    //   //   searchText:'',
    //   //   curpage:1,
    //   //   goods_list:[],
    //   // })
    //   return
    // }
    var gc_id = that.data.gc_id
    if(gc_id == that.data.goods_class[index].gc_id){
      return
    }else{
      that.setData({
        gc_id:that.data.goods_class[index].gc_id,
        searchText:'',
        curpage:1,
        goods_list:[],
      })
      app.gc_id = that.data.goods_class[index].gc_id
      that.goodsList()
    }
  },

  searchipt(e){
    this.setData({
      searchText:e.detail.value
    })
  },

  searchClick(){
    this.setData({
      gc_id:'',
      curpage:1,
      goods_list:[],
    })
    this.goodsList()
  },

  //添加按钮
  add: function (e) {
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.showToast({
        title: '请先登录',
        icon:'none'
      })
      return;
    }
    var type = e.currentTarget.dataset.type  //加减
    var index = e.currentTarget.dataset.index  //商品下标
    var specIndex = e.currentTarget.dataset.specindex  //规格下标
    var goods_list = that.data.goods_list  //goods_list

    var goods_id = ''
    var cart_num = ''
    if(type == 'add'){
      if(specIndex){
        goods_list[index].goods_list[specIndex].cart_num = JSON.parse(goods_list[index].goods_list[specIndex].cart_num) + 1
        cart_num = goods_list[index].goods_list[specIndex].cart_num
        goods_id = goods_list[index].goods_list[specIndex].goods_id
      }else{
        goods_list[index].goods_list[0].cart_num = JSON.parse(goods_list[index].goods_list[0].cart_num) + 1
        cart_num = goods_list[index].goods_list[0].cart_num
        goods_id = goods_list[index].goods_list[0].goods_id
      }
    }else if(type == 'del'){
      if(specIndex){
        goods_list[index].goods_list[specIndex].cart_num = JSON.parse(goods_list[index].goods_list[specIndex].cart_num) - 1
        cart_num = goods_list[index].goods_list[specIndex].cart_num
        goods_id = goods_list[index].goods_list[specIndex].goods_id
      }else{
        goods_list[index].goods_list[0].cart_num = JSON.parse(goods_list[index].goods_list[0].cart_num) - 1
        cart_num = goods_list[index].goods_list[0].cart_num
        goods_id = goods_list[index].goods_list[0].goods_id
      }
    }
    if(cart_num == 0){
      request.postUrl("cart.delete", {
        goods_id: goods_id
      }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            goods_list: goods_list
          })
          that.initRed() //气泡
        }
      });
    }else if(cart_num > 0){
      request.postUrl('cart.add_shequ', { 
        goods_id: goods_id,
        quantity: cart_num
      }, function (res) {  
        if(res.data.code == '200'){
          that.setData({
            goods_list: goods_list
          })
          that.initRed() //气泡
        }
      })
    }
  },
  //多规格显示隐藏
  specClick(e){
    var index = e.currentTarget.dataset.index  //商品下标
    var goods_list = this.data.goods_list
    goods_list[index].specShow = !goods_list[index].specShow
    this.setData({
      goods_list:goods_list
    })
  },

  //更新购物车气泡数
  initRed: function () {
    var that = this;
    request.postUrl('cart.count_shequ', {}, function (res) {
      if (res) {
        if (res.data.code != 200 || res.data.datas.count == 0) {
          wx.hideTabBarRedDot({
            index: 2,
          })
        } else {
          wx.setTabBarBadge({
            index: 2,
            text: (res.data.datas.count + ""),
          })
        }
      }
    })
  },

  //跳详情
  godetail(e){
    var that = this;
    var id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../goodsDetail_tuan/goodsDetail_tuan?secen=' + 'goods_id|'+ id + '#tz_id|0',
    })
  },

  gopay(e){
    if (!wx.getStorageSync("user_token")) {
      wx.showToast({
        title: '请先登录',
        icon:'none'
      })
      return;
    }
    if(!wx.getStorageSync('tuanzhang_id') || wx.getStorageSync('tuanzhang_id') == 0){
      wx.showToast({
        title: '请选择您的团长',
        icon:'none'
      })
      return
    }
    var id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: '../sureOrder_she/sureOrder_she?cart_id=' + id+'|1' + '&ifcart=' + 0,
    })
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