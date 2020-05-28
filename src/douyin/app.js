App({
    client_url: 'https://wxapi.hangomart.com/weiApi/', //线上请求地址
    // client_url: 'http://www.test.hangowa.com/weiApi/', //请求地址 
    api_key: 'c1dca569396ba260fe6a7d552b6b7d75', //api_key
    api_secret: 'testhgwapi', //api_secret
    app_id: 'tt8ea5f818118f5358',
    app_secret: 'd484be226d8002fd3dd83e638a0854020fe5376c',
    province_id: "",
    city_id: "",
    area_id: "",
    address_info:'',
    Atatus :0,//
    showModal : false,  //弹框内部显示
    router : '',
    class_id:0,
    onLaunch: function (options) {
         //var user_token = 'f312e7b3c246a667612d749d321d4d61';
         //wx.setStorageSync('user_token', user_token);
        var user_token = wx.getStorageSync('user_token');
        if (!user_token){
            this.showModal = true;
        }
    },
    
})
