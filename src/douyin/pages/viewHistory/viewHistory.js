var request = require('../../utils/request.js');
Page({
  data: {
    selected: false,
    hasfinished: true, //管理、完成
    hasViewHistory: true,
    goodsList: [], //已售出收藏店铺列表
    selectAllStatus: false, // 全选状态，默认全选
    goods_id: '', //商品收藏
    goodsSelectedId: [], //选中删除商品的id
    JSONstoreSelectdId: '',
    browsetime: '',
    page_num: 10, //返回数据个数
    cur_page: 1, //设置加载页数，默认第1页
    xianshi:false
  },
  onLoad: function() {
    //
  },
  onShow: function() {
    var that = this;
    request.postUrl('member_goodsbrowse.browse_list', {
        cur_page: 1,
        page_num: that.data.page_num,
      },
      function(res) {
        if (res.data.code == 200) {
          // 初始化渲染页面
          console.log('23', res.data.datas.goodsbrowse_list)
          var len = res.data.datas.goodsbrowse_list.length;
          if (len) {
            that.setData({
              hasViewHistory: true,
              goodsList: res.data.datas.goodsbrowse_list,
              browsetime: res.data.datas.goodsbrowse_list
            })
          } else {
            that.setData({
              hasViewHistory: false
            })
          }
        }
      })
  },
  // 取消商品收藏：
  deleteStore: function(e) {
    var that = this;
    var goodsIndex = e.currentTarget.dataset.index;
    var goodsList = that.data.goodsList;
    let goodsSelectedId = parseInt(goodsList[goodsIndex].goods_id);
    request.postUrl('member_goodsbrowse.browse_clearall', {
      browse_id_list: JSON.stringify([goodsSelectedId])
    }, function(res) {
      if (res.data.code == 200) {
        wx.showToast({
          title: '删除成功',
          icon: 'none'
        })
      }
    })
    goodsList.splice(goodsIndex, 1);
    that.setData({
      goodsList: goodsList
    });
    if (!goodsList.length) {
      that.setData({
        hasViewHistory: false,
      })
    }
  },
  // 管理按钮
  Management: function() {
    var value = !this.data.hasfinished;
    this.setData({
      hasfinished: value
    })
  },
  // 当前商品选中事件
  selectList: function(e) {
    const index = e.currentTarget.dataset.index;
    let goodsList = this.data.goodsList;
    const selected = goodsList[index].selected;
    let goodsSelectedId = parseInt(goodsList[index].goods_id);
    goodsSelectedId = this.data.goodsSelectedId.push(goodsSelectedId)
    goodsList[index].selected = !selected;
    this.setData({
      selectAllStatus: false,
      goodsList: goodsList,
      JSONstoreSelectdId: JSON.stringify(this.data.goodsSelectedId)
    });
  },
  // 全选：
  selectAll(e) {
    var that = this;
    let selectAllStatus = that.data.selectAllStatus;
    selectAllStatus = !selectAllStatus;
    let goodsSelectedId = that.data.goodsSelectedId;
    let goodsList = that.data.goodsList;

    if (selectAllStatus == true) {
      for (let item of goodsList) {
        goodsSelectedId.push(parseInt(item.goods_id));
      }
    } else if (selectAllStatus == false) {
      goodsSelectedId.splice(0, goodsSelectedId.length)
    }

    for (let i = 0; i < goodsList.length; i++) {
      goodsList[i].selected = selectAllStatus
    }

    this.setData({
      selectAllStatus: selectAllStatus,
      goodsList: goodsList,
      JSONstoreSelectdId: JSON.stringify(this.data.goodsSelectedId)
    });
  },
  // 取消收藏
  cancelCollect: function(e) {
    var temp = [];
    var that = this;
    var goodsList = this.data.goodsList;
    request.postUrl('member_goodsbrowse.browse_clearall', {
      browse_id_list: this.data.JSONstoreSelectdId
    }, function(res) {
      if (res.data.code == 200) {
        console.log('11111111111111', res)
      }
    })
    for (var i = 0; i < goodsList.length; i++) {
      if (!goodsList[i].selected) {
        temp.push(goodsList[i]);
      }
    }
    if (!temp.length) {
      that.setData({
        hasViewHistory: false
      })
    }
    that.setData({
      goodsList: temp
    })
  },
  goGoodsDetails(e) {
    var item = e.currentTarget.dataset.item;
    wx.navigateTo({
      url: '../goodsDetails/goodsDetails?goods_id=' + item.goods_id,
    })

  },
  goShop: function (e) {
    var store_id = e.currentTarget.dataset.id;
    if (parseInt(store_id) <= 0) {
      return;
    }
    wx.navigateTo({
      url: '../shopDetails/shopDetails?store_id=' + store_id
    })
  },
  //上拉加载
  handlescrolltolower() {
    var that = this;
    request.postUrl('member_goodsbrowse.browse_list', {
      cur_page: that.data.cur_page + 1,
      page_num: that.data.page_num,
    }, function(res) {
      var temp_list = res.data.datas.goodsbrowse_list
      var l = Object.keys(temp_list).length;
      if (l > 0) {
        var goodsList = that.data.goodsList;
        goodsList = goodsList.concat(temp_list);
        that.setData({
          goodsList: goodsList,
          cur_page: that.data.cur_page + 1,
        })
        console.log(that.data.cur_page, that.data.goodsList);
      } else {
        wx.showToast({
          title: '无更多数据',
        })
      }
    })
  }

})