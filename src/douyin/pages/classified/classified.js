var request = require('../../utils/request.js');
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    gc_id_2: 0,
    gc_id_3: 0,
    gc_id_1: 0,
    gc_child_list: [],
    goods_list: [],
    is_bottom: false,
    cur_page: 1
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (option) {
      if (!option) {
          wx.switchTab({
              url: '../me/me'
          });
          return;
      }
      if (option.gc_id_3){
          this.setData({
              gc_id_2: option.gc_id_2,
              gc_id_3: option.gc_id_3
          })
      } else {
          if (option.gc_id_1){
              app.class_id = option.gc_id_1;
              this.setData({
                  gc_id_1: option.gc_id_1,
                  gc_id_2: option.gc_id_2,
              })
          }else{
              this.setData({
                  gc_id_2: option.gc_id_2,
              })
          }
      }  
      console.log(this.data.gc_id_2)
      this.getChildList();
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
      
  },

  getChildList: function () {
    var that = this;
    if(that.data.gc_id_1){
        var gc_id_2 = that.data.gc_id_1;
    }else{
        var gc_id_2 = that.data.gc_id_2;
    }
    request.postUrl('goods_class.get_child_list', {
      gc_id: gc_id_2
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
            if (that.data.gc_id_3) { //有三级分类
                if (Object.keys(res.data.datas.child_list).indexOf(that.data.gc_id_3) == -1) {//没有找到gc_id_2数据
                    var gc_id = 0; //展示二级全部分类
                } else {
                    var gc_id = that.data.gc_id_3;
                }
            } else {
                var gc_id = 0;
            }
            that.setData({
                gc_child_list: res.data.datas.child_list,
                gc_id_3: gc_id,
                toView: 'n' + gc_id,
            })
            that.getGoodsList();
        }
    });
  },
  getGoodsList: function () {
    var that = this;
     var gc_id = 0;
     if (that.data.gc_id_3 > 0) {
         gc_id = that.data.gc_id_3;
     } else {
         if (that.data.gc_id_1){
            gc_id = that.data.gc_id_1;
         }else{
            gc_id = that.data.gc_id_2;
         } 
     }
      console.log(gc_id)
    request.postUrl('goods.list', {
        gc_id: gc_id,curpage: that.data.cur_page
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
            if (that.data.cur_page === 1) {
                that.setData({
                    goods_list: res.data.datas.goods_list,
                    is_bottom: res.data.hasmore > 0 ? false : true
                })
            } else {
                that.setData({
                    goods_list: that.data.goods_list.concat(res.data.datas.goods_list),
                    is_bottom: res.data.hasmore > 0 ? false : true
                })
            }
        }
    });
  },

    refresh: function () {
        this.setData({
            cur_page: 1
        });
        this.getGoodsList();
    },
    getMore: function () {
        var that = this;
        if (!that.data.is_bottom) {
            that.setData({
                cur_page: that.data.cur_page + 1
            });
            that.getGoodsList();
        }
    },

  tabClick: function (e) {
    var gc_id_3 = e.currentTarget.dataset.index;
     this.setData({
       gc_id_3: gc_id_3,
       cur_page: 1,
       toView: e.target.id,
     })
     this.getGoodsList();
  },

  tabClickAll: function () {
    this.setData({
        gc_id_3: 0,
        cur_page: 1
    })
    this.getGoodsList();
  }
})