/**
 * Created by Administrator on 2017/2/16 0016.
 */
$(function () {
    var goods_id = _getUrlString('goods_id');
    var posturl = getSign('goods.detail')+'&client='+client;

    //商品id
    var json = {goods_id : goods_id};
    //获取数据
    getData(posturl, json, getPostData);
    //数据回调
    function getPostData(data) {
        var result = eval('(' + data + ')');
        var list = result.datas;
        if (result.code == '200') {
            var goods_image = list.goods_image;
            //goods_info
            var goods_info = list.goods_info;
            $("#goods_name").html(goods_info.goods_name);
            $("#goods_price").html('￥'+goods_info.goods_price);
            $("#goods_marketprice").html('￥'+goods_info.goods_marketprice);
            $("#goods_salenum").html('销量：'+goods_info.goods_salenum+'笔');

            //goods_hair_info
            var goods_hair_info = list.goods_hair_info;
            $("#goods_hair_info_content").html('快递：'+goods_hair_info.content);
            $("#area_name").html(goods_hair_info.area_name);
            var gift_array = list.gift_array;
            if(gift_array.length > 0){
                $("#gift_body").css('display','block');
                $("#gift_array").html('活动：'+gift_array);
            }
            //图片轮播
            var goods_image_html = '';
            var goods_doc_html = '';
            if(goods_image.length > 0){
                goods_image_html += '<div class="mui-slider-item mui-slider-item-duplicate">';
                goods_image_html += '   <a href="#"> <img src="'+goods_image[goods_image.length-1]+'" data-preview-src="" data-preview-group="1"> </a>';
                goods_image_html += '</div>';
                for(var i=0;i<goods_image.length;i++){
                    if(i == 0){
                        goods_doc_html += '<div class="mui-indicator mui-active"></div>';
                    }else{
                        goods_doc_html += '<div class="mui-indicator"></div>';
                    }
                    goods_image_html += ' <div class="mui-slider-item">';
                    goods_image_html += '     <a href="#"> <img src="'+goods_image[i]+'" data-preview-src="" data-preview-group="1"> </a>';
                    goods_image_html += '</div>';
                }
                goods_image_html += '<div class="mui-slider-item mui-slider-item-duplicate">';
                goods_image_html += '   <a href="#"> <img src="'+goods_image[0]+'" > </a>';
                goods_image_html += '</div>';
                $("#goods_images").html(goods_image_html);
                $("#goods_doc").html(goods_doc_html);
            }
            mui("#slider").slider({interval: 5000});

            //goods_eval_list评价
            var goods_eval_list = list.goods_eval_list;
            $("#member_avatar").attr('src',goods_eval_list.member_avatar);
            $("#geval_frommembername").html(goods_eval_list.geval_frommembername);
            $("#geval_content").html(goods_eval_list.geval_content);
            //全部评价
        } else {
            console.log('error');
        }
        $("#eval_other").click(function () {
           window.location.href = 'goods_comment_list.html?goods_id='+goods_id;
        })
    }
    //打开App
    $("#openApp").click(function(){
    	// alert('zoule');
        // window.location = 'schema://HanGouWang?goods_id='+goods_id;
        if(navigator.userAgent.match(/android/i)){
            //android
            var ifr = document.createElement('iframe');
            ifr.src = 'hangowaspc://ProductDetailsActivity/?goods_id='+goods_id;
            // alert(ifr.src);
            ifr.style.display = 'none';
            document.body.appendChild(ifr);
            var openTime = +new Date();
            window.setTimeout(function(){
                document.body.removeChild(ifr);
                if( (+new Date()) - openTime > 2500 ){
                    window.location = 'http://exam.com/xxxx.apk';
                }
            },2000);
            // alert('qmct');
            //此操作会调起app并阻止接下来的js执行
            // $('body').append("<iframe src='qmct://LecturerDetailActivity/?teacherId="+goods_id+"' style='display:none' target='' ></iframe>");
            //
            // //没有安装应用会执行下面的语句,安卓下载地址
            // setTimeout(function(){window.location = 'http://XXX.apk'},600);
        }else if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)){
            //ios
            if(/Safari/.test(navigator.userAgent.toLowerCase())==false){
                alert("请使用Safari浏览器打开");
            }
            document.location = 'HanGouWang://?goods_id='+goods_id;
        }
    })
    $("#downloadApp").click(function(){
        if(navigator.userAgent.match(/android/i)){
            //android
            window.location.href = downloadAndroid;
        }else if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)){
            //ios
            window.location.href = "";
        }
    })

    function isWeixinBrowser() {
        return (/micromessenger/.test(ua)) ? true : false;
    }

    function isQQBrowser() {
        return (ua.match(/QQ/i) == "qq") ? true : false;
    }
})