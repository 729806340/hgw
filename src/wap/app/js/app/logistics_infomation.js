/**
 * Created by Administrator on 2017/2/23 0023.
 */
$(function () {
    var order_id = _getUrlString('order_id');
    // var order_id = 258799;
//url
    var posturl = getSign('member_order.search_deliver') + '&key=' + key + '&client='+client;
//订单id
    var json = {order_id: order_id};
//获取数据
    var index =  layer.open({
        type: 2
        ,content: '加载中'
    });
    getData(posturl, json, getPostData);
//数据回调
    function getPostData(data) {
        layer.closeAll();
        var result = eval('(' + data + ')');
        if (result.code == '200') {
            $("#express_name").html(result.datas.express_name);
            $("#shipping_code").html(result.datas.shipping_code);
            var deliver_info = result.datas.deliver_info;
            var deliverHtml = '';
            // index.close();
            if (deliver_info.length > 0) {
                //判断物流状态
                if(deliver_info.length == 1){
                    $("#logistics_status").html('已发货');
                }else if (deliver_info.length > 1){
                    if(deliver_info[0].context.indexOf("已签收") >= 0){
                        $("#logistics_status").html('已签收');
                    }else{
                        $("#logistics_status").html('运输中');
                    }
                }
                for (var i = 0; i < deliver_info.length; i++) {
                    if(i == 0){
                        deliverHtml += '<dl class="paddinglr logistics-dl w logistics-dl-new">';
                    }else{
                        deliverHtml += '<dl class="paddinglr logistics-dl w">';
                    }
                    deliverHtml += '    <dt class=""></dt>';
                    deliverHtml += '    <dd class="cob9">';
                    deliverHtml += '        <p class="font12 lh150">'+deliver_info[i].context+'</p>';
                    deliverHtml += '        <h5 class="font10 lh150">'+deliver_info[i].time+'</h5>';
                    deliverHtml += '        <div class="logistics-line"></div>';
                    deliverHtml += '    </dd>';
                    deliverHtml += '</dl>';
                }
                $("#deliver_info").html(deliverHtml);
            }
        } else {
            //提示
            layer.open({
                content: result.datas.error
                ,skin: 'msg'
                ,time: 2 //2秒后自动关闭
            });
        }
    }
})