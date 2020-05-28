$(function () {

    var headerClone = $('#header').clone();
    $(window).scroll(function () {
        if ($(window).scrollTop() <= $('#main-container1').height()) {
            headerClone = $('#header').clone();
            $('#header').remove();
            headerClone.addClass('transparent').removeClass('');
            headerClone.prependTo('.nctouch-home-top');
        } else {
            headerClone = $('#header').clone();
            $('#header').remove();
            headerClone.addClass('').removeClass('transparent');
            headerClone.prependTo('body');
        }
    });
    $.ajax({
        url: ApiUrl + "/index.php?act=index",
        type: 'get',
        dataType: 'json',
        success: function (result) {
            var data = result.datas.list;
            console.log("data数据",data);
            var html = '';

            $.each(data, function (k, v) {
                $.each(v, function (kk, vv) {
                    switch (kk) {
                        case 'adv_list':
                        case 'home3':
                        //case 'miaosha':
                        case 'explode3':
                        case 'explode4':
                            $.each(vv.item, function (k3, v3) {
                                vv.item[k3].url = buildUrl(v3.type, v3.data);
                            });
                            break;

                        case 'home1':
                            vv.url = buildUrl(vv.type, vv.data);
                            break;

                        case 'home2':
                        case 'home4':
                            vv.square_url = buildUrl(vv.square_type, vv.square_data);
                            vv.rectangle1_url = buildUrl(vv.rectangle1_type, vv.rectangle1_data);
                            vv.rectangle2_url = buildUrl(vv.rectangle2_type, vv.rectangle2_data);
                            break;
                    }
                    if (k == 0) {
                        $("#main-container1").html(template.render(kk, vv));
                    } else {
                        html += template.render(kk, vv);
                    }
                    return false;
                });
            });

            $("#main-container2").html(html);



            $('.adv_list').each(function () {
                if ($(this).find('.item').length < 2) {
                    return;
                }

                Swipe(this, {
                    startSlide: 2,
                    speed: 400,
                    auto: 3000,
                    continuous: true,
                    disableScroll: false,
                    stopPropagation: false,
                    callback: function (index, elem) {},
                    transitionEnd: function (index, elem) {}
                });
            });
            if ($(document).find("img[rel='lazy']").length > 0) {
                $(window).scroll(function () {
                    fade();
                });
            };
            fade();















            var list_len = result.datas.list.length;
            for( var index = 0 ; index < list_len; index++){
                if( result.datas.list[index]["miaosha"]){
                    var data = result.datas.list[index]["miaosha"];
                    console.log('index为',index);
                    var h = '';
                    var b = data.item.xian_shi.list;
                    var len = b.length;
                    // if(data){
                    //     $('.miaosha .title').show();
                    // }else {
                    //     $('.miaosha .title').hide();
                    // }

                    for( var i = 0; i < len; i++){
                        //console.log('图片地址',data.item.xian_shi.list[0].goods_image);

                        h += '<div class="swiper-slide">';
                        h += '<a href="tmpl/product_detail.html?goods_id=' + b[i].goods_id + '">';
                        h += '<div class="goods-pic"><img shopwwi-url="'+b[i].goods_image+'" rel=\'lazy\' src="/img/loading.gif" alt=""></div>';
                        h += '<dl class="goods-info">';
                        h += '<dt class="goods-name">' + b[i].goods_name + '</dt>';
                        h += '<dd class="goods-price"><span class="current-price">&yen;<em>' + b[i].goods_price + '</em></span><span class="original-price">&yen;<em>' + b[i].xianshi_price + '</em></span></dd>';
                        h += '</dl>';
                        h += '</a>';
                        h += '</div>';
                        $('.swiper-wrapper').html(h);
                    }




                    //倒计时
                    var start_time = data.item.info.start_time*1000;
                    var end_time = data.item.info.end_time*1000;
                    var now_time = data.item.info.now_time*1000;
                    var new_settime;
                    var i =1;
                    setInterval(function () {

                        function Appendzero(obj){
                            if ( obj < 10 ) return '0' + obj;
                            else return obj;
                        }

                        console.log('开始时间',start_time);
                        console.log('当前时间',now_time);
                        //console.log('开始时间格式',new Date(start_time));

                        if ( now_time < start_time ) {
                            $('.time-text').text("距开场还剩");
                            var set_time = start_time - now_time;
                            var secondTime = parseInt(set_time);// 秒
                            var minuteTime = 0;// 分
                            var hourTime = 0;// 小时
                            if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                                //获取分钟，除以60取整数，得到整数分钟
                                minuteTime = parseInt(secondTime / 60);
                                //获取秒数，秒数取佘，得到整数秒数
                                secondTime = parseInt(secondTime % 60);
                                //如果分钟大于60，将分钟转换成小时
                                if(minuteTime > 60) {
                                    //获取小时，获取分钟除以60，得到整数小时
                                    hourTime = parseInt(minuteTime / 60);
                                    //获取小时后取佘的分，获取分钟除以60取佘的分
                                    minuteTime = parseInt(minuteTime % 60);
                                }
                            }
                            $(".hour").text(Appendzero(hourTime));
                            $(".minute").text(Appendzero(minuteTime));
                            $(".seconds").text(Appendzero(secondTime));
                        }
                        if ( now_time >= start_time && now_time <= end_time ) {
                            $('.time-text').text("距结束还剩");
                            var set_time = end_time - now_time;

                            var secondTime = parseInt(set_time);// 秒
                            var minuteTime = 0;// 分
                            var hourTime = 0;// 小时
                            if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                                //获取分钟，除以60取整数，得到整数分钟
                                minuteTime = parseInt(secondTime / 60);
                                //获取秒数，秒数取佘，得到整数秒数
                                secondTime = parseInt(secondTime % 60);
                                //如果分钟大于60，将分钟转换成小时
                                if(minuteTime > 60) {
                                    //获取小时，获取分钟除以60，得到整数小时
                                    hourTime = parseInt(minuteTime / 60);
                                    //获取小时后取佘的分，获取分钟除以60取佘的分
                                    minuteTime = parseInt(minuteTime % 60);
                                }
                            }
                            $(".hour").text(Appendzero(hourTime));
                            $(".minute").text(Appendzero(minuteTime));
                            $(".seconds").text(Appendzero(secondTime));
                        }
                        if ( now_time > end_time ) {
                            $('.time-text').text("已结束");
                            var set_time = now_time - end_time;
                            var secondTime = parseInt(set_time);// 秒
                            var minuteTime = 0;// 分
                            var hourTime = 0;// 小时
                            if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                                //获取分钟，除以60取整数，得到整数分钟
                                minuteTime = parseInt(secondTime / 60);
                                //获取秒数，秒数取佘，得到整数秒数
                                secondTime = parseInt(secondTime % 60);
                                //如果分钟大于60，将分钟转换成小时
                                if(minuteTime > 60) {
                                    //获取小时，获取分钟除以60，得到整数小时
                                    hourTime = parseInt(minuteTime / 60);
                                    //获取小时后取佘的分，获取分钟除以60取佘的分
                                    minuteTime = parseInt(minuteTime % 60);
                                }
                            }
                            // $(".hour").text(Appendzero(hourTime));
                            // $(".minute").text(Appendzero(minuteTime));
                            // $(".seconds").text(Appendzero(secondTime));
                            $(".hour").text('00');
                            $(".minute").text('00');
                            $(".seconds").text('00');
                        }

                        now_time = now_time+1;

                    },1000)







                }
            }






            //添加父元素
            $(".explode2").wrapAll("<div class='wrap'></div>");
            $(".explode2:nth-child(2)").find("h3").html('<img src="images/hmt-logo@2x.png">');


            //秒杀滑动效果
            var mySwiper = new Swiper('.swiper-container',{
                slidesPerView: 3
            })















        }

    });









    //
    // $.ajax({
    //     url: ApiUrl + "/index.php?act=index",
    //     type: 'get',
    //     dataType: 'json',
    //
    //     success:function (result) {
    //
    //
    //
    //
    //
    //     }
    // })














});