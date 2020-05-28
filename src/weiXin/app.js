App({
    // client_url: 'https://wxapi.hangomart.com/weiApi/', //线上请求地址
    client_url: 'http://www.test.hangowa.com/weiApi/', //请求地址
    api_key: 'c1dca569396ba260fe6a7d552b6b7d75', //api_key
    api_secret: 'testhgwapi', //api_secret
    app_id: 'wx66d8e5f039ce2822',
    map_key: "cd7401879acdc7b16d187e8545ba2827", //高德地图key
    map_web_key: "d12d9a0bfd46d5df9dba2a7958b41515", //Web高德地图key
    s_map: "", //选中的map
    s_area: "", //选择的区域信息 
    province_id: "",
    city_id: "",
    area_id: "",
    address_info:'',
    Atatus :0,//
    showModal : false,  //弹框内部显示
    router : '',
    class_id:0,
    gc_id:'',
    user_city:'',
    onLaunch: function (options) {
         //var user_token = 'f312e7b3c246a667612d749d321d4d61';
         //wx.setStorageSync('user_token', user_token);
        //  wx.setStorageSync('tuanzhang_id', '21');
        var user_token = wx.getStorageSync('user_token');
        if (!user_token){
            this.showModal = true;
        }

        wx.getSetting({
            success (res){
              if (res.authSetting['scope.userInfo']) {
                // 已经授权，可以直接调用 getUserInfo 获取头像昵称
                wx.getUserInfo({
                  success: function(res) {
                    console.log(res.userInfo)
                    wx.setStorageSync("user_img", res.userInfo.avatarUrl);
                    wx.setStorageSync("nick_name", res.userInfo.nickName);
                  }
                })
              }
            }
          })
    },
    
})
