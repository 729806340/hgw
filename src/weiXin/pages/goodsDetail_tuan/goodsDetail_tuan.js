
var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
const HtmlParser = require('../../html-view/index');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    shareImgPath: '',
    goods_id:'',
    totalData:[],//总得数据
    indicatorDots: true,
    interval: 3000,
    goods_info:[],//商品详情信息
    count:0,
    share_show: false,
    cur_time:'',
    interval_out:'',
    time:'',
    if_timeShow:true,
    goods_list:[],
    spec_all:[],
    item_list:[],
    spec_id:[],
    if_haibao: false,
    goods_img: "",//商品图片
    erweima: "",//base64二维码
    box_img:'',//海报边框
    info: '',//数据
    goods_name:'',
    tz_id:'',//默认团长id
    if_show:false,//不同团长弹窗是否显示
    changeTuanList:'',//不同团长数据
    view_tuanzhang_id:0,//扫码查看的团长id
    time_text:'距团购结束',
  },
  //海报弹窗显示
  haibaoshow() {
    var that = this
    if (!wx.getStorageSync('user_img')) {
      that.setData({
        login_show: true
      })
      return
    }
    that.drawImg()
    that.setData({
      share_show: false
    })
    // setTimeout(function () {
    //   that.setData({
    //     if_haibao: true
    //   })
    // }, 500)
  },
  //海报弹窗隐藏
  haibaohide() {
    this.setData({
      if_haibao: false
    })
  },

  //分享弹窗关闭
  sharehide() {
    var that = this
    that.setData({
      share_show: false,
    })
  },
  //分享弹窗显示
  shareshow() {
    var that = this
    that.setData({
      share_show: true,
    })
  },
  //下载图片到本地
  download_http(goods_img,box_img,erweima) {
    var that = this;
    wx.downloadFile({
      url: goods_img,
      success: function (res) {
        console.log(res,'-------------------------')
        that.setData({
          goods_img: res.tempFilePath
        })
        console.log('商品图片下载成功--' + that.data.goods_img)
      }
    })
    wx.downloadFile({
      url: box_img,
      success: function (res) {
        console.log(res, '-------------------------')
        that.setData({
          box_img: res.tempFilePath
        })
        console.log('海报边框下载成功--' + that.data.box_img)
      }
    })
    wx.downloadFile({
      url: erweima,
      success: function (res) {
        console.log(res, '-------------------------')
        that.setData({
          erweima: res.tempFilePath
        })
        console.log('二维码下载成功--' + that.data.erweima)
      }
    })
  },


  //生成海报
  drawImg() {
    let that = this;
    wx.showLoading({
      title: '加载中'
    })
    if(that.data.shareImgPath != ''){
      that.setData({
        if_haibao: true
      })
      wx.hideLoading()  //图片已经绘制出来，隐藏提示框
      return
    }
    let context = wx.createCanvasContext('share')  //这里的“share”是“canvas-id”

    var box_img = that.data.box_img  //盒子背景
    var goods_img = that.data.goods_img  //商品图片
    var erweima = that.data.erweima  //二维码图片
    var goods_name = that.data.goods_info.goods_name//团购名称  44字两排
    var chr = goods_name.split("");//这个方法是将一个字符串分割成字符串数组
    var temp = "";
    var row = [];
    for (var a = 0; a < chr.length; a++) {
      if (context.measureText(temp).width < 180) {
        temp += chr[a];
      } else {
        a--;
        row.push(temp);
        temp = "";
      }
    }
    row.push(temp);
    if (row.length > 3) {
      row[2] = row[2].substring(0, row[2].length - 3) + '...';
      row = row.slice(0, 3);
    }
    console.log(row)
    for (var b = 0; b < row.length; b++) {
      context.fillText(row[b], 10, 30 + b * 30, 300);
    }
    var i = '￥'
    var goods_price = that.data.goods_info.goods_price //商品价格
    var goods_marketprice = '￥' + that.data.goods_info.goods_marketprice //商品 原 价格

/////////////////////////////

    context.setFillStyle('#fff')    //这里是绘制白底，让图片有白色的背景
    context.fillRect(0, 0, 414, 736)

    context.drawImage(box_img, 0, 0, 414, 736) //盒子背景

    context.drawImage(goods_img, 84, 126, 245, 245) //商品图片

    context.setFontSize(17) // 商品名称
    context.setFillStyle('#333') // 商品名称
    if (row.length == 1) {
      context.fillText(row[0], 50, 437)
    } else if (row.length == 2) {
      context.fillText(row[0], 50, 437)
      context.fillText(row[1], 50, 461)
    } else if (row.length == 3) {
      context.fillText(row[0], 50, 437)
      context.fillText(row[1], 50, 461)
      context.fillText(row[2], 50, 485)
    }

    context.setFontSize(15) // ￥
    context.setFillStyle('#F64234') // ￥
    context.fillText(i, 50, 520) 

    context.setFontSize(24) // 商品价格
    context.setFillStyle('#F64234') // 商品价格
    context.fillText(goods_price, 68, 520) 

    var pri_W = context.measureText(goods_price).width
    context.setFontSize(16) // 商品 原 价格
    context.setFillStyle('#999') // 商品 原 价格
    context.fillText(goods_marketprice, 68 + pri_W + 5, 520) 

    var m_pri_W = context.measureText(goods_marketprice).width
    context.setFillStyle('#999')    //原价横线
    context.fillRect(68 + pri_W + 5, 514, m_pri_W + 2, 1)

    context.drawImage(erweima, 50, 610, 90, 90) //二维码

    var text_a = '长按识别小程序'
    context.setFontSize(18) // 提示文字
    context.setFillStyle('#333') // 提示文字
    context.fillText(text_a, 155, 643) 

    var text_b = '汉购网 —— 健康 品质 生活'
    context.setFontSize(15) // 提示文字
    context.setFillStyle('#666') // 提示文字
    context.fillText(text_b, 155, 675) 

    //把画板内容绘制成图片，并回调画板图片路径
    context.draw(false, function () {
      wx.canvasToTempFilePath({
        x: 0,
        y: 0,
        width: 414,
        height: 736,
        destWidth: 1242,
        destHeight: 2208,
        canvasId: 'share',
        fileType: 'jpg', //图片的质量，目前仅对 jpg 有效。取值范围为 (0, 1]，不在范围内时当作 1.0 处理。
        quality: 1,
        success: a => {
          that.setData({
            shareImgPath: a.tempFilePath,  //将绘制的图片地址保存在shareImgPath 中
            if_haibao: true
          })
          console.log(that.data.shareImgPath)    
          // wx.previewImage({     //将图片预览出来
          //     urls: [that.data.shareImgPath]
          // })
          wx.hideLoading()  //图片已经绘制出来，隐藏提示框
        },
        fail: e => { console.log('失败') }
      })
    })
  },

  //点击保存图片
  save() {
    let that = this
    //若二维码未加载完毕，加个动画提高用户体验
    wx.showToast({
      icon: 'loading',
      title: '正在保存图片',
      duration: 1000
    })
    //判断用户是否授权"保存到相册"
    wx.getSetting({
      success(res) {
        //没有权限，发起授权
        if (!res.authSetting['scope.writePhotosAlbum']) {
          wx.authorize({
            scope: 'scope.writePhotosAlbum',
            success() {//用户允许授权，保存图片到相册
              that.savePhoto();
            },
            fail() {//用户点击拒绝授权，跳转到设置页，引导用户授权
              wx.openSetting({
                success() {
                  wx.authorize({
                    scope: 'scope.writePhotosAlbum',
                    success() {
                      that.savePhoto();
                    }
                  })
                }
              })
            }
          })
        } else {//用户已授权，保存到相册
          that.savePhoto()
        }
      }
    })
  },
  //保存图片到相册，提示保存成功
  savePhoto() {
    let that = this
    wx.saveImageToPhotosAlbum({
      filePath: that.data.shareImgPath,
      success(res) {
        that.setData({
          if_haibao: false
        })
        wx.showToast({
          title: '保存成功',
          icon: "success",
          duration: 1000
        })
      }
    })
  },
  //不同团长弹窗隐藏
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
      lay_x:0,
      lay_y:0,
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
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var op = options.secen.split('#');
    var goods_id_array = op[0].split('|');
    var tz_id_array = op[1].split('|');
    that.setData({
      goods_id:goods_id_array[1],
      view_tuanzhang_id:tz_id_array[1]
    })
    if(that.data.view_tuanzhang_id != 0 && wx.getStorageSync('is_shequ_tuanzhang') !=2){
      that.change_tuanzhang()//不同团长
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  /* 跳转至全部评价页面 */
  allEvaluate: function () {
    wx.navigateTo({
      url: '../commentDetails/commentDetails?goods_id=' + this.data.goods_id
    })
    
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;
    const fsm = wx.getFileSystemManager();  //文件管理器
    fsm.readdir({  // 获取文件列表
      dirPath: wx.env.USER_DATA_PATH,// 当时写入的文件夹
      success(res) {
        console.log(res)
        res.files.forEach((el) => { // 遍历文件列表里的数据
          // 删除存储的垃圾数据
          fsm.unlink({
            filePath: `${wx.env.USER_DATA_PATH}/${el}`, // 这里注意。文件夹也要加上，如果直接文件名的话会无法找到这个文件
            fail(e) {
              console.log('readdir文件删除失败：', e)
            }
          });
        })
      }
    })
    that.getIndex()
    that.initRed()//气泡
  },

  getIndex: function (){
    var that = this;
    // wx.showLoading({
    //   title: '加载中',
    // })
    request.postUrl('goods.detail3',
    { goods_id: that.data.goods_id},
    function (res) {
      if(res.data.code == 200){
        wx.setNavigationBarTitle({
          title: res.data.datas.goods_info.goods_name
        })
        var goods_info = res.data.datas.goods_info
        goods_info.mobile_body = goods_info.mobile_body.replace(/\<img/gi, '<img style="width:100%;height:auto;display:block;" ')
        that.setData({
          totalData: res.data.datas,
          goods_name: res.data.datas.goods_info.goods_name,
          goods_info: goods_info,
          spec_all: res.data.datas.spec_all,
          goods_list: res.data.datas.goods_list,

        })
        if (res.data.datas.goods_info.goods_spec) {
          var spec_name = ''
          var spec_id = []
          for (let i in res.data.datas.goods_info.goods_spec) {
            spec_name = spec_name + '-' + res.data.datas.goods_info.goods_spec[i]
            spec_id.push(i)
          }
          spec_name = spec_name.substr(1)
          that.setData({
            spec_name: spec_name,
            spec_id: spec_id
          })
        }

        clearInterval(that.data.interval_out);
        var end_time = res.data.datas.config_tuan_end_date
        if(res.data.datas.if_shequ_xianshi){
          end_time = res.data.datas.shequ_xianshi_info.end_time
          that.setData({
            time_text:'距秒杀结束'
          })
        }
        var timestamp = Date.parse(new Date());
        timestamp = timestamp / 1000;
        var cur_time = parseInt(end_time) - timestamp
        that.setData({
          cur_time: cur_time
        })

        var interval_out = setInterval(function () {
          if (that.data.cur_time <= 0) {
            clearInterval(that.data.interval_out);
            that.setData({
              if_timeShow: false
            })
            return
          }
          that.setData({
            if_timeShow: true
          })
          var time = util.GetRTime_t(that.data.cur_time);
          that.setData({
            time: time,
            cur_time: that.data.cur_time - 1,
          })
          // console.log(that.data.time)
        }, 1000);

        that.data.interval_out = interval_out;

        that.download_http(res.data.datas.goods_info.goods_image_url, 'http://www.test.hangowa.com/data/upload/mobile/special/s0/s0_06439730780143186.png',res.data.datas.goods_share_qr)
      // wx.hideLoading()

      }else{
        wx.showToast({
          title: res.data.datas.error,
          icon: 'none'
        })
      }
    })
  },
  gopath(e){
    wx.switchTab({
      url: e.currentTarget.dataset.path,
    })
  },
  //添加按钮
  add: function (e) {
    var that = this;
    if(!that.data.if_timeShow){
      return;
    }
    if (!wx.getStorageSync("user_token")) {
      wx.showToast({
        title: '请先登录',
        icon:'none'
      })
      return;
    }
    var type = e.currentTarget.dataset.type
    var index = e.currentTarget.dataset.index
    var totalData = that.data.totalData
    if(type == 'add'){
      totalData.goods_info.carts_num = JSON.parse(totalData.goods_info.carts_num) + 1
    }else if(type == 'del'){
      totalData.goods_info.carts_num = JSON.parse(totalData.goods_info.carts_num) - 1
    }
    if(totalData.goods_info.carts_num == 0){
      request.postUrl("cart.delete", {
        goods_id: that.data.goods_id
      }, function (res) {
        if(res.data.code == '200'){
          that.setData({
            totalData: totalData
          })
          that.initRed() //气泡
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
      });
    }else if(totalData.goods_info.carts_num > 0){
      request.postUrl('cart.add_shequ', { 
        goods_id: that.data.goods_id,
        quantity: totalData.goods_info.carts_num
      }, function (res) {  
        if(res.data.code == '200'){
          that.setData({
            totalData: totalData
          })
          that.initRed()//气泡
        }else{
          wx.showToast({
            title: res.data.datas.error,
            icon:'none'
          })
        }
      })
    }   
  },
  //立即购买
  gopay(e){
    if(!this.data.if_timeShow){
      return;
    }
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
    var that = this
    var id = that.data.goods_id
    wx.navigateTo({
      url: '../sureOrder_she/sureOrder_she?cart_id=' + id+'|1' + '&ifcart=' + 0,
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
          that.setData({
            count:0
          })
        } else {
          wx.setTabBarBadge({
            index: 2,
            text: (res.data.datas.count + ""),
          })
          that.setData({
            count:res.data.datas.count
          })
        }
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
    that.setData({
      animationData: animation.export(),
      specStatus: true,
    })
    setTimeout(function () {
      animation.translateY(0).step()
      that.setData({
        specStatus: true,
        animationData: animation.export()
      })
    }.bind(that),200)
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

  changeSpc(e){
    var that = this
    var spec_value_id = e.currentTarget.dataset.spec_value_id
    var index = e.currentTarget.dataset.index
    var spec_id = that.data.spec_id
    spec_id[index] = spec_value_id
    for(var i=0;i<spec_id.length;i++){
      spec_id[i] = parseInt(spec_id[i])
    }
    var goods_list = that.data.goods_list
    var id_a = []
    for(var i=0;i<goods_list.length;i++){
      var id_b = []
      for(var y=0;y<goods_list[i].spec_info.length;y++){
        id_b.push(goods_list[i].spec_info[y].spec_value_id)
      }
      id_b = JSON.stringify(id_b)
      id_a.push(id_b)
    }
    for (let key in id_a) {
      if(id_a[key] === JSON.stringify(spec_id)){
        that.setData({
          goods_id:that.data.goods_list[key].goods_id
        })
        that.getIndex()
        that.hideModal()
        return
      }
    }
  },




  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    clearInterval(this.data.interval_out);
    this.setData({
      view_tuanzhang_id:0
    })
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    clearInterval(this.data.interval_out);
    this.setData({
      view_tuanzhang_id:0
    })
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
    var tz_id = 0
    if(wx.getStorageSync('tuanzhang_id')){
      tz_id = wx.getStorageSync('tuanzhang_id')
    }
    return {
      title: this.data.goods_info.goods_name,
      imageUrl:this.data.totalData.goods_image[0],
      path: 'pages/goodsDetail_tuan/goodsDetail_tuan?secen=' + 'goods_id|'+ this.data.goods_id + '#tz_id|' + tz_id,
    }
  }
})