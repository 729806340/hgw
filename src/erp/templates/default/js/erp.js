/**
 * 运输服务相关
 **/
if (typeof window.console === "undefined") {
    console = {
        log: function (message) {
        }
    };
}

if (typeof window.HanShop === "undefined")
    HanShop = {};
if (typeof window.jQuery === "undefined") {
    console.error('jQuery必须在erp/main.js之前引入');
} else {
    HanShop.Erp = (function ($) {
        var user, order_id, bt = baidu.template,
            HanAlert = function(message){
                var modal = $('#alert-modal');
                modal.find('.modal-body').text(message);
                modal.modal('show');
            },
            initParam = function (param) {
                user = param.user;
                order_id = param.order_id
            },
            showPage = function (name) {
                $('.page').hide();
                $('#page-' + name).show();
            },
            showController = function (page, name) {
                page.find('.controller').hide();
                page.find('#' + name).show();
            },
            initOrderList = function () {
                showPage('order-index');
            },
            initOrderDistribution = function () {
                var thisPage = $('#page-order-distribution');
                showController(thisPage, 'loading');
                $.get('/erp/index.php?act=main&op=ajax_distribution&order_id=' + order_id)
                    .done(function (res) {
                        if (res.status > 0) {
                            return showController(thisPage, 'error');
                        }
                        console.log(res);
                        renderDistribution(res.data);
                        return showController(thisPage, 'content');
                    })
                    .fail(function (error) {
                        return showController(thisPage, 'error');
                    });
                showPage('order-distribution');
            },
            renderDistribution = function (data) {
                var html = bt('order-distribution-template', data), thisPage = $('#page-order-distribution');
                thisPage.find('#content').html(html);
                thisPage.find('.distribution').click(function (e) {
                    // 点击分配
                    var $this = $(this),
                        key = $this.data('key'),
                        num = $this.data('num'),
                        image = $this.data('image'),
                        name = $this.data('name'),
                        distribution = $this.data('distribution'),
                        template = thisPage.find('.distribution-template').clone();
                    template.removeClass('distribution-template').addClass('distribution-item').appendTo('#distribution-' + key);


                });
                thisPage.find('.distributions').on('click', '.remove-distribution', function (e) {
                    $(this).parents('.distribution-item').remove();
                });
                thisPage.find('.submit-button').on('click', function (e) {
                    var total = 0, error=0, distributions = [];
                    // ajax 提交分配方案
                    $('.distributions').each(function (index, rec) {
                        var $rec = $(rec), rec_id = $rec.data('key'), distribution = {
                            num: 0,
                            rec_id: rec_id,
                            goods_commonid: $rec.data('goods-commonid'),
                            items: []
                        };
                        $rec.find('.distribution-item').each(function (index, item) {
                            var $item = $(item),
                                store_id = $item.find('.item-store').val(),
                                num = $item.find('.item-num').val(),
                                price = $item.find('.item-price').val(),
                                cost = $item.find('.item-cost').val();
                            if (!store_id || !num || !price) {
                                return error++;
                                //return window.HanAlert('分配店铺/单价/数量为必填项目，多余项目请删除');
                            }
                            distribution.items.push({
                                rec_id: rec_id,
                                store_id: store_id,
                                num: num,
                                price: price,
                                cost: cost,
                            });
                            distribution.num += parseInt(num);
                        });
                        total += distribution.num;
                        distributions.push(distribution);
                    });
                    if(error>0) return window.HanAlert('分配店铺/单价/数量为必填项目，多余项目请删除');
                    if (total < 1) return window.HanAlert('没有添加任何分配方案');
                    $.post('/shop/index.php?c=main&a=save_distribution&order_id=' + order_id, {
                        distributions: distributions,
                    })
                        .done(function (res) {
                            window.HanAlert(res);
                            res = JSON.parse(res);
                            if (res.state) {
                                return window.HanAlert('分配方案保存成功');
                                //location.reload();
                            }
                            else {
                                window.HanAlert(res.msg);
                            }
                        })
                        .fail(function (error) {
                            window.HanAlert('异常错误');
                        });
                })
            };
        return {
            init: function () {
                // 初始化数据，根据数据显示页面
                window.HanAlert = HanAlert;
                initParam(appParam);
                // 若没有appParam.user，则显示主页面
                // 若没有appParam.erpparam，则显示订单列表页
                // 否则显示分配页面
                if (!user) {
                    showPage('index');
                } else if (!order_id) {
                    initOrderList();
                } else {
                    initOrderDistribution();
                }

            }
        };
    })(jQuery);
    jQuery(function () {
        HanShop.Erp.init();
    });
}




