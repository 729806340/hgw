// pages/store_details/store_details.js
var request = require("../../utils/request");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    chain_info:"", //门店详情
    goods_list :[],//商品列表
    class_list: [], //总分类
    gc_id: 0,//选中的1级分类id
    currentTab:0,
    toView:0,
    miss:0,
    tc_id:0, //总分类锚点定位
    chain_id : 0, //从列表传来的ID
    is_bottom:false,//到底数据
    chain_chosse:false,//本地储存的商品信息
    cart:{
      num :0.00, //商品总价
      cart_count :0//商品数量
    },
    cart_id:'', //支付传参
    btn_choose: false, //支付按钮样式
    cur_page:1 , //页数
    no_count:true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var chain_id = options.chain_id;
    if (wx.getStorageSync("chain_list")){
    }else{
      wx.setStorageSync("chain_list", {})
    }
    that.setData({
      chain_id: chain_id,
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
    this.store_details() //列表
  },
  store_details(){ //列表数据
    var that= this;
    var chain_id = that.data.chain_id
    if (that.data.chain_id){
      request.postUrl("chain.chain_info", { chain_id: that.data.chain_id},function(res){
        if (!res.data.code) {
          return;
        }
        if (res.data.code != 200) {
          wx.showToast({
            title: res.data.datas.error
          });
          return;
        }
        var goods_list = res.data.datas.goods_list;
        var chain_list = wx.getStorageSync("chain_list");
        for (var l of goods_list) {
          l.cart_num = 0
          l.hasOrm = true
        }
        if (chain_list[chain_id]!=undefined) {
          for (var i = 0; i < chain_list[chain_id].length; i++) {
            for (var j = 0; j < goods_list.length; j++) {
              if (chain_list[chain_id][i].goods_id == goods_list[j].goods_id) {
                goods_list[j].cart_num = chain_list[chain_id][i].cart_num
                goods_list[j].hasOrm = false;
              }
            }
          }
        }
        that.setData({
          goods_list: goods_list,
        })
        that.getMoney()//计算金额
        that.setData({
          chain_info: res.data.datas.chain_info,
          goods_list: goods_list,
          class_list: res.data.datas.class_list,
          //chain_list: chain_list,
        })
      })
    }
  },
  go_dress(){ //跳转地图
    //授权成功之后，再调用chooseLocation选择地方
    var that = this;
    wx.getLocation({//获取当前经纬度
      type: 'wgs84', //返回可以用于wx.openLocation的经纬度，官方提示bug: iOS 6.3.30 type 参数不生效，只会返回 wgs84 类型的坐标信息  
      success: function (res) {
        wx.openLocation({//​使用微信内置地图查看位置。
          latitude: Number(that.data.chain_info.latitude),//要去的纬度-地址
          longitude: Number(that.data.chain_info.longitude),//要去的经度-地址
          name: that.data.chain_info.chain_name,
          address: that.data.chain_info.chain_address
        })
      }
    })
  },
  // 点击总商品列表
  tabClick: function (e) {
    var that = this;
    var gc_id = e.currentTarget.dataset.gc_id;
    var index = e.currentTarget.dataset.index;
    var chain_id = that.data.chain_id;
    that.setData({
      toView: e.target.id,
      currentTab: index,
      gc_id: gc_id,
    })
    request.postUrl("chain.get_goods_list",{
      chain_id: that.data.chain_id,
      gc_id: gc_id,
      curpage :1
    },function(res){
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      that.setData({
        is_bottom: false,
      })
      var goods_list = res.data.datas.goods_list;
      var chain_list = wx.getStorageSync("chain_list");
      for (var l of goods_list) {
        l.hasOrm = true
      }
      for (var l of goods_list) {
        l.cart_num = 0
        l.hasOrm = true
      }
      if (chain_list[chain_id] != undefined) {
        if (chain_list[chain_id].length > 0) {
          for (var i = 0; i < chain_list[chain_id].length; i++) {
            for (var j = 0; j < goods_list.length; j++) {
              if (chain_list[chain_id][i].goods_id == goods_list[j].goods_id) {
                goods_list[j].cart_num = chain_list[chain_id][i].cart_num
                goods_list[j].hasOrm = false;
              }
            }
          }
        } 
      } 
      that.setData({
        goods_list: goods_list,
      }) 
    })
  },
  /** 
   * 滑动切换tab 
   */
  bindChange: function (e) {
    var that = this;
    var index = e.detail.current;
    var gc_id = that.data.class_list[index].gc_id;
    var chain_id  = this.data.chain_id;
    that.setData({
      currentTab: e.detail.current,
      toView: 'list'+e.detail.current,
      gc_id: gc_id,
    });
    request.postUrl("chain.get_goods_list", {
      chain_id: that.data.chain_id,
      gc_id: gc_id,
      cur_page: 1
    }, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      var goods_list = res.data.datas.goods_list;
      for (var l of goods_list) {
        l.cart_num = 0
        l.hasOrm = true
      }
      that.setData({
        is_bottom : false,
      })
      var chain_list = wx.getStorageSync("chain_list");
      if (chain_list[chain_id] != undefined) {
        if (chain_list[chain_id].length > 0) {
          for (var i = 0; i < chain_list[chain_id].length; i++) {
            for (var j = 0; j < goods_list.length; j++) {
              if (chain_list[chain_id][i].goods_id == goods_list[j].goods_id) {
                goods_list[j].cart_num = chain_list[chain_id][i].cart_num
                goods_list[j].hasOrm = false;
              }
            }
          }
          that.setData({
            goods_list: goods_list,
          })
        } 
      }else{
        for (var l of goods_list) {
          l.cart_num = 0
          l.hasOrm = true
        }
        that.setData({
          goods_list: goods_list,
        })
      } 
    })
  },
  getMore: function () { //上拉加载更多
    var chain_id  =this.data.chain_id
    console.log("lalal ")
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      request.postUrl("chain.get_goods_list", {
        chain_id: that.data.chain_id,
        gc_id: that.data.gc_id,
        curpage: that.data.cur_page,
      }, function (res) {
        if (!res.data.code) {
          return;
        }
        if (res.data.code != 200) {
          wx.showToast({
            title: res.data.datas.error
          });
          return;
        }
        var goods_list = res.data.datas.goods_list;
        var chain_list = wx.getStorageSync('chain_list')
        if (that.data.cur_page === 1) {
          if (chain_list[chain_id] != undefined) {
            if (chain_list[chain_id].length > 0) {
              for (var i = 0; i < goods_list.length; i++) {
                for (var l = 0; l < chain_list[chain_id].length; l++) {
                  if (chain_list[chain_id][l].goods_id == goods_list[i].goods_id) {
                    goods_list[i].cart_num = chain_list[chain_id][l].cart_num
                    goods_list[i].hasOrm = false;
                  }
                }
              }
              that.setData({
                goods_list: goods_list,
              })
            } else {
              for (var l of goods_list) {
                l.cart_num = 0
                l.hasOrm = true
              }
              that.setData({
                goods_list: goods_list,
                is_bottom: res.data.hasmore > 0 ? false : true
              })
            }
          }
        } else {
          for (var l of goods_list) {
            l.cart_num = 0
            l.hasOrm = true
          }
          if (chain_list[chain_id] != undefined) {
            if (chain_list[chain_id].length > 0) {
              for (var i = 0; i < goods_list.length; i++) {
                for (var l = 0; l < chain_list[chain_id].length; l++) {
                  if (chain_list[chain_id][l].goods_id == goods_list[i].goods_id) {
                    goods_list[i].cart_num = chain_list[chain_id][l].cart_num
                    goods_list[i].hasOrm = false;
                  }
                }
              }
            } 
          }
          that.setData({
            goods_list: that.data.goods_list.concat(res.data.datas.goods_list),
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        }
      })
    }
  },
  getMoney(){ //计算总额
    var posNUM = 0;
    var prcie = 0;
    var chain_list = wx.getStorageSync("chain_list")
    var chain_id = this.data.chain_id;
    if (chain_list[chain_id] != undefined) {
        for (var i = 0; i < chain_list[chain_id].length; i++) {
          posNUM += parseInt(chain_list[chain_id][i].cart_num);
          prcie += chain_list[chain_id][i].cart_num * chain_list[chain_id][i].goods_price
        }
        this.setData({
          btn_choose: true,
          cart: {
            num: prcie.toFixed(2), //商品总价
            cart_count: posNUM//商品数量
          }
        })
    } else {
      this.setData({
        btn_choose: false,
        cart: {
          num: 0.00, //商品总价
          cart_count: 0//商品数量
        }
      })
    } 
  },
  bindPlus:function(e){ // 添加购物车
    var item = e.currentTarget.dataset.item;
    var index = e.currentTarget.dataset.index;
    var goods_list = this.data.goods_list;
    var chain_id = this.data.chain_id
    var num =0; //数量增加
    var chain_list = wx.getStorageSync("chain_list"); //获取储存的商品列表信息
    console.log(goods_list[index])
    this.setData({
      no_count: true,  //避免触发input事件
    })
    if (goods_list[index].stock==0){
      return;
    }
    if (goods_list[index].cart_num==0){ //该商品没有增加过
      num++;
      if (num >= goods_list[index].stock){ //库存不够
        wx.showToast({
          title:"没有库存啦！"
        })
        num = goods_list[index].stock
      }
      goods_list[index].hasOrm = false;
      goods_list[index].cart_num = num;
      var rilm = {
        goods_id: goods_list[index].goods_id,
        goods_price: goods_list[index].goods_price,
        cart_num: num,
        cart_id: goods_list[index].goods_id + "|" + num,
      }
      if (chain_list.hasOwnProperty(chain_id)){
        chain_list[chain_id].push(rilm)
      }else{
        chain_list[chain_id] = [];
        chain_list[chain_id].push(rilm)
      }
      this.setData({
        goods_list: goods_list,
      })
      wx.setStorageSync("chain_list",chain_list)
    }else{ //增加过该商品
      for(var l =0;l<chain_list[chain_id].length;l++){
        if (chain_list[chain_id][l].goods_id == goods_list[index].goods_id){
          var carnum = chain_list[chain_id][l].cart_num
        }
      }
      carnum++;
      if (carnum >= goods_list[index].stock) { //库存不够
        wx.showToast({
          title: "没有库存啦！"
        })
        carnum = goods_list[index].stock
      }
      goods_list[index].cart_num = carnum
      goods_list[index].hasOrm = false;
      this.setData({
        goods_list: goods_list,
      })
      for (var i = 0; i < chain_list[chain_id].length;i++){
        if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id){
          chain_list[chain_id][i].cart_num = carnum
          chain_list[chain_id][i].cart_id = goods_list[index].goods_id + "|" + carnum
        }
      }
      wx.setStorageSync("chain_list", chain_list)
    }
    this.getMoney() //计算金额
  },
  bindMinus:function(e){ //删除
    var item = e.currentTarget.dataset.item;
    var chain_list = wx.getStorageSync("chain_list"); //获取储存的商品列表信息
    var goods_list=this.data.goods_list;
    var index = e.currentTarget.dataset.index;
    var chain_id = this.data.chain_id;
    for (var i = 0; i < chain_list[chain_id].length; i++) {
      if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id) {
        var num = chain_list[chain_id][i].cart_num; //数量
      }
    }
    
    num--;
    if (num == 0) {//该商品减少为0
      goods_list[index].cart_num = 0;
      goods_list[index].hasOrm = true;
      this.setData({
        no_count : false,  //避免触发input事件
      })
      for (var i = 0; i < chain_list[chain_id].length;i++){
        if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id){
          chain_list[chain_id].splice(i,1)
        }
      }
      if (chain_list[chain_id].length==0){
        delete chain_list[chain_id]
      }
    } else{
      goods_list[index].cart_num = num;
      goods_list[index].hasOrm = false;
      for (var i = 0; i < chain_list[chain_id].length; i++) {
        if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id) {
          chain_list[chain_id][i].cart_num = num
          chain_list[chain_id][i].cart_id = goods_list[index].goods_id + "|" + num
        }
      }
    }
    this.setData({
      goods_list: goods_list,
    })
    wx.setStorageSync("chain_list", chain_list);
    this.getMoney() //计算金额
  },
  /* 输入框事件 */
  bindManual: function (e) {
    var item = e.currentTarget.dataset.item;
    var goods_list = this.data.goods_list;
    var chain_list = wx.getStorageSync("chain_list"); //获取储存的商品列表信息
    var index = e.currentTarget.dataset.index;
    var chain_id = this.data.chain_id;
    if (this.data.no_count == false) {
      return;
    }
    if (e.detail.value == '' || e.detail.value == 0) {
      e.detail.value = 1;
    }
    var carts_num = parseInt(e.detail.value);
    if (carts_num <= 0) {
      carts_num = 1;
    }
    if (carts_num > goods_list[index].stock) {
      wx.showToast({
        title: '商品所剩无几了'
      });
      goods_list[index].hasOrm = false;
      goods_list[index].cart_num = 1;
      for (var i = 0; i < chain_list[chain_id].length;i++){
        if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id){
          chain_list[chain_id][i].cart_num = 1;
          chain_list[chain_id][i].cart_id = goods_list[i].goods_id + "|" + 1;
        }
      }
      this.setData({
        goods_list: goods_list
      })
      wx.setStorageSync("chain_list", chain_list) 
      this.getMoney() //计算金额
      return;
    }else{
      goods_list[index].hasOrm = false;
      goods_list[index].cart_num = carts_num
      for (var i = 0; i < chain_list[chain_id].length; i++) {
        if (chain_list[chain_id][i].goods_id == goods_list[index].goods_id) {
          chain_list[chain_id][i].cart_num = carts_num;
          chain_list[chain_id][i].cart_id = goods_list[index].goods_id + "|" + carts_num;
        }
      }
      this.setData({
        goods_list: goods_list
      })
      wx.setStorageSync("chain_list", chain_list);
      this.getMoney() //计算金额
      return;
    }
  },
  goSureOrder(){ //结算
    var user_token = wx.getStorageSync("user_token");
    var chain_list = wx.getStorageSync("chain_list");
    var chain_id = this.data.chain_id;
    if (user_token==""){
      wx.switchTab({
        url: '../me/me',
      })
      return;
    }
    if (user_token != "" && chain_list[chain_id]!=undefined){
      console.log(1233)
      var strings = [];
      for (var l of chain_list[chain_id]) {
        strings.push(l.cart_id)
      }
      var cart_id = strings.join(",");
      wx.navigateTo({
        url: '../sureOrder/sureOrder?cart_id=' + cart_id + '&ifcart=' + 0 + '&chain_id=' + this.data.chain_id,
      })
    }else{
      return;
    }
  },
  callTel:function(){
    var that = this;
    if (that.data.chain_info.chain_phone){
      wx.makePhoneCall({
        phoneNumber: this.data.chain_info.chain_phone,
      })
    }
    
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