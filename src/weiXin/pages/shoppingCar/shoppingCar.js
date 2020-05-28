// pages/shoppingCar/shoppingCar.js
var request = require('../../utils/request.js');
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    hasGoods: false,
    cart_list: [], 
    check_list: [],
    cart: {
      num: 0,  //总价
      cart_count: 0, //数量
    },
    all_check: false, //全选
    click_check:false,//单选
    shop_list: [], // 购物车列表
    hasList: false,// 列表是否有数据
    totalPrice: 0,// 总价，初始为0
    selectAllStatus: true,// 全选状态，默认全选
    goods_num: '' ,//商品数量
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onLoad: function (options) {
   
  },
  onShow: function () {
    var that = this;
    this.initRed()//气泡
      if (wx.getStorageSync('user_token') == '') {
        wx.switchTab({
          url: '../me/me'
        })
        return;
    }
    var check_list = wx.getStorageSync('check_list');
    if (check_list == '') {
      check_list = [];
    }
    that.setData({
      check_list: check_list,
    })
    this.list_wa()
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    
  },
  //添加按钮
  add: function (e) {
    var that = this;
    var item = e.currentTarget.dataset.item; //goodsid 参数
    var index1 = e.currentTarget.dataset.index
    var index = e.currentTarget.dataset.numer
    var goods = this.data.shop_list[index].goods[index1]
    goods.goods_num++
    if (goods.goods_num<=1){
        goods.goods_num=1;
      that.setData({
        shop_list: that.data.shop_list,
      })
    }
    if (goods.goods_num >= goods.goods_storage){
      goods.goods_num = goods.goods_storage;
      that.setData({
        shop_list: that.data.shop_list,
      })
      wx.showToast({
        title:"没库存啦！"
      })
    }
    request.postUrl('cart.add_shequ', { goods_id: item, quantity: goods.goods_num,config_tuan_id:'1'}, function (res) {  
      that.setData({
        shop_list: that.data.shop_list
      })
      that.calTotalMoney() //总价
      that.initRed()//气泡
    })
   
  },
  del: function (e) {
    var that = this;
    var item = e.currentTarget.dataset.item; //id参数
    var index1 = e.currentTarget.dataset.index
    var index = e.currentTarget.dataset.numer
    var goods = this.data.shop_list[index].goods[index1]
    goods.goods_num = parseInt(goods.goods_num) - 1;
    console.log(goods.goods_num)
    if (goods.goods_num == 0) {
      goods.goods_num = 1;
      wx.showModal({
        title: '提示',
        content: '确认要移出购物车？',
        success: function (res) {
          if (res.confirm) {
            request.postUrl('cart.remove', { cart_id: item}, function (res) {
              if (res.data.code == 200) {
                let dealerStorage = wx.getStorageSync('dealer_storage');
                if (dealerStorage) {
                  let pyramid = dealerStorage[goods.goods_id];
                  if (pyramid) {
                    dealerStorage[goods.goods_id] = '';
                    wx.setStorageSync('dealer_storage', dealerStorage);
                  }
                }

                if (that.data.shop_list[index].goods.length == 1) {
                  that.data.shop_list.splice(index, 1)
                } else {
                  that.data.shop_list[index].goods.splice(index1, 1)
                }
                if (that.data.shop_list.length == 0) {
                  that.data.all_check = false
                }
                that.setData({
                  all_check: that.data.all_check,
                  shop_list: that.data.shop_list,
                  cart: {
                    num: that.data.cart.num,
                    cart_count: that.data.cart.cart_count
                  }
                })
                that.initRed()//气泡
                that.calTotalMoney() //金额计算
              }
            })
          } else if (res.cancel) {
            that.setData({
              shop_list: that.data.shop_list
            })
          }
        }
      })
    } else {
      request.postUrl('cart.edit', { cart_id: item, quantity: goods.goods_num}, function (res) {
        that.setData({
          shop_list:that.data.shop_list
        })
        that.calTotalMoney() //总价
        that.initRed()//气泡
      })
    }
  },
  // 单个选择
  choose: function (e) {
    var index1 = e.currentTarget.dataset.index
    var index = e.currentTarget.dataset.lop
    var list = this.data.shop_list[index]['goods'],
      len = list.length;
    var check_list = this.data.check_list
    if (list[index1]['checked']) {
      this.data.shop_list[index]['checked'] = false;
      this.all_check = false;
      list[index1]['checked'] = !list[index1]['checked'];
      var index2 = check_list.indexOf(list[index1].goods_id);
      if (index2 > -1) {
        check_list.splice(index2, 1);
      } 
    } else {
      list[index1]['checked'] = !list[index1]['checked'];
      // 判断是否选择当前店铺的全选
      var flag = true;
      for (var i = 0; i < list.length; i++) {
        if (list[i].checked == false) {
          flag = false;
          break; 
        }
      }
      console.log(flag)
      flag == true ? this.data.shop_list[index]['checked'] = true : this.data.shop_list[index]['checked'] = false;
      if (check_list.indexOf(list[index1].goods_id) == -1 ){
        check_list.push(list[index1].goods_id);
      }
      
    }
    this.setData({
      shop_list: this.data.shop_list,
      all_check: this.data.all_check,
      check_list:this.data.check_list
    })
    wx.setStorageSync("check_list",check_list)
    // 判断是否选择所有商品的全选
    this.isChooseAll();
    //总价
    this.calTotalMoney()
  },
  //店铺全选
  checkAll: function (e) {
    var that = this;
    var index = e.currentTarget.dataset.id
    var shop_list = that.data.shop_list
    var list = shop_list[index].goods,
    len = list.length;
    var check_list = this.data.check_list
    if (shop_list[index].checked) {
      for (var i = 0; i < len; i++) {
        list[i].checked = false; 
        var index2 = check_list.indexOf(list[i].goods_id);
        console.log(index2)
        if (index2 > -1) {
          check_list.splice(index2, 1);
        } 
      }
    } else {
      for (var i = 0; i < len; i++) {
        list[i].checked = true;
        check_list.push(list[i].goods_id);
        }
    }
    that.data.shop_list[index].checked = !that.data.shop_list[index].checked;
    that.isChooseAll() //判断是否全选
    that.calTotalMoney()//总价
    wx.setStorageSync("check_list", check_list)
    that.setData({
      check_list: check_list,
      shop_list: shop_list
    })
  },
  // 全部商品全选
  chooseAllGoods: function () {
    var flag = true;
    if (this.data.all_check) {
      flag = false;
    }
    var len = this.data.shop_list.length
    for (var i = 0 ; i < len; i++) {
      this.data.shop_list[i]['checked'] = flag;
      var list = this.data.shop_list[i].goods;
      var len1 = list.length;
      for (var k = 0; k < len1; k++) {
        list[k]['checked'] = flag;
      }
    }
    this.data.all_check = !this.data.all_check;
    this.calTotalMoney() //总价
    var check_list = [];
    var temp = this.data.shop_list;
    if (this.data.all_check) {
      for (var shop of temp) {
        for (var goods of shop.goods) {
          check_list.push(goods.goods_id);
        }
      }
    }
    wx.setStorageSync("check_list", check_list)
    this.setData({
      check_list: check_list,
      all_check: this.data.all_check,
      shop_list: this.data.shop_list
    })
  },
  // 计算商品总金额 和数量
  calTotalMoney: function () {
    var oThis = this;
    this.data.cart.num = 0;
    this.data.cart.cart_count = 0;
    for (var i = 0, len = this.data.shop_list.length; i < len; i++) {
      var list = this.data.shop_list[i].goods;
      list.forEach(function (item, index, arr) {
        if (list[index]['checked']) {
          if (item.xianshi_info.xianshi_name!=""){ //是限时商品
            if (item.xianshi_info.xianshi_limit == 0 ||item.goods_num<=item.xianshi_info.xianshi_limit){
              oThis.data.cart.num += parseFloat(item.xianshi_info.xianshi_price) * parseFloat(item.goods_num);
            }else{
              oThis.data.cart.num += parseFloat(item.xianshi_info.xianshi_price) * parseFloat(item.xianshi_info.xianshi_limit) + parseFloat(item.goods_price) * parseFloat(item.goods_num - item.xianshi_info.xianshi_limit);
            }
            oThis.data.cart.cart_count += parseFloat(item.goods_num)
          }else{
            oThis.data.cart.num += parseFloat(item.goods_price) * parseFloat(item.goods_num);
            oThis.data.cart.cart_count += parseFloat(item.goods_num)
          }
        }
      });  
    }
    var numph = oThis.data.cart.num.toFixed(2)
    var numh = oThis.data.cart.cart_count
    oThis.setData({
      cart:{
        num: numph, //总价
        cart_count: numh  //数量
      }
    })
  },
  //右上角删除
  icon_delete: function (e) {
    var that = this;
    var id = e.currentTarget.dataset.id
    var index1 = e.currentTarget.dataset.index
    var index = e.currentTarget.dataset.lop
    var goodsid = e.currentTarget.dataset.goodsid
    wx.showModal({
      title: '提示',
      content: '确定删除这些商品?',
      success: function (res) {
        if (res.confirm) {
          request.postUrl("cart.remove", { cart_id: id }, function (res) {
            if(res.data.code==200){
              if (that.data.shop_list[index].goods.length==1){
                that.data.shop_list.splice(index, 1)
              }else{
                that.data.shop_list[index].goods.splice(index1, 1)
              }
              if(that.data.shop_list.length==0){
                that.data.all_check=false
              }
              that.calTotalMoney() //金额计算
              that.setData({
                all_check: that.data.all_check,
                shop_list: that.data.shop_list,
                cart:{
                  num : that.data.cart.num,
                  cart_count : that.data.cart.cart_count
                }
              })
              var check_list = that.data.check_list;
              check_list.splice(check_list.indexOf(goodsid),1);
              wx.setStorageSync('check_list', check_list);
              that.initRed()//气泡
            }
          })
        } else if (res.cancel) {
          that.setData({
            shop_list: that.data.shop_list
          })
        }
       
        
      }
    })
  },
  // 判断是否选择所有商品的全选
  isChooseAll: function () {
    var flag1 = true;
    for (var i = 0, len = this.data.shop_list.length; i < len; i++) {
      if (this.data.shop_list[i]['checked'] == false) {
        flag1 = false;
        break;
      }
    }
    flag1 == true ? this.data.all_check = true : this.data.all_check = false;
    this.setData({
      all_check: this.data.all_check
    })
  },
  //更新购物车气泡数
  initRed: function () {
    var that = this;
    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
      console.log(temp_goods.length)
      if (temp_goods != '') {
        sum = temp_goods.length;
        wx.setTabBarBadge({
          index: 2,
          text: sum + "",
        })
      } else {
        wx.hideTabBarRedDot({
          index: 2,
        })
      }
    } else {
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
    }
  },
  /* 输入框事件 */
  bindManual: function (e) {
    var that = this;
    var item = e.currentTarget.dataset.item;
    var quantity = e.detail.value
    var index1 = e.currentTarget.dataset.index
    var index = e.currentTarget.dataset.lop
    if (e.detail.value == '' || e.detail.value == 0) {
      quantity = 1;
    }
    request.postUrl('cart.edit', { cart_id: item, quantity: quantity}, function (res) {
      if(res.data.code==200){
        that.data.shop_list[index].goods[index1].goods_num = res.data.datas.quantity
        that.calTotalMoney() //金额计算
        that.setData({
          shop_list:that.data.shop_list
        })
        that.initRed()//气泡
      }
    })
  },
  //刷新列表数据
  list_wa: function(){
    var that = this;
    if(wx.getStorageSync('user_token')){
      request.postUrl("cart.list_shequ", {}, function (res) {
        if (!res.data.code) {
          return;
        }
        if (res.data.datas.cart_list) {
          var num1 = res.data.datas.cart_list
          var check_list = wx.getStorageSync('check_list');
          for (var i = 0; i < num1.length; i++) {
            num1[i].checked = true;
            for (var j = 0; j < num1[i].goods.length; j++) {
              num1[i].goods[j].checked = true;
            }
          }
      
          if (check_list == '') {
            for (var i = 0; i < num1.length; i++) {
              num1[i].checked = false;
              for (var j = 0; j < num1[i].goods.length; j++) {
                num1[i].goods[j].checked = false;
              }
            }
            check_list = [];
          } else {
            if (check_list.length == 0) {
              return;
            } else {
              for (var i = 0; i < num1.length; i++) {
                for (var j = 0; j < num1[i].goods.length; j++) {
                  if (check_list.indexOf(num1[i].goods[j].goods_id) != -1) {
                    num1[i].goods[j].checked = true;
                  } else {
                    num1[i].goods[j].checked = false;
                  }
                  // 判断是否选择当前店铺的全选
                  if (num1[i].goods[j]['checked'] == false) {
                    num1[i].checked = false;
                    that.data.all_check = false
                  } 
                }
              }

            }
          }
          console.log(num1)
          that.setData({
            shop_list: num1,
            hasList: true,
            check_list: check_list
          })
          that.calTotalMoney();
          that.isChooseAll();
        }
      })
    }else{
      var temp_goods = wx.getStorageSync("temp_goods")
      this.setData({
        shop_list: temp_goods
      })
      that.calTotalMoney() //金额计算
    } 
  },
  goSureOrder: function () {
    if(!wx.getStorageSync('tuanzhang_id') || wx.getStorageSync('tuanzhang_id') == 0){
      wx.showToast({
        title: '请选择您的团长',
        icon:'none'
      })
      return
    }
    var that = this;
    var list = this.data.shop_list;
    var check_list = this.data.check_list;
    if(wx.getStorageSync('user_token')){
      if (check_list.length == 0) {
        return;
      }
      var order_check = [];
      var cart_id = [];
      for (let shop of list) {
        for (let goods of shop.goods) {
          if (check_list.indexOf(goods.goods_id) != -1) {
            cart_id.push(goods.cart_id + "|" + goods.goods_num);
            order_check.push(goods.goods_id);
          }
        }
      }
      wx.setStorageSync("order_check", order_check);
      wx.navigateTo({
        url: '../sureOrder_she/sureOrder_she?cart_id=' + cart_id + '&ifcart=' + 1,
      })
    }else{
      wx.switchTab({
          url: '../me/me'
      })
        return;
    }
    
  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetail_tuan/goodsDetail_tuan?secen=goods_id|'+item.goods_id+'#tz_id|0'
    })
  },
  goClass: function () {
    wx.switchTab({
      url: '../classify_tuan/classify_tuan',
    })
  },
  goStore: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + item.store_id,
    })
  },
  goquan(){ //跳转领券页面

  }
})