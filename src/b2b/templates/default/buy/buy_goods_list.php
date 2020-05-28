<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="ncc-receipt-info">
    <div class="ncc-receipt-info-title" style="
    height: 32px;
    padding: 0;
    line-height: 32px;
">
        <h3>商品清单</h3>
        <span style="
    position: relative;
    width: 100px;
    height: 32px;
    display: inline-block;
">
            <input type="file" id="uploadAddressFile" hidefocus="true" size="1" class="input-file" name="file"
                   style="opacity: 0;width: 100px;height: 32px;"/>
            <a href="javascript:void(0)" id="uploadAddress" style="
    position: absolute;
    width: 100px;
    left: 0;
    top: 7px;
    z-index: -1;
">上传收货地址</a>
        </span>
        <a href="<?php echo RESOURCE_SITE_URL; ?>/examples/addresses.xlsx">下载模板v2（含单价）</a>
    </div>
    <table class="ncc-table-style">
        <thead>
        <tr>
            <th class="w50"></th>
            <th></th>
            <th><?php echo $lang['cart_index_store_goods']; ?></th>
            <th class="w150"><?php echo $lang['cart_index_price'] . '(' . $lang['currency_zh'] . ')'; ?></th>
            <th class="w100"><?php echo $lang['cart_index_amount']; ?></th>
            <th class="w150"><?php echo $lang['cart_index_sum'] . '(' . $lang['currency_zh'] . ')'; ?></th>
            <th class="w150">商品操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($output['cart_list'] as $cart_info) { ?>
            <tr id="cart_item_<?php echo $cart_info['goods_id']; ?>"
                data-cart-id="<?php echo $cart_info['cart_id']; ?>"
                data-goods-id="<?php echo $cart_info['goods_id']; ?>"
                class="shop-list <?php echo ($cart_info['state'] && $cart_info['storage_state']) ? '' : 'item_disabled'; ?>"
                <?php if ($cart_info['jjgRank'] > 0) { ?>
                    data-jjg="<?php echo $cart_info['jjgRank']; ?>"
                <?php } ?>
            >
                <td class="td-border-left">
                    <?php if ($cart_info['state'] && $cart_info['storage_state']) { ?>
                        <input type="hidden"
                               value="<?php echo $cart_info['cart_id'] . '|' . $cart_info['goods_num']; ?>"
                               store_id="<?php echo $store_id ?>" name="cart_id[]">
                        <input type="hidden"
                               value="<?php echo $cart_info['goods_id'] . '|' . $cart_info['goods_num']; ?>"
                               store_id="<?php echo $store_id ?>" name="goods_id[]">
                    <?php } ?></td>
                <?php if ($cart_info['bl_id'] == '0') { ?>
                    <td class="w100"><a
                                href="<?php echo urlB2b('goods', 'index', array('goods_id' => $cart_info['goods_id'])); ?>"
                                target="_blank" class="ncc-goods-thumb"><img src="<?php echo thumb($cart_info); ?>"
                                                                             alt="<?php echo $cart_info['goods_name'] . '(' . $cart_info['goods_calculate'] . ')';; ?>"/></a>
                    </td>
                <?php } ?>
                <td class="tl" <?php if ($cart_info['bl_id'] != '0') { ?>colspan="2"<?php } ?>>
                    <dl class="ncc-goods-info">
                        <dt>
                            <a href="<?php echo urlB2b('goods', 'index', array('goods_id' => $cart_info['goods_id'])); ?>"
                               target="_blank"><?php echo '(ID:' . $cart_info['goods_id'] . ') ' . $cart_info['goods_name'] . '(' . $cart_info['goods_calculate'] . ')'; ?></a>
                        </dt>
                        <?php if (!$cart_info['bl_id']) { ?>
                            <dd class="goods-spec"><?php echo $cart_info['goods_spec']; ?></dd>
                        <?php } ?>

                        <!-- S 消费者保障服务 -->
                        <?php if ($cart_info["contractlist"]) { ?>
                            <dd class="goods-cti">
                                <?php foreach ($cart_info["contractlist"] as $gcitem_k => $gcitem_v) { ?>
                                    <span
                                        <?php if ($gcitem_v['cti_descurl']){ ?>onclick="window.open('<?php echo $gcitem_v['cti_descurl']; ?>');"
                                        style="cursor: pointer;"<?php } ?> title="<?php echo $gcitem_v['cti_name']; ?>"> <img
                                                src="<?php echo $gcitem_v['cti_icon_url_60']; ?>"/> </span>
                                <?php } ?>
                            </dd>
                        <?php } ?>
                        <!-- E 消费者保障服务 --> <!-- S 商品赠品列表 -->
                        <?php if (!empty($cart_info['gift_list'])) { ?>
                            <dd class="ncc-goods-gift"><span>赠品</span>
                                <ul class="ncc-goods-gift-list">
                                    <?php foreach ($cart_info['gift_list'] as $goods_info) { ?>
                                        <li nc_group="<?php echo $cart_info['cart_id']; ?>"><a
                                                    href="<?php echo urlB2b('goods', 'index', array('goods_id' => $goods_info['gift_goodsid'])); ?>"
                                                    target="_blank" class="thumb"
                                                    title="赠品：<?php echo $goods_info['gift_goodsname']; ?> * <?php echo $goods_info['gift_amount'] * $cart_info['goods_num']; ?>"><img
                                                        src="<?php echo cthumb($goods_info['gift_goodsimage'], 60, $store_id); ?>"
                                                        alt="<?php echo $goods_info['gift_goodsname']; ?>"/></a></li>
                                    <?php } ?>
                                </ul>
                            </dd>
                        <?php } ?>
                        <!-- E 商品赠品列表 -->
                    </dl>
                </td>
                <td><!-- S 商品单价 -->

                    <?php if (!empty($cart_info['xianshi_info'])) { ?>
                        <em class="goods-old-price tip" title="商品原价格"><?php echo $cart_info['goods_yprice']; ?></em>
                    <?php } ?>
                    <em class="goods-price"><?php echo $cart_info['goods_price']; ?></em><!-- E 商品单价 -->
                </td>
                <td class="goods-num"><?php echo $cart_info['state'] ? $cart_info['goods_num'] : ''; ?></td>
                <td class="td-border-right"><?php if ($cart_info['state'] && $cart_info['storage_state']) { ?>
                        <em cart_id="<?php echo $cart_info['cart_id']; ?>"
                            goods_id="<?php echo $cart_info['goods_id']; ?>"
                            nc_type="eachGoodsTotal<?php echo $store_id ?>"
                            tpl_id="<?php echo $cart_info['transport_id'] ?>"
                            class="goods-subtotal"><?php echo $cart_info['goods_total']; ?></em> <span
                                id="no_send_tpl_<?php echo $cart_info['transport_id'] ?>"
                                style="color: #F00;display:none">无货</span>
                    <?php } elseif (!$cart_info['storage_state']) { ?>
                        <span style="color: #F00;">库存不足</span>
                    <?php } elseif (!$cart_info['state']) { ?>
                        <span style="color: #F00;">无效</span>
                    <?php } ?></td>
                <td class="td-border-right">
                    <a href="javascript:void(0)" class="ncbtn ncbtn-grapefruit addAddress">添加收货信息</a>
                </td>
            </tr>

        <?php } ?>
        <a href="javascript:void(0)" nc_type="dialog" dialog_title="注册采购商信息" dialog_id="goods_address_add"
           uri="<?php echo B2B_SITE_URL . '/index.php?act=buy&op=load_addr'; ?>" dialog_width="720" title="选择收货地址"
           id="goods_address_add" style="display: none;">添加收货信息</a>

        <tr>
            <td colspan="20">
                <div class="ncc-msg">买家留言：
                    <textarea name="pay_message[<?php echo $store_id; ?>]" class="ncc-msg-textarea"
                              placeholder="选填：对本次交易的说明（建议填写已经和商家达成一致的说明）" title="选填：对本次交易的说明（建议填写已经和商家达成一致的说明）"
                              maxlength="150"></textarea>
                </div>
                <div class="ncc-store-account">
                    <dl>
                        <dt>商品金额：</dt>
                        <dd class="rule"></dd>
                        <dd class="sum"><em
                                    id="eachStoreGoodsTotal_<?php echo $store_id; ?>"><?php echo $output['goods_total']; ?></em>
                        </dd>
                    </dl>
                    <dl>
                        <dt>物流运费：</dt>
                        <dd class="rule">
                            <?php if (!empty($output['cancel_calc_sid_list'][$store_id])) { ?>
                                <?php echo $output['cancel_calc_sid_list'][$store_id]['desc']; ?>
                            <?php } ?>
                        </dd>
                        <dd class="sum"><em nc_type="eachStoreFreight" id="eachStoreFreight_<?php echo $store_id; ?>">0.00</em>
                        </dd>
                    </dl>
                </div>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <!-- S rpt list -->
        <tr id="rpt_panel" style="display: none">
            <td class="pd-account" colspan="20">
                <div class="ncc-store-account">
                    <dl>
                        <dt>平台红包：</dt>
                        <dd class="rule">
                            <select nctype="rpt" id="rpt" name="rpt" class="select">
                            </select>
                        <dd class="sum"><em id="orderRpt" class="subtract">-0.00</em></dd>
                    </dl>
                </div>
            </td>
        </tr>
        <!-- E rpt list -->
        <tr>
            <td colspan="20"><?php if (!empty($output['ifcart'])) { ?>
                    <a href="index.php?act=cart" class="ncc-prev-btn"><i
                                class="icon-angle-left"></i><?php echo $lang['cart_step1_back_to_cart']; ?></a>
                <?php } ?>
                <div class="ncc-all-account">订单总金额：<em
                            id="orderTotal"><?php echo $output['goods_total']; ?></em><?php echo $lang['currency_zh']; ?>
                </div>
                <a href="javascript:void(0)" id='submitOrder'
                   class="ncc-next-submit"><?php echo $lang['cart_index_submit_order']; ?></a></td>
        </tr>
        </tfoot>
    </table>
