var request = require('../../utils/request.js');
Page({
  data: {
    showModalStatus: false, //到货提醒按钮初始化状态 //是否显示弹窗
    order_id :'',//商品ID
    refund : null, //判断是否可以全额退款
    chooimg : false, //选中状态
    order :{},//全款返回列表
    message : '',//退款理由,
    productInfo: [], //图片src列表
    order_goods_id : '',
    goods : {}, //商品信息列表
    reason_list :[], //退款原因列表
    refund_type : 1, //售后类型  1仅退款 2退款退货
    show :false, // 
    reason_id : null, //理由ID
    values : '', //选中的理由,
    show: false,
    refund_value :'',//选中类型名字
    refund_amount:0, //单个退款的金额
    moneys:'',
    is_post:true,
  },
  onLoad :function(options){
    var order_id = options.order_id;
    var refund = options.refund;
    var order_goods_id = options.order_goods_id;
    this.setData({
      order_id: order_id,
      refund : refund,
      order_goods_id: order_goods_id,
    })
  },
  onShow:function(){
    var that = this;
    if (this.data.order_id == '' || this.dataorder_goods_id==''){
        wx.switchTab({
            url: '../me/me'
        });
    }
    if (that.data.refund==1){ //全额退款
      request.postUrl('member_refund.refund_all_form', { order_id: that.data.order_id},function(res){
        if(res.data.code!=200){
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none',
          })
          return
        }
        that.setData({
          order: res.data.datas.order,
          moneys: res.data.datas.order.allow_refund_amount
        })
      })
    } else if (that.data.refund == 0){  //单个订单退款
      request.postUrl('member_refund.refund_form', {
        order_id: that.data.order_id, order_goods_id: that.data.order_goods_id
      }, function (res) {
        if (res.data.code != 200) {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none',
          })
          return
        }
        var reason_list = res.data.datas.reason_list;
        for (var p of reason_list){
          p.chose=false;
        }
        that.setData({
          order: res.data.datas.order,
          goods: res.data.datas.goods,
          reason_list: reason_list, 
          refund_amount: res.data.datas.goods.goods_pay_price,
          moneys: res.data.datas.goods.goods_pay_price,
        })
      })
    } else{
      return;
    }
  },
  Refunds (e) {  //退款说明：
    var value = e.detail.value;
    var length = value.length;
    if (length >= 20) {
      wx.showToast({
        title: "字数超出限制"
      })
    }
    this.setData({
      message: value
    })
  }, 
  change_pay_type: function (e) { //部分退款宣增类型
    var values = this.data.refund_value
    if (e.currentTarget.dataset.type == 1){
      values = '仅退款'
    }else{
      values = '退货退款'
    }
    this.setData({
      refund_type: e.currentTarget.dataset.type,
      refund_value: values,
    })
    this.hideModal() //影藏
  },
  addimg(){//上传图片
    var that = this;
    var num = 5 - that.data.productInfo.length;
    console.log(that.data.productInfo.length)
    if (that.data.productInfo.length<5){
      wx.chooseImage({
        count: num,  //最多可以选择的图片总数  
        sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有  
        sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有  
        success: function (res) {
          // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片  
          var tempFilePaths = res.tempFilePaths;
          //启动上传等待中...  
          wx.showToast({
            title: '正在上传...',
            icon: 'loading',
            mask: true,
            duration: 10000
          })
          var uploadImgCount = 0;
          for (var i = 0, h = tempFilePaths.length; i < h; i++) {
            wx.uploadFile({
              url: request.getTrueUrl('index.new_upload_pic'),
              filePath: tempFilePaths[i],
              name: 'file',
              formData: {
                'upload_type': 'refund_pic',
                'key': wx.getStorageSync("user_token")
              },
              success: function (res) {
                uploadImgCount++;
                var data = JSON.parse(res.data);
                var productInfo = that.data.productInfo;
                if (productInfo.length == 0) {
                  productInfo = [];
                }
                productInfo.push({
                  "src": data.datas.http_pic,
                  checked : false,
                  pic : data.datas.pic
                  }
                );
                that.setData({
                  productInfo: productInfo
                });
                //如果是最后一张,则隐藏等待中  
                if (uploadImgCount == tempFilePaths.length) {
                  wx.hideToast();
                }
              },
              fail: function (res) {
                wx.hideToast();
                wx.showModal({
                  title: '错误提示',
                  content: '上传图片失败',
                  showCancel: false,
                  success: function (res) { }
                })
              }
            });
          }
        }
      });  
    }else{
      wx.showToast({
        title : "上传图片达到上限",
        icon : 'none',
      })
    } 
  },
  show(e){
    var index = e.currentTarget.dataset.index;
    var productInfo = this.data.productInfo;
    productInfo[index].checked = true;
    this.setData({
      productInfo: productInfo,
    })
  },
  //删除图片
  clearImg(e){
    var index = e.currentTarget.dataset.index;
    var productInfo = this.data.productInfo;
    productInfo.splice(index,1)
    this.setData({
      productInfo: productInfo
    })
  },
  getMoney(e){ //单个退款金额选择部分退款（获取）
    var value = e.detail.value//金额
    var regu = /^[0-9]+\.?[0-9]*$/;
    if (parseInt(value) < 0 || value == "") {
      this.setData({
        moneys: "",
        refund_amount: this.data.goods.goods_pay_price,
      })
      return;
    }
    if (!regu.test(value)){
      wx.showToast({
        title:'请输入正确的金额',
        icon:'none'
      })
      this.setData({
        moneys: "",
        refund_amount: this.data.goods.goods_pay_price,
      })
      return;
    }
    if (parseFloat(value) > parseFloat(this.data.goods.goods_pay_price)){
      wx.showToast({
        title:"达到上限",
        icon:'none'
      })
      this.setData({
        refund_amount: this.data.goods.goods_pay_price,
        moneys: ""
      })
    }else{
      var ret = /^\d+(\.\d{1,2})?$/;
      if (!ret.test(parseFloat(value))){ //超过两位
        this.setData({
          refund_amount: parseInt(value).toFixed(2),
          moneys: parseInt(value).toFixed(2),
        })
      }else{
        this.setData({
          refund_amount: value,
          moneys: value,
        })
      } 
    }
  },
  fouc(e){ //获得焦点
    if (e.detail.value == this.data.goods.goods_pay_price){
      this.setData({
        moneys:'',
      })
    }
  },
  blur(e){ //失去焦点
    if(e.detail.value ==""){
      this.setData({
        moneys: this.data.goods.goods_pay_price,
      })
    }
  },
  //上传信息
  formSubmit(){
    var that = this;
    if(!that.data.is_post){
      return
    }
    that.setData({
      is_post: false
    })
    var order_id = that.data.order_id;
    var productInfo = that.data.productInfo;
    var refund_pic = [];
    var buyer_message = that.data.message;
    for (var pl of productInfo) {
      refund_pic.push(pl.pic)
    }
    if (that.data.refund == 1){ //全额整单退款提交
      var goods = that.data.goods;
      request.postUrl('member_refund.refund_all_post', { order_id: order_id, buyer_message: buyer_message, goods: goods, refund_pic: JSON.stringify(refund_pic)},function(res){
        that.setData({
          is_post: true
        }) 
        if (res.data.code != 200) {
           wx.showToast({
             title: res.data.datas.error,
             icon: 'none',
           })
           return
         }
         wx.navigateTo({
           url:'../quitList/quitList',
         })
       })
    }else{ //部分单个商品退款提交
      var order_goods_id = that.data.order_goods_id;
      var refund_amount = that.data.refund_amount;
      console.log(refund_amount);
      var goods_num = that.data.goods.goods_num;
      var reason_id = that.data.reason_id;
      var ovj = {
        order_id: order_id,
        order_goods_id: order_goods_id,
        reason_id: reason_id,
        refund_type: that.data.refund_type,
        refund_amount: refund_amount,
        goods_num: goods_num,
        buyer_message: buyer_message,
        refund_pic: JSON.stringify(refund_pic)
      }
      request.postUrl('member_refund.refund_post', ovj,function(res){
        that.setData({
          is_post: true
        }) 
        if (res.data.code != 200) {
          wx.showToast({
            title: res.data.datas.error,
            icon: 'none',
          })
          return
        }
        wx.navigateTo({
          url: '../quitList/quitList',
        })
      })
    }
  },
  checkreson_id(e){
    var reason_id = e.currentTarget.dataset.reason_id;
    var index = e.currentTarget.dataset.index;
    var reason_list = this.data.reason_list;
    for (var l of reason_list){
      l.chose = false;
    }
    reason_list[index].chose = true;
    var values = reason_list[index].reason_info
    this.setData({
      reason_id: reason_id,
      reason_list: reason_list,
      values: values,
    })
    this.hideModal() //影藏
  },
  showModal: function(e) {
    var that = this;
    // 显示遮罩层
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })
    that.animation = animation
    animation.translateY(300).step()
    if(e.currentTarget.dataset.type == 1){
      if (e.currentTarget.dataset.refund != 1){
        that.setData({
          animationData: animation.export(),
          showModalStatus: true
        })
      }else{
        that.setData({
          showModalStatus: false
        })
      }
    }else{
      that.setData({
        animationData: animation.export(),
        show: true
      })
    }
    
    setTimeout(function() {
      animation.translateY(0).step()
      that.setData({
        animationData: animation.export()
      })
    }.bind(that), 200)
  },
  hideModal: function() {
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
    setTimeout(function() {
      animation.translateY(0).step()
      that.setData({
        animationData: animation.export(),
        showModalStatus: false,
        show: false,
      })
    }.bind(that), 200)
  },
})