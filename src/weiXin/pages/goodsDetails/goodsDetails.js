function getImageInfo(url) {
  return new Promise((resolve, reject) => {
    wx.getImageInfo({
      src: url,
      success: resolve,
      fail: reject,
    })
  })
}

function createRpx2px() {
  const { windowWidth } = wx.getSystemInfoSync()

  return function (rpx) {
    return windowWidth / 750 * rpx
  }
}

const rpx2px = createRpx2px()

function canvasToTempFilePath(option, context) {
  return new Promise((resolve, reject) => {
    wx.canvasToTempFilePath({
      ...option,
      success: resolve,
      fail: reject,
    }, context)
  })
}

function saveImageToPhotosAlbum(option) {
  return new Promise((resolve, reject) => {
    wx.saveImageToPhotosAlbum({
      ...option,
      success: resolve,
      fail: reject,
    })
  })
}



var tabH = [{
  name: "商品",
  id: 0
},
{
  name: "推荐",
  id: 1
},
{
  name: "评价",
  id: 2
},
{
  name: "详情",
  id: 3
}
];
const HtmlParser = require('../../html-view/index');
var request = require('../../utils/request.js');
var common = require('../../utils/common.js');
var areaTool = require('../../utils/area.js');
var format = require('../../utils/util.js')
var app = getApp();
Page({
  data: {
    hasEvaluate: false, //是否有评价
    evaluateNum: 0, //评价数量
    tabH: tabH,
    status: 0,
    imgUrls: [], //焦点图
    indicatorDots: false,
    autoplay: true,
    interval: 3000,
    duration: 500,
    indicatorActiveColor: '#68D465',
    clickId: 0,
    num: 0,
    hiddenName: false, //点击加入购物车
    cartNum: 0, //购物车商品数量
    goods_name: '', //商品名称
    store_name: '', //店铺名称
    store_logo: '', //店铺logo
    store_desccredit: '', //店铺描述
    store_servicecredit: '', //店铺服务
    store_deliverycredit: '', //物流服务
    goods_id: 0, //商品id
    goods_price: '', //商品价格
    goods_marketprice: '', //商品原价
    goods_salenum: 0, //商品销售数量
    goods_storage: 1, //商品库存
    goods_eval_list: [], //商品评价列表
    is_xianshi: false, //是否是限时秒杀
    tabCollect: false, //商品收藏初始化状态
    cur_time: 7200,
    time: ['-', '-', '-'], //限时倒计时
    xs_interval: "", //倒计时
    pg_interval:"",
    show_ms: false, //是否显示满送活动列表
    mansong_info: [], //满送活动信息
    defaultText: '全国',
    if_store_cn: '',
    provinces: [],
    citys: [],
    areas: [],
    multiArray: "",
    value: [0, 0, 0],
    spec_all: [],
    showSpec: false,
    spec_name: '', //选中当前规格名称
    current_spc_id: 0, //选中当前规格商品规格id
    current_spc_value_id: 0, //选中当前规格商品规格value_id
    current_spc_img_url: '', //选中当前规格商品图片url
    goods_list: [], //多规格商品列表
    goods_jingle: '',
    carts_num: 0, //加入购物车数量
    cart_count: 0, //购物车商品数量
    store_info: {}, //店铺信息
    goods_commend_list: [], //店铺热卖商品
    mobile_body: '', //商品描述
    hasDetails: false, //是否有详情
    goods_unit: '', //商品规格
    tabs: [], //商品所有规格
    item: "", //外部传进来的数据
    goods_info: "",
    select: 0,
    scrollTop: 0,
    has_top: false,
    showModalStatus: false, //到货提醒按钮初始化状态 //是否显示弹窗
    VoucherStatus : false, //优惠券弹框显示
    RedStatus:false, //红包弹框显示
    form_id: '',
    animationData: '',
    scrollLeftNumber: 0,
    isDisabled: false,
    Atatus: 0,
    crrC: 1, //自定义轮播下标,
    list: [],
    arrive_notice:0, //订阅状态 0：未订阅  1：已订阅
    store_voucher_list:[], //优惠券列表
    vou_state : false, //优惠券领取状态
    vou_text :'领取',//
    xianshi_info:0,//限时的信息
    specStatus:false, //规格弹框
    col_type:false, //拼团弹框
    col_details:false,//拼团详情弹框
    mossage:'',//整个信息
    tuan_list:[],//拼购列表信息
    itm:'',//该组拼团信息
    user_list:[],//拼团团的列表
    inTime : '',//拼团弹框内计时
    pgmarst_interval:"",//拼团弹框定时器
    oldLisimg: [],//剩余开团人数
    tuan_details_list:[],//参团详情列表数据
    details_interval:'',//参团详情定时器
    cur_page:1, //參團詳情商品分頁
    pintuan_goods_id:0,//參團ID
    is_bottom:false,
    canID:0,//參團列表ID
    tuan_id:0,//分享传来的ID
    ssss:false,
    dealer_id: '',
    dealer_buy_id: '',
    is_pyramid_goods: '',
    share_url: '',
    visible: false,

    canvasWidth: 850,
    canvasHeight: 1400,
    imageFile: '',
    responsiveScale: 1,
  },
  onShow: function (options) {
    wx.showShareMenu({
      withShareTicket: true
    })
    if (app.Atatus == 1 || wx.getStorageSync("user_token")) {
      this.setData({
        Atatus: 1
      })
    }
  },
  onHide(){
    this.hideModal();
  },
  onUnload: function () {
    var that = this;
    clearInterval(that.data.xs_interval);
    clearInterval(that.data.pg_interval);
    clearInterval(that.data.pgmarst_interval);
    clearInterval(that.data.details_interval)
  },
  onLoad: function (options) {
    //页面加载默认没有收藏
    var that = this;
    var goods_id = 0;
    let dealer_id = '';

    if (that.data.goods_id) {
      goods_id = that.data.goods_id;
    } else if (options.goods_id){
      goods_id = options.goods_id;
    } else if (options.scene){
      let dummy = options.scene.split('-');
      if (dummy.length > 1) {
        goods_id = dummy[0];
        dealer_id = dummy[1];
      }
      else {
        goods_id = options.scene;
      }
    }
  
    if (options.dealer_id) {
      dealer_id = options.dealer_id;
    }

    if (dealer_id) {
      that.setData({
        dealer_id: dealer_id,
        dealer_buy_id : dealer_id
      })

      wx.setStorageSync('dealer_id', dealer_id);
    }
    console.log('#####')
    console.log(that.data.dealer_id)
    console.log('#####')
    //goods_id = 104319;
    if (!goods_id) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    if (options.tuan_id != undefined) {
      this.setData({
        tuan_id: options.tuan_id
      })
    }
    that.setData({
      goods_id: goods_id
    })
    wx.showShareMenu({
      withShareTicket: true
    });
    that.getDetail(that.data.goods_id);
    //that.getAreaList();
    that.initRed()
    

    const designWidth = 375
    const designHeight = 603 // 这是在顶部位置定义，底部无tabbar情况下的设计稿高度

    // 以iphone6为设计稿，计算相应的缩放比例
    const { windowWidth, windowHeight } = wx.getSystemInfoSync()
    const responsiveScale =
      windowHeight / ((windowWidth / designWidth) * designHeight)
    if (responsiveScale < 1) {
      this.setData({
        responsiveScale,
      })
    }
  },
  //改变地区获取是否有货
  changeDeliverGoods: function () {
    var that = this;
    var area_id = app.city_id;
    var goods_id = that.data.goods_id;
    if (area_id <= 0 || goods_id <= 0) {
      return;
    }
    request.postUrl('goods.calc', {
      goods_id: goods_id,
      area_id: area_id
    }, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        //todo
        return;
      }
      that.setData({
        if_store_cn: res.data.datas.if_store_cn
      });
    });
  },
  // 浮动菜单
  navTabClick(e) {
    var that = this;
    // console.log('切换', e.currentTarget.dataset.index)
    var index = e.currentTarget.dataset.index;
    that.setData({
      select: -1,
      status: index
    })
  },
  //导航栏随滑动切换
  bindscroll(e) {
    var that = this;
    var scrollTop = e.detail.scrollTop;
    if (e.detail.scrollTop > 100) {
      that.setData({
        has_top: true
      })
    } else {
      that.setData({
        has_top: false
      })
    }
    // console.log('距顶部距离', e.detail.scrollTop);

    if (scrollTop <= '485') {
      that.setData({
        status: -1,
        select: 0
      })
    } else if ('486' <= scrollTop && scrollTop <= '683') {
      that.setData({
        select: 1,
        status: -1
      })
    } else if ('684' <= scrollTop && scrollTop <= '975') {
      that.setData({
        select: 2,
        status: -1
      })
    } else {
      that.setData({
        select: 3,
        status: -1
      })
    }
  },
  getDetail: function (id) {
    var that = this;
    clearInterval(that.data.interval);
    request.postUrl("goods.detail2", {
      goods_id: id,
      area_id: app.city_id,
      tuan_id: that.data.tuan_id
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

      let pyramid = res.data.datas.invite_id
      if (pyramid && pyramid != '0') {
        if (!that.data.dealer_buy_id) {
            that.setData({
                dealer_buy_id: res.data.datas.invite_id
            })
        }

        that.setData({
          dealer_id: res.data.datas.invite_id,
        })
      }

      var item = res.data.datas.goods_info;
      wx.setNavigationBarTitle({
          title: item.goods_name
      });
      var list = res.data.datas.goods_list[0];
      if (wx.getStorageSync('user_token') == '') {
        item.carts_num = parseInt(that.data.carts_num);
      } else {
        item.carts_num = parseInt(item.carts_num);
      }
      if (res.data.datas.goods_info.goods_storage ==0){
        that.setData({
          goods_storage: 0,
        })
      }else{
        that.setData({
          goods_storage: res.data.datas.goods_info.goods_storage,
        })
      }
      var store_voucher_list = res.data.datas.store_voucher_list;
      if (res.data.datas.red_t_list){
        var red_t_list = res.data.datas.red_t_list;
        for (var l of store_voucher_list) {
          l.vou_state = false;
          l.vou_text = '领取';
          if (l.voucher_t_desc.length > 40){
            var str = "";
            str = l.voucher_t_desc.substring(0, 40) + "...";
            l.voucher_t_desc = str
          }
        }
        for (var l of red_t_list) {
          l.rpacket_t_start_date = format.format(new Date(l.rpacket_t_start_date * 1000))
          l.rpacket_t_end_date = format.format(new Date(l.rpacket_t_end_date * 1000))
          l.vou_state = false;
          l.vou_text = '领取';
        }
        console.log(red_t_list)
        that.setData({
          red_t_list: red_t_list,
        })
      }
      if (res.data.datas.tuan_share_buy==true){ //分享过来可以直接购买
        var cart_id = that.data.goods_id + '|1';
        if (parseInt(res.data.datas.tuan_info.limit_floor) <=1){
          var is_pintuan = 1
        }else{
          var is_pintuan = res.data.datas.tuan_info.limit_floor
        }
        wx.navigateTo({
          url: '../sureOrder/sureOrder?ifcart=0&cart_id=' + cart_id + '&is_pintuan=' + is_pintuan + '&tuan_id=' + that.data.tuan_id
        })
      }
      that.setData({
        imgUrls: res.data.datas.goods_image,
        goods_name: res.data.datas.goods_info.goods_name,
        goods_jingle: res.data.datas.goods_info.goods_jingle,
        carts_num: res.data.datas.goods_info.carts_num,
        goods_marketprice: res.data.datas.goods_info.goods_marketprice,
        goods_price: res.data.datas.goods_info.goods_price,
        goods_salenum: res.data.datas.goods_info.goods_salenum,
        item: item,
        list: list,
        // cart_count: res.data.datas.cart_count,
        goods_eval_list: res.data.datas.goods_eval_list,
        if_store_cn: res.data.datas.goods_hair_info.if_store_cn,
        store_info: res.data.datas.store_info,
        goods_commend_list: res.data.datas.goods_commend_list,
        goods_list: res.data.datas.goods_list,
        store_voucher_list: store_voucher_list,
        xianshi_info: res.data.datas.goods_info.xianshi_info,
        arrive_notice: res.data.datas.arrive_notice,
        mossage : res.data.datas,
        is_pyramid_goods: res.data.datas.is_pyramid_goods
      });
      if (res.data.datas.goods_eval_list.length == 0) {
        that.setData({
          isDisabled: true
        })
      }
      if (res.data.datas.goods_info.mobile_body) {
        that.setData({
          hasDetails: true,
          mobile_body: new HtmlParser(res.data.datas.goods_info.mobile_body).nodes
        });
      }
      if (res.data.datas.mansong_info.hasOwnProperty("rules")) {
        that.setData({
          mansong_info: res.data.datas.mansong_info.rules
        });
      }
      if (res.data.datas.spec_all.length > 0) {
        for (let key in that.data.goods_list) {
          if (that.data.goods_list[key].goods_id == that.data.goods_id) {
            that.setData({
              current_spc_id: that.data.goods_list[key].spec_info[0].spec_id,
              current_spc_value_id: that.data.goods_list[key].spec_info[0].spec_value_id,
              current_spc_img_url: that.data.goods_list[key].goods_image_url
            });
          }
        }

        if (that.data.current_spc_id > 0) {
          that.setData({
            showSpec: true,
            spec_name: res.data.datas.goods_info.goods_spec[Object.keys(res.data.datas.goods_info.goods_spec)[0]],
            spec_all: res.data.datas.spec_all
          });
        }
      }
      //秒杀活动
      if (res.data.datas.goods_info.promotion_type == 'xianshi') {
        that.setData({
          is_xianshi: true
        });
        clearInterval(that.data.xs_interval);
        var xianshi_end_time = parseInt(res.data.datas.goods_info.xianshi_info.end_time - common.getTimestamp())
        var time = common.getTime(xianshi_end_time);
        that.setData({
          time: time,
          cur_time: xianshi_end_time
        })
        var xs_interval = setInterval(function () {
          var time = common.getTime(that.data.cur_time);
          if (that.data.cur_time == 0) { //重新加载
            that.onLoad()
          }
          that.setData({
            time: time,
            cur_time: that.data.cur_time - 1
          })
        }, 1000);
        that.data.xs_interval = xs_interval;
      }
      //拼购活动
      if (res.data.datas.tuan_flag == '1') {
        var tuan_list = res.data.datas.tuan_list;
        clearInterval(that.data.pg_interval);
        for (var l of tuan_list){
          var pingo_end_time = parseInt(l.expires_time - common.getTimestamp())
          var time = common.getTime(pingo_end_time);
          var time1 = time[0]+":"+time[1]+":"+time[2]
          l.time1 = time1 ; 
          l.time = pingo_end_time
          that.setData({
            tuan_list: tuan_list
          })
        }  
          var pg_interval = setInterval(function () {
            for (var l of tuan_list){
              var time = common.getTime(l.time);
              var time1 = time[0] + ":" + time[1] + ":" + time[2]
              l.time1 = time1;
              if (l.time == 0) { //重新加载
                that.onLoad()
              }
              l.time = l.time - 1
              that.setData({
                tuan_list: tuan_list
              })
            }
          }, 1000);
          that.data.pg_interval = pg_interval; 
      }
      //收藏
      if (res.data.datas.is_favorate) {
        that.setData({
          tabCollect: true
        });
      }
    })
  },

  getAreaList: function () {
    var that = this;
    request.postUrl('area.wei_area_list', {}, function (res) {
      if (res.data.code == 200) {
        var AreaJson = res.data.datas;
        areaTool.setAreaJson(AreaJson);
        var provinces = [{
          area_id: 0,
          area_name: "选择省份"
        }];
        var value = that.data.value;
        for (var i = 0; i < AreaJson.length; i++) {
          provinces.push(AreaJson[i]);
        }
        if (that.data.flag == 0) {
          app.province_id = 0;
          app.city_id = 0;
          app.area_id = 0;
        }
        var multiArray = [];
        var provinces_name = [];
        var city_name = [];
        var area_name = [];
        for (let key in provinces) {
          provinces_name.push(provinces[key].area_name);
          if (provinces[key].area_id == app.province_id) {
            value[0] = key;
          }
        }
        var citys = areaTool.getCitys(value[0]);
        for (let key in citys) {
          city_name.push(citys[key].area_name);
          if (citys[key].area_id == app.city_id) {
            value[1] = key;
          }
        }
        var areas = areaTool.getAreas(value[0], value[1]);
        for (let key in areas) {
          area_name.push(areas[key].area_name);
          if (areas[key].area_id == app.area_id) {
            value[2] = key;
          }
        }
        multiArray.push(provinces_name);
        multiArray.push(city_name);
        multiArray.push(area_name);
        that.setData({
          provinces: provinces,
          citys: citys,
          areas: areas,
          multiArray: multiArray,
          value: value,
        })
      }
    })
  },

  showModal: function (e) {
    var type = e.currentTarget.dataset.type;
    var itm = e.currentTarget.dataset.itm;
    var id = e.currentTarget.dataset.id
    var that = this;
    // 显示遮罩层
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })
    that.animation = animation
    animation.translateY(520).step()
    if (type=='g'){
      that.setData({
        animationData: animation.export(),
        specStatus: true,
      })
    }else if(type =='y'){
      that.setData({
        animationData: animation.export(),
        VoucherStatus: true,
      })
    }else if(type=='h'){
      that.setData({
        animationData: animation.export(),
        RedStatus: true,
      })
    }else if(type=='col'){
      clearInterval(that.data.pgmarst_interval);
      that.setData({
        animationData: animation.export(),
        col_type: true,
        itm: itm
      })
      request.postUrl('tuan.info',{
        tuan_id: itm.tuan_id
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
        var pgmarst_interval = setInterval(function () {
          var pingo_end_time = parseInt(itm.expires_time - common.getTimestamp())
          var time = common.getTime(pingo_end_time);
            var inTime = time[0] + ":" + time[1] + ":" + time[2]
            that.setData({
              inTime: inTime
            })
            if (time == 0) { //重新加载
              that.onLoad()
            }
            time = time - 1
            that.setData({
              inTime: inTime
            })
        }, 1000);
        that.data.pgmarst_interval = pgmarst_interval; 
        var user_list = res.data.datas.user_list;
        var limit_user = that.data.item.pintuan_info.limit_user;
        var length = limit_user - user_list.length
        for (var i = 0; i < length;i++){
          that.data.oldLisimg[i] = 1
        }
        that.setData({
          user_list: user_list,
          oldLisimg: that.data.oldLisimg
        })
      })
    } else if (type =="col_details"){
      that.setData({
        animationData: animation.export(),
        col_details: true,
        canID:id
      })
      that.getCollageDeatils(id,1);
      
    }else{
      return;
    }
    setTimeout(function () {
      animation.translateY(0).step()
      that.setData({
        ssss:true,
        animationData: animation.export()
      })
    }.bind(that), 200)
  },
  hideModal: function () {//影藏彈框
    var that = this;
    // 隐藏遮罩层
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })
    that.animation = animation
    animation.translateY(520).step()
    that.setData({
      animationData: animation.export(),
    })
    setTimeout(function () {
      animation.translateY(0).step()
      that.setData({
        animationData: animation.export(),
        VoucherStatus: false,
        specStatus: false,
        RedStatus:false,
        col_type:false,
        col_details:false,
        ssss: false,
      })
    }.bind(that), 200)
  },
  getCollageDeatils:function(id,curpage){ //更多參團信息
    var that = this;
    request.postUrl('goods.tuan_list', {
      pintuan_goods_id: id,
      curpage: curpage,
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
      if (res.data.code == 200) {
        var tuan_details_list = res.data.datas.tuan_list;
        if (that.data.cur_page === 1) {
          that.setData({
            tuan_details_list: tuan_details_list,
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        } else {
          tuan_details_list = that.data.tuan_list.concat(res.data.datas.tuan_list);
          that.setData({
            tuan_details_list: tuan_details_list,
            is_bottom: res.data.hasmore > 0 ? false : true
          })
        }
        clearInterval(that.data.details_interval);
        for (var l of tuan_details_list) {
          var pingo_end_time = parseInt(l.expires_time - common.getTimestamp())
          var time = common.getTime(pingo_end_time);
          var time1 = time[0] + ":" + time[1] + ":" + time[2]
          l.time1 = time1;
          l.time = pingo_end_time
          that.setData({
            tuan_details_list: tuan_details_list
          })
        }
        var details_interval = setInterval(function () {
          for (var l of tuan_details_list) {
            var time = common.getTime(l.time);
            var time1 = time[0] + ":" + time[1] + ":" + time[2]
            l.time1 = time1;
            if (l.time == 0) { //重新加载
              that.onLoad()
            }
            l.time = l.time - 1
            that.setData({
              tuan_details_list: tuan_details_list
            })
          }
        }, 1000);
        that.data.details_interval = details_interval;
      }
    })
  },
  getmore:function(){
    var that = this;
    if (!that.data.is_bottom) {
      that.setData({
        cur_page: that.data.cur_page + 1
      });
      that.getCollageDeatils(that.data.canID, that.data.cur_page);
    }
  },
  //到货提醒
  formSubmit: function (e) {
    var that = this;
    console.log('formid', e.detail.formId);
    that.setData({
      form_id: e.detail.formId
    })
    if (wx.getStorageSync('user_token') !== '') {
      var token = wx.getStorageSync("user_token")
      request.postUrl('goods.arrive_notice', {
        form_id: that.data.form_id,
        goods_id: that.data.goods_id,
        user_token: token,
      }, function (res) {
        if(res.data.code ==200){
          wx.showModal({
            title: '订阅成功',
            content: '订阅成功，商品到货后，我们将第一时间通知您。',
            showCancel: false,
            success: function (res) {
              if (res.confirm) {
                that.setData({
                  arrive_notice:1,
                })
              }
            }
          })
        }else{
          wx.showToast({
            title: res.data.datas.error
          })
        }
      })   
    } else {
      wx.switchTab({
        url: '../me/me'
      })
      return;
    }
  },
  tabAlarm: function (e) { 
    
  },

  //商品规格选择
  tabClick: function (res) {
    var that = this;
    var item = res.currentTarget.dataset.item;
    var index = res.currentTarget.dataset.index;
    that.setData({
      goods_id: item.goods_id,
      clickItem: that.data.tabs[index],
    })
    that.getDetail(item.goods_id);
  },

  //点击切换收藏
  onChangeCollect: function (even) {
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    if (that.data.tabCollect == false) {
      request.postUrl('member_favorites.favorites_add', {
        goods_id: that.data.goods_id
      }, function (res) {
        if (res.data.code == 200) {
          that.setData({
            tabCollect: (!that.data.tabCollect)
          })
          wx.showToast({
            title: '商品收藏成功',
            icon: 'none'
          })
        }
      })
    }
    if (that.data.tabCollect == true) {
      request.postUrl('member_favorites.favorites_del', {
        goods_id_list: JSON.stringify(Array(that.data.goods_id))
      }, function (res) {
        if (res.data.code == 200) {
          that.setData({
            tabCollect: (!that.data.tabCollect),
          })
          wx.showToast({
            title: '商品已取消收藏',
            icon: 'none'
          })
        }
      })
    }
  },
  dymaticUpdateDealer(goods_id, append, number) {
    let dealerId = this.data.dealer_buy_id;
    if (dealerId) {
      let dealerStorage = wx.getStorageSync('dealer_storage');
      if (dealerStorage == '') {
        if (append) {
          dealerStorage = { [goods_id]: dealerId };
        }
      }
      else {
        if (append) {
          dealerStorage[goods_id] = dealerId;
        }

        if (!append && (number == 0)) {
          dealerStorage[goods_id] = '';
        }
      }

      wx.setStorageSync('dealer_storage', dealerStorage);
    }
  },
  //点击减号
  bindMinus: function () {
    var that = this;
    var item = that.data.item;

    var check_list = wx.getStorageSync('check_list');
    if (check_list == '') {
      check_list = [];
    }
    var carts_num = parseInt(that.data.carts_num) - 1;
    if (parseInt(that.data.carts_num) - 1 > that.data.goods_storage) {
      carts_num = that.data.goods_storage;
    }

    that.dymaticUpdateDealer(item.goods_id, false, carts_num)

    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      if (temp_goods == '') {
        temp_goods = [];
      }
      for (var i = 0; i < temp_goods.length; i++) {
        if (temp_goods[i].goods_id == item.goods_id) {
          if (temp_goods[i].carts_num - 1 == 0) {
            temp_goods.splice(i, 1);
            if (check_list.indexOf(item.goods_id) != -1) {
              check_list.splice(check_list.indexOf(item.goods_id), 1);
            }
          } else {
            temp_goods[i].carts_num = temp_goods[i].carts_num - 1;
          }
        }
      }
      wx.setStorageSync('temp_goods', temp_goods);
      wx.setStorageSync('check_list', check_list);
      that.initRed() //气泡
    } else {
      //等于0时删除购物车
      if (carts_num == 0) {
        request.postUrl("cart.delete", {
          goods_id: that.data.goods_id
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
          if (check_list.indexOf(that.data.goods_id) != -1) {
            check_list.splice(check_list.indexOf(that.data.goods_id), 1);
          }
          wx.setStorageSync("check_list", check_list);
          that.setData({
            carts_num: 0
          })
          that.initRed() //气泡
        });

        if (that.data.cart_count > 0) {
          that.setData({
            cart_count: that.data.cart_count - 1
          })
        }
        return;
      }

      request.postUrl("cart.add", {
        goods_id: that.data.goods_id,
        quantity: carts_num
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
        if (check_list.indexOf(that.data.goods_id) != -1) {
          check_list.splice(check_list.indexOf(that.data.goods_id), 1);
        }
        wx.setStorageSync('check_list', check_list);
        that.setData({
          carts_num: carts_num
        })
        that.initRed()//气泡
      });
    }
  },
  /* 点击加号 */
  bindPlus: function () {
    var that = this;
    var item = that.data.item;
    var list = that.data.list;
    var check_list = wx.getStorageSync('check_list');
    if (check_list == '') {
      check_list = [];
    }
    if (parseInt(that.data.carts_num) + 1 > that.data.goods_storage) {
      wx.showToast({
        title: '商品所剩无几了'
      });
      return;
    }

    that.dymaticUpdateDealer(item.goods_id, 1)

    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      if (temp_goods == '') {
        temp_goods = [];
      }
      var flag = true;
      for (var temp_goods_item of temp_goods) {
        if (temp_goods_item.goods_id == item.goods_id) {
          temp_goods_item.carts_num = temp_goods_item.carts_num + 1;
          item.carts_num = temp_goods_item.carts_num + 1;
          temp_goods_item.goods_num = temp_goods_item.goods_num + 1;
          flag = false;
          break;
        }
      }
      if (flag) {
        check_list.push(item.goods_id);
        temp_goods.push({
          carts_num: 1,
          goods_id: list.goods_id,
          goods_num: 1,
        });
        item.carts_num = 1;
      }
      wx.setStorageSync('temp_goods', temp_goods);
      wx.setStorageSync('check_list', check_list);
      that.initRed();
    } else {
      request.postUrl("cart.add", {
        goods_id: that.data.goods_id,
        quantity: parseInt(that.data.carts_num) + 1
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
        if (check_list.indexOf(that.data.goods_id) == -1) {
          check_list.push(that.data.goods_id);
        }
        wx.setStorageSync('check_list', check_list);
        if (parseInt(that.data.carts_num) == 0) {
          that.setData({
            cart_count: parseInt(that.data.cart_count) + 1
          })
        }
        that.setData({
          carts_num: parseInt(that.data.carts_num) + 1
        })
        that.initRed()//购物车气泡
      });
    }
  },
  /* 输入框事件 */
  bindManual: function (e) {
    var that = this;
    var item = that.data.item;
    var list = that.data.list;
    if (e.detail.value == '' || e.detail.value == 0) {
      e.detail.value = 1;
    }
    var carts_num = parseInt(e.detail.value);
    if (carts_num <= 0) {
      carts_num = 1;
    }
    if (carts_num > that.data.goods_storage) {
      wx.showToast({
        title: '商品所剩无几了'
      });
      that.setData({
        carts_num: that.data.carts_num
      })
      return;
    }

    that.dymaticUpdateDealer(item.goods_id, true, carts_num)

    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      if (temp_goods == '') {
        temp_goods = [];
      }
      var flag = true;
      for (var i = 0; i < temp_goods.length; i++) {
        if (temp_goods[i].goods_id == item.goods_id) {
          temp_goods[i].carts_num = carts_num;
          temp_goods[i].goods_num = temp_goods[i].goods_num + 1;
          flag = false;
        }
      }
      if (flag) {
        temp_goods.push({
          carts_num: 1,
          goods_id: list.goods_id,
          goods_num: 1,
        });
      }
      that.setData({
        carts_num: carts_num,
      });
      wx.setStorageSync('temp_goods', temp_goods);
      that.initRed();
    } else {
      request.postUrl("cart.add", {
        goods_id: that.data.goods_id,
        quantity: carts_num
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
        that.setData({
          carts_num: carts_num
        })
        that.initRed()//气泡
      });
    }
  },
  /* 跳转购物车 */
  goShoppingCar: function () {
    if (wx.getStorageSync('user_token')) {
      wx.switchTab({
        url: '../shoppingCar/shoppingCar',
      })
    } else {
      wx.switchTab({
        url: '../me/me',
      })
    }
  },
  /* 跳转立即购买 */
  buyNow: function (e) {
    var is_pintuan = e.currentTarget.dataset.is_pintuan;
    var tuanid = e.currentTarget.dataset.tuanid;
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    //todo 判断商品状态

    that.dymaticUpdateDealer(that.data.goods_id, true, 1)

    var cart_id = that.data.goods_id + '|1';
    console.log(is_pintuan)
    if (is_pintuan==1){
      if (parseInt(that.data.mossage.tuan_info.limit_floor) <= 1) {
        is_pintuan = 1
      } else {
        is_pintuan = that.data.mossage.tuan_info.limit_floor
      }
      if (tuanid!=undefined){
        wx.navigateTo({
          url: '../sureOrder/sureOrder?ifcart=0&cart_id=' + cart_id + '&is_pintuan=' + is_pintuan + '&tuan_id=' + tuanid
        })
      }else{
        wx.navigateTo({
          url: '../sureOrder/sureOrder?ifcart=0&cart_id=' + cart_id + '&is_pintuan=' + is_pintuan
        })
      }
    }else{
      wx.navigateTo({
        url: '../sureOrder/sureOrder?ifcart=0&cart_id=' + cart_id
      })
    }
  },
  //更新购物车气泡数
  initRed: function () {
    var that = this;
    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
      var flag = true;
      if (temp_goods != '') {
        for (var temp_goods_item of temp_goods) {
          if (temp_goods_item.goods_id == that.data.goods_id) {
            that.setData({
              carts_num: temp_goods_item.carts_num,
            })
            flag = false;
          }
        }
        if (flag) {
          that.setData({
            carts_num: 0,
          })
        }
        sum = temp_goods.length;
      } else {
        that.setData({
          carts_num: 0,
        })
      }
      wx.setTabBarBadge({
        index: 2,
        text: sum + "",
      })
      that.setData({
        cart_count: sum,
      })
      if (sum == 0) {
        wx.hideTabBarRedDot({
          index: 2,
        })
      }
    } else {
      request.postUrl('cart.count', {}, function (res) {
        if (res) {
          wx.setTabBarBadge({
            index: 2,
            text: (res.data.datas.count + ''),
          })
          that.setData({
            cart_count: res.data.datas.count,
          })
          if (res.data.datas.count == 0) {
            wx.hideTabBarRedDot({
              index: 2,
            })
            that.setData({
              cart_count: 0,
            })
          }
        }
      })
    }
  },
  Receive_voucher(e){  //领取优惠券
    var that = this;
    var tid = e.currentTarget.dataset.id;
    var index = e.currentTarget.dataset.index;
    var store_voucher_list = that.data.store_voucher_list;
    if (wx.getStorageSync('user_token') == '') {
      wx.switchTab({
        url: '../me/me',
      })
      return;
    }
    if (store_voucher_list[index].vou_state==false){
      request.postUrl('member_voucher.voucher_freeex', {
        tid: tid,
      }, function (res) {
        if (res.data.code == 200) {
          store_voucher_list[index].vou_state = true;
          store_voucher_list[index].vou_text = '已领取';
          that.setData({
            store_voucher_list: store_voucher_list,
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
    }else{
      return;
    }
  },

  Receive_redpick(e){
    var that = this;
    var tid = e.currentTarget.dataset.id;
    var index = e.currentTarget.dataset.index;
    var red_t_list = that.data.red_t_list;
    if (wx.getStorageSync('user_token') == '') {
      wx.switchTab({
        url: '../me/me',
      })
      return;
    }
    if (red_t_list[index].vou_state == false) {
      request.postUrl('member_redpacket.rpt_free', {
        tid: tid,
      }, function (res) {
        if (res.data.code == 200) {
          red_t_list[index].vou_state = true;
          red_t_list[index].vou_text = '已领取';
          that.setData({
            red_t_list: red_t_list,
          })
          wx.showToast({
            title: '领取红包成功！',
            icon: 'none'
          })
        } else {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none'
          })
        }

      })
    } else {
      return;
    }
  },


  /* 收藏店铺 */
  collectStore: function () {
    var that = this;
    if (!wx.getStorageSync("user_token")) {
      wx.switchTab({
        url: '../me/me'
      });
      return;
    }
    if (that.data.store_info.is_favorate) {
      wx.showToast({
        title: '您已收藏了该店铺'
      });
      return;
    }
    request.postUrl('member_favorites_store.favorites_add', {
      store_id: that.data.store_info.store_id
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
      that.data.store_info.is_favorate = true;
      that.setData({
        store_info: that.data.store_info
      })
      wx.showToast({
        title: '店铺收藏成功',
        icon: 'none'
      })
    })
  },

  goGoodsDetails: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id
    })
  },
  previewImage: function (e) {
    var that = this;
    var item = e.currentTarget.dataset.item
    wx.previewImage({
      current: item, // 当前显示图片的http链接
      urls: that.data.imgUrls // 需要预览的图片http链接列表
    })
  },

  previewCommentImage: function (e) {
    var url = e.currentTarget.dataset.url;
    var item = e.currentTarget.dataset.item;
    var imgUrls = item.geval_image;
    wx.previewImage({
      current: url, // 当前显示图片的http链接
      urls: imgUrls // 需要预览的图片http链接列表
    })
  },

  //改变规格
  changeSpc: function (e) {
    var that = this;
    var spec_id = e.currentTarget.dataset.spec_id;
    var spec_value_id = e.currentTarget.dataset.spec_value_id;
    var spec_name = e.currentTarget.dataset.spec_name;
    for (let key in that.data.goods_list) {
      if (that.data.goods_list[key].spec_info[0].spec_id == spec_id && that.data.goods_list[key].spec_info[0].spec_value_id == spec_value_id) {
        that.setData({
          current_spc_id: that.data.goods_list[key].spec_info[0].spec_id,
          current_spc_value_id: that.data.goods_list[key].spec_info[0].spec_value_id,
          current_spc_img_url: that.data.goods_list[key].goods_image_url,
          goods_id: that.data.goods_list[key].goods_id,
          goods_storage: that.data.goods_list[key].goods_storage,
          spec_name: spec_name,
          goods_price: that.data.goods_list[key].goods_price,
          //carts_num: that.data.goods_list[key].carts_num,
          goods_salenum: that.data.goods_list[key].goods_salenum,
          goods_marketprice: that.data.goods_list[key].goods_marketprice,
          //tabCollect: that.data.goods_list[key].is_favorate
        });
      }
    }
    // 更新该规格商品收藏及购物车数量
    request.postUrl("goods.changeSpc", {
      goods_id: that.data.goods_id
    }, function (res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        return;
      }
      that.setData({
        carts_num: res.data.datas.carts_num,
        tabCollect: res.data.datas.is_favorate
      });
    });

  },

  currentC(e) { //自定义轮播下标
    var that = this;
    that.setData({
      crrC: e.detail.current + 1
    })
  },

  //显示满减满赠
  show_ms: function () {
    this.setData({
      show_ms: true,
    })
  },
  //隐藏满减满赠
  hide_ms: function () {
    this.setData({
      show_ms: false,
    })
  },
  // 赠品列表
  /*goZp: function (e) {
    var item = e.currentTarget.dataset.item;
    console.log(item);
    wx.navigateTo({
      url: '../zp_list/zp_list?goods=' + JSON.stringify(item),
    })
  }*/
  /* 跳转首页 */
  goIndex: function () {
    wx.switchTab({
      url: '../index/index'
    });
  },
  /* 跳转店铺详情 */
  goShopDetail: function () {
    var that = this;
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + that.data.store_info.store_id
    })
  },
  /* 跳转至全部评价页面 */
  allEvaluate: function () {
    wx.navigateTo({
      url: '../commentDetails/commentDetails?goods_id=' + this.data.goods_id
    })
  },

  shareUrl() {
    let that = this;
    request.postUrl('goods.goods_share', 
                    { goods_id: that.data.goods_id, dealer_id: that.data.dealer_id }, 
                    function(res) {
                      if (!res) {
                        wx.showToast({
                          title: '什么问题'
                        });
                        return;
                      }
                      if (res.data.code != 200) {
                        wx.showToast({
                          title: res.data.datas.error
                        });
                        return;
                      }

                      that.draw(res.data.datas.wx_image)
                    })
  },

  draw(qrcodeUrl) {
    wx.showLoading()

    const that = this;
    let avatarUrl = 'https://wxapi.hangowa.com/data/upload/shop/common/default_user_portrait.gif';
    if (wx.getStorageSync("user_img")) {
      avatarUrl = wx.getStorageSync("user_img");
    }

    let imageUrl = that.data.imgUrls[0];
    imageUrl = imageUrl.replace(/http:\/\/www/, "https:\/\/wxapi");
    const avatarPromise = getImageInfo(avatarUrl);
    const commodityPromise = getImageInfo(imageUrl);
    const qrcodePromise = getImageInfo(qrcodeUrl);

    Promise.all([avatarPromise, commodityPromise, qrcodePromise]).then(([avatar, picture, qrcode]) => {
      that.setData({
        visible: true
      })

      const ctx = wx.createCanvasContext('share');

      ctx.setFillStyle('white');
      ctx.fillRect(0, 0, that.data.canvasWidth * 2, that.data.canvasHeight * 2);

      const top = 20;
      const left = 40;
      const width = that.data.canvasWidth - 2 * left
      const canvasW = rpx2px(width * 2);
      const canvasH = rpx2px(width * 2);

      // 绘制商品图片
      ctx.drawImage(
        picture.path,
        left,
        top,
        canvasW,
        canvasH
      )

      let title = that.data.goods_name;
      let yValue = canvasH + 2 * top;

      // 标题商品标题
      title = title.length < 40 ? title : title.substring(0, 40) + '...';
      let titleHeight = format.wrapText({
        ctx,
        text: title,
        x: left,
        y: yValue,
        w: canvasW - left,
        fontStyle: {
          lineHeight: 60,
          textAlign: 'left',
          textBaseline: 'top',
          font: 'normal 45px arial',
          fillStyle: '#535353'
        }
      });

      yValue = (titleHeight + 5 * top);
      let price = '￥' + that.data.goods_price;
      let priceHeight = format.wrapText({
        ctx,
        text: price,
        x: left,
        y: yValue,
        w: that.data.canvasWidth - left,
        fontStyle: {
          lineHeight: 58,
          textAlign: 'left',
          textBaseline: 'top',
          font: 'normal 60px arial',
          fillStyle: '#fe5a4c'
        }
      });

      let marketpriceX = left + ctx.measureText(price).width + top;
      let marketpriceY = yValue + top;
      let marketprice = '原价:￥' + that.data.goods_marketprice;
      format.wrapText({
        ctx,
        text: marketprice,
        x: marketpriceX,
        y: marketpriceY,
        w: that.data.canvasWidth - left,
        fontStyle: {
          lineHeight: 20,
          textAlign: 'left',
          textBaseline: 'top',
          font: 'normal 30px arial',
          fillStyle: '#aaa'
        }
      });

      let size = ctx.measureText(marketprice);
      ctx.beginPath();
      ctx.moveTo(marketpriceX, marketpriceY + 15);
      ctx.lineTo(marketpriceX + size.width, marketpriceY + 15);
      ctx.stroke();
      ctx.closePath();
      
      yValue = (priceHeight + 5 * top);

      // 绘制头像
      const radius = rpx2px(60 * 2);

      ctx.save();
      ctx.beginPath();
      ctx.arc(left + radius, yValue + radius, radius, 0, 2 * Math.PI);
      ctx.fill();
      ctx.clip();

      ctx.drawImage(
        avatar.path,
        left,
        yValue,
        radius * 2,
        radius * 2,
      )
      ctx.restore();

      let name = '店小二';
      if (wx.getStorageSync("nick_name")) {
        name = wx.getStorageSync("nick_name");
      }

      format.wrapText({
        ctx,
        text: name,
        x: left + 2 * radius + top,
        y: yValue + top,
        w: that.data.canvasWidth - left,
        fontStyle: {
          lineHeight: 20,
          textAlign: 'left',
          textBaseline: 'top',
          font: 'normal 40px arial',
          fillStyle: '#535353'
        }
      });

      let comment = '汉购网精选推荐';
      let commentX = left + 2 * radius + top;
      yValue = format.wrapText({
        ctx,
        text: comment,
        x: commentX,
        y: yValue + 4 * top,
        w: that.data.canvasWidth - left,
        fontStyle: {
          lineHeight: 45,
          textAlign: 'left',
          textBaseline: 'top',
          font: 'bold 40px arial',
          fillStyle: '#535353'
        }
      });

      const qrcodeWidth = rpx2px(200 * 2);
      const qrcodeHeight = rpx2px(200 * 2);

      // 绘制小程序码
      let commentSize = ctx.measureText(comment);
      ctx.drawImage(
        qrcode.path,
        canvasW - left - qrcodeWidth / 2,
        priceHeight + 4 * top,
        qrcodeWidth,
        qrcodeHeight
      )

      // 长按小程序码查看详情
      ctx.font = 'normal 38px arial';
      ctx.fillStyle = '#535353';
      ctx.fillText('长按立即购买', 2 * left + 2 * radius, yValue + 5 * top);

      ctx.draw(false, () => {
        canvasToTempFilePath({
          canvasId: 'share',
        }, that).then(({ tempFilePath }) => that.setData({ imageFile: tempFilePath }))
      })

      wx.hideLoading()
    })
    .catch(() => {
        wx.hideLoading()
      })
  },

  shareToFriend() {
    this.setData({ visible: false })

    let pymramPath = '';
    if (this.data.dealer_id) {
      pymramPath += '&dealer_id=' + this.data.dealer_id;
    }

    let friend = wx.getStorageSync("nick_name");
    friend = friend.length > 0 ? friend : '您的好朋友';

    if (wx.getStorageSync('user_token') != '') {
      if (!this.data.dealer_id) {
        wx.navigateTo({
          url: '../distributionCenter/distributionCenter',
        })

        return;
      }

      return {
        title: friend + '向您推荐:\n' + this.data.goods_name,
        path: '/pages/goodsDetails/goodsDetails?goods_id=' + this.data.goods_id + pymramPath,
        imageUrl: this.data.imgUrls[0]
      }
    }

    return {
      title: friend + '向您推荐:\n' + this.data.goods_name,
      path: '/pages/goodsDetails/goodsDetails?goods_id=' + this.data.goods_id + pymramPath,
      imageUrl: this.data.imgUrls[0]
    }
  },

  handleSave() {
    let that = this;
    const { imageFile } = this.data

    if (imageFile) {
      saveImageToPhotosAlbum({
        filePath: imageFile,
      }).then(() => {
        wx.showModal({
          title: '提示',
          showCancel: false,
          confirmText: '知道了',
          confirmColor: '#0facf3',
          content: '已成功为您保存图片到手机相册。',
          success: (res) => {
            if (res.confirm) {
              console.log('保存成功，隐藏模态框')
              that.setData({ visible: false })
            }
          }
        })
      })
        .catch((res) => {
          wx.hideLoading();
          wx.showModal({
            title: '保存出错',
            showCancel: false,
            confirmText: '知道了',
            confirmColor: '#0facf3',
            content: '您拒绝了授权 ，如果您要保存图片，请删除小程序，再重新打开。',
            success: (res) => {
              console.log(res)
            }
          })
        })
    }
  },

  /**
   * 自定义分享
   */
  onShareAppMessage: function (res) {
    var that = this;
    that.setData({ visible: false })

    let pymramPath = '';
    if (this.data.dealer_id) {
      pymramPath += '&dealer_id=' + this.data.dealer_id;
    }

    let friend = wx.getStorageSync("nick_name");
    friend = friend.length > 0 ? friend : '你的好朋友';

    if(wx.getStorageSync('user_token')!=''){
      if (!this.data.dealer_id) {
        wx.navigateTo({
          url: '../distributionCenter/distributionCenter',
        })

        return;
      }

      if (that.data.mossage.tuan_flag == 1){ //拼团商品
        console.log(32)
        return {
          title: friend + that.data.mossage.member_info.member_name + "超值推荐:\n" + that.data.mossage.tuan_info.pintuan_price + "元拼团" + that.data.mossage.tuan_info.goods_name,
          path: '/pages/goodsDetails/goodsDetails?tuan_id=' + that.data.mossage.tuan_info.pintuan_id + '&goods_id=' + that.data.mossage.tuan_info.goods_id + pymramPath,
          imageUrl: that.data.imgUrls[0]
        }
      } else {
        return {
          title: friend + '向您推荐:\n' + that.data.goods_name,
          path: '/pages/goodsDetails/goodsDetails?goods_id=' + that.data.goods_id + pymramPath,
          imageUrl: that.data.imgUrls[0]
        }
      }
    }else{
      return {
        title: friend + '向您推荐:\n' + that.data.goods_name,
        path: '/pages/goodsDetails/goodsDetails?goods_id=' + that.data.goods_id + pymramPath,
        imageUrl: that.data.imgUrls[0]
      }
    }
  },
})