</div>

<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script>
    var addressRow, goodsRow;
    function checkGoodsAddress() {
        var res = true;
        $('.shop-list ').each(function (index, item) {
            var _item = $(item), goodsId = _item.data('goods-id'),
                total = parseInt(_item.find('.goods-num').text());
            $('.goods-address-' + goodsId + ' .address-num').each(function (index1, item1) {
                var _this = $(item1);
                console.log(_this.val());
                total -= _this.val();
                if (total < 0) return res = false;
            });
            if (total !== 0) return res = false;
        });

        return res;
    }
    function submitNext() {
        if (!SUBMIT_FORM) return;

        if ($('input[name="cart_id[]"]').size() == 0) {
            showDialog('所购商品无效', 'error', '', '', '', '', '', '', '', '', 2);
            return;
        }
        if ($('#address_id').val() == '') {
            showDialog('<?php echo $lang['cart_step1_please_set_address'];?>', 'error', '', '', '', '', '', '', '', '', 2);
            return;
        }
        if (!checkGoodsAddress()) {
            showDialog('商品收货信息不正确', 'error', '', '', '', '', '', '', '', '', 2);
            return;
        }
        SUBMIT_FORM = false;

        $('#order_form').submit();
    }

    //计算总运费和每个店铺小计
    function calcOrder() {
        allTotal = 0;
        $('em[nc_type="eachStoreTotal"]').each(function () {
            store_id = $(this).attr('store_id');
            var eachTotal = 0;
            $('em[nc_type="eachGoodsTotal' + store_id + '"]').each(function () {
                if (no_send_tpl_ids[$(this).attr('tpl_id')]) {
                    $(this).next().show();
                    $('#cart_item_' + $(this).attr('cart_id')).addClass('item_disabled');
                } else {
                    if (no_chain_goods_ids[$(this).attr('goods_id')]) {
                        $(this).next().show();
                        $('#cart_item_' + $(this).attr('cart_id')).addClass('item_disabled');
                    } else {
                        $(this).next().hide();
                        $('#cart_item_' + $(this).attr('cart_id')).removeClass('item_disabled');
                    }
                }
            });
            if ($('#eachStoreGoodsTotal_' + store_id).length > 0) {
                eachTotal += parseFloat($('#eachStoreGoodsTotal_' + store_id).html());
            }
            if ($('#eachStoreManSong_' + store_id).length > 0) {
                eachTotal += parseFloat($('#eachStoreManSong_' + store_id).html());
            }
            if ($('#eachStoreVoucher_' + store_id).length > 0) {
                eachTotal += parseFloat($('#eachStoreVoucher_' + store_id).html());
            }
            if ($('#eachStoreFreight_' + store_id).length > 0) {
                eachTotal += parseFloat($('#eachStoreFreight_' + store_id).html());
            }
            allTotal += eachTotal;
            $(this).html(eachTotal.toFixed(2));
        });

        if ($('#orderRpt').length > 0) {
            iniRpt(allTotal.toFixed(2));
            $('#orderRpt').html('-0.00');
        }
        $('#orderTotal').html(allTotal.toFixed(2));
        $('#submitOrder').on('click', function (e) {
            submitNext()
        }).addClass('ok');
    }
    $(function () {
        var tpl = $('#jjg-valid-skus-tpl').html(),
            jjgValidSkus = <?php echo json_encode($output['jjgValidSkus']); ?>,
            body = $('body');

        $footers = {};
        $('[data-jjg]').each(function () {
            var id = $(this).attr('data-jjg');
            if (!$footers[id]) {
                var $footer = $('<tr><td colspan="20"></td></tr>');
                $footers[id] = $footer;
                $("tr[data-jjg='" + id + "']:last").after($footer);
            }
        });

        $.each(jjgValidSkus || {}, function (k, v) {
            $.each(v || {}, function (kk, vv) {
                var s = tpl.replace(/%(\w+)%/g, function ($m, $1) {
                    return vv[$1];
                });
                var $s = $(s);
                $s.find('img[data-src]').each(function () {
                    this.src = $(this).attr('data-src');
                });
                $footers[k].before($s);
            });
        });
        $('*[nc_type="dialog"]').click(function () {
            var id = $(this).attr('dialog_id');
            var title = $(this).attr('dialog_title') ? $(this).attr('dialog_title') : '';
            var url = $(this).attr('uri');
            var width = $(this).attr('dialog_width');
            CUR_DIALOG = ajax_form(id, title, url, width, 0);
            return false;
        });
        $('.addAddress').click(function (e) {
            var _this = $(this), _parentTr = _this.parents('tr'), goodsId = _parentTr.data('goods-id');
            goodsRow = _parentTr;
            addressRow = getAddressRow(goodsId);
            $('#goods_address_add').trigger('click');
            //_parentTr.after(addressRow);
        });


        body.on('click', '.removeAddress', function (e) {
            var _this = $(this);
            _this.parents('tr').remove();
        });
        body.on('focus', '.address-num', function (e) {
            console.log('焦点');
        });

        /*$('#uploadAddress').click(function (e) {
         var $this = $(this);
         alert('假装上传了文件');
         });*/
        $('#uploadAddressFile').fileupload({
            dataType: 'json',
            url: '<?php echo B2B_SITE_URL;?>/index.php?act=buy&op=upload',
            done: function (e, data) {
                var res = data.result;
                if (res.state == false) {
                    showError(param.msg);
                    return false;
                }
                // 循环res.data全部值，将他们加入到收货地址列表
                $('.goods-address-list').remove();
                for (var i in res.data) {
                    if (!res.data.hasOwnProperty(i) || i == 0) continue;
                    var item = res.data[i];
                    if (!item[0]) continue;
                    var goodsId = item[0], goodsPrice = item[1],
                        goodsNum = item[2], buyerName = item[3],
                        address = item[4], phone = item[5];
                    goodsRow = $('#cart_item_' + goodsId);
                    addressRow = getAddressRow(goodsId);
                    var msg = hideAddrList('excel-' + i, buyerName, address, phone, goodsPrice, goodsNum, true);
                    if (true !== msg) {
                        showDialog(msg)
                    }

                }
                //showSucc(tips);
                //setTimeout("window.location.reload()", 3000);
            }
        });


        $('#submitOrder').on('click', function (e) {
            console.log(e);
            submitNext();
        }).addClass('ok');
    });
    //ableSubmitOrder();

    function getFiletype(filePath) {
        var extStart = filePath.lastIndexOf(".") + 1;
        return filePath.substring(extStart, filePath.length).toUpperCase();
    }

    function getAddressRow(goodsId) {
        return $('<tr class="goods-address-list goods-address-' + goodsId + '" data-goods-id="' + goodsId + '"><td colspan="1" class="td-border-left"><input type="hidden" class="address-id" name="goods_address_id[' + goodsId + '][]"><input type="hidden" class="address-name" name="goods_address_name[' + goodsId + '][]"><input type="hidden" class="address-address" name="goods_address_address[' + goodsId + '][]"><input type="hidden" class="address-phone" name="goods_address_phone[' + goodsId + '][]"></td><td colspan="2" class=" address">请选择收货地址</td><td colspan="1" class="goods-price"><input type="text" class="address-price" placeholder="单价" name="address_price[' + goodsId + '][]" value=""></td><td colspan="1" class="goods-num"> x <input type="text" class="address-num" placeholder="数量" style="width: 48px;" name="address_num[' + goodsId + '][]" value="1"></td><td colspan="1" class="td-border-right goods-total"><em class="address-total"></em></td><td colspan="1" class="td-border-right action"><a href="javascript:;" class="removeAddress" data-goods-id="' + goodsId + '">删除</a></td></tr>');
    }
    function hideAddrList(addr_id, true_name, address, phone, goodsPrice, goodsNum, ret) {
        var goodsId = goodsRow.data('goods-id'),
            total = parseInt(goodsRow.find('.goods-num').text()),
            dialog_close_button = $('.dialog_close_button'),
            price = parseFloat(goodsRow.find('.goods-price').text()),
            address_total = addressRow.find('.address-total'),
            address_price = addressRow.find('.address-price'),
            address_num = addressRow.find('.address-num'),
            onModify = function () {
                var priceValue = parseFloat(address_price.val()), numValue = parseFloat(address_num.val());
                priceValue = isNaN(priceValue) ? 0 : priceValue;
                numValue = isNaN(numValue) ? 0 : numValue;
                address_total.text((priceValue * numValue).toFixed(2));
            },
            onNumBlur = function (e) {
                var _this = address_num,
                    _parent = addressRow,
                    goodsId = _parent.data('goods-id'),
                    total = parseInt($('#cart_item_' + goodsId).find('.goods-num').text());
                if (parseInt(_this.val()) != _this.val()
                    || parseInt(_this.val()) < 0) {
                    _this.val(_this.data('value'));
                    onModify();
                    return showDialog('商品分配数量已经超过最大数量！');
                }
                $('.goods-address-' + goodsId).each(function (index, item) {
                    total -= $(item).find('.address-num').val();
                });

                if (total < 0) {
                    _this.val(_this.data('value'));
                    onModify();
                    return showDialog('商品分配数量已经超过最大数量！');
                }
                _this.data('value', _this.val());
            }
        ;
        addressRow.find('.address').html('<strong>' + true_name + '</strong> ' + address + ' (' + phone + ')');
        addressRow.addClass('address-id-' + addr_id);
        if ($('.goods-address-' + goodsId + '.address-id-' + addr_id).length > 0)
            return ret === undefined ? showDialog('已经添加过该地址！') : '已经添加过该地址';
        // 计算数量
        $('.goods-address-' + goodsId).each(function (index, item) {
            total -= $(item).find('.address-num').val();
        });

        if (total <= 0) {
            dialog_close_button.click();
            return ret === undefined ? showDialog('商品已经分配完毕，请调整后再添加！') : '商品已经分配完毕，请调整后再添加！';
        }
        if (goodsNum !== undefined && goodsNum > 0) {
            if (goodsNum > total) {
                return ret === undefined ? showDialog('商品已经分配完毕，请调整后再添加！') : '商品已经分配完毕，请调整后再添加！';
            } else {
                total = goodsNum;
            }
        }
        if(goodsPrice !== undefined && goodsPrice>0){
            price = parseFloat(goodsPrice);
        }
        address_num.val(total).data('value', total).on('input', onModify).on('blur', onNumBlur);
        addressRow.find('.address-id').val(addr_id);
        addressRow.find('.address-name').val(true_name);
        addressRow.find('.address-address').val(address);
        addressRow.find('.address-phone').val(phone);
        address_price.val(price.toFixed(2)).on('input', onModify);
        onModify();
        //address_total.text((price*total).toFixed(2));

        goodsRow.after(addressRow);
        dialog_close_button.click();
        return true;
        //$('#edit_payment').click();
    }

</script> 
