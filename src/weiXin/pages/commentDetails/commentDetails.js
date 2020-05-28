var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    goods_id: 0,
    commentAllNum: 0,
    commentGoodNum: 0,
    commentNormalNum: 0,
    currentTab: 'all',
    commentBadNum: 0,
    allCommentList: [],
    allCurrentPage: 1,
    allHasMore: false,
    goodCommentList: [],
    goodCurrentPage: 1,
    goodHasMore: false,
    middleCommentList: [],
    middleCurrentPage: 1,
    middleHasMore: false,
    badCommentList: [],
    badCurrentPage: 1,
    badHasMore: false,
    hasAll: true,
    hasGood: false,
    hasNormal: false,
    hasBad: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var goods_id = options.goods_id;
    if (goods_id<=0) {
        wx.switchTab({
            url: '../index/index'
        });
    }
    that.setData({
        goods_id: goods_id
    });
    that.getCommentNum();
    that.getCommentList();
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
   * 获取评价 好评 中评 数量
   */
  getCommentNum: function () {
      var that = this;
      request.postUrl('goods.evaluate_decode', {
          goods_id: that.data.goods_id
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
          that.setData({
              commentAllNum: res.data.datas.all,
              commentGoodNum: res.data.datas.good,
              commentNormalNum: res.data.datas.normal,
              commentBadNum: res.data.datas.bad
          });

      })
  },

  /**
   * 获取评价列表
   */
  getCommentList: function () {
      var that = this;
      var change_type = that.data.currentTab;
      var curpage = 1;
      if (change_type == 1) {
          curpage = that.data.goodCurrentPage;
      } else if (change_type == 2) {
          curpage = that.data.middleCurrentPage;
      } else if (change_type == 3) {
          curpage = that.data.badCurrentPage;
      } else {
          curpage = that.data.allCurrentPage;
      }

      request.postUrl('goods.evaluate', {
          goods_id: that.data.goods_id,
          type: change_type,
          curpage: curpage
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
          var goods_eval_list = res.data.datas.goods_eval_list;
          var has_more = res.data.hasmore > 0 ? true : false;
          if (change_type == 1) {
              if (curpage === 1) {
                  that.setData({
                      goodCommentList: goods_eval_list,
                      goodHasMore: has_more
                  })
              } else {
                  that.setData({
                      goodCommentList: that.data.goodCommentList.concat(goods_eval_list),
                      goodHasMore: has_more
                  })
              }
          } else if (change_type == 2) {
              if (curpage === 1) {
                  that.setData({
                      middleCommentList: goods_eval_list,
                      middleHasMore: has_more
                  })
              } else {
                  that.setData({
                      middleCommentList: that.data.middleCommentList.concat(goods_eval_list),
                      middleHasMore: has_more
                  })
              }
          } else if (change_type == 3) {
              if (curpage === 1) {
                  that.setData({
                      badCommentList: goods_eval_list,
                      badHasMore: has_more
                  })
              } else {
                  that.setData({
                      badCommentList: that.data.badCommentList.concat(goods_eval_list),
                      badHasMore: has_more
                  })
              }
          } else {
              if (curpage === 1) {
                  that.setData({
                      allCommentList: goods_eval_list,
                      allHasMore: has_more
                  })
              } else {
                  that.setData({
                      allCommentList: that.data.allCommentList.concat(goods_eval_list),
                      allHasMore: has_more
                  })
              }
          }
      })
  },
  /**
   * 获取更多评价数据
   */
  getMore: function (option) {
    var that = this;
    var change_type = that.data.currentTab;
    if (change_type == 1) {
      if (that.data.goodHasMore) {
        that.setData({
            goodCurrentPage: that.data.goodCurrentPage + 1
        });
        that.getCommentList();
      }
    } else if (change_type == 2) {
        if (that.data.middleHasMore) {
            that.setData({
                middleCurrentPage: that.data.middleCurrentPage + 1
            });
            that.getCommentList();
        }
    } else if (change_type == 3) {
        if (that.data.badHasMore) {
            that.setData({
                badCurrentPage: that.data.badCurrentPage + 1
            });
            that.getCommentList();
        }
    } else {
        if (that.data.allHasMore) {
            that.setData({
                allCurrentPage: that.data.allCurrentPage + 1
            });
            that.getCommentList();
        }
    }
  },

  /**
   * 切换评论类型
  */
  tabClick: function (e) {
    var that = this;
    var change_type = e.currentTarget.dataset.index;
    that.setData({
        currentTab: change_type
    });
    if (change_type == 1) {
        that.setData({
            hasAll: false,
            hasGood: true,
            hasNormal: false,
            hasBad: false
        });
    } else if (change_type == 2) {
        that.setData({
            hasAll: false,
            hasGood: false,
            hasNormal: true,
            hasBad: false
        });
    } else if (change_type == 3) {
        that.setData({
            hasAll: false,
            hasGood: false,
            hasNormal: false,
            hasBad: true
        });
    } else {
        that.setData({
            hasAll: true,
            hasGood: false,
            hasNormal: false,
            hasBad: false
        });
    }
    that.getCommentList();
  },

  /**
   * 图片预览
  */
  previewImage: function(e) {
    var url = e.currentTarget.dataset.url;
    var item = e.currentTarget.dataset.item;
    var imgUrls = item.geval_image;
    wx.previewImage({
      current: url, // 当前显示图片的http链接
      urls: imgUrls // 需要预览的图片http链接列表
    })
  }
})