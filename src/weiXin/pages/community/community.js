var request = require('../../utils/request.js');
var util = require('../../utils/util.js');
const HtmlParser = require('../../html-view/index');
var app = getApp()

Page({

  /**
   * 页面的初始数据
   */
  data: {
    select:0,
    detail_show:false,
    time: ['0','00','00','00'],
    cur_time:'',
    interval:'',
    info:'',//数据
    tuan_description:'',
    item_index:'',
    mobile_body:'',
    z_num:0,
    z_price:0,
    getNP:'',
    timeText:'距结束还剩',
    share_show:false,
    dinosaur_id:'',
    login_show:false,
    if_gd:false,
    shareImgPath:'',
    if_haibao:false,
    user_img:'',
    goods_img:"",
    erweima:"",
    if_end:2,  //1结束  2未结束
    if_add:1,  //1可以添加  2不可以添加
    config_send_time:'',//发货时间
    zt_address:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    // console.log(options.scene)
    // console.log(app.client_url)
    var that = this
    this.setData({
      dinosaur_id:options.scene
    })

    const fsm = wx.getFileSystemManager();  //文件管理器
      fsm.readdir({  // 获取文件列表
        dirPath: wx.env.USER_DATA_PATH,// 当时写入的文件夹
        success(res){
          console.log(res)
          res.files.forEach((el) => { // 遍历文件列表里的数据
            // 删除存储的垃圾数据
            fsm.unlink({
              filePath: `${wx.env.USER_DATA_PATH}/${el}`, // 这里注意。文件夹也要加上，如果直接文件名的话会无法找到这个文件
              fail(e){
                console.log('readdir文件删除失败：',e)
              }
            });
          })
        }
      })

    // wx.getSavedFileList({  // 获取文件列表
    //   success (res) {
    //     res.fileList.forEach((val, key) => { // 遍历文件列表里的数据
    //         // 删除存储的垃圾数据
    //       wx.removeSavedFile({
    //           filePath: val.filePath
    //       });
    //     })
    //   }
    // })
    wx.showLoading({
      title: '加载中',
    })
    that.orderList()//订单列表
    if(wx.getStorageSync('user_img')){
      that.download_user(wx.getStorageSync('user_img'))
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
    
  },
  //弹窗显示
  zzshow(e){
    var that = this
    that.setData({
      detail_show:true,
      select:0,
      item_index:e.currentTarget.dataset.index,
      mobile_body: new HtmlParser(that.data.info.goods_list[e.currentTarget.dataset.index].goods_list[that.data.select].mobile_body).nodes
    })
    console.log(this.data.item_index)
  },
  //弹窗关闭
  zzhide(){
    this.setData({
      detail_show:false,
      select:0,
    })
  },
  //分享弹窗显示
  shareshow(){
    var that = this
    that.setData({
      share_show:true,
    })
  },
   //分享弹窗关闭
   sharehide(){
    var that = this
    that.setData({
      share_show:false,
    })
  },
  //海报弹窗显示
  haibaoshow(){
    var that = this
    if(!wx.getStorageSync('user_img')){
      that.setData({
        login_show:true
      })
      return
    }
    that.drawImg()
    that.setData({
      share_show:false
    })
    setTimeout(function(){
      that.setData({
        if_haibao:true
      })
    },500)
  },
  //海报弹窗隐藏
  haibaohide(){
    this.setData({
      if_haibao:false
    })
  },
  //选择规格
  ggclick(e){
    var that = this
    that.setData({
      select:e.currentTarget.dataset.index
    })
    console.log(e.currentTarget.dataset.index)
  },
  //添加
  addCar(e){
    var that = this
    var info = that.data.info
    var index = e.currentTarget.dataset.index
    if(info.goods_list[index].goods_list.length == 1){
      var goods_storage = info.goods_list[index].goods_list[0].goods_storage  //库存
      var num = info.goods_list[index].goods_list[0].num  //当前数量
      if(num == goods_storage){
        wx.showToast({
          title: '库存不足',
          icon:'none',
          duration: 1500
        })
        return
      }else{
        info.goods_list[index].goods_list[0].num = info.goods_list[index].goods_list[0].num + 1
        info.goods_list[index].num = info.goods_list[index].num + 1
        that.setData({
          info:info
        })
      }
    }else{
      var goods_storage = info.goods_list[index].goods_list[that.data.select].goods_storage  //库存
      var num = info.goods_list[index].goods_list[that.data.select].num  //当前数量
      if(num == goods_storage){
        wx.showToast({
          title: '库存不足',
          icon:'none',
          duration: 1500
        })
        return
      }else{
        info.goods_list[index].goods_list[that.data.select].num = info.goods_list[index].goods_list[that.data.select].num + 1
        that.setData({
          info:info
        })
      }
    }
    that.totalFN()//总价总数
    console.log(that.data.info)
  },
  //减少
  delCar(e){
    var that = this
    var info = that.data.info
    var index = e.currentTarget.dataset.index
    if(info.goods_list[index].goods_list.length == 1){
      var num = info.goods_list[index].goods_list[0].num  //当前数量
      info.goods_list[index].goods_list[0].num = info.goods_list[index].goods_list[0].num - 1
      info.goods_list[index].num = info.goods_list[index].num - 1
      that.setData({
        info:info
      })
    }else{
      var num = info.goods_list[index].goods_list[that.data.select].num  //当前数量
      info.goods_list[index].goods_list[that.data.select].num = info.goods_list[index].goods_list[that.data.select].num - 1
      that.setData({
        info:info
      })
    }
    that.totalFN()//总价总数
    console.log(that.data.info)
  },
  //总价总数
  totalFN(){
    var that = this
    var goods_list = that.data.info.goods_list
    var z_num = 0
    var z_price = 0
    var getNP = []
    for(var i=0;i<goods_list.length;i++){
      for(var y=0;y<goods_list[i].goods_list.length;y++){
        var item = goods_list[i].goods_list[y]
        z_num = z_num + item.num
        z_price = z_price + (item.num * parseFloat(item.goods_price))
        z_price = Math.round(z_price * 10) / 10
        if(item.num > 0){
          var getNP_i = item.goods_id +'|'+item.num
          getNP.push(getNP_i)
        }
      }
    }
    getNP = getNP.join(',')
    that.setData({
      z_num:z_num,
      z_price:z_price,
      getNP:getNP
    })
    console.log(that.data.getNP)
  },
  //拨打电话
  phoneCall(){
    var that = this
    wx.makePhoneCall({
      phoneNumber: that.data.info.tuan_zhang_info.phone //仅为示例，并非真实的电话号码
    })
  },
  //没选商品点击我要团购
  toastclick(){
    wx.showToast({
      title: '您还未选择商品~',
      icon: 'none',
      duration: 2000
    })
  },
  //我要团购
  gosureorder(){
    var that = this
    request.postUrl('shequ_dinosaur_buy.index', {
      dinosaur_id:that.data.dinosaur_id,
      cart_id:that.data.getNP,
      wx_nick_name:wx.getStorageSync('nick_name'),
      wx_user_avatar:wx.getStorageSync('user_img')
    }, function(res) {
      if (res.data.code == 200) {
        var info = {}
        info.deliver_type = that.data.info.deliver_type
        info.tuan_zhang_info = that.data.info.tuan_zhang_info
        info.zt_address_info = that.data.info.zt_address_info
        wx.navigateTo({
          url: '../sureOrder_she/sureOrder_she?info='+JSON.stringify(info)+'&datalist='+JSON.stringify(res.data.datas)+'&dinosaur_id='+that.data.dinosaur_id+'&cart_id='+that.data.getNP
        });
      }else{
        that.setData({
          login_show:true
        })
      }
    })
  },
  //关闭授权
  loginhide(){
    this.setData({
      login_show:false
    })
  },
  //订单列表
  orderList(){
    var that = this
    request.postUrl('shequ_dinosaur.index', {
      dinosaur_id:that.data.dinosaur_id
    }, function(res) {
      if (res.data.code == 200) {
        wx.hideLoading()
        that.download(res.data.datas.info.tuan_info.config_pic,res.data.datas.info.erweima)
        var info = res.data.datas.info
        for(var i=0;i<info.goods_list.length;i++){
          info.goods_list[i].num = 0
          for(var y=0;y<info.goods_list[i].goods_list.length;y++){
            info.goods_list[i].goods_list[y].num = 0
          }
        }
        that.setData({
          z_price:0,
          z_num:0,
          info:info,
          tuan_description: new HtmlParser(res.data.datas.info.tuan_description).nodes,
          config_send_time:info.tuan_info.config_send_time,
          zt_address:info.zt_address_info.area+info.zt_address_info.street+info.zt_address_info.community+info.zt_address_info.address
        })
        clearInterval(that.data.interval);
        var timestamp = Date.parse(new Date());
        timestamp = timestamp / 1000;
        if(timestamp > res.data.datas.info.end_time){
          that.setData({
            if_end:1,  //1结束  2未结束
            if_add:2,  //1可以添加  2不可以添加
          })
          return
        }
        if(timestamp < res.data.datas.info.start_time){
          var cur_time = parseInt(res.data.datas.info.start_time) - parseInt(timestamp)
          var time = util.GetRTime_t(cur_time);
          that.setData({
            time:time,
            cur_time: cur_time,
            if_end:2,  //1结束  2未结束
            if_add:2,  //1可以添加  2不可以添加
          })
          var interval = setInterval(function() {
            if(that.data.cur_time <= 0){
              clearInterval(that.data.interval);
              wx.showToast({
                title: '活动已开始',
                icon: 'none',
                duration: 2000
              })
              var cur_time = parseInt(res.data.datas.info.end_time) - timestamp
              var time = util.GetRTime_t(cur_time);
              that.setData({
                cur_time: cur_time,
                if_end:2,  //1结束  2未结束
                if_add:1,  //1可以添加  2不可以添加
              })
              var interval = setInterval(function() {
                if(that.data.cur_time <= 0){
                  clearInterval(that.data.interval);
                  wx.showToast({
                    title: '活动已结束',
                    icon: 'none',
                    duration: 2000
                  })
                  that.setData({
                    if_end:1,  //1结束  2未结束
                    if_add:2,  //1可以添加  2不可以添加
                  })
                  return
                }
                var time = util.GetRTime_t(that.data.cur_time);
                that.setData({
                  time: time,
                  cur_time: that.data.cur_time - 1,
                  timeText:'距结束还剩',
                  if_end:2,  //1结束  2未结束
                  if_add:1,  //1可以添加  2不可以添加
                })
              }, 1000);
              that.data.interval = interval;
              return
            }else{
              var time = util.GetRTime_t(that.data.cur_time);
              that.setData({
                time: time,
                cur_time: that.data.cur_time - 1,
                timeText:'距开始还剩',
                if_end:2,  //1结束  2未结束
                if_add:2,  //1可以添加  2不可以添加
              })
            }
          }, 1000);
          that.data.interval = interval;
        }else{//已开始
          var cur_time = parseInt(res.data.datas.info.end_time) - timestamp
          var time = util.GetRTime_t(cur_time);
          that.setData({
            cur_time: cur_time,
            if_end:2,  //1结束  2未结束
            if_add:1,  //1可以添加  2不可以添加
          })
          var interval = setInterval(function() {
            if(that.data.cur_time <= 0){
              clearInterval(that.data.interval);
              wx.showToast({
                title: '活动已结束',
                icon: 'none',
                duration: 2000
              })
              that.setData({
                if_end:1,  //1结束  2未结束
                if_add:2,  //1可以添加  2不可以添加
              })
              return
            }
            var time = util.GetRTime_t(that.data.cur_time);
            that.setData({
              time: time,
              cur_time: that.data.cur_time - 1,
              timeText:'距结束还剩',
              if_end:2,  //1结束  2未结束
              if_add:1,  //1可以添加  2不可以添加
            })
          }, 1000);
          that.data.interval = interval;
        }
      }else{
        wx.switchTab({
          url: '../index/index'
        })
      }
    })
  },
  //查看更多
  dengduo(){
    this.setData({
      if_gd:true
    })
  },
  bindGetUserInfo: function(res) {
    var that = this;
    wx.showLoading({
      title: '正在授权中..'
    });
    var temp_goods = wx.getStorageSync('temp_goods');
    var temp = [];
    for (var l = 0; l < temp_goods.length; l++) {
      var goods_id = temp_goods[l].goods_id
      var goods_num = temp_goods[l].goods_num
      var lf = {
        goods_id,
        goods_num
      }
      temp.push(lf)
    }
    wx.login({
      success: function(login_res) {
        if (login_res.code) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称
          wx.getUserInfo({
            success: function(res) {
              that.download_user(res.userInfo.avatarUrl)
              wx.setStorageSync("user_img", res.userInfo.avatarUrl);
              wx.setStorageSync("nick_name", res.userInfo.nickName);
              request.postUrl("connect_weixin.login", {
                user_code: login_res.code,
                user_cookie: JSON.stringify(temp),
                tid: that.data.tid
              }, function(result) {
                if (!result.data.code) {
                  wx.showToast({
                    title: '登陆失败!'
                  });
                  return;
                }
                if (result.data.code != 200) {
                  wx.showToast({
                    title: result.data.datas.error
                  });
                  return;
                }
                wx.setStorageSync("open_id", result.data.datas.open_id);

                if (result.data.datas.user_token) {
                  wx.setStorageSync("user_token", result.data.datas.user_token);
                  wx.hideLoading();
                  wx.setStorageSync("temp_goods", "");
                  /*wx.switchTab({
                             url: '../me/me'
                         });*/
                  // that.showInfo();
                  return;
                }

                if (result.data.datas.union_id) {
                  wx.setStorageSync("union_id", result.data.datas.union_id);
                  wx.hideLoading();
                  wx.navigateTo({
                    url: '../bindTelephone/bindTelephone'
                  });
                  return;
                } else {
                  let dealer_id = ''
                  if (wx.getStorageSync('dealer_id')) {
                    dealer_id = wx.getStorageSync('dealer_id')
                  }
                  
                  request.postUrl("connect_weixin.weixin_iv_login", {
                    session_key: result.data.datas.session_key,
                    encrypted_data: res.encryptedData,
                    iv: res.iv,
                    open_id: result.data.datas.open_id,
                    user_cookie: JSON.stringify(temp),
                    tid: that.data.tid,
                    dealer_id: dealer_id,
                    callTel:1
                  }, function(res1) {
                    if (!res1.data.code) {
                      wx.showToast({
                        title: '登陆失败!!'
                      });
                      return;
                    }
                    if (res1.data.code != 200) {
                      wx.showToast({
                        title: res1.data.datas.error
                      });
                      return;
                    }

                    if (res1.data.datas.user_token) {
                      that.setData({
                        login_show: false
                      })
                      wx.showToast({
                        title: '授权成功',
                        icon: 'success',
                        duration: 2000
                      })
                      wx.setStorageSync("user_token", res1.data.datas.user_token);
                      wx.hideLoading();
                      wx.setStorageSync("temp_goods", "");
                      /*wx.switchTab({
                          url: '../me/me'
                      });*/
                      // that.showInfo();
                      return;
                    }
                    wx.setStorageSync("union_id", res1.data.datas.union_id);
                    wx.hideLoading();
                    wx.navigateTo({
                      url: '../bindTelephone/bindTelephone'
                    });
                    return;
                  })
                }
              })
            },
            fail: function(res) {
              wx.showToast({
                title: '授权失败',
                icon: "none",
              })
              wx.hideLoading();
            }
          })
        }
      }
    });
  },

  //下载图片到本地
  download(goods_img,erweima){
    var that = this;
    util.base64src(goods_img,'haibao', res => {
      console.log('海报下载成功---'+res) // 返回图片地址，直接赋值到image标签即可
      that.setData({
        goods_img: res
      })
    });

    util.base64src(erweima,'erweima', res => {
      console.log('二维码下载成功---'+res) // 返回图片地址，直接赋值到image标签即可
      that.setData({
        erweima: res
      })
    });
  },
  download_user(user_img){
    var that = this;
    wx.downloadFile({
      url: user_img,
      success: function (res) {
        that.setData({
          user_img: res.tempFilePath
        })
        console.log('用户头像下载成功--'+that.data.user_img)
      }
    })
  },


  //生成海报
  drawImg(){
    wx.showLoading({
      title: '加载中',
      duration:500
    })
    let that=this;
    let context = wx.createCanvasContext('share')  //这里的“share”是“canvas-id”

    var user_img = that.data.user_img  //用户头像
    var nick_name = wx.getStorageSync('nick_name')  //用户名称
    var time = that.data.info.tuan_info.add_time_text + ' 发布了一条团购信息' //时间
    var goods_img = that.data.goods_img//团购海报
    var goods_name = that.data.info.tuan_info.name//团购名称  44字两排
    var chr = goods_name.split("");//这个方法是将一个字符串分割成字符串数组
    var temp = "";
    var row = [];
    for (var a = 0; a < chr.length; a++) {
      if (context.measureText(temp).width < 225) {
        temp += chr[a];
      } else {
        a--; 
        row.push(temp);
        temp = "";
      }
    }
    row.push(temp); 
    if(row.length > 3){
      row[2] = row[2].substring(0,row[2].length-3) + '...';
      row = row.slice(0, 3);
    }
    console.log(row)
    for (var b = 0; b < row.length; b++) {
      context.fillText(row[b], 10, 30 + b * 30, 300);
    }
    var i = '￥'
    var goods_min_price = that.data.info.tuan_info.share_goods_price//商品价格
    var erweima = that.data.erweima//二维码
    var text = '长按扫码进入，立即参与团购'
    

    context.setFillStyle('#fff')    //这里是绘制白底，让图片有白色的背景
    context.fillRect(0, 0, 414, 736)

    context.drawImage(user_img, 20, 20, 50, 50) //绘制用户头像

    context.setFontSize(16) // 用户名称
    context.setFillStyle('#333') // 用户名称
    context.fillText(nick_name, 80,44) 

    context.setFontSize(12) // 时间
    context.setFillStyle('#666') // 时间
    context.fillText(time, 80,67) 

    context.drawImage(goods_img, 20, 80, 370, 430) //商品图片

    var H = 0
    var H2 = 0

    context.setFontSize(16) // 商品名称
    context.setFillStyle('#333') // 商品名称
    if(row.length == 1){
      context.fillText(row[0], 20,540) 
      H = 0
      H2 = 0
    }else if(row.length == 2){
      context.fillText(row[0], 20,540)
      context.fillText(row[1], 20,562) 
      H = 18
      H2 = 14
    }else if(row.length == 3){
      context.fillText(row[0], 20,540)
      context.fillText(row[1], 20,562) 
      context.fillText(row[2], 20,584) 
      H = 34
      H2 = 18
    }
    

    context.setFontSize(12) // ￥
    context.setFillStyle('#F64234') // ￥
    context.fillText(i, 20,580 + H) 

    context.setFontSize(18) // 商品价格
    context.setFillStyle('#F64234') // 商品价格
    context.fillText(goods_min_price, 35,580 + H) 

    context.drawImage(erweima, 20, 610 + H2, 80, 80) //二维码

    context.setFontSize(12) // 提示
    context.setFillStyle('#666') // 提示
    context.fillText(text, 120,650 + H2) 


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
          success:a=>{
            that.setData({
              shareImgPath:a.tempFilePath  //将绘制的图片地址保存在shareImgPath 中
            })  
            // console.log(that.data.shareImgPath)    
            // wx.previewImage({     //将图片预览出来
            //     urls: [that.data.shareImgPath]
            // })
            // wx.hideLoading()  //图片已经绘制出来，隐藏提示框
          },
          fail:e=>{console.log('失败')}
      })
    })                       
  },

  //点击保存图片
 save () {
  let that = this
  //若二维码未加载完毕，加个动画提高用户体验
  wx.showToast({
   icon: 'loading',
   title: '正在保存图片',
   duration: 1000
  })
  //判断用户是否授权"保存到相册"
  wx.getSetting({
   success (res) {
    //没有权限，发起授权
    if (!res.authSetting['scope.writePhotosAlbum']) {
     wx.authorize({
      scope: 'scope.writePhotosAlbum',
      success () {//用户允许授权，保存图片到相册
       that.savePhoto();
      },
      fail () {//用户点击拒绝授权，跳转到设置页，引导用户授权
       wx.openSetting({
        success () {
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
        if_haibao:false
      })
     wx.showToast({
      title: '保存成功',
      icon: "success",
      duration: 1000
     })
    }
   })
 },

 //我的订单
 goorder_she(){
   var that = this
   if(!wx.getStorageSync('user_token')){
    that.setData({
      login_show:true
    })
    return
   }
  wx.navigateTo({
    url: '../myOrder_s/myOrder_s?currentTab=' + 0
  })
 },
 //我参与的团购
 gohome(){
   var that = this
  if(!wx.getStorageSync('user_token')){
    that.setData({
      login_show:true
    })
    return
   }
  wx.navigateTo({
    url: '../my_shopping/my_shopping'
  })
 },







  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    // clearInterval(this.data.interval);
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
  onShareAppMessage: function (options) {
    return {
      title: this.data.info.tuan_title,
      imageUrl:this.data.goods_img,
      path: 'pages/community/community?scene=' + this.data.dinosaur_id
    }
  }
})