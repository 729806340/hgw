
var request = require('../../utils/request.js');
var util = require('../../utils/util.js');

Page({
  data: {
    if_haibao: false,
    rpx:'',
    dataList:[],
    goods_list:[],
    tuanzhang_info:[],
    share_wei_qr:'',
    shareImgPath: '',
    tempFilePath:'',
    // 默认虚拟数据
    goods_price:'',
    goods_marketprice:'',
    goods_name: '',
    context: {
      //需要https图片路径,下载到本地然后去绘制
      cardbg: "http://www.hangowa.com/data/upload/mobile/special/s0/s0_06435423757097243.png",
      // 二维码
      codeImg: "",
    }
  },

  onLoad:function(){
    var that = this;
    that.setData({
      if_haibao:true
    })
    // that.getIndex()
  },
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
  },
  //获取数据
  getIndex() {
    var that = this
    request.postUrl("shequ_tuan_tool.poster", {}, function (res) {
      console.log(res)
      if (res.data.code == '200') {
        that.setData({
          dataList: res.data.datas,
          goods_list: res.data.datas.goods_list,
          // share_wei_qr: res.data.datas.share_wei_qr,
          codeImg: res.data.datas.share_wei_qr,
          goods_image: res.data.datas.goods_list.goods_image
        })
        that.getQrCode('http://www.hangowa.com/data/upload/mobile/special/s0/s0_06435423757097243.png', res.data.datas.share_wei_qr,
          res.data.datas.goods_list[0].goods_image,
          res.data.datas.goods_list[1].goods_image,
          res.data.datas.goods_list[2].goods_image,
          res.data.datas.goods_list[3].goods_image,
          res.data.datas.goods_list[4].goods_image,
          res.data.datas.goods_list[5].goods_image, )   
      }
    })
  },

  /**
   * 
     * 下载二维码图片
     */
  getQrCode: function (cardbg, codeImg, goods_img0, goods_img1, goods_img2, goods_img3,goods_img4,goods_img5) {
    console.log('-------------------------')
    var that = this;
    wx.downloadFile({
      url: cardbg,
      success: function (res) {
        console.log('cardbg下载完成')
        that.setData({
          cardbg: res.tempFilePath
        })
        wx.downloadFile({
          url: codeImg,
          success: function (res) {
            console.log('codeImg下载完成')
            that.setData({
              codeImg: res.tempFilePath
            })
            wx.downloadFile({
              url: goods_img0,
              success: function (res) {
                console.log('goods_img0下载完成')
                that.setData({
                  goods_img0: res.tempFilePath
                })
                wx.downloadFile({
                  url: goods_img1,
                  success: function (res) {
                    console.log('goods_img1下载完成')
                    that.setData({
                      goods_img1: res.tempFilePath
                    })
                    wx.downloadFile({
                      url: goods_img2,
                      success: function (res) {
                        console.log('goods_img2下载完成')
                        that.setData({
                          goods_img2: res.tempFilePath
                        })
                        wx.downloadFile({
                          url: goods_img3,
                          success: function (res) {
                            console.log('goods_img3下载完成')
                            that.setData({
                              goods_img3: res.tempFilePath
                            })
                            wx.downloadFile({
                              url: goods_img4,
                              success: function (res) {
                                console.log('goods_img4下载完成')
                                that.setData({
                                  goods_img4: res.tempFilePath
                                })
                                wx.downloadFile({
                                  url: goods_img5,
                                  success: function (res) {
                                    console.log('goods_img5下载完成')
                                    that.setData({
                                      goods_img5: res.tempFilePath
                                    })
                                    that.getCanvas();
                                  }
                                })
                              }
                            })
                          }
                        })
                      }
                    })
                  }
                })
              }
            })
          }
        })
      }
    })
  },

  /**
   * 开始用canvas绘制分享海报
   * @param cardbg 下载的海报背景图路径
   * @param codeImg   下载的二维码图片路径
   */
  getCanvas: function () {
    wx.showLoading({
      title: '正在加载中...',
      mask: true,
    })
    this.setData({
      if_haibao:true
    })
    var that = this;
    var context = that.data.context; //需要绘制的数据集合
    const ctx = wx.createCanvasContext('myCanvas'); //创建画布
    var box_img = that.data.context.cardbg  //盒子背景res.data.datas.goods_list[2].goods_image
    var goods_img0 = that.data.goods_img0  //商品图片
    var goods_img1 = that.data.goods_img1 
    var goods_img2 = that.data.goods_img2 
    var goods_img3 = that.data.goods_img3 
    var goods_img4 = that.data.goods_img4
    var goods_img5 = that.data.goods_img5 
    var erweima = that.data.context.codeImg  //二维码图片
    var address = that.data.dataList.tuanzhang_info.address;//这是要绘制的文本
    console.log(address)
    var chr = address.split("");//这个方法是将一个字符串分割成字符串数组
    var temp = "";
    var row = [];
    for (var a = 0; a < chr.length; a++) {
      if (ctx.measureText(temp).width < 140) {
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
    for (var b = 0; b < row.length; b++) {
      ctx.fillText(row[b], 10, 30 + b * 30, 300);
    }

    var colonel = that.data.dataList.tuanzhang_info.tuanzhang_name;//这是要绘制的-------团长
    var chr_c = colonel.split("");//这个方法是将一个字符串分割成字符串数组
    var temp_c = "";
    var row_c = [];
    // //这是要绘制的团长
    for (var a = 0; a < chr_c.length; a++) {
      if (ctx.measureText(temp_c).width < 70) {
        temp_c += chr_c[a];
      } else {
        a--;
        row_c.push(temp_c);
        temp_c = "";
      }
    }
    row_c.push(temp_c);
    if (row_c.length > 1) {
      row_c[0] = row_c[0].substring(0, row_c[0].length - 1) + '...';
      row_c = row_c.slice(0, 1);
    }
    for (var r = 0; r < row_c.length; r++) {
      ctx.fillText(row_c[r], 10, 30 + r * 30, 300);
    }
/////////////////////////////////////////////////////////////////////////////////////////
//--------------左一--将一个字符串分割成字符串数组------
    var namel_1 = that.data.goods_list[0].goods_name;//这是要绘制的-------左一
    var chrl_1 = namel_1.split("");//这个方法是将一个字符串分割成字符串数组
    var templ_1 = "";
    var rowl_1 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrl_1.length; a++) {
      if (ctx.measureText(templ_1).width < 70) {
        templ_1 += chrl_1[a];
      } else {
        a--;
        rowl_1.push(templ_1);
        templ_1 = "";
      }
    }
    rowl_1.push(templ_1);
    if (rowl_1.length > 1) {
      rowl_1[0] = rowl_1[0].substring(0, rowl_1[0].length - 1) + '...';
      rowl_1 = rowl_1.slice(0, 1);
    }
    for (var r = 0; r < rowl_1.length; r++) {
      ctx.fillText(rowl_1[r], 10, 30 + r * 30, 300);
    }
//--------------左二--将一个字符串分割成字符串数组------
    var namel_2 = that.data.goods_list[2].goods_name;//这是要绘制的-------左二
    var chrl_2 = namel_2.split("");//这个方法是将一个字符串分割成字符串数组
    var templ_2 = "";
    var rowl_2 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrl_2.length; a++) {
      if (ctx.measureText(templ_2).width < 70) {
        templ_2 += chrl_2[a];
      } else {
        a--;
        rowl_2.push(templ_2);
        templ_2 = "";
      }
    }
    rowl_2.push(templ_2);
    if (rowl_2.length > 1) {
      rowl_2[0] = rowl_2[0].substring(0, rowl_2[0].length - 1) + '...';
      rowl_2 = rowl_2.slice(0, 1);
    }
    for (var r = 0; r < rowl_2.length; r++) {
      ctx.fillText(rowl_2[r], 10, 30 + r * 30, 300);
    }

    var namel_3 = that.data.goods_list[4].goods_name;//这是要绘制的-------左三
    var chrl_3 = namel_3.split("");//这个方法是将一个字符串分割成字符串数组
    var templ_3 = "";
    var rowl_3 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrl_3.length; a++) {
      if (ctx.measureText(templ_3).width < 70) {
        templ_3 += chrl_3[a];
      } else {
        a--;
        rowl_3.push(templ_3);
        templ_3 = "";
      }
    }
    rowl_3.push(templ_3);
    if (rowl_3.length > 1) {
      rowl_3[0] = rowl_3[0].substring(0, rowl_3[0].length - 1) + '...';
      rowl_3 = rowl_3.slice(0, 1);
    }
    for (var r = 0; r < rowl_3.length; r++) {
      ctx.fillText(rowl_3[r], 10, 30 + r * 30, 300);
    }



    var nameR_1 = that.data.goods_list[1].goods_name;//这是要绘制的-------右一
    var chrr_1 = nameR_1.split("");//这个方法是将一个字符串分割成字符串数组
    var tempr_1 = "";
    var rowr_1 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrr_1.length; a++) {
      if (ctx.measureText(tempr_1).width < 70) {
        tempr_1 += chrr_1[a];
      } else {
        a--;
        rowr_1.push(tempr_1);
        tempr_1 = "";
      }
    }
    rowr_1.push(tempr_1);
    if (rowr_1.length > 1) {
      rowr_1[0] = rowr_1[0].substring(0, rowr_1[0].length - 1) + '...';
      rowr_1 = rowr_1.slice(0, 1);
    }
    for (var r = 0; r < rowr_1.length; r++) {
      ctx.fillText(rowr_1[r], 10, 30 + r * 30, 300);
    }

    var namer_2 = that.data.goods_list[3].goods_name;//这是要绘制的-------左二
    var chrr_2 = namer_2.split("");//这个方法是将一个字符串分割成字符串数组
    var tempr_2 = "";
    var rowr_2 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrr_2.length; a++) {
      if (ctx.measureText(tempr_2).width < 70) {
        tempr_2 += chrr_2[a];
      } else {
        a--;
        rowr_2.push(tempr_2);
        tempr_2 = "";
      }
    }
    rowr_2.push(tempr_2);
    if (rowr_2.length > 1) {
      rowr_2[0] = rowr_2[0].substring(0, rowr_2[0].length - 1) + '...';
      rowr_2 = rowr_2.slice(0, 1);
    }
    for (var r = 0; r < rowr_2.length; r++) {
      ctx.fillText(rowr_2[r], 10, 30 + r * 30, 300);
    }

    var namer_3 = that.data.goods_list[5].goods_name;//这是要绘制的-------左三
    var chrr_3 = namer_3.split("");//这个方法是将一个字符串分割成字符串数组
    var tempr_3 = "";
    var rowr_3 = [];
    // //这是要绘制的鲍师傅肉松小贝
    for (var a = 0; a < chrr_3.length; a++) {
      if (ctx.measureText(tempr_3).width < 70) {
        tempr_3 += chrr_3[a];
      } else {
        a--;
        rowr_3.push(tempr_3);
        tempr_3 = "";
      }
    }
    rowr_3.push(tempr_3);
    if (rowr_3.length > 1) {
      rowr_3[0] = rowr_3[0].substring(0, rowr_3[0].length - 1) + '...';
      rowr_3 = rowr_3.slice(0, 1);
    }
    for (var r = 0; r < rowr_3.length; r++) {
      ctx.fillText(rowr_3[r], 10, 30 + r * 30, 300);
    }

    


