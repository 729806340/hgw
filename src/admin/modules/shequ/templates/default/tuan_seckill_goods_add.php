<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .rpt-range li{padding: 5px 5px 0;border: 1px solid gray;margin: 5px 0;}
    .rpt-range li.selected{background: lightgrey;}
    .rpt-range li img{height: 24px;width: 24px;}
    .rpt-range li span{line-height: 24px;vertical-align: top;}
    .rpt-range li .del{display: inline-block;float: right;}
    #goods-select-box{width: 45%;float: right;}
    #goods-selected-list{width: 45%;float: left;}
    #list-head{font-size: 16px;margin: 10px 0 0;}
    .ui-datepicker {
        width: 19em;
        padding: .2em .2em 0;
        display: none;
    }
</style>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=seckill_list&config_tuan_id=<?php echo $output['tuan_config_id']; php?>" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>社区团购活动</h3>
                <h5>社区团购活动设置与管理</h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>

        </ul>
    </div>

    <form id="rpt_form" method="post" name="rpt_form" enctype="multipart/form-data">
        <div class="ncap-form-default">
            <dl class="row" id="range-select-row" style="display: none;">
                <dd class="opt" style="width: 100%;">
                    <div class="rpt-range">
                        <div>
                            <input type="hidden" name="rpt_skus" id="rpt-skus" value="<?php echo $output['selected_sku']; ?>"/>
                        </div>
                        <div id="goods-selected-list">
                            <div id="list-head">
                                <span id="select-goods-class-name">已选商品</span>
                                <a href="JavaScript:cleanSku();" class="ncap-btn ncap-btn-red" id="select-goods-reset">清空已选商品</a>
                                <a href="JavaScript:submitSku();" class="ncap-btn ncap-btn-green" id="select-goods-reset">确定提交下列商品</a>
                            </div>
                            <ul id="list-body">

                                <?php if (!empty($output['goods_list'])) {?>
                                    <?php foreach ($output['goods_list'] as $val) {?>
                                        <li id="selected-sku-<?php echo $val['goods_id']; ?>" data-store="<?php echo $val['store_id']; ?>" data-name="<?php echo $val['goods_name']; ?>"  data-image="<?php echo $val['goods_image']; ?>" data-key="<?php echo $val['goods_id']; ?>" data-price="<?php echo $val['goods_price']; ?>">
                                            <div>
                                                <img src="/data/upload/shop/store/goods/<?php echo $val['store_id']; ?>/<?php echo $val['goods_image']; ?>"><span><?php echo $val['goods_name']; ?></span><span>&nbsp;&nbsp;&nbsp;&nbsp;销售价格:¥</span><span><?php echo $val['goods_price']; ?></span>
                                                <div class="del">删除</div>
                                            </div>
                                            <div>限时折扣价格：<input type="text" class="sku-xianshi_price" value="<?php echo $val['xianshi_price']; ?>">&nbsp;元</div>
                                            <div>限时折扣库存：<input type="text" class="sku-xianshi_storage" value="<?php echo $val['xianshi_storage']; ?>">&nbsp;件</div>
                                            <div>限时折扣限购：<input type="text" class="sku-xianshi_limit" value="<?php echo $val['xianshi_limit']; ?>">&nbsp;件</div>
                                            <div><input id="man" type="checkbox"  class="sku-first_order" value="1"/>仅限首单</div>

                                        </li>
                                    <?php }?>
                                <?php }?>
                            </ul>
                        </div>
                        <div id="goods-select-box">
                            <div style="margin: 10px 0 0;">
                                <input type="text" placeholder="搜索商品名称" value="" name="goods_name" id="goods_name" maxlength="20" class="input-txt">
                                <a id="goods-search" href="JavaScript:void(0);" class="ncap-btn mr5">搜索</a>
                            </div>
                            <div id="goods-search-result">请搜索商品...</div>
                        </div>
                    </div>
                </dd>
            </dl>
        </div>
    </form>
