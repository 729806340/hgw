// Component/pot_layer/pot_layer.js
var util = require('../../utils/util.js');
var request = require('../../utils/request.js');
Component({
    /**
     * 组件的属性列表
     */
    properties: {
        type: {
            type: Array,
            value: []
        },
        data:{
            type:Object,
            value:{}
        },
    },

    /**
     * 组件的初始数据
     */
    data: {
        showModal:true,
        display:false,
        color:'linear-gradient(-180deg, #FD564B 0%, #F72020 100%)'
    },
    /**
     * 组件的方法列表
     */
    //进入时动画
    attached(){
        var animation = wx.createAnimation({
            duration: 600,
            timingFunction: "linear",
            delay: 0
        })
        animation.translateY(-600).step()
        this.setData({
          animationData: animation.export(),
        })
        setTimeout(function () {
            animation.translateY(0).step()
            this.setData({
              display: true,
              animationData: animation.export()
            })
        }.bind(this), 500)
    },
    methods: {
        hideModal: function () {
            var that = this;
            // 隐藏遮罩层
            var animation = wx.createAnimation({
                duration: 500,
                timingFunction: "linear",
                delay: 0
            })
            that.animation = animation
            animation.translateY(700).step()
            that.setData({
                animationData: animation.export(),
            })
            setTimeout(function () {
                animation.translateY(0).step()
                that.setData({
                    animationData: animation.export(),
                    showModal: false,
                })
            }.bind(that), 300)
        },
        GoSome: function (e) {
          var that = this;
          var mytype = e.currentTarget.dataset.type;
          var data = e.currentTarget.dataset.data;

          if (mytype == "wei_url") {
            wx.navigateTo({
              url: data,
            })

            that.hideModal()
            return;
          }

          //跳分销中心
          if (mytype == "wei_pyramid") {
            if (wx.getStorageSync('user_token') == '') {
              wx.showToast({
                title: '请登录'
              })
              return
            }

            wx.navigateTo({
              url: '../distributionCenter/distributionCenter',
            })

            return;
          }

          if (mytype == "cate") {
            if (data == "") {
              app.class_id = 0;
              wx.switchTab({
                url: '../classify/classify'
              })
              return;
            } else {
              request.postUrl('goods_class.class_info', {
                cate_id: data
              }, function (res) {
                if (res.data.code != 200) {
                  wx.switchTab({
                    url: '../classify/classify'
                  })
                  return;
                }
                if (res.data.datas.parent_class == 1) {
                  app.class_id = data;
                  wx.switchTab({
                    url: '../classify/classify'
                  })
                } else if (res.data.datas.parent_class == 2) { //二级分类全部展示
                  wx.navigateTo({
                    url: '../classified/classified?gc_id_1=' + res.data.datas.gc_parent_id + '&gc_id_2=' + data,
                  })
                } else if (res.data.datas.parent_class == 3) { //三级分类展示全部 选中展示的三级分类商品
                  wx.navigateTo({
                    url: '../classified/classified?gc_id_3=' + data + '&gc_id_2=' + res.data.datas.gc_parent_id,
                  })
                }

              })
            }

            return;
          }

          if (data == "") {
              return;
          }
          //跳专题
          if (mytype == "special") {
            wx.navigateTo({
              url: '../special_index/special_index?special_id=' + data,
            })
            return;
          }
          //跳商品
          if (mytype == "goods") {
            wx.navigateTo({
              url: '../goodsDetails/goodsDetails?goods_id=' + data,
            })
            return;

          }
          //跳店铺
          if (mytype == "store") {
            wx.navigateTo({
              url: '../shopDetails/shopDetails?store_id=' + data
            })
            return;

          }
          //优惠券
          if (mytype == "voucher") {
            if (wx.getStorageSync('user_token') == '') {
              wx.switchTab({
                url: '../me/me',
              })
              return;
            }
            request.postUrl('member_voucher.voucher_freeex', {
              tid: data
            }, function (res) {
              if (res.data.code == 200) {
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
            return;
          }
          //红包
          if (mytype == "red_packet") {
            if (wx.getStorageSync('user_token') == '') {
              wx.switchTab({
                url: '../me/me',
              })
              return;
            }
            request.postUrl('member_redpacket.rpt_free', {
              tid: data
            }, function (res) {
              if (res.data.code == 200) {
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
            return;

          }
        },
      preventTouchMove: function () {

      },
    },
})