<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/template.min.js" charset="utf-8"></script>
<script type="text/javascript">
    $(document).ready(function(){

        // 当前编辑对象，默认为空
        $edit_item = {};

        //现实商品搜索
        $('#btn_show_goods_select').on('click', function() {
            $('#div_goods_select').show();
        });

        //隐藏商品搜索
        $('#btn_hide_goods_select').on('click', function() {
            $('#div_goods_select').hide();
        });

        //搜索商品
        $('#btn_search_goods').on('click', function() {
            var url = "<?php echo urlShop('store_promotion_pintuan', 'goods_select');?>";
            url += '&' + $.param({goods_name: $('#search_goods_name').val()});
            $('#div_goods_search_result').load(url);
        });
        $('#div_goods_search_result').on('click', 'a.demo', function() {
            $('#div_goods_search_result').load($(this).attr('href'));
            return false;
        });

        //添加拼团商品弹出窗口
        $('#div_goods_search_result').on('click', '[nctype="btn_add_pintuan_goods"]', function() {
            $('#dialog_goods_id').val($(this).attr('data-goods-id'));
            $('#dialog_goods_name').text($(this).attr('data-goods-name'));
            $('#dialog_goods_price').text($(this).attr('data-goods-price'));
            $('#dialog_input_goods_price').val($(this).attr('data-goods-price'));
            $('#dialog_goods_img').attr('src', $(this).attr('data-goods-img'));
            $('#dialog_goods_storage').text($(this).attr('data-storage'));
            $('#dialog_add_pintuan_goods').nc_show_dialog({width: 640, title: '拼团商品规则设定'});
            $('#dialog_pintuan_price').val('');
            $('#dialog_add_pintuan_goods_error').hide();
        });

        //添加拼团商品
        $('#div_goods_search_result').on('click', '#btn_submit', function() {
            var goods_id = $('#dialog_goods_id').val();
            var pintuan_id = <?php echo $_GET['pintuan_id'];?>;
            var goods_price = Number($('#dialog_input_goods_price').val());
            var pintuan_price = Number($('#dialog_pintuan_price').val());
            var pintuan_storage = Number($('#dialog_pintuan_storage').val());
            var limit_time = Number($('#dialog_limit_time').val());
            var limit_user = Number($('#dialog_limit_user').val());
            var minimum_user = Number($('#dialog_minimum_user').val());
            var limit_floor = Number($('#dialog_limit_floor').val());
            var limit_ceilling = Number($('#dialog_limit_ceilling').val());
            var limit_total = Number($('#dialog_limit_total').val());
            if(!isNaN(pintuan_price) && pintuan_price > 0 && pintuan_price < goods_price) {
                $.post('<?php echo urlShop('store_promotion_pintuan', 'pintuan_goods_add');?>',
                    {goods_id: goods_id, pintuan_id: pintuan_id, pintuan_price: pintuan_price, pintuan_storage: pintuan_storage, limit_time: limit_time, limit_user: limit_user, minimum_user: minimum_user, limit_floor: limit_floor, limit_ceilling: limit_ceilling, limit_total: limit_total},
                    function(data) {
                        if(data.result) {
                            $('#dialog_add_pintuan_goods').hide();
                            $('#pintuan_goods_list').prepend(template.render('pintuan_goods_list_template', data.pintuan_goods)).hide().fadeIn('slow');
                            $('#pintuan_goods_list_norecord').hide();
                            showSucc(data.message);
                        } else {
                            showError(data.message);
                        }
                    }, 
                'json');
            } else {
                $('#dialog_add_pintuan_goods_error').show();
            }
        });

        //编辑拼团商品
        $('#pintuan_goods_list').on('click', '[nctype="btn_edit_pintuan_goods"]', function() {
            $edit_item = $(this).parents('tr.bd-line');
            var pintuan_goods_id = $(this).attr('data-pintuan-goods-id');
            var pintuan_price = $edit_item.find('[nctype="pintuan_price"]').text();
            var pintuan_storage = $edit_item.find('[nctype="pintuan_storage"]').text();
            var limit_time = $edit_item.find('[nctype="limit_time"]').text();
            var limit_user = $edit_item.find('[nctype="limit_user"]').text();
            var minimum_user = $edit_item.find('[nctype="minimum_user"]').text();
            var limit_floor = $edit_item.find('[nctype="limit_floor"]').text();
            var limit_ceilling = $edit_item.find('[nctype="limit_ceilling"]').text();
            var limit_total = $edit_item.find('[nctype="limit_total"]').text();
            var goods_price = $(this).attr('data-goods-price');
            $('#dialog_pintuan_goods_id').val(pintuan_goods_id);
            $('#dialog_edit_goods_price').text(goods_price);
            $('#dialog_edit_pintuan_price').val(pintuan_price);
            $('#dialog_edit_pintuan_storage').val(pintuan_storage);
            $('#dialog_edit_limit_time').val(limit_time);
            $('#dialog_edit_limit_user').val(limit_user);
            $('#dialog_edit_minimum_user').val(minimum_user);
            $('#dialog_edit_limit_floor').val(limit_floor);
            $('#dialog_edit_limit_ceilling').val(limit_ceilling);
            $('#dialog_edit_limit_total').val(limit_total);
            $('#dialog_edit_pintuan_goods').nc_show_dialog({width: 450, title: '修改价格'});
        });

        $('#btn_edit_pintuan_goods_submit').on('click', function() {
            var pintuan_goods_id = $('#dialog_pintuan_goods_id').val();
            var pintuan_price = Number($('#dialog_edit_pintuan_price').val());
            var goods_price = Number($('#dialog_edit_goods_price').text());
            var pintuan_storage = Number($('#dialog_edit_pintuan_storage').val());
            var limit_time = Number($('#dialog_edit_limit_time').val());
            var limit_user = Number($('#dialog_edit_limit_user').val());
            var minimum_user = Number($('#dialog_edit_minimum_user').val());
            var limit_floor = Number($('#dialog_edit_limit_floor').val());
            var limit_ceilling = Number($('#dialog_edit_limit_ceilling').val());
            var limit_total = Number($('#dialog_edit_limit_total').val());
            if(!isNaN(pintuan_price) && pintuan_price > 0 && pintuan_price < goods_price) {
                $.post('<?php echo urlShop('store_promotion_pintuan', 'pintuan_goods_price_edit');?>',
                    {pintuan_goods_id: pintuan_goods_id, pintuan_price: pintuan_price, pintuan_storage: pintuan_storage, limit_time: limit_time, limit_user: limit_user, minimum_user: minimum_user, limit_floor: limit_floor, limit_ceilling: limit_ceilling, limit_total: limit_total},
                    function(data) {
                        if(data.result) {
                            $edit_item.find('[nctype="pintuan_price"]').text(data.pintuan_price);
                            $edit_item.find('[nctype="pintuan_discount"]').text(data.pintuan_discount);
                            $edit_item.find('[nctype="pintuan_storage"]').text(data.pintuan_storage);
                            $edit_item.find('[nctype="limit_time"]').text(data.limit_time);
                            $edit_item.find('[nctype="limit_user"]').text(data.limit_user);
                            $edit_item.find('[nctype="minimum_user"]').text(data.minimum_user);
                            $edit_item.find('[nctype="limit_floor"]').text(data.limit_floor);
                            $edit_item.find('[nctype="limit_ceilling"]').text(data.limit_ceilling);
                            $edit_item.find('[nctype="limit_total"]').text(data.limit_total);
                            $('#dialog_edit_pintuan_goods').hide();
                        } else {
                            showError(data.message);
                        }
                    }, 'json'
                ); 
            } else {
                $('#dialog_edit_pintuan_goods_error').show();
            }
        });

        //删除限时活动商品
        $('#pintuan_goods_list').on('click', '[nctype="btn_del_pintuan_goods"]', function() {
            var $this = $(this);
            if(confirm('确认删除？')) {
                var pintuan_goods_id = $(this).attr('data-pintuan-goods-id');
                $.post('<?php echo urlShop('store_promotion_pintuan', 'pintuan_goods_delete');?>',
                    {pintuan_goods_id: pintuan_goods_id},
                    function(data) {
                        if(data.result) {
                            $this.parents('tr').hide('slow', function() {
                                var pintuan_goods_count = $('#pintuan_goods_list').find('.bd-line:visible').length;
                                if(pintuan_goods_count <= 0) {
                                    $('#pintuan_goods_list_norecord').show();
                                }
                            });
                        } else {
                            showError(data.message);
                        }
                    }, 'json'
                );
            }
        });
    });
