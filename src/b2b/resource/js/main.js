/**
 * Created by Shen.L on 2016/8/22.
 */

var Hangowa = function () {
    var hango = {};
    var initial = function () {
    };
    var initialUser = function () {
        // 初始化用户信息
        getUserInfo(function () {
            var bar = $('#bar-user-info');
            var user = hango.userInfo;
            console.log(user.member_truename?user.member_truename:user.member_name);
            if(user.member_avatar) bar.find('.avatar img').attr('src',user.member_avatar);
            $('#bar-user-name').text(user.member_truename?user.member_truename:user.member_name);
            $('#bar-user-level').text(user.grade.level_name);
            $('#bar-user-exp').text(user.member_exppoints);
        });
        hango.username = jQuery.cookie('hango_member_name');
        hango.userId = jQuery.cookie('hango_member_id');
        if(hango.userId&&hango.userId>0) {
            $('.when-user').show();
            $('.when-guest').hide();
            $('#user-login').hide();
            $('#purchase-btn2').hide();
            $('#bar-user-login').hide();
            $('#top-username').text(hango.username);
            $('#user-info').show();
            $('#purchase-btn1').show();
            $('#bar-user-info').show();
        }else {
            $('.when-user').hide();
            $('.when-guest').show();
            $('#user-info').hide();
            $('#purchase-btn1').hide();
            $('#purchase-btn2').show();
            $('#bar-user-info').hide();
            $('#user-login').show();
            $('#bar-user-login').show();
        }
    };

    var isLogged = function () {
        hango.userId = jQuery.cookie('hango_member_id');
        return (hango.userId&&hango.userId>0);
    };
    
    var getUserInfo = function (fn) {
        var url = '/index.php?act=ajax&op=user_info';
        $.get(url,function (res) {
            if(res.error==0){
                hango.userInfo = res.data;
                if(fn&&typeof fn == 'function') fn();
            }
        },'json');
    };

    var barUserInfo = function () {
        var bar = $('#bar-user-info');
        var isShown = bar.data('show');
        var userInfo = bar.find('.user-info');
        if(isShown) {
            userInfo.hide();
            bar.data('show',false);
        } else {
            userInfo.show();
            bar.data('show',true);
        }
    };
    
    var updateCartCount = function () {
        var cart_goods_num = $.cookie('hango_cart_goods_num')||0;
        $('#rtoobar_cart_count').html(cart_goods_num).show();
        $('#top-cart-count').text(cart_goods_num).show();
        return;
        var url = '/index.php?act=cart&op=ajax_load';
        $.get(url,function (res) {
            if(res.cart_goods_num&&res.cart_goods_num>0){
                $('#rtoobar_cart_count').html(res.cart_goods_num).show();
                $('#top-cart-count').text(res.cart_goods_num).show();
            }
        },'json');
    };

    var loadGoodsInfo = function (id,fn) {
        var url = '/index.php?act=ajax&op=goods_info&id='+id;
        $.get(url,function (res) {
            if(res.error==0){
                var goods = hango.goodsInfo = res.data;
                /**
                 * TODO 处理商品价格信息
                 * 商品正常价格
                 * 商品促销价格
                 * 预定商品价格
                 * 预售商品价格
                 * 显示隐藏购物车
                 */
                $('[model]').each(function (index,item) {
                    var $this = $(this);
                    var  value = eval('(typeof '+$this.attr('model')+'!= "undefined")&&'+$this.attr('model'))||goods[$this.attr('model')]||'';
                    $this.html(value);
                });
                $('[ctrl]').each(function (index,item) {
                    var $this = $(this);
                    if(!eval($this.attr('ctrl'))) $this.remove();
                });
                goods.remain>0&&bookRemainTick(goods.remain);
                $('#goods-loading').remove();
                $('#goods-summary').show();
                if(fn&&typeof fn == 'function') fn();
                //console.log(goods);
            }
        },'json');
    };
    
    var bookRemainTick = function (remain) {
        var goods={};
        goods.day = Math.floor(remain/86400);
        var r = remain%86400;
        goods.hour = Math.floor(r/3600);
        r %= 3600;
        goods.minute = Math.floor(r/60);
        r %= 60;
        goods.second = r;
        $('[remain]').each(function (index,item) {
            var $this = $(this);
            var  value =goods[$this.attr('remain')];
            console.log(value);
            $this.text(value);
        });
        if(remain>0){
            remain -=1;
            setTimeout('Hangowa.bookRemainTick('+remain+')',1000);
        }
    };
    var loadGoodsPromotion = function (id) {
        // TODO 初始化商品促销
    };

    var initialGoods = function (id) {
        hango.goods = {id:id};
        loadGoodsInfo(id);
        loadGoodsPromotion(id);
    };

    return {
        version: '0.0.1',
        initial:initial,
        initialUser:initialUser,
        isLogged:isLogged,
        updateCartCount:updateCartCount,
        initialGoods:initialGoods,
        bookRemainTick:bookRemainTick,


        getInstance:function () {
            return hango;
        },


        barUserInfo:barUserInfo,



        
        
    }
}();