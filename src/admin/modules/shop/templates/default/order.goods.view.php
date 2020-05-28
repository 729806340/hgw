<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
.ncm-goods-gift {
	text-align: left;
}
.ncm-goods-gift ul {
    display: inline-block;
    font-size: 0;
    vertical-align: middle;
}
.ncm-goods-gift li {
    display: inline-block;
    letter-spacing: normal;
    margin-right: 4px;
    vertical-align: top;
    word-spacing: normal;
}
.ncm-goods-gift li a {
    background-color: #fff;
    display: table-cell;
    height: 30px;
    line-height: 0;
    overflow: hidden;
    text-align: center;
    vertical-align: middle;
    width: 30px;
}
.ncm-goods-gift li a img {
    max-height: 30px;
    max-width: 30px;
}
</style>
<div class="ncap-order-details">
	<div class="tabs-panels">
		<div class="goods-info">
          <h4>商品更多信息</h4>
          <table>
            <thead>
              <tr>

                <th>商品支付金额</th>
                <th>商品成本</th>
                <th>商品红包</th>
                <th>进项税</th>
				<th>销项税</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="w100"><?php echo $output['goods_info']['goods_pay_price']; ?></td>
                <td class="w60"><?php echo $output['goods_info']['goods_cost']; ?></td>
                <td class="w80"><?php echo $output['goods_info']['rpt_bill']; ?></td>
				<td class="w80"><?php echo $output['goods_info']['tax_input']; ?></td>
				<td class="w80"><?php echo $output['goods_info']['tax_output']; ?></td>
              </tr>
            </tbody>
          </table>
        </div>
	</div>
</div>