</script>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <?php if($output['pintuan_info']['editable']) { ?>
    <a id="btn_show_goods_select" class="ncbtn ncbtn-mint" href="javascript:;"><i></i>添加商品</a></div>
    <?php } ?>
<table class="ncsc-default-table">
  <tbody>
    <tr>
      <td class="w90 tr"><strong><?php echo '活动名称'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['pintuan_info']['pintuan_name'];?></td>
      <td class="w90 tr"><strong><?php echo '开始时间'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo date('Y-m-d H:i',$output['pintuan_info']['start_time']);?></td>
      <td class="w90 tr"><strong><?php echo '结束时间'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo date('Y-m-d H:i',$output['pintuan_info']['end_time']);?></td>
      <td class="w90 tr"><strong><?php echo '购买下限'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['pintuan_info']['limit_floor'];?></td>
      <td class="w90 tr"><strong><?php echo '状态'.$lang['nc_colon'];?></strong></td>
      <td class="w120 tl"><?php echo $output['pintuan_info']['pintuan_state_text'];?></td>
    </tr>
</table>
<div class="alert">
  <strong>说明<?php echo $lang['nc_colon'];?></strong>
  <ul>
    <li>1、拼团商品的时间段不能重叠</li>
    <li>2、点击添加商品按钮可以搜索并添加参加活动的商品，点击删除按钮可以删除该商品</li>
  </ul>
