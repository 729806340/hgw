<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style>
    @page {
        size: auto;  /* auto is the initial value */
        margin: 15mm 20mm; /* this affects the margin in the printer settings */
    }
    .print-page{
        max-width: 1000px;
        margin: 0 auto;
    }
    h1{
        text-align: center;
        font-size: 18px;
    }
    .store-joinin tbody th{
        text-align: center;
    }
</style>
    <div class="print-page">
        <div class="action"><button id="print">立即打印</button></div>
        <?php
        $tihuodan = $output['tihuodan'];
        foreach ($tihuodan as $supplier) { ?>
            <h1>【<?php echo $supplier['supplier']; ?>】提货单</h1>
            <div class="supplier">
                <h2>基本信息</h2>
                <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
                    <tbody>
                        <tr>
                            <th>团购时间：</th>
                            <td><?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_start_time'])?> &nbsp;至&nbsp; <?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_end_time'])?></td>
                            <th>配送时间：</th>
                            <td><?php echo $output['config_xianshi_info']['send_product_date']  ? date('Y-m-d', $output['config_xianshi_info']['send_product_date']) : ''?></td>
                        </tr>
                    </tbody>
                </table>

                <h2>商品信息</h2>
                <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
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
                <div style="page-break-after: always;"></div>
            </div>
        <?php } ?>
    </div>
<script type="text/javascript">
    $(function(){
        $("#print").click(function (e) {
            $(".action").hide();
            window.print();
        });
        $(".print-page").click(function (e) {
            $(".action").show();
        });
    })
</script>