///////////////////////////////////////////////////////////////////////////////////////
    var goods_name =  that.data.goods_name //商品名字

    var width = "";
    wx.createSelectorQuery().select('#canvas-container').boundingClientRect(function (rect) {
      var height = rect.height;
      var right = rect.right;
      width = rect.width * 0.8;
      var left = rect.left + 5;
      ctx.setFillStyle('#fff');
      ctx.fillRect(0, 0, rect.width, height);
      // 这里处理自适应
      let rpx = 1;
      wx.getSystemInfo({
        success(res) {
          rpx = res.windowWidth / 375;
          that.setData({
            rpx: res.windowWidth / 375
          })
          console.log(rpx,'rpx')
        },
      })

      //背景图
        ctx.drawImage(that.data.cardbg, 0 * rpx, 0 * rpx, 300 * rpx, 490 * rpx);
      //  绘制二维码
      ctx.drawImage(that.data.codeImg, 195 * rpx, 350 * rpx, 78 * rpx, 78 * rpx)
      // 左一排
      ctx.drawImage(goods_img0, 25 * rpx, 85 * rpx, 51 * rpx, 51 * rpx)
      ctx.drawImage(goods_img2, 25 * rpx, 175 * rpx, 51 * rpx, 51 * rpx)
      ctx.drawImage(goods_img4, 25 * rpx, 265 * rpx, 51 * rpx, 51 * rpx)
      // 右边一排
      ctx.drawImage(goods_img1, 155 * rpx, 85 * rpx, 51 * rpx, 51 * rpx)
      ctx.drawImage(goods_img3, 155 * rpx, 175 * rpx, 51 * rpx, 51 * rpx)
      ctx.drawImage(goods_img5, 155 * rpx, 265 * rpx, 51 * rpx, 51 * rpx)

      

      ctx.setFontSize(11 * rpx) // 地址
      ctx.setFillStyle('#333') // 地址
      if (row.length == 1) {
        ctx.fillText(row[0], 25 * rpx, 395 * rpx)
      } else if (row.length == 2) {
        ctx.fillText(row[0], 25 * rpx, 395 * rpx)
        ctx.fillText(row[1], 25 * rpx, 410 * rpx)
      } else if (row.length == 3) {
        ctx.fillText(row[0], 25 * rpx, 395 * rpx)
        ctx.fillText(row[1], 25 * rpx, 410 * rpx)
        ctx.fillText(row[2], 25 * rpx, 425 * rpx)
      }
      // 团长
      if (row_c.length == 1) {
        ctx.fillText(row_c[0], 70 * rpx, 370 * rpx)
      } 
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#333');
      // 左一
      if (rowl_1.length == 1) {
        ctx.fillText(rowl_1[0], 81 * rpx, 100 * rpx)
      }
      // 左二
      if (rowl_2.length == 1) {
        ctx.fillText(rowl_2[0], 81 * rpx, 190 * rpx)
      }
      // 左三
      if (rowl_3.length == 1) {
        ctx.fillText(rowl_3[0], 81 * rpx, 280 * rpx)
      } 
      // 右一
      if (rowr_1.length == 1) {
        ctx.fillText(rowr_1[0], 211 * rpx, 100 * rpx)
      }
      // 右二
      if (rowr_2.length == 1) {
        ctx.fillText(rowr_2[0], 211 * rpx, 190 * rpx)
      }
      // 右三
      if (rowr_3.length == 1) {
        ctx.fillText(rowr_3[0], 211 * rpx, 280 * rpx)
      } 

      var i = '￥'
      var goods_price = that.data.goods_price //商品价格
      var goods_marketprice = '￥' + that.data.goods_marketprice //商品 原 价格

/////左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左//////
      
      // 左一
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A');
      ctx.fillText(i, 80 * rpx, 115 * rpx); //￥
      
      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[0].goods_price, 90 * rpx, 115 * rpx)

      var pri_W = ctx.measureText(that.data.goods_list[0].goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格that.data.goods_list[0].goods_marketprice
      ctx.fillText('￥'+that.data.goods_list[0].goods_marketprice, 80 * rpx, 130 * rpx) 

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[0].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(82  * rpx, 125 * rpx, m_pri_W, 1 * rpx)


      // 左二
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A');
      ctx.fillText(i, 80 * rpx, 205 * rpx); //￥

      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[3].goods_price, 90 * rpx, 205 * rpx)

      var pri_W = ctx.measureText(that.data.goods_list[3].goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格
      ctx.fillText('￥' + that.data.goods_list[3].goods_marketprice, 80 * rpx, 220 * rpx)

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[3].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(82 * rpx, 215 * rpx, m_pri_W , 1 * rpx)
      // 左三
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A');
      ctx.fillText(i, 80 * rpx, 295 * rpx); //￥

      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[3].goods_price, 90 * rpx, 295 * rpx)

      var pri_W = ctx.measureText(that.data.goods_list[3].goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格
      ctx.fillText('￥' + that.data.goods_list[3].goods_marketprice, 80 * rpx, 310 * rpx)

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[3].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(81 * rpx, 305 * rpx, m_pri_W , 1 * rpx)
/////左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左左//////


//右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右
      // 右一
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A');
      ctx.fillText(i, 210 * rpx, 115 * rpx); //￥

      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[1].goods_price, 220 * rpx, 115 * rpx)

      var pri_W = ctx.measureText(that.data.goods_list[1].goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格
      ctx.fillText('￥' + that.data.goods_list[1].goods_marketprice, 210 * rpx, 130 * rpx)

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[1].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(211 * rpx, 125 * rpx, m_pri_W, 1 * rpx)

      // 右二
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A');
      ctx.fillText(i, 210 * rpx, 205 * rpx); //￥

      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[3].goods_price, 220 * rpx, 205 * rpx)

      var pri_W = ctx.measureText(that.data.goods_list[3].goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格
      ctx.fillText('￥' + that.data.goods_list[3].goods_marketprice, 210 * rpx, 220 * rpx)

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[3].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(211 * rpx, 215 * rpx, m_pri_W, 1 * rpx)

      // 右三
      ctx.setFontSize(10 * rpx);
      ctx.setFillStyle('#FF523A'); 
      ctx.fillText(i, 210 * rpx, 295 * rpx); //￥

      ctx.setFontSize(10 * rpx) // 商品价格
      ctx.setFillStyle('#FF523A') // 商品价格
      ctx.fillText(that.data.goods_list[5].goods_price, 220 * rpx, 295 * rpx)

      var pri_W = ctx.measureText(goods_price).width
      ctx.setFontSize(10 * rpx) // 商品 原 价格
      ctx.setFillStyle('#999') // 商品 原 价格
      ctx.fillText('￥' + that.data.goods_list[5].goods_marketprice, 210 * rpx, 310 * rpx)

      var m_pri_W = ctx.measureText('￥' + that.data.goods_list[5].goods_marketprice).width
      ctx.setFillStyle('#999')    //原价横线
      ctx.fillRect(211 * rpx, 305 * rpx, m_pri_W, 1 * rpx)

//右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右右
      ctx.setFillStyle('#E8E8E8')    //底部横线
      ctx.fillRect(15 * rpx, 340 * rpx, 270 * rpx, 1 * rpx)
      // 团长
      ctx.setFontSize(11 * rpx);
      ctx.setFillStyle('#333');
      ctx.setTextAlign('left');
      ctx.fillText("团长 ：", 25 * rpx, 370 * rpx); //姓名

      //把画板内容绘制成图片，并回调画板图片路径
    // ctx.draw(false, function () {
    //   wx.canvasToTempFilePath({
    //     x: 0,
    //     y: 0,
    //     width: 414 ,
    //     height: 736,
    //     destWidth: 1242,
    //     destHeight: 2208 ,
    //     canvasId: 'myCanvas',
    //     fileType: 'jpg', //图片的质量，目前仅对 jpg 有效。取值范围为 (0, 1]，不在范围内时当作 1.0 处理。
    //     quality: 1,
    //     success: a => {
    //       that.setData({
    //         tempFilePath: a.tempFilePath  //将绘制的图片地址保存在shareImgPath 中
    //       })
    //       console.log(that.data.shareImgPath)    
    //       // wx.previewImage({     //将图片预览出来
    //       //     urls: [that.data.shareImgPath]
    //       // })
    //       // wx.hideLoading()  //图片已经绘制出来，隐藏提示框
    //     },
    //     fail: e => { console.log('失败') }
    //   })
    // })

    }).exec()

    setTimeout(function () {
      ctx.draw();
      wx.hideLoading();
    }, 1000)


  },


  //点击保存到相册
  saveShareImg: function () {
    var that = this;
    wx.showLoading({
      title: '正在保存',
      mask: true,
    })
    setTimeout(function () {
      wx.canvasToTempFilePath({
        canvasId: 'myCanvas',
        success: function (res) {
          console.info("res", res);
          wx.hideLoading();
          var tempFilePath = res.tempFilePath;
         
          console.info("tempFilePath", tempFilePath);
          wx.saveImageToPhotosAlbum({
            filePath: res.tempFilePath,
            success(res) {
              that.setData({
                shareImgPath: res.tempFilePath  //将绘制的图片地址保存在shareImgPath 中
              })
              console.log(that.data.shareImgPath,'that.data.shareImgPath')
              console.info(res);
              wx.showModal({
                content: '图片已保存到相册，赶紧晒一下吧~',
                showCancel: false,
                confirmText: '好的',
                confirmColor: '#333',
                success: function (res) {
                  if (res.confirm) { }
                },
                fail: function (res) { }
              })
            },
            fail: function (res) {
              console.log(res)
              if (res.errMsg === "saveImageToPhotosAlbum:fail:auth denied") {
                console.log("打开设置窗口");
                wx.openSetting({
                  success(settingdata) {
                    console.log(settingdata)
                    if (settingdata.authSetting["scope.writePhotosAlbum"]) {
                      console.log("获取权限成功，再次点击图片保存到相册")
                    } else {
                      console.log("获取权限失败")
                    }
                  }
                })
              }
            }
          })
        }
      });
    }, 1000);
  },



})