</div>
<!-- 商品搜索 -->
<div id="div_goods_select" class="div-goods-select" style="display: none;">
    <table class="search-form">
      <tr><th class="w150"><strong>第一步：搜索店内商品</strong></th><td class="w160"><input id="search_goods_name" type="text w150" class="text" name="goods_name" value=""/></td>
        <td class="w70 tc"><a href="javascript:void(0);" id="btn_search_goods" class="ncbtn"/><i class="icon-search"></i><?php echo $lang['nc_search'];?></a></td><td class="w10"></td><td><p class="hint">不输入名称直接搜索将显示店内所有普通商品，特殊商品不能参加。</p></td>
      </tr>
    </table>
  <div id="div_goods_search_result" class="search-result"></div>
  <a id="btn_hide_goods_select" class="close" href="javascript:void(0);">X</a> </div>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w10"></th>
      <th class="w50"></th>
      <th class="tl"><?php echo $lang['goods_name'];?></th>
      <th class="w90"><?php echo $lang['goods_store_price'];?></th>
      <th class="w120">拼团价格</th>
      <th class="w120">折扣率</th>
        <th class="w120">限时库存</th>
        <th class="w120">成团时限</th>
        <th class="w120">成团人数</th>
        <th class="w120">凑团人数</th>
        <th class="w120">购买下限</th>
        <th class="w120">购买上限</th>
        <th class="w120">累计购买上限</th>
      <th class="w120"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody id="pintuan_goods_list">
    <?php if (!empty($output['pintuan_goods_list'])) {?>
    <?php foreach ($output['pintuan_goods_list'] as $val) {?>
    <tr class="bd-line">
        <td></td>
        <td><div class="pic-thumb"><a href="<%=goods_url%>" target="_blank"><img src="<?php echo $val['image_url'];?>" alt=""></a></div></td>
        <td class="tl"><dl class="goods-name"><dt><a href="<?php echo $val['goods_url']?>" target="_blank"><?php echo $val['goods_name'];?></a></dt></dl></td>
        <td><?php echo $lang['currency']; ?><?php echo $val['goods_price'];?></td>
        <td><?php echo $lang['currency']; ?><span nctype="pintuan_price"><?php echo $val['pintuan_price'];?></span></td>
        <td><span nctype="pintuan_discount"><?php echo $val['pintuan_discount'];?></span></td>
        <td><span nctype="pintuan_storage"><?php echo $val['pintuan_storage'];?></span></td>
        <td><span nctype="limit_time"><?php echo intval($val['limit_time']/3600);?></span></td>
        <td><span nctype="limit_user"><?php echo $val['limit_user'];?></span></td>
        <td><span nctype="minimum_user"><?php echo $val['minimum_user'];?></span></td>
        <td><span nctype="limit_floor"><?php echo $val['limit_floor'];?></span></td>
        <td><span nctype="limit_ceilling"><?php echo $val['limit_ceilling'];?></span></td>
        <td><span nctype="limit_total"><?php echo $val['limit_total'];?></span></td>
        <td class="nscs-table-handle">
        <?php if($output['pintuan_info']['editable']) { ?>
        <span><a nctype="btn_edit_pintuan_goods" class="btn-bluejeans" data-pintuan-goods-id="<?php echo $val['pintuan_goods_id']?>" data-goods-price="<?php echo $val['goods_price'];?>" href="javascript:void(0);"><i class="icon-edit"></i><p><?php echo $lang['nc_edit'];?></p></a></span>
            <span><a nctype="btn_del_pintuan_goods" class="btn-grapefruit" data-pintuan-goods-id="<?php echo $val['pintuan_goods_id']?>" href="javascript:void(0);"><i class="icon-trash"></i><p><?php echo $lang['nc_del'];?></p></a></span>
        <?php } ?>
        </td>
    </tr>
    <?php }?>
    <?php }?>
    <tr id="pintuan_goods_list_norecord" style="display:none">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
  </tbody>
  <tfoot>
    <?php if(!empty($output['pintuan_goods_list'])){?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<div class="bottom">
  <label class="submit-border"><input type="submit" class="submit" id="submit_back" value="<?php echo $lang['nc_back'].$lang['pintuan_index'];?>" onclick="window.location='index.php?act=store_promotion_pintuan&op=pintuan_list'"></label>
</div>
<div id="dialog_edit_pintuan_goods" class="eject_con" style="display:none;">
    <input id="dialog_pintuan_goods_id" type="hidden">
    <dl><dt>商品价格：</dt><dd><span id="dialog_edit_goods_price"></dd>
    </dl>
    <dl><dt>拼团价格：</dt><dd><input id="dialog_edit_pintuan_price" type="text" class="text w70"><em class="add-on"><i class="icon-renminbi"></i></em>
    <p id="dialog_edit_pintuan_goods_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i>折扣价格不能为空，且必须小于商品价格</label></p>
    </dl>
    <dl><dt>拼团库存：</dt><dd><input id="dialog_edit_pintuan_storage" type="text" class="text w70"><em class="add-on">件</em>
            <p id="dialog_edit_pintuan_goods_sold_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i>拼团库存应低于正常商品库存</label></p>
    </dl>
    <dl><dt>成团时限：</dt><dd><input id="dialog_edit_limit_time" type="text" class="text w70"><em class="add-on">小时</em>
            <p id="dialog_edit_pintuan_goods_sold_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i>拼团库存应低于正常商品库存</label></p>
    </dl>
    <dl><dt>成团人数：</dt><dd><input id="dialog_edit_limit_user" type="text" class="text w70"><em class="add-on">人</em>
            <p id="dialog_edit_pintuan_goods_sold_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i>拼团库存应低于正常商品库存</label></p>
    </dl>
    <dl><dt>凑团人数：</dt><dd><input id="dialog_edit_minimum_user" type="text" class="text w70"><em class="add-on">人</em>
            <p id="dialog_edit_pintuan_goods_sold_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i>拼团库存应低于正常商品库存</label></p>
    </dl>
    <dl><dt>购买下限：</dt><dd><input id="dialog_edit_limit_floor" type="text" class="text w70"><em class="add-on">件</em>
            <p id="dialog_edit_pintuan_goods_limit_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i></label></p>
    </dl>
    <dl><dt>购买上限：</dt><dd><input id="dialog_edit_limit_ceilling" type="text" class="text w70"><em class="add-on">件</em>
            <p id="dialog_edit_pintuan_goods_limit_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i></label></p>
    </dl>
    <dl><dt>累计购买上限：</dt><dd><input id="dialog_edit_limit_total" type="text" class="text w70"><em class="add-on">件</em>
            <p id="dialog_edit_pintuan_goods_limit_error" style="display:none;"><label for="dialog_edit_pintuan_goods_error" class="error"><i class='icon-exclamation-sign'></i></label></p>
    </dl>
    <div class="eject_con">
        <div class="bottom"><a id="btn_edit_pintuan_goods_submit" class="submit" href="javascript:void(0);">提交</a></div>
    </div>
</div>
<script id="pintuan_goods_list_template" type="text/html">
<tr class="bd-line">
    <td></td>
    <td><div class="pic-thumb"><a href="<%=goods_url%>" target="_blank"><img src="<%=image_url%>" alt=""></a></div></td>
    <td class="tl"><dl class="goods-name"><dt><a href="<%=goods_url%>" target="_blank"><%=goods_name%></a></dt></dl></td>
    <td><?php echo $lang['currency']; ?><%=goods_price%></td>
    <td><?php echo $lang['currency']; ?><span nctype="pintuan_price"><%=pintuan_price%></span></td>
    <td><span nctype="pintuan_discount"><%=pintuan_discount%></span></td>
    <td><span nctype="pintuan_storage"><%=pintuan_storage%></span></td>
    <td><span nctype="limit_floor"><%=limit_floor%></span></td>
    <td><span nctype="limit_ceilling"><%=limit_ceilling%></span></td>
    <td><span nctype="limit_total"><%=limit_total%></span></td>
    <td class="nscs-table-handle">
    <?php if($output['pintuan_info']['editable']) { ?>
    <span><a nctype="btn_edit_pintuan_goods" class="btn-bluejeans" data-pintuan-goods-id="<%=pintuan_goods_id%>" data-goods-price="<%=goods_price%>" href="javascript:void(0);"><i class="icon-edit"></i><p><?php echo $lang['nc_edit'];?></p></a></span>
        <span><a nctype="btn_del_pintuan_goods" class="btn-grapefruit" data-pintuan-goods-id="<%=pintuan_goods_id%>" href="javascript:void(0);"><i class="icon-trash"></i><p><?php echo $lang['nc_del'];?></p></a></span>
    <?php } ?>
    </td>
</tr>
</script>