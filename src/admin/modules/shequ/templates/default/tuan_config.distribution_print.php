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
        <h1>【<?php echo $output['dingtang_info']['tz_name']; ?>】配送单</h1>
        <div class="supplier">
            <h2>基本信息</h2>
            <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
                <tbody>
                <tr>
                    <th>名称：</th>
                    <td><?php echo  $output['dingtang_info']['tz_name']?></td>
                    <th>电话：</th>
                    <td><?php echo $output['dingtang_info']['tz_phone']?></td>
                </tr>
                <tr>
                    <th>地址：</th>
                    <td><?php echo $output['dingtang_info']['address']?> </td>
                    <th>门牌号：</th>
                    <td><?php echo $output['dingtang_info']['building'] ?></td>
                </tr>
                <tr>
                    <th>司机：</th>
                    <td><?php echo $output['dingtang_info']['driver_name']?>  (<?php echo $output['dingtang_info']['driver_phone'] ?>)</td>
                    <th>车牌号：</th>
                    <td><?php echo $output['dingtang_info']['driver_car_number'] ?></td>
                </tr>
                <tr>
                    <th>团购时间：</th>
                    <td><?php echo date('Y-m-d H:i', $output['dingtang_info']['start_time'])?> &nbsp;至&nbsp; <?php echo date('Y-m-d H:i', $output['dingtang_info']['end_time'])?></td>
                    <th>配送时间：</th>
                    <td><?php echo $output['dingtang_info']['send_product_date']  ? date('Y-m-d', $output['dingtang_info']['send_product_date']) : ''?></td>
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
                    <th>核对</th>
                </tr>
                <?php
                $totalNum = 0;
                $totalCost = 0;
                foreach ($output['items'] as $k => $item) {
                    $totalNum += $item['goods_num'];
                    $totalCost += $item['cost_price'];
                    ?>
                    <tr>
                        <td><?php echo $k + 1; ?></td>
                        <td><?php echo $item['goods_name']; ?></td>
                        <td><?php echo $item['goods_num']; ?></td>
                        <td></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>合计</td>
                    <td></td>
                    <td><?php echo $totalNum; ?></td>
                    <td></td>
                </tr>
                </tbody>

            </table>

            <h2>商品分配</h2>
            <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
                <tbody>

                <tr>
                    <th>序号</th>
                    <th>团员(电话)</th>
                    <th>商品名称</th>
                    <th>数量</th>
                    <th>发放</th>
                </tr>
                <?php
                $totalNum = 0;
                foreach ($output['order_goods_list'] as $key => $valss) {
                    $totalNum += $valss['goods_num'];
                    ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $valss['buyer_name'];?> (<?php echo $valss['buyer_phone'];?>)</td>
                        <td><?php echo $valss['goods_name']; ?></td>
                        <td><?php echo $valss['goods_num']; ?></td>
                        <td></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>合计</td>
                    <td></td>
                    <td></td>
                    <td><?php echo $totalNum; ?></td>
                    <td></td>
                </tr>
                </tbody>

            </table>
            <div style="page-break-after: always;"></div>
        </div>

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