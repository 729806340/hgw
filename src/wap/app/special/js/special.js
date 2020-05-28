$(function () {
    var special_id = getQueryString('special_id');
    // alert(special_id);
    loadSpecial(special_id);
})

function loadSpecial(special_id) {
    // index.special
    var posturl = getSign('index.special') + '&special_id=' + special_id + '&client=' + client;
    var json = {
        'special_id': special_id
    };
    $.ajax({
        url: posturl,
        type: 'post',
        dataType: 'json',
        data: json,
        success: function (result) {
            // $('title,h1').html(result.datas.special_desc);
            $('title').html(result.datas.special_desc);
            var data = result.datas.list;
            var html = '';
            $.each(data, function (k, v) {
                $.each(v, function (kk, vv) {
                    switch (kk) {
                        case 'adv_list':
                        case 'explode3':
                        case 'explode4':
                        case 'home3':
                            $.each(vv.item, function (k3, v3) {
                                console.log(v3);
                                // vv.item[k3].url = buildUrl(v3.type, v3.data);
                                vv.item[k3].url = 'www.fuhao.com?data='+vv.item[k3].data+'&type='+vv.item[k3].type;
                            });
                            break;
                        case 'home1':
                            vv.url = 'www.fuhao.com?data='+vv.data+'&type='+vv.type;
                            // vv.url = buildUrl(vv.type, vv.data);
                            break;
                        case 'home2':
                            
                        case 'home4':
                            vv.square_url = 'www.fuhao.com?data='+vv.data+'&type='+vv.type;
                            vv.rectangle1_url = 'www.fuhao.com?data='+vv.data+'&type='+vv.type;
                            vv.rectangle2_url = 'www.fuhao.com?data='+vv.data+'&type='+vv.type;
                            // vv.square_url = buildUrl(vv.square_type, vv.square_data);
                            // vv.rectangle1_url = buildUrl(vv.rectangle1_type, vv.rectangle1_data);
                            // vv.rectangle2_url = buildUrl(vv.rectangle2_type, vv.rectangle2_data);
                            break;
                    }
                    html += template.render(kk, vv);
                    return false;
                });
            });

            $("#main-container").html(html);

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
                    callback: function (index, elem) {
                    },
                    transitionEnd: function (index, elem) {
                    }
                });
            });
            if ($(document).find("img[rel='lazy']").length > 0) {
                $(window).scroll(function () {
                    fade();
                });
            }
            ;
            fade();
        }
    });

}
