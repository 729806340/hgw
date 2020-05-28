<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=config_tuan_list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
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

    <div class="search-goods">
        <h3>选择商品添加</h3>
        <h5>商品关键字：</h5>
        <input id="txt_goods_name" type="text" class="txt w200" name="">
        <a id="btn_mb_special_goods_search" class="ncap-btn" href="javascript:;" style="vertical-align: top; margin-left: 5px;">搜索</a>
        <div id="mb_special_goods_list"></div>
    </div>

    <table class="ncsc-default-table">
        <thead>
        <tr>
            <th class="w10"></th>
            <th class="w50"></th>
            <th class="tl">商品名称</th>
            <th class="w90">商品价格</th>
            <th class="w120">商品佣金</th>
        </tr>
        </thead>
        <tbody id="xianshi_goods_list">
        <?php if (!empty($output['goods_list'])) {?>
            <?php foreach ($output['goods_list'] as $val) {?>
                <tr class="bd-line">
                    <td></td>
                    <td></td>
                    <td class="tl"><dl class="goods-name"><dt><?php echo $val['goods_name'];?></dt></dl></td>
                    <td><?php echo $lang['currency']; ?><?php echo $val['goods_price'];?></td>
                    <td><?php echo $lang['currency']; ?><?php echo $val['return_price'];?></td>
                </tr>
            <?php }?>
        <?php }?>
        <tr id="xianshi_goods_list_norecord" style="display:none">
            <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
        </tr>
        </tbody>
        <tfoot>
        <?php if(!empty($output['goods_list'])){?>
            <tr>
                <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
            </tr>
        <?php } ?>
        </tfoot>
    </table>
</div>

<script>
$(function(){

    $('#btn_mb_special_goods_search').on('click', function() {
        var url = '<?php echo urlAdminShop('tuan_config', 'goods_select');?>';
        var keyword = $('#txt_goods_name').val();
        var tuan_config_id = "<?php echo $output['tuan_config_id'];?>";
        $('#mb_special_goods_list').load(url + '&' + $.param({goods_name: keyword,tuan_config_id: tuan_config_id}));
    });

/*    $('#mb_special_goods_list').on('click', '[nctype="btn_add_goods"]', function() {
        var item = {};
        item.goods_id = $(this).attr('data-goods-id');
        item.goods_name = $(this).attr('data-goods-name');
        item.goods_price = $(this).attr('data-goods-price');
        item.goods_image = $(this).attr('data-goods-image');
        var html = template.render('item_goods_template', item);
        $('[nctype="item_content"]').append(html);
    });*/
});
</script>
