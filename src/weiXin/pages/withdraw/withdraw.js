var request = require('../../utils/request.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    total_amount: '',
    integer_bit: '',
    decimal_place: '',
    input_amount: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let amount = 0;
    let bit = '0';
    let decimal = '00';
    
    if (options.total_amount) {
      amount = options.total_amount;
      let dummy = amount.split('.');
      if (dummy.length > 1) {
        bit = dummy[0];
        decimal = dummy[1];
      }
      else if (dummy.length == 1) {
        bit = dummy[0];
      }
    }

    this.setData({
      total_amount: amount,
      integer_bit: bit,
      decimal_place: decimal
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  totalAmount() {
    this.setData({
      input_amount: this.data.total_amount
    })
  },

  formSubmit: function (e) {
    let value = e.detail.value.applyAmount;
    if (parseFloat(value) > parseFloat(this.data.total_amount)) {
      wx.showToast({
        title: '超出了可提现金额',
      })

      return
    }

    this.submitWithdraw(value)
  }, 

  submitWithdraw(e) {
    let that = this;

    request.postUrl('pyramid_selling.crash_out_apply', 
                    { apply_money: e }, 
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
                        title: '提交申请成功'
                      })

                      setTimeout(function () {
                        wx.navigateBack()
                      }, 500)
                      
                    })
  }
})