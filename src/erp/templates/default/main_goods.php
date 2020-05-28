<div style="width: 350px; margin-top: 15px;padding: 10px 10px; border: 1px dashed  black" class="wrapper">
    <div class="goods-info">
        <h4>商品信息</h4>
        <table style="font-size: 10px;">
            <tr>
                <td><img src="<?php echo thumb($output['goods_info'], '60'); ?>"></td>
                <td><?php echo $output['goods_info']['goods_name'];?></td>
                <td><?php echo "{$output['goods_info']['goods_price']}（销售价） -- {$output['goods_info']['goods_promotion_price']}（促销价）"?></td>
            </tr>
        </table>
    </div>
</div>