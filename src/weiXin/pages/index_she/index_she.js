var request = require('../../utils/request.js');
var amapFile = require('../../utils/amap-wx.js');
var app = getApp();
var util = require('../../utils/util.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    Istrue: true, //自动轮播
    banner_list:[],
    goods_class_list:[],
    default_address:'',
    dataList:'',//首页所有数据
    tab:'0',//自提 包邮
    a:false,

    is_Location:true,//是否授权定位
    default_tz:'',//默认团长信息
    if_show:false,//不同团长弹窗是否显示
    lay_x:'',
    lay_y:'',
    view_tuanzhang_id:'',//二维码进来团长id
    changeTuanList:'',//不同团长数据
    is_shequ_tuanzhang:'',//自己是不是团长

    cur_time:'',
    interval:'',
    time:'',

    city:'',
    text_xs:'',
    time_xs_text:'',
    type_xs:0,//0未开始  1已开始  2已结束
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    // var temp_goods = wx.getStorageSync('temp_goods');
    // wx.setStorageSync("user_token", res1.data.datas.user_token);
    // that.setData({
    //   view_tuanzhang_id:21
    // })
    if(options.scene){
      if(!wx.getStorageSync('user_token')){
        wx.setStorageSync("tuanzhang_id", options.scene)
      }
      that.setData({
        view_tuanzhang_id:options.scene
      })
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
    var that = this
    that.setData({
      is_shequ_tuanzhang:wx.getStorageSync('is_shequ_tuanzhang')
    })
    if(!wx.getStorageSync('tuanzhang_id')){
      if(that.data.view_tuanzhang_id == ''){
        that.getLocation()  //获取定位后  开始加载相关数据
      }else{
        wx.setStorageSync("tuanzhang_id", that.data.view_tuanzhang_id)
        that.default_tuanzhang() //默认团长
      }
    }else{
      if(!wx.getStorageSync('user_token')){
        wx.setStorageSync("tuanzhang_id", that.data.view_tuanzhang_id)
      }
      that.default_tuanzhang() //默认团长
      if(that.data.view_tuanzhang_id != '' && that.data.view_tuanzhang_id != wx.getStorageSync('tuanzhang_id')){
        that.change_tuanzhang() //不同团长
      }
    }
  },
  getLocation: function () {
    var that = this;
    wx.getLocation({
      type: "gcj02",
      success: function (res) {
        console.log(res)
        var longitude = res.longitude;
        var latitude = res.latitude;
        that.setData({
          lay_x:longitude,
          lay_y:latitude
        })
        that.setData({
          is_Location:true
        })
        var myAmapFun = new amapFile.AMapWX({
          key: app.map_key
        });
        myAmapFun.getRegeo({
          location: longitude+','+latitude,
          success: function(res) {
            that.setData({
              city:res[0].regeocodeData.addressComponent.city
            })
            app.user_city = res[0].regeocodeData.addressComponent.city
            that.default_tuanzhang() //默认团长
          },
          fail: function(res) {
            console.log(res);
          }
        })
      },
      fail: function(res){
        that.setData({
          is_Location:false
        })
      }
    })
  },
  //获取数据
  getIndex(){
    var that = this
    request.postUrl("shequ_captial_home.index", {
      // area_type:that.data.city
      tz_id:wx.getStorageSync('tuanzhang_id'),
    }, function (res) {
        if(res.data.code == '200'){
          // 停止下拉动作
          wx.stopPullDownRefresh();
          var list = res.data.datas
          for(var i=0;i<list.goods_list.bydj.length;i++){
            list.goods_list.bydj[i].fengmian = true
            list.goods_list.bydj[i].specShow = false
          }
          for(var i=0;i<list.goods_list.mdzt.length;i++){
            list.goods_list.mdzt[i].fengmian = true
            list.goods_list.mdzt[i].specShow = false
          }
          that.setData({
            dataList:list
          })
          console.log(that.data.dataList)
          if(res.data.datas.goods_list.mdzt.length == 0){
            that.setData({
              tab:1
            })
          }
          clearInterval(that.data.interval);
          var end_time = res.data.datas.tuan_info.end_time
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

          // 秒杀时间
          if(res.data.datas.xianshi_list.length > 0){
            var end_time_xs = res.data.datas.xianshi_list[0].end_time  
            var start_time_xs = res.data.datas.xianshi_list[0].start_time 
            var time_xs_text = util.js_date_time(start_time_xs*1000)
            that.setData({
              time_xs_text:time_xs_text
            })
            //0未开始  1已开始  2已结束
            if(timestamp > start_time_xs){
              if(timestamp > end_time_xs){
                that.setData({
                  text_xs:'已结束',
                  type_xs:2
                })
              }else{
                that.setData({
                  text_xs:'抢购中',
                  type_xs:1
                })
              }
            }else{
              that.setData({
                text_xs:'即将开始',
                type_xs:0
              })
            }
          }
          
          that.data.interval = interval;
          that.initRed() //气泡
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
    })
  },
  //跳转自提点
  goAddress: function () {
    var that = this;
    if (!wx.getStorageSync('user_token')) {
      wx.switchTab({
        url: '../me/me'
      })
      return;
    }
    wx.getSetting({
      success: (res) => {
        if (!res.authSetting['scope.userLocation']) {
          wx.getLocation({
            type: "gcj02",
            success: function (res) {
              wx.navigateTo({
                url: '../dinosaur_address/dinosaur_address',
              })
            },
            fail: function(res){
              that.setData({
                is_Location:false
              })
            }
          })
          // wx.openSetting({
          //   success: (res) => {
          //     if (res.authSetting['scope.userLocation']) {
          //       wx.navigateTo({
          //         url: '../dinosaur_address/dinosaur_address',
          //       })
          //       // that.getLocation();
          //     }

          //   }
          // })
        } else {
          wx.navigateTo({
            url: '../dinosaur_address/dinosaur_address',
          })
        }
      }
    })
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
    var dataList = that.data.dataList  //dataList.goods_list.mdzt

    var goods_id = ''
    var cart_num = ''
    if(type == 'add'){
      if(specIndex){
        dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num = JSON.parse(dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num) + 1
        cart_num = dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num
        goods_id = dataList.goods_list.mdzt[index].goods_list[specIndex].goods_id
      }else{
        dataList.goods_list.mdzt[index].goods_list[0].cart_num = JSON.parse(dataList.goods_list.mdzt[index].goods_list[0].cart_num) + 1
        cart_num = dataList.goods_list.mdzt[index].goods_list[0].cart_num
        goods_id = dataList.goods_list.mdzt[index].goods_list[0].goods_id
      }
    }else if(type == 'del'){
      if(specIndex){
        dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num = JSON.parse(dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num) - 1
        cart_num = dataList.goods_list.mdzt[index].goods_list[specIndex].cart_num
        goods_id = dataList.goods_list.mdzt[index].goods_list[specIndex].goods_id
      }else{
        dataList.goods_list.mdzt[index].goods_list[0].cart_num = JSON.parse(dataList.goods_list.mdzt[index].goods_list[0].cart_num) - 1
        cart_num = dataList.goods_list.mdzt[index].goods_list[0].cart_num
        goods_id = dataList.goods_list.mdzt[index].goods_list[0].goods_id
      }
    }
    if(cart_num == 0){
      request.postUrl("cart.delete", {
        goods_id: goods_id
      }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            dataList: dataList
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
            dataList: dataList
          })
          that.initRed() //气泡
        }
      })
    }
  },
  //多规格显示隐藏
  specClick(e){
    var index = e.currentTarget.dataset.index  //商品下标
    var tab = this.data.tab
    var dataList = this.data.dataList
    if(tab == 0){
      dataList.goods_list.mdzt[index].specShow = !dataList.goods_list.mdzt[index].specShow
      this.setData({
        dataList:dataList
      })
    }
    if(tab == 1){
      dataList.goods_list.bydj[index].specShow = !dataList.goods_list.bydj[index].specShow
      this.setData({
        dataList:dataList
      })
    }
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

  typeTab(e){
    this.setData({
      tab:e.currentTarget.dataset.tab
    })
  },

  //开启定位
  openLocation(){
    var that = this
    wx.getSetting({
      success: (res) => {
        if (!res.authSetting['scope.userLocation']) {
          wx.openSetting({
            success: (res) => {
              if (res.authSetting['scope.userLocation']) {
                that.getLocation();
              }

            }
          })
        } else {
          
        }
      }
    })
  },
  
  //默认团长
  default_tuanzhang(){
    var that = this
    request.postUrl('shequ_captial_home.default_tuanzhang', {
      tz_id:wx.getStorageSync('tuanzhang_id'),
      lay_x:that.data.lay_x,
      lay_y:that.data.lay_y
    }, function (res) {
      if(res.data.code == '200'){
        that.setData({
          default_tz:res.data.datas
        })
        wx.setStorageSync("tuanzhang_id", res.data.datas.tuanzhang_info.id)
        that.getIndex()//首页数据
        if(wx.getStorageSync('is_shequ_tuanzhang') !=2 && that.data.view_tuanzhang_id != '' && that.data.view_tuanzhang_id != wx.getStorageSync('tuanzhang_id')){
          that.change_tuanzhang() //不同团长
        }
      }
    })
  },
  //关闭不同团长弹窗
  hide(){
    this.setData({
      if_show:false
    })
  },
  //设置默认团长
  setTuanzhang(e){
    var that = this
    var tz_id = e.currentTarget.dataset.id
    request.postUrl("member_index.set_default_tuanzhang", {
      tz_id:tz_id
    }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            if_show:false
          })
          wx.setStorageSync("tuanzhang_id", tz_id)
          that.setData({
            default_tz:res.data.datas
          })
          that.getIndex()//首页数据
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
    })
  },

  //不同团长
  change_tuanzhang(){
    var that = this
    request.postUrl("shequ_captial_near.change_tuanzhang", {
      lay_x:that.data.lay_x,
      lay_y:that.data.lay_y,
      view_tuanzhang_id:that.data.view_tuanzhang_id,
    }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            changeTuanList:res.data.datas,
            if_show:true
          })
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
    })
  },

  //导航切换
  navClick(e){
    var index = e.currentTarget.dataset.index
    this.setData({
      nav_index:index
    })
  },
  //跳团长页面
  goTuan(){
    wx.navigateTo({
      url: '../community/community?scene=7',
    })
  },

  playVideo(e){
    var index = e.currentTarget.dataset.index
    let videoContext = wx.createVideoContext(`video-${index}`)
    videoContext.play()
    this.setData({
      a:true
    })
  },

  //跳分类
  goClassify(e){
    var gc_id = e.currentTarget.dataset.gcid
    app.gc_id = gc_id
    wx.switchTab({
      url: '../classify_tuan/classify_tuan',
    })
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    clearInterval(this.data.interval)
    this.setData({
      view_tuanzhang_id:''
    })
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    clearInterval(this.data.interval);
    this.setData({
      view_tuanzhang_id:''
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    var that = this
    that.setData({
      is_shequ_tuanzhang:wx.getStorageSync('is_shequ_tuanzhang')
    })
    that.getLocation()  //获取定位后  开始加载相关数据
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
    var tz_id = 0
    if(wx.getStorageSync('tuanzhang_id')){
      tz_id = wx.getStorageSync('tuanzhang_id')
    }
    return {
      path: 'pages/index_she/index_she?secen=' + tz_id,
    }
  }
})