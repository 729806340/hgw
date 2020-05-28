var request = require('../../utils/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    order_id : '',
    order_goods: [],//商品信息列表
    productInfo: [], //图片src列表,
    stroe_desccredit: 5, //店铺评价：商品描述（5 最高 0最低）
    des : 0,
    store_servicecredit: 5,//店铺评价：服务(5 最高 0 最低)
    ser: 0,
    store_deliverycredit: 5, //店铺评价：发货速度（5 高低 0 最低）
    del : 0,
    max: 30, //最多字数 ,
    goods_evaluate: [], //商品评价json串
    score :[],//商品评分
    commodityNum : 5, //商品最高星
    Num :0 ,
    list: [], //储存商品星级列表 commodityNum（选中的星级）
    file_name : [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var order_id = options.order_id;
    var rec_id = options.rec_id;
    if (order_id == '' || rec_id.length==0){
      return;
    }
    this.setData({
      order_id: JSON.parse(order_id),
      rec_id: rec_id,
    })
    request.postUrl('member_evaluate.index',{order_id: that.data.order_id},function(res){
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });
        return;
      }
      var message = res.data.datas.order_goods;
      var lstnth = res.data.datas.order_goods.length;
      var goods_evaluate = [];
      var list = [];
      var file_name = [];
      var productInfo = that.data.productInfo
      for (var i = 0; i < lstnth;i++){
        list[i] = { commodityNum:5,Num:0}
      }
      for (var i = 0; i < lstnth; i++) {
        file_name[i] = [];
        productInfo[i] = [];
      }
      for (var i = 0; i < lstnth;i++){
        goods_evaluate[i] = {
          rec_id: '',
          score: 5,
          comment: '',
          evaluate_image: [],
        }
      }
      that.setData({
        order_goods: message,
        list: list,
        goods_evaluate: goods_evaluate,
        file_name: file_name,
        productInfo: productInfo
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
  formSubmit(e){ //提交评价信息 
    var that = this ;
    var order_id = that.data.order_id;
    var stroe_desccredit = that.data.stroe_desccredit;
    var store_servicecredit = that.data.store_servicecredit;
    var store_deliverycredit = that.data.store_deliverycredit;
    var goods_evaluate = that.data.goods_evaluate;
    var list = that.data.list;
     for (var i = 0; i < goods_evaluate.length;i++){ 
       goods_evaluate[i].score = list[i].commodityNum;
       goods_evaluate[i].rec_id = that.data.order_goods[i].rec_id;
       goods_evaluate[i].evaluate_image = that.data.file_name[i]
    }
    that.setData({
      goods_evaluate: goods_evaluate,
    })
      request.postUrl('member_evaluate.save', {
        order_id: order_id, 
        goods_evaluate: JSON.stringify(goods_evaluate),
        stroe_desccredit: stroe_desccredit,
        store_servicecredit: store_servicecredit,
        store_deliverycredit: store_deliverycredit,
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
          var order_goods = that.data.order_goods;
          var lists =[];
          for (var i = 0; i < order_goods.length;i++){
          lists.push(order_goods[i].goods_id)
          }
          wx.navigateTo({
            url: '../commentDetails/commentDetails?goods_id=' + lists
          })
        })
  },
  addimg(e) {//上传图片
    var that = this;
    var index = e.currentTarget.dataset.index;
    var num = 5 - that.data.productInfo[index].length;
    console.log(that.data.productInfo[index].length)
    if (that.data.productInfo[index].length < 5) {
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
              url: request.getTrueUrl('sns_album.file_upload'),
              filePath: tempFilePaths[i],
              name: 'file',
              formData: {
                key: wx.getStorageSync("user_token"),
              },
              success: function (res) {
                uploadImgCount++;
                var data = JSON.parse(res.data);
                var productInfo = that.data.productInfo;
                var file_name = that.data.file_name
                if (productInfo.length == 0) {
                  productInfo = [];
                }
                productInfo[index].push({ "src": data.datas.file_url, checked:false})
                file_name[index].push(data.datas.file_name)
                that.setData({
                  productInfo: productInfo,
                  file_name:file_name,
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
    } else {
      wx.showToast({
        title: "上传图片达到上限",
        icon: 'none',
      })
    }
  },
  //删除图片
  clearImg(e) {
    var index = e.currentTarget.dataset.index;
    var porindex = e.currentTarget.dataset.porindex;
    var productInfo = this.data.productInfo;
    productInfo[index].splice(porindex, 1)
    this.setData({
      productInfo: productInfo
    })
  },
  show(e){
    var index = e.currentTarget.dataset.index;
    var porindex = e.currentTarget.dataset.porindex;
    var productInfo = this.data.productInfo;
    var item = productInfo[index]
    item[porindex].checked =true;
    this.setData({
      productInfo: productInfo
    })
  },
  shop(e){ //店铺评价
    var id = e.currentTarget.dataset.id
    var num1,
        num2,
        num3
    if (id == 'des'){
      num1 = Number(e.currentTarget.dataset.index)
      this.setData({
        des: 5 - num1,  
        stroe_desccredit: num1, 
      })
    }
    if(id == 'dess'){  
      num1 = Number(e.currentTarget.dataset.index) + this.data.stroe_desccredit;
      this.setData({
        des: 5 - num1, 
        stroe_desccredit:num1,
      })
    }
    if (id == 'ser') {
      num2 = Number(e.currentTarget.dataset.index)
      this.setData({
        ser: 5 - num2,
        store_servicecredit: num2,
      })
    }
    if (id == 'sers') {
      num2 = Number(e.currentTarget.dataset.index) + this.data.store_servicecredit;
      this.setData({
        ser: 5 - num2,
        store_servicecredit: num2,
      })
    }
    if (id == 'del') {
      num3 = Number(e.currentTarget.dataset.index)
      this.setData({
        del: 5 - num3,
        store_deliverycredit: num3,
      })
    }
    if (id == 'dels') {
      num3 = Number(e.currentTarget.dataset.index) + this.data.store_deliverycredit;
      this.setData({
        del: 5 - num3,
        store_deliverycredit:num3,
      })
    }
  },
  ly_input(e){
    // 获取输入框的内容
    var value = e.detail.value;
    var index = e.currentTarget.dataset.index;
    var goods_evaluate = this.data.goods_evaluate;
    goods_evaluate[index].comment = value;
    console.log(goods_evaluate)
    // 获取输入框内容的长度
    var len = parseInt(value.length);
    //最多字数限制
    if (len > this.data.max){
      wx.showToast({
        title: '超出字数限制'
      })
      return;
    } 
    this.setData({
      goods_evaluate: goods_evaluate,
    })
  },
  commodity(e){ //商品评价
    var id = e.currentTarget.dataset.id;
    var index = e.currentTarget.dataset.index;
    var list = this.data.list;
    var num
    if (id == 'coy'){
      num = Number(e.currentTarget.dataset.in)
    }else{
      num = Number(e.currentTarget.dataset.in) + this.data.list[index].commodityNum;
      console.log(num)
    }
    list[index].Num = 5 - num;
    list[index].commodityNum = num;
    this.data.goods_evaluate[index].score = num
    this.setData({
     list : list,
      goods_evaluate: this.data.goods_evaluate,
    })
  },
})