<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style>
    .store-joinin tbody th{
        text-align: center;
    }
</style>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=config_tuan_list" title="返回列表"><i
                        class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>社区团购</h3>
                <h5>社区团购活动设置与管理</h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span></div>
        <ul>
            <li></li>
        </ul>
    </div>
    <div class="tihuodan">
        <div class="ncap-form-default">
            <div class="title">
                <h3>【<?php echo $output['config_xianshi_info']['config_tuan_name']?>】 提货单</h3>

            </div>
            <dl class="row">
                <dt class="tit">活动标题</dt>
                <dd class="opt"><?php echo $output['config_xianshi_info']['config_tuan_title']?></dd>
            </dl>
            <dl class="row">
                <dt class="tit">起止日期</dt>
                <dd class="opt"><?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_start_time'])?> &nbsp;至&nbsp; <?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_end_time'])?></dd>
            </dl>
            <dl class="row">
                <dt class="tit">发货日期</dt>
                <dd class="opt"><?php echo $output['config_xianshi_info']['send_product_date']  ? date('Y-m-d', $output['config_xianshi_info']['send_product_date']) : ''?></dd>
            </dl>
            <div class="bot">
                <a class="ncap-btn-big" target="_blank" href="index.php?act=tuan_config&op=tihuodan_print&config_tuan_id=<?php echo $output['config_xianshi_info']['config_tuan_id']?>">打印</a>
            </div>
        </div>
        <?php
        $tihuodan = $output['tihuodan'];
        foreach ($tihuodan as $supplier) { ?>
            <div class="supplier">
                <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
                    <thead>

                    <tr>
                        <th colspan="10">供应商：<?php echo $supplier['supplier']; ?></th>
                    </tr>
                    <td></td>
                    </thead>
                    <tbody>

                    <tr>
                        <th>序号</th>
                        <th>商品名称</th>
                        <th>商品数量</th>
                        <th>成本</th>
                    </tr>
                    <?php
                    $items = $supplier['items'];
                    $totalNum = 0;
                    $totalCost = 0;
                    foreach ($items as $k => $item) {
                        $totalNum += $item['goods_num'];
                        $totalCost += $item['cost_price'];
                        ?>
                        <tr>
                            <td><?php echo $k + 1; ?></td>
                            <td><?php echo $item['goods_name']; ?></td>
                            <td><?php echo $item['goods_num']; ?></td>
                            <td><?php echo $item['cost_price']; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>合计</td>
                        <td></td>
                        <td><?php echo $totalNum; ?></td>
                        <td><?php echo $totalCost; ?></td>
                    </tr>
                    </tbody>

                </table>
            </div>
        <?php } ?>
    </div>
</div>
<script>
    //按钮先执行验证再提交表单
    $(function () {
        function insert_editor(file_path) {
            KE.appendHtml('article_content', '<img src="' + file_path + '" alt="' + file_path + '">');
        }
    });

</script>