</div>

<script>

    $(function(){
        $('#range-select-row').fadeIn();
        $('#goods-search').click(function (e) {
            $(this).data('page',1);
            getGoods($('#goods_name').val(),1);
            return true;
        });

        $('#goods-select-box').on('click','li',function (e) {
            var $this = $(this);
            var sku = $this.data('key')+'';
            // TODO 添加删除
            var selectedSku = getSelectedSku();
            if($.inArray(sku,selectedSku)>-1){
                removeSku(sku);
            }else{
                addSku(sku,$this);
            }
        });
        $('#list-body').on('click','.del',function (e) {
            var $this = $(this);
            var $li = $this.parents('li');
            var sku = $li.data('key')+'';
            removeSku(sku);
        });
    });
    function getGoods(name,page) {
        console.log('搜索商品');
        //搜索按钮
        var goodsSearch = $('#goods-search');
        //按钮赋值
        goodsSearch.data('page',page);
        //上级下拉框禁用选择
        $("#rpacket_t_goods_type").attr("disabled","disabled");
        //读取上级下拉框的值
        //var goods_type = $("#rpacket_t_goods_type").val();

        //拿到商品列表
        var url = 'index.php?act=tuan_config&op=get_seckill_goods_list&goods_name='+name+'&curpage='+page+'&tuan_config_id=<?php echo $output['tuan_config_id']?>';
        $.get(url,function (data) {
            goodsSearch.data('total',data.total);
            // console.log('这不是我要的结果！');
            renderResult(data.items);
        },'json');
    }
    function renderResult(items) {
        // TODO 渲染结果页面
        var resElem = $('#goods-search-result');
        if(typeof items != 'object'||items.length <=0){
            resElem.text('没有找到对应结果');
            return false;
        }
        var content = '<ul>';
        var sku = getSelectedSku();
        for(var i=0;i<items.length;i++){
            var item = items[i];
            var isSelected = $.inArray(item.goods_id,sku)>-1;
            var className = isSelected?'selected':'';
            var itemHtml='';
            itemHtml = '<li class="'+className+'" id="search-sku-'+item.goods_id+'" data-store="'+item.store_id+'" data-name="'+item.goods_name+'"  data-image="'+item.goods_image+'" data-key="'+item.goods_id+'" data-price="'+item.goods_price+'"><img src="/data/upload/shop/store/goods/'+item.store_id+'/'+item.goods_image+'"> <span>'+item.goods_name+'</span>&nbsp;&nbsp;&nbsp;<span>销售价格:¥'+item.goods_price+'</span></li>';
            content += itemHtml;
        }
        content += '</ul>';
        content += '<div>' +
            '<a href="javascript:prevSearch();" class="ncap-btn mr5">上一页</a>' +
            '<a href="javascript:nextSearch();" class="ncap-btn mr5">下一页</a>' +
            '</div>';
        resElem.html(content);

    }
    function prevSearch() {
        var goodsSearch = $('#goods-search');
        var curPage = goodsSearch.data('page');
        if(curPage <=1) {
            alert('当前为第一页');
            return;
        }
        var totalPage = goodsSearch.data('total');
        getGoods($('#goods_name').val(),parseInt(curPage)-1);
    }
    function nextSearch() {
        var goodsSearch = $('#goods-search');
        var curPage = goodsSearch.data('page');
        var totalPage = goodsSearch.data('total');
        if(curPage >= totalPage) {
            alert('当前为第一页');
            return;
        }
        getGoods($('#goods_name').val(),parseInt(curPage)+1);
    }
    function getSelectedSku() {
        var goods = $('#rpt-skus').val();
        if(goods == '') return [];
        return goods.split(',');
    }
    function addSku(sku,obj) {
        var selectedSku = getSelectedSku();
        var index = $.inArray(sku,selectedSku);
        if(index>-1) return;
        selectedSku.push(sku);
        $('#rpt-skus').val(selectedSku.join(','));
        var content = '<li id="selected-sku-'+obj.data('key')+'" data-store="'+obj.data('store')+'" data-name="'+obj.data('name')+'"  data-image="'+obj.data('image')+'" data-key="'+obj.data('key')+'" data-price="'+obj.data('price')+'"><div>'+obj.html()+'<div class="del">删除</div></div><div>限时折扣价格：<input type="text" class="sku-xianshi_price">元</div><div>限时折扣库存：<input type="text" class="sku-xianshi_storage">件</div><div>限时折扣限购：<input type="text" class="sku-xianshi_limit">件</div><div><input id="man" class="sku-first_order" type="checkbox"  value="1"/>仅限首单</div></li>';
        $('#list-body').append(content);
        $('#search-sku-'+sku).addClass('selected');
    }

    function removeSku(sku) {
        var selectedSku = getSelectedSku();
        var index = $.inArray(sku,selectedSku);
        if(index==-1) return;
        selectedSku.splice(index,1);
        $('#rpt-skus').val(selectedSku.join(','));
        $('#selected-sku-'+sku).remove();
        $('#search-sku-'+sku).removeClass('selected');

    }
    function cleanSku() {
        $('#rpt-skus').val('');
        $('#list-body').empty();
        $('#goods-search-result li').removeClass('selected');
    }

    function submitSku() {

        var $lis = $('#list-body').find('li');
        var data = [];
        var error = false;
        $lis.each(function (index,item) {
            if (error) return ;
            var $item = $(item);
            // 组装数据，
            var goods_name = $item.data('name');
            //var commis = $item.find('.sku-commis').val(); //佣金
            var xianshi_price = $item.find('.sku-xianshi_price').val(); //限时折扣价格
            var xianshi_storage = $item.find('.sku-xianshi_storage').val(); //限时折扣库存
            var xianshi_limit = $item.find('.sku-xianshi_limit').val(); //限时折扣限购
            var sku = $item.find('.sku-first_order').prop('checked'); //仅限首单
            var goods_price = $item.data('price'); //原价格
            if(sku){
                var first_order = 1
            }else{
                var first_order = 0
            }
            if (!xianshi_price) {
                error = true;
                return alert('请设置【'+goods_name+'】的限时折扣价格');

            }
            if (!xianshi_storage) {
                error = true;
                return alert('请设置【'+goods_name+'】限时折扣库存');
            }
            if (!xianshi_limit) {
                error = true;
                return alert('请设置【'+goods_name+'】限时折扣限购');
            }
            if (parseInt(xianshi_limit) > parseInt(xianshi_storage)) {
                error = true;
                return alert('【'+goods_name+'】的限时折扣限购不能大于限时折扣库存');
            }

            if (parseFloat(xianshi_price) > parseFloat(goods_price)) {
                error = true;
                return alert('【'+goods_name+'】的限时折扣价格不能大于原价');
            }
            var sku = {
                goods_id:$item.data('key'),
                store_id:$item.data('store'),
                goods_name:goods_name,
                //type:$item.data('type'),
                goods_image:$item.data('image'),
                xianshi_price:xianshi_price,
                xianshi_storage:xianshi_storage,
                xianshi_limit:xianshi_limit,
                first_order:first_order,
            };
            data.push(sku)
        });
        if (error) return;
        var url = 'index.php?act=tuan_config&op=tuan_seckill_add_goods_save';
        $.post(url,{data:data,tuan_config_id:<?php echo $output['tuan_config_id'];?>,xianshi_id:<?php echo $output['xianshi_id'];?>},undefined,'json').done(function (res) {
            console.log(res);
            if (res.code == 400){
                return  alert(res.msg);
            }
            return  alert(res.data);

        }).fail(function (xhr,reason) {
            console.log(xhr,reason)
        });
    }

</script>

