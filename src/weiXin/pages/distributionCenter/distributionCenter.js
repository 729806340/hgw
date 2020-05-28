var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    member_name: '',
    member_avatar: '',
    animationData: null,
    distributionStatus: false,
    classifies: null,
    classifyId: '',
    page: 1,
    is_bottom: false,
    commodities:null,
    is_pyramid: false,
    total_amount: 0,
    able_amount: 0,
    integer_bit: '0',
    decimal_place: '00'
  },
  
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      member_name: wx.getStorageSync("nick_name"),
      member_avatar: wx.getStorageSync("user_img"),
    })
   
  },

  getStoreData() {
    let that = this

    request.postUrl('pyramid_selling.my_shop_new', {}, function(res) {
      if (!res.data.code) {
        return;
      }
      if (res.data.code != 200) {
        wx.showToast({
          title: res.data.datas.error
        });

        setTimeout(function () {
          wx.navigateBack({})
        }, 1000);

        return;
      }

      let id = '';
      if (res.data.datas.goods_class_list[0]) {
        let classify = res.data.datas.goods_class_list[0];
        id = classify.gc_id;
      }

      let dummy = res.data.datas.invite_amount.split('.');
      let bit = '0';
      let decimal = '00';
      if (dummy.length > 1) {
        bit = dummy[0];
        decimal = dummy[1];
      }
      else if (dummy.length == 1) {
        bit = dummy[0];
      }

      that.setData({
        is_pyramid: res.data.datas.is_pyramid == '1',
        integer_bit: bit,
        decimal_place: decimal,
        able_amount: res.data.datas.invite_available_amount,
        total_amount: res.data.datas.invite_amount,
        classifies: res.data.datas.goods_class_list
      })

      if (id && !that.data.commodities) {
        that.setData({
          classifyId: id,
        })

        that.getCommodities(id);
      }
    })
  },

  getCommodities(e) {
    let that = this;

    request.postUrl('pyramid_selling.get_invite_goods_list', 
                  { gc_id: e, curpage: that.data.page }, 
                    function(res){
                      if (!res.data.code) {
                        return;
                      }
                      if (res.data.code != 200) {
                        wx.showToast({
                          title: res.data.datas.error
                        });

                        return;
                      }

                      let list = res.data.datas.invite_goods_list
                      if (that.data.page > 1) {
                        list = that.data.commodities.concat(res.data.datas.invite_goods_list);
                      }

                      that.setData({
                        commodities: list,
                        is_bottom: res.data.datas.is_pyramid == '1'
                      })
                    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      page: 1
    })
    
    this.getStoreData() 
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

  getMore: function () {
    if (this.data.is_bottom && this.data.commodities.length > 0) {
      this.setData({
        page: this.data.page + 1
      });

      this.getCommodities(this.data.classifyId);
    }
  },

  applyToDealer: function() {
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })

    this.animation = animation

    animation.translateY(-550).step()
    this.setData({
      animationData: animation.export(),
      distributionStatus: true,
    })

    setTimeout(function () {
      animation.translateY(0).step()
      this.setData({
        animationData: animation.export()
      })
    }.bind(this), 300)
  },
  
  hideModal: function () {
    var that = this;
    // 隐藏遮罩层
    var animation = wx.createAnimation({
      duration: 500,
      timingFunction: "linear",
      delay: 0
    })
    that.animation = animation
    animation.translateY(1000).step()
    that.setData({
      animationData: animation.export(),
    })
    setTimeout(function () {
      animation.translateY(0).step()
      that.setData({
        animationData: animation.export(),
        distributionStatus: false,
      })
    }.bind(that), 300)
  },

  formSubmit: function (e) {
    let value = e.detail.value.storeName;
    let length = value.length;

    if (length < 2) {
      wx.showToast({
        title: "店名至少两个字"
      })

      return;
    }

    if (length >= 30) {
      wx.showToast({
        title: "字数超出限制"
      })

      return;
    }
    
    this.submitForDealer(value)
  }, 

  submitForDealer(e) {
    let that = this

    request.postUrl('pyramid_selling.be_winner', 
                    { shop_name: e}, 
                    function(res) {
                      if (!res.data.code) {
                        return;
                      }
                      if (res.data.code != 200) {
                        wx.showToast({
                          title: res.data.datas.error
                        });
                        return;
                      }

                      wx.showToast({
                        title: '开通成功领取红包！'
                      });

                      that.setData({
                        distributionStatus: false,
                      })

                      that.getStoreData()
                    })
  },

  gotoOrder() {
    wx.navigateTo({
      url: '../distributionOrder/distributionOrder'
    });
  },

  gotoRecord() {
    wx.navigateTo({
      url: '../withdrawRecord/withdrawRecord'
    });
  },

  gotoWithdraw() {
    if (parseFloat(this.data.able_amount) <= 0.0) {
      wx.showToast({
        title: "没有佣金可提现"
      })

      return;
    }

    wx.navigateTo({
      url: '../withdraw/withdraw?total_amount=' + this.data.able_amount
    });
  },

  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id + '&dealer_id=' + item.invite_one,
    })
  },

  tabClick: function (e) {
    const id = e.currentTarget.dataset.id;
    const scrollLeftNumber = this.data.scrollLeftNumber;
    const x = e.currentTarget.offsetLeft;

    this.setData({
      page: 1,
      classifyId: id,
      scrollLeftNumber: x - 160,
    })

    this.getCommodities(id);
  },
  /**
   * 自定义分享
   */
  onShareAppMessage: function (res) {
    let index = res.target.dataset.index;
    let commodity = this.data.commodities[index];
    let pymramPath = '';
    if (commodity.invite_one) {
      pymramPath += '&dealer_id=' + commodity.invite_one;
    }

    return {
      title: '你的好朋友超值推荐' + commodity.goods_name,
      path: '/pages/goodsDetails/goodsDetails?goods_id=' + commodity.goods_id + pymramPath,
      imageUrl: commodity.goods_image
    }
  },
})