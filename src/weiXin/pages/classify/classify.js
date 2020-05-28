var request = require('../../utils/request.js');
const app = getApp()
Page({
  data: {
    hot_goods: [],
    scrollLeftNumber: 0,
    is_bottom: false,
    gc_id: 0,//选中的1级分类id
    parent_list:[], //一级分类
    all_child_list:[], //所有的二级分类
    child_list:[],//当前选中1级分类的二级分类,
    gclass_id : 0, //一级分类传过来的gc_id
    height1:0,
    height2:0,
    toView :'',
  },
  onShow: function (e){
      if (app.class_id) {
          var gclass_id = app.class_id;
      } else {
          var gclass_id = 0;
      }
      this.setData({
          gclass_id: gclass_id,
      })
    this.initRed()//气泡
    // if (Object.keys(this.data.parent_list).length <= 0) {
    //     this.getClassList();
    // }
    this.getClassList();
  },
  onLoad: function () {
    //this.getClassList();
  },
  //展示分类
  getClassList: function () {
    var that = this;
    request.postUrl('goods_class.get_class_list_new', {},function (res) {
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
          var parent_list = res.data.datas.parent_list;
          var child_list = res.data.datas.child_list;
          if (parent_list.length <= 0) {
            return;
          }
          if(that.data.gclass_id!=0){
            var gc_id = parent_list[0].gc_id;
            for (var i=0;i<parent_list.length;i++){
              if (parent_list[i].gc_id == that.data.gclass_id){
                gc_id = that.data.gclass_id;
              }
            }
            request.postUrl('goods_class.hot_sale', {
                cate_id: gc_id
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
                  that.setData({
                    hot_goods: res.data.datas.hot_goods
                  });
                }
              })
            // if (Object.keys(parent_list).indexOf(that.data.gclass_id)==-1){
            //   var gc_id = Object.keys(parent_list)[0];
            // }else{
            //   var gc_id = that.data.gclass_id;
            //   request.postUrl('goods_class.hot_sale', {
            //     cate_id: gc_id
            //   }, function (res) {
            //     if (!res.data.code) {
            //       return;
            //     }
            //     if (res.data.code != 200) {
            //       wx.showToast({
            //         title: res.data.datas.error
            //       });
            //       return;
            //     }
            //     if (res.data.code == 200) {
            //       that.setData({
            //         hot_goods: res.data.datas.hot_goods
            //       });
            //     }
            //   })
            // }
          }else{
            var gc_id = parent_list[0].gc_id;
            var hot_goods = res.data.datas.hot_goods;
            that.setData({
              hot_goods: hot_goods
            });
            if (hot_goods.length > 0) {
              that.setData({
                is_bottom: true
              })
            }
          }
          that.setData({
            parent_list: parent_list,
            all_child_list: child_list,
            child_list: child_list[gc_id],
            gc_id: gc_id,
            toView: 'n' + gc_id,
          });
        }
    })
  },

  // 点击大分类改变子分类
  tabClick: function (e) {
    var that = this;
    var gc_id = e.currentTarget.dataset.index;
    var child_list = that.data.all_child_list;
    var scrollLeftNumber = that.data.scrollLeftNumber;
    var x = e.currentTarget.offsetLeft;
    app.class_id = gc_id;
    that.setData({
      gc_id: gc_id,
      child_list: child_list[gc_id],
      scrollLeftNumber: x - 160,
      //toView : e.target.id,
    })
    request.postUrl('goods_class.hot_sale', {
        cate_id: gc_id
    },function (res) {
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
            that.setData({
                hot_goods: res.data.datas.hot_goods
            });
        }
    })

  },
  //分类详情
  switchTab: function(e) {
    var gc_id = e.currentTarget.dataset.id;
    wx.navigateTo({
        url: '../classified/classified?gc_id_2=' +gc_id
    });
  },
  goGoodsDetail: function (e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })
  },
  //更新购物车气泡数
  initRed: function () {
    var that = this;
    if (wx.getStorageSync('user_token') == '') {
      var temp_goods = wx.getStorageSync('temp_goods');
      var sum = 0;
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
      request.postUrl('cart.count', {}, function (res) {
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
})