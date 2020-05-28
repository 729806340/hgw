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
    $dingtang_batch_info = $output['dingtang_batch_info'];
    foreach ($dingtang_batch_info as $value) { ?>
        <h1>【<?php echo $value['tz_name']; ?>】配送单</h1>
        <div class="supplier">
            <h2>基本信息</h2>
            <table  border="0" cellpadding="0" cellspacing="0"  class="store-joinin">
                <tbody>
                <tr>
                    <th>名称：</th>
                    <td><?php echo  $value['tz_name']?></td>
                    <th>电话：</th>
                    <td><?php echo $value['tz_phone']?></td>
                </tr>
                <tr>
                    <th>地址：</th>
                    <td><?php echo $value['address']?> </td>
                    <th>门牌号：</th>
                    <td><?php echo $value['building'] ?></td>
                </tr>
                <tr>
                    <th>司机：</th>
                    <td><?php echo $value['driver_name']?>  (<?php echo $value['driver_phone'] ?>)</td>
                    <th>车牌号：</th>
                    <td><?php echo $value['driver_car_number'] ?></td>
                </tr>
                <tr>
                    <th>团购时间：</th>
                    <td><?php echo date('Y-m-d H:i', $value['start_time'])?> &nbsp;至&nbsp; <?php echo date('Y-m-d H:i', $value['end_time'])?></td>
                    <th>配送时间：</th>
                    <td><?php echo $value['send_product_date']  ? date('Y-m-d', $value['send_product_date']) : ''?></td>
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
                $items = $value['items'];
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
                $goods_list = $value['goods_list'];
                $totalNum = 0;
                foreach ($goods_list as $key => $val) {
                    $totalNum += $val['goods_num'];
                    ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $val['buyer_name'];?> (<?php echo $val['buyer_phone'];?>)</td>
                        <td><?php echo $val['goods_name']; ?></td>
                        <td><?php echo $val['goods_num']; ?></td>
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