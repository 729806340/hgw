<link href="<?php echo SHOP_TEMPLATES_URL?>/css/base.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL?>/css/seller_center.css" rel="stylesheet" type="text/css">
<style>
    body {
        min-width:800px;
    }
    input[type=text] {
        height: 30px;
    }
</style>
<div class="eject_con">
    <div id="warning"></div>
    <?php if(!$output['error']) {?>
        <form method="get" action="index.php?act=jdy_mapping&op=mapping">
            <input type="hidden" name="act" value="jdy_mapping">
            <input type="hidden" name="op" value="mapping">
            <dl>
                <dt>商品:</dt>
                <dd>
                    <input type="hidden" id="mapping-goods-id" name="goods_id" value="<?php echo $output['goods_info']['goods_id']; ?>">
                    商品名称:<?php echo $output['goods_info']['goods_name']; ?>
                    商品价格: <span style="color: red">¥<?php echo $output['goods_info']['goods_price']; ?></span>
                    商品库存: <span style="color: red"><?php echo $output['goods_info']['goods_storage']; ?></span>
                </dd>
            </dl>
            <dl>
                <dt>jdy商品：</dt>
                <dd>
                    <input type="text" placeholder="jdy商品名称" class="text w140" name="item_name" value="<?php echo $_GET['item_name']; ?>"/>
                    <label class="submit-border">
                        <input class="submit" type="submit" value="搜索" />
                    </label>
                </dd>
            </dl>
        </form>
        <table class="ncsc-default-table" id="goods-search-result">
            <thead>
            <tr>
                <th class="w80">商品编号</th>
                <th class="w160">商品名称</th>
                <th class="w60">单位</th>
                <th class="w160">供应商</th>
                <!--<th class="w40">倍率</th>-->
                <th class="w80">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if(!empty($output['jdy_goods_list'])) {?>
                <?php foreach ($output['jdy_goods_list'] as $val) {?>
                    <tr class="bd-line">
                        <td><?php echo $val['item_code']?></td>
                        <td><?php echo $val['item_name']?></td>
                        <td><?php echo $val['unit_name']?></td>
                        <td>
                            <div style="width: 120px;position: relative;margin: 0 auto">
                                <input type="hidden" value="0" hg_type="supplier_unique_id" name="supplier_unique_id" />
                                <input type="text" style="width: 120px;height: 28px;box-sizing: border-box;" />
                                <img class="get-supplier" style="width: 26px; height: 26px; position: absolute; top: 2px; right: 0;" src="<?php echo SHOP_SITE_URL.DS.'resource'.DS.'img'.DS.'mapping_more.jpg';?>">
                            </div>
                        </td>
                       <!-- <td>
                            <input type="text" hg_type='multiple' style="width:60px;box-sizing: border-box;" name="multiple" value="1">
                        </td>-->
                        <td class="hgbtn-mini hgbtn-bluejeansjeans mt10">
                            <span style="cursor: pointer;"  class="item-mapping" data-item-id="<?php echo $val['item_id']?>" >映射</span>
                        </td>
                    </tr>
                <?php }?>
               <?php } else {?>
                <tr>
                    <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span>暂无符合条件的数据记录</span></div></td>
                </tr>
            <?php }?>
            </tbody>
            <tfoot>
            <?php if(!empty($output['jdy_goods_list'])) {?>
                <tr>
                    <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
                </tr>
            <?php }?>
            </tfoot>
        </table>
       <?php } else {?>
        <p style="line-height:80px;text-align:center"><?php echo $output['error']; ?></p>
   <?php }?>
</div>
<div style="display: none" id="layer_supplier_name"></div>
<div style="display: none" id="layer_supplier_unique_id"></div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/layer/layer.js"></script>
<script type="text/javascript">
    function show_supplier(element) {
        var supplier_name = $('#layer_supplier_name').html();
        var supplier_unique_id = $('#layer_supplier_unique_id').html();
        element.prev().attr('value', supplier_name);
        element.prev().prev().attr('value', supplier_unique_id);
    }
    $(function(){
        $('#goods-search-result').on('click', '.get-supplier', function () {
            var v_value = $(this);
            layer.ready(function(){
                layer.open({
                    type: 2,
                    title: '供应商',
                    maxmin: true,
                    area: ['90%', '90%'],
                    content: "index.php?act=jdy_mapping&op=supplier",
                    end: function(){
                        show_supplier(v_value);
                    }
                });
            });
            return false;
        });

        $('#goods-search-result').on('click','.item-mapping',function (e) {
            var $this = $(this);
            var goods_id = $('#mapping-goods-id').val();
            var item_id = $this.attr('data-item-id');
            //var multiple = $(this).parent().prev().find("input[hg_type='multiple']").val();
            var multiple = 1;
            var supplier_unique_id = $(this).parent().prev().find("input[hg_type='supplier_unique_id']").val();
            $.post("index.php?act=jdy_mapping&op=mapping_save",{
                goods_id: goods_id,
                item_id: item_id,
                supplier_unique_id: supplier_unique_id,
                multiple : multiple
            }, function(data) {
                if(data.result) {
                    parent.layer.closeAll();
                } else {
                    layer.alert(data.message);
                }
            }, 'json');
        });
    });
</script>