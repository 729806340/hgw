// pages/chooseLib/chooseLib.js
var app = getApp();
var request = require('../../utils/request.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    pay_list:[], //返回数据列表,
    cartid:'', //购物车传来的数据
    addressid:null, //缓存中获取的地址id
    address_info:'',//返回的地址信息,
    area_info :'',
    address:'', //自提点的地址信息
    vat_hash: '', //上一步返回的vat hash
    offpay_hash: '', //上一步返回的offpay_hash
    offpay_hash_batch: '',//上一步返回的offpay_hash_batch,
    available_predeposit: 0,//用户预存款余额
    available_rc_balance: 0,//用户充值卡余额,
    pd_pay : 0, // 实时预存款
    rcb_pay :0 ,//实时卡余额
    order_amount : 0,//实时金额
    amount : 0, //红包未减去金额
    num : 0,//总件数,
    is_submit : false, //提交按钮
    pay_name:'online',//支付类型,
    ifcart: 1,  //是否从购物车提交：1从购物车提交，0直接购买
    rpt_list :[], //红包列表
    store_voucher_list :[], //代金券列表,
    show_ms :false, //遮罩层,
    checked : false, //红包状态,
    choseture: false,//红包按钮状态,
    hobao : "",//红包信息（id|num）
    show_vh:false, //代金券遮罩层,
    coupon: "",//代金券信息（id|num）,
    Redenvelopes : 0, //红包可用张数
    redNm: 0,//红包选中金额
    Voucheramount : 0,//代金券选中金额,
    vou_allmessage : '',//优惠券信息
    vou_allNum : 0,//选中优惠券金额
    message: '', // 留言信息,
    yuesdsdwd : false,
    yuesdsdwds :false,
    rcb_pays : 0,
    pd_pays  : 0,
    order_info :{},
    again : true, //支付避免重复提交
    VoucherStatus : false, //优惠券领取遮罩
    store_voucher_list_all : [],//优惠使用券列表
    store_voucher_list :[],//优惠券领取
    vou_text : '领取',
    vou_texts:'使用',
    vou_state :false,//领取状态
    VouStatus :false, // 优惠券使用弹框
    index:0, //储存外层索引
    chain_id: 0,//自提ID
    cha_info: "",//格式化自提点地址
    is_pintuan:0, //拼团订单
    tuan_id:0 , //拼团ID
    tuan_user_list:[],//参团列表
    limit_user:0,//几人团
    pinImg:'',//拼团中的头像
    oldLisimg:[],//剩余开团人数
    form_id:'',
    pyramid_goods: [] //分销商品
    },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var cartid =options.cart_id;
    var ifcart;
    var is_pintuan;
    var chain_id=0;
    var tuan_id;
    if (ifcart!=''){
      ifcart = options.ifcart;
    }
    if (options.chain_id!=undefined){
      chain_id = options.chain_id; //自提ID
    }
    if (options.is_pintuan != undefined){
      is_pintuan = options.is_pintuan
      this.setData({
        is_pintuan: is_pintuan,
      })
    }
    if (options.tuan_id != undefined){
      tuan_id = options.tuan_id
      this.setData({
        tuan_id: tuan_id,
      })
    }
    this.setData({
      cartid: cartid,
      ifcart: ifcart,
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
    var that = this;
    if (app.address_info) {
      that.setData({
        address_info: app.address_info
      })
    }
    if (that.data.is_pintuan!=0){
      if (that.data.tuan_id!=0){
        var obj = {
          chain_id: that.data.chain_id,
          cart_id: that.data.cartid,
          ifcart: that.data.ifcart,
          address_id: app.address_info.address_id,
          is_pintuan: 1,
          tuan_id: that.data.tuan_id,
        }
      }else{
        var obj = {
          chain_id: that.data.chain_id,
          cart_id: that.data.cartid,
          ifcart: that.data.ifcart,
          address_id: app.address_info.address_id,
          is_pintuan: 1
        }
      }  
    }else{
      var obj = {
        chain_id: that.data.chain_id,
        cart_id: that.data.cartid,
        ifcart: that.data.ifcart,
        address_id: app.address_info.address_id
      }
    }
   
    request.postUrl('buy.step1', obj, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error,
          duration:1000
        });
        setTimeout(function () {
          wx.navigateBack()
        }, 1000)
        return;
      }
      var list = res.data.datas.store_cart_list;
      var num = 0;
      let pyramids = [];

      for (var op of list) {
        for (var ls of op.goods_list) {
          num += parseInt(ls.goods_num)

          let dealerStorage = wx.getStorageSync('dealer_storage');
          let pyramid = dealerStorage[ls.goods_id];
          if (pyramid) {
            pyramids.push({ [ls.goods_id] : pyramid })
          }
        }
      }
      var rpt_list = res.data.datas.rpt_list; //红包
      for (var lp of rpt_list) {
        lp.checked = false
        lp.vou_texts = "使用"
      }
      if (res.data.datas.address_info.address_id < 0){
        that.setData({
          is_submit: false,
        })
      }else{
        that.setData({
          is_submit: true,
          addressid: res.data.datas.address_info.address_id
        })
      }
      var pay_list = res.data.datas.store_cart_list;
      for (var p of pay_list){
        for (var l of p.store_voucher_list_all){
          l.stype = 0; //优惠券领取状态
          l.vou_text = that.data.vou_text
        }
        for (var s of p.store_voucher_list){
          s.ptype = 0; //优惠券使用状态
          s.vou_texts = that.data.vou_texts
        }
        p.vou_allNum = 0
      }
      if (that.data.chain_id!=0){
        that.setData({
          cha_info: res.data.datas.chain_info.area_info.replace(/\s+/g, ""),
          address: res.data.datas.chain_info,
        })
      }
      if (that.data.is_pintuan!=0){
        if (res.data.datas.tuan_user_list) { //参团订单
          var limit = res.data.datas.store_cart_list[0].goods_list[0].pintuan.limit_user
          var limit_user = parseInt(limit) - parseInt(res.data.datas.tuan_user_list.length)
          for (var i = 0; i < limit_user; i++) {
            that.data.oldLisimg[i] = 1
          }
          that.setData({
            tuan_user_list: res.data.datas.tuan_user_list,
            limit_user: limit_user,
            oldLisimg: that.data.oldLisimg
          })
        } else {
          var limit = res.data.datas.store_cart_list[0].goods_list[0].pintuan.limit_user
          var limit_user = parseInt(limit) - 1;
          var pinImg = wx.getStorageSync("user_img")
          that.setData({
            limit_user: limit_user,
            pinImg: pinImg
          })
        }
      }
      that.setData({
        pay_list: pay_list,
        area_info: res.data.datas.address_info.area_info.replace(/\s+/g, ""),
        vat_hash: res.data.datas.vat_hash,
        offpay_hash: res.data.datas.offpay_hash,
        offpay_hash_batch: res.data.datas.offpay_hash_batch,
        available_predeposit: res.data.datas.available_predeposit,
        available_rc_balance: res.data.datas.available_rc_balance,
        order_amount: res.data.datas.order_amount,
        num: num,
        amount: res.data.datas.order_amount,
        rpt_list: rpt_list,
        is_submit: that.data.is_submit,
        Redenvelopes: res.data.datas.rpt_list.length,
        pyramid_goods: pyramids
      })
      if (!app.address_info && res.data.datas.address_info.address_id > 0) {
          app.address_info = res.data.datas.address_info;
          that.setData({
            address_info: app.address_info
          })
      }
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

  },
  goMap(){ //自提点地图
    var latitude = this.data.address.latitude;
    var longitude = this.data.address.longitude;
    var address = this.data.address.chain_address;
    var name = this.data.address.chain_name;
    wx.getLocation({//获取当前经纬度
      type: 'wgs84', //返回可以用于wx.openLocation的经纬度，官方提示bug: iOS 6.3.30 type 参数不生效，只会返回 wgs84 类型的坐标信息  
      success: function (res) {
        wx.openLocation({//​使用微信内置地图查看位置。
          latitude: Number(latitude),//要去的纬度-地址
          longitude: Number(longitude),//要去的经度-地址
          name: name,
          address: address,
        })
      }
    })
  },
  ly_input(e){  //留言信息
    var value = e.detail.value;
    var length = value.length;
    var storeid = e.currentTarget.dataset.storeid
    if (length>=30){
      wx.showToast({
        title:"字数超出限制"
      })
    }
    var message = storeid + "|" + value
    this.setData({
      message: message,
    })
    ;
  }, 
  change_pay_type: function(e){
    if (e.currentTarget.dataset.type == 1) { //预存款
      if (this.data.yuesdsdwds){
        this.setData({
          yuesdsdwds :false,
          pd_pay : 0,
          pd_pays : 0,
        })
      }else{
        this.setData({
          yuesdsdwds: true,
          pd_pay: this.data.available_predeposit,
          pd_pays: 1,
        });
      }
    } else {  //卡支付
      if (this.data.yuesdsdwd) {
        this.setData({
          yuesdsdwd: false,
          rcb_pay :0,
          rcb_pays :0,
        })
      } else {
        this.setData({
          yuesdsdwd: true,
          rcb_pay: this.data.available_rc_balance,
          rcb_pays: 1,
        })
      }
    }
  },
  goShopMa (e){
    if (wx.getStorageSync("user_token")) {
      wx.navigateTo({
        url: '../address/address?flag=' + 2
      })
    }
  },
  //显示红包
  show_ms: function () {
    for (var i = 0; i < this.data.rpt_list.length; i++) {
      this.data.rpt_list[i].checked = false;
      this.data.rpt_list[i].vou_texts = "使用"
    }
    this.setData({
      show_ms: true,
      rpt_list : this.data.rpt_list,
    })
  },
  //隐藏
  hide_ms: function (e) {
    this.setData({
      show_ms: false,
      show_vh:false,
      choseture: this.data.choseture,
    })
  },
  check(e){ //红包
    var index = e.currentTarget.dataset.index
    var rpt_list = this.data.rpt_list;
    var temp = rpt_list[index];
    var del_p = 0;
    if (temp.checked){
      
    }else{
      for (var i = 0; i < rpt_list.length;i++){
        rpt_list[i].checked =false
        rpt_list[i].vou_texts = "使用"
      }
      temp.checked = true;
      temp.vou_texts = "已使用"
      var n1 = temp.rpacket_price; 
      var n2 = temp.rpacket_t_id;
      var hobao = n2 +"|"+n1;
      this.data.redNm = n1
      this.setData({
        rpt_list :rpt_list,
        hobao :hobao,
        redNm : this.data.redNm,
      })
      del_p = n1
    }
    if (this.data.vou_allNum !=0){
      var amount = this.data.amount - del_p - this.data.vou_allNum;
    }else{
      var amount = this.data.amount - del_p;
    }
    this.setData({
      order_amount : amount.toFixed(2),
    })
  }, 
  // show_vh(e){  //显示代金券
  //   var store_voucher_list = e.currentTarget.dataset.list;
  //   for (var i = 0; i < store_voucher_list.length; i++) {
  //     store_voucher_list[i].checked = false;
  //   }
  //   this.setData({
  //     show_vh: true,
  //     store_voucher_list: store_voucher_list
  //   })
  // },
  // voucher(e){ //代金券
  //   var index = e.currentTarget.dataset.index;
  //   var store_voucher_list = this.data.store_voucher_list;
  //   var temp = store_voucher_list[index];
  //   var del_v = 0;
  //   if (temp.checked) {
  //     temp.checked = false;
  //     this.setData({
  //       store_voucher_list: store_voucher_list,
  //       coupon: '',
  //       Voucheramount :0,
  //     })      
  //   } else {
  //     for (var i = 0; i < store_voucher_list.length; i++) {
  //       store_voucher_list[i].checked = false
  //     }
  //     temp.checked = true;
  //     this.data.choseture = true;
  //     var n1 = temp.voucher_price;
  //     var n2 = temp.voucher_store_id
  //     this.data.coupon = n2 + "|" + n1;
  //     this.data.Voucheramount = n1;  
  //     this.setData({
  //       store_voucher_list: this.data.store_voucher_list,
  //       choseture: this.data.choseture,
  //       coupon: this.data.coupon,
  //       Voucheramount: this.data.Voucheramount,
  //     })
  //     del_v = n1;
  //   }
  //   if (this.data.redNm !=0){
  //     var amount = this.data.amount - del_v -this.data.redNm;
  //   }else{
  //     var amount = this.data.amount - del_v;
  //   }
  //   this.setData({
  //     order_amount: amount.toFixed(2),
  //   })
  // },
  Receive_voucher(e) {//优惠券
    var that = this;
    var tid = e.currentTarget.dataset.id;
    var index = that.data.index;
    var indexs = e.currentTarget.dataset.index;
    var store_voucher_list_all = that.data.pay_list[index].store_voucher_list_all;
    var store_voucher_list = that.data.pay_list[index].store_voucher_list;
    var type = e.currentTarget.dataset.type;
    if (wx.getStorageSync('user_token') == '') {
      wx.switchTab({
        url: '../me/me',
      })
      return;
    }
    if (type == 2 && store_voucher_list_all[indexs].stype==0){ //未领券领券
     request.postUrl('member_voucher.voucher_freeex', {
       tid: tid,
     }, function (res) {
       if (res.data.code == 200) {
         if (store_voucher_list.length == 0) {
           var lop = {
             ptype: 0,
             vou_texts: '使用',
             voucher_end_date: store_voucher_list_all[indexs].voucher_t_end_date,
             voucher_start_date: store_voucher_list_all[indexs].voucher_t_start_date,
             voucher_desc: store_voucher_list_all[indexs].voucher_t_desc,
             voucher_price: store_voucher_list_all[indexs].voucher_t_price,
             voucher_t_id: store_voucher_list_all[indexs].voucher_t_id,
             voucher_store_id: store_voucher_list_all[indexs].voucher_t_store_id,
           }
           store_voucher_list.push(lop)
         } else {
           var lop = {
             ptype: 0,
             vou_texts: '使用',
             voucher_end_date: store_voucher_list_all[indexs].voucher_t_end_date,
             voucher_start_date: store_voucher_list_all[indexs].voucher_t_start_date,
             voucher_desc: store_voucher_list_all[indexs].voucher_t_desc,
             voucher_price: store_voucher_list_all[indexs].voucher_t_price,
             voucher_t_id: store_voucher_list_all[indexs].voucher_t_id,
             voucher_store_id: store_voucher_list_all[indexs].voucher_t_store_id,
           }
           store_voucher_list.push(lop)
         }
         store_voucher_list_all[indexs].stype = 1;
         store_voucher_list_all[indexs].vou_text = '已领取';
         that.setData({
           store_voucher_list_all: store_voucher_list_all,
           store_voucher_list: store_voucher_list,
           pay_list: that.data.pay_list,
         })
         wx.showToast({
           title: '领取优惠券成功！',
           icon: 'none'
         })
       } else {
         wx.showToast({
           title: res.data.datas.error,
           icon: 'none'
         })
       }

     })
     
    } else if (type == 2 && store_voucher_list_all[indexs].stype == 1){
      return;
    } else if (type == 1 && store_voucher_list[indexs].ptype == 0){ //未使用
      console.log("开始使用")
      var del_v = 0;
      that.data.pay_list[index].vou_allNum = store_voucher_list[indexs].voucher_price
      that.data.pay_list[index].store_goods_total = (parseInt(that.data.pay_list[index].store_goods_total) - parseInt(that.data.pay_list[index].vou_allNum)).toFixed(2)
      for (var i = 0; i < store_voucher_list.length;i++){
        store_voucher_list[i].ptype = 0;
        store_voucher_list[i].vou_texts = '使用';
        if(i==indexs){
          store_voucher_list[indexs].ptype = 1;
          store_voucher_list[indexs].vou_texts = '已使用';
        }
      }
      for (var lp of that.data.pay_list){
        del_v = parseInt(del_v) + parseInt(lp.vou_allNum);
      }
      var dID = store_voucher_list[indexs].voucher_t_id
      var sID = store_voucher_list[indexs].voucher_store_id
      var mID = store_voucher_list[indexs].voucher_price
      if (that.data.coupon !=''){
        var copon1 = dID + "|" + sID + "|" + mID;
        var copon = that.data.coupon + "," + copon1
        that.setData({
          coupon: copon
        })
      }else{
        var copon = dID + "|" + sID + "|" + mID;
        that.setData({
          coupon: copon,
        })
      }
      if (this.data.redNm != 0) {
       var amount = this.data.amount - del_v -this.data.redNm;
     }else{
       var amount = this.data.amount - del_v;
     }
      that.setData({
        order_amount: amount.toFixed(2),
        store_voucher_list_all: store_voucher_list_all,
        store_voucher_list: store_voucher_list,
        pay_list: that.data.pay_list,
        vou_allNum: del_v,
      })
    }else{
      return;
    }
  },
  formSubmit(e){ //表单提交
    var that =this;
    that.setData({
      form_id: e.detail.formId
    })
    if (!wx.getStorageSync("user_token")){
      return;
    }
    if (that.data.address_info == '' && that.data.chain_id==0) {
      wx.showToast({
        title: "请选择收货地址",
        icon: "none",
      })
      return;
    }
    if (!that.data.is_submit) {
      return;
    }
    if (that.data.again){ //避免重复提交支付订单
      if (that.data.chain_id !=0){ //自提订单
        wx.showModal({
          title: '提示',
          content: '该订单为自提订单，需要去以上自提点自提',
          success: function (res) {
            if (res.confirm) {
              that.payto();
              that.setData({
                again: false,
              })
            } else if (res.cancel) {
            }
          }
        })  
      }else{
        that.payto();  //支付
        that.setData({
          again: false,
        })
      }
    }
  },
  payto:function(){ //支付
    var that = this;
    var poass = false; //是否是自提
    wx.showLoading({
      title: '订单提交中',
      mask: "none",
    })
    if(that.data.chain_id!=0){
      poass=true;//自提
      var list = {
        cart_id: this.data.cartid,
        ifcart: this.data.ifcart,
        address_id: app.address_info.address_id,
        vat_hash: this.data.vat_hash,
        offpay_hash: this.data.offpay_hash,
        offpay_hash_batch: this.data.offpay_hash_batch,
        pay_name: this.data.pay_name,
        invoice_id: 0,
        voucher: this.data.coupon,
        pd_pay: this.data.pd_pay,
        rcb_pay: this.data.rcb_pay,
        rpt: this.data.hobao,
        pay_message: this.data.message,
        is_wei_chain:true,
        chain_id: that.data.chain_id,
        form_id: that.data.form_id,
        pyramid_goods: JSON.stringify(that.data.pyramid_goods),
        order_form:7
      };
    }else{
      var list = {
        cart_id: this.data.cartid,
        ifcart: this.data.ifcart,
        address_id: app.address_info.address_id,
        vat_hash: this.data.vat_hash,
        offpay_hash: this.data.offpay_hash,
        offpay_hash_batch: this.data.offpay_hash_batch,
        pay_name: this.data.pay_name,
        invoice_id: 0,
        voucher: this.data.coupon,
        pd_pay: this.data.pd_pay,
        rcb_pay: this.data.rcb_pay,
        rpt: this.data.hobao,
        pay_message: this.data.message,
        is_pintuan: this.data.is_pintuan,
        tuan_id: this.data.tuan_id,
        form_id: that.data.form_id,
        pyramid_goods: JSON.stringify(that.data.pyramid_goods),
        order_form:7
      };
    }  
    console.log(list)
    request.postUrl('buy.step2', list, function (res) {
      wx.hideLoading();
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }

      let dealerStorage = wx.getStorageSync('dealer_storage');
      if (dealerStorage) {
        for (let value of that.data.pyramid_goods) {
          for (let key in value) {
            dealerStorage[key] = '';
          }
        }

        wx.setStorageSync('dealer_storage', dealerStorage);
      }

      request.postUrl('buy.douyin_ali_prepay_id', { 
        pay_sn: res.data.datas.pay_sn,
        open_id: wx.getStorageSync("open_id"),
        rcb_pay: that.data.rcb_pays,
        pd_pay: that.data.pd_pays,
      }, 
        function (res) {
        if (res.data.code == 200) {
          if (res.data.datas.pay_ok) {
            wx.redirectTo({
              url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(res.data.datas.order_info) + '&fin=' + 1 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan
            })
            wx.setStorageSync("chain_list", {});
            return;
          }else{
            that.setData({
              order_info: res.data.datas.order_info
            })
            console.log(res.data.datas.order_info)
            var pay_info = res.data.datas.pay_info
            //
            console.log('开始请求支付')
            tt.pay({
              orderInfo: {
                app_id: pay_info.app_id,//ok
                sign_type: pay_info.sign_type,//ok
                out_order_no: pay_info.out_order_no,
                merchant_id: pay_info.merchant_id, //ok
                timestamp: pay_info.timestamp,
                product_code: pay_info.product_code, //ok
                payment_type: pay_info.payment_type,  //ok
                total_amount: pay_info.total_amount,  
                trade_type: pay_info.trade_type, //ok
                uid: pay_info.uid,
                version: pay_info.version, //ok
                currency: pay_info.currency,  //ok
                subject: pay_info.subject,
                body: pay_info.body,
                trade_time: pay_info.trade_time,
                valid_time: pay_info.valid_time,
                notify_url: pay_info.notify_url,
                wx_url: pay_info.wx_url,
                wx_type: pay_info.wx_type,
                alipay_url: pay_info.alipay_url,
                sign: pay_info.sign,
                risk_info: pay_info.risk_info
              },
              service: 1,
              // getOrderStatus(res) {
              //   let { out_order_no } = res;
              //   console.log(res,'res')
              //   return new Promise(function(resolve, reject) {
              //     // 商户前端根据 out_order_no 请求商户后端查询微信支付订单状态
              //     tt.request({
              //       url: "<your-backend-url>",
              //       success(res) {
              //         // 商户后端查询的微信支付状态，通知收银台支付结果
              //         resolve({ code: 0 | 1 | 2 | 3 | 9 });
              //       },
              //       fail(err) {
              //         reject(err);
              //       }
              //     });
              //   });
              // },
              'success': function (res) {
                console.log("调用支付=", res);
                if(res.code == 0){
                  console.log("支付成功", res);
                  wx.redirectTo({
                    url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 1 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan,
                  })
                }else{
                  console.log("支付失败", res);
                  wx.redirectTo({
                    url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 2 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan,
                  })
                }
              },
              'fail': function (res) {
                console.log("调用支付失败", res);
                wx.redirectTo({
                  url: '../offline_pay_finish/offline_pay_finish?order_info=' + JSON.stringify(that.data.order_info) + '&fin=' + 2 + '&poass=' + poass + '&tuan_id=' + that.data.tuan_id + '&is_pintuan=' + that.data.is_pintuan,
                })
              }
            })
            wx.setStorageSync("check_list", []);
          }
          wx.setStorageSync("chain_list", {});
        } else {
          wx.showToast({
            title: res.data.datas.error
          });
        }
      })
    }) 
  },
  showModal: function (e) {
    var type = e.currentTarget.dataset.type;
    // var store_voucher_list_all = e.currentTarget.dataset.list;
    var pay_list = this.data.pay_list;
    var index = e.currentTarget.dataset.index;
    var that = this;
    // 显示遮罩层
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })
    that.animation = animation
    animation.translateY(300).step()
    console.log(type)
    if(type ==2){ //领券
      var store_voucher_list_all = pay_list[index].store_voucher_list_all;
      that.setData({
        VoucherStatus : true,
        animationData: animation.export(),
        pay_list: pay_list,
        index: index,
        store_voucher_list_all: store_voucher_list_all,
      })
    } else if (type == 1){ //使用
      var store_voucher_list = pay_list[index].store_voucher_list;
      that.setData({
        VouStatus: true,
        animationData: animation.export(),
        store_voucher_list: store_voucher_list,
        pay_list: pay_list,
        index: index,
      })
    }else{
      that.setData({
        show_ms: true,
        rpt_list: that.data.rpt_list,
        animationData: animation.export(),
      })
    }
    setTimeout(function () {
      animation.translateY(0).step()
      that.setData({
        animationData: animation.export()
      })
    }.bind(that), 200)
  },
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
        VoucherStatus: false,
        VouStatus: false,
        show_ms:false,
      })
    }.bind(that), 200)
  },
})