<?php defined('ByShopWWI') or exit('access invalid!');?>

<div class="tabmenu">
    <?php include template('layout/submenu');?>
    <a href="javascript:void(0)" class="ncbtn ncbtn-aqua" nc_type="dialog" style="right:80px" dialog_title="导入退款" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_order_refund&op=index&action=importrefund">导入退款</a>
    <a title="下载模板" class="ncbtn ncap-btn-black" href="/shop/resource/refundtpl.zip">下载模板</a>
</div>
<form method="get" action="index.php">
    <table class="search-form">
        <input type="hidden" name="act" value="fenxiao_order_refund" />
        <input type="hidden" name="op" value="index" />
        <tr>
            <td>&nbsp;</td>
            <th style="width: 72px;">订单编号：</th>
            <td class="w160"><input type="text" class="text" placeholder="输入订单编号" name="order_id" value="<?php echo $_GET['order_id']; ?>"/></td>
            <td class="tc w70"><label class="submit-border"><input type="submit" class="submit" value="搜索"></label></td>
        </tr>
    </table>
</form>
<table class="ncsc-default-table">
    <thead>
        <tr nc_type="table_header">
            <th class="w180">退款记录id</th>
            <th class="w180">订单号</th>
            <th class="w180">退款类型/标题</th>
            <th class="w180">售后状态</th>
            <th class="w100">商家处理状态</th>
            <th class="w100">用户申请时间</th>
            <th class="w100">商家退款时间</th>
            <th class="w100">订单金额</th>
            <th class="w100">退款金额</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($output['order_refund'])) { ?>
        <?php foreach ($output['order_refund'] as $val) { ?>
            <tr>
                <td><span><?php echo $val['refund_id'];?></span></td>
                <td><span><?php echo $val['order_sn'];?></span></td>
                <td><span><?php echo $val['refund_type'];?></span></td>
                <td><span><?php echo $val['refund_state']; ?></span></td>
                <td><span><?php echo $val['seller_state']; ?></span></td>
                <td><span><?php echo $val['add_time'];?></span></td>
                <td><span><?php echo $val['seller_time'];?></span></td>
                <td><span><?php echo $lang['currency'].ncpriceformat($val['order_money']); ?></span></td>
                <td><span><?php echo $lang['currency'].ncpriceformat($val['refund_amount']); ?></span></td>
            </tr>
            <tr style="display:none;"><td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td></tr>
        <?php } ?>
    <?php } else { ?>
    <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
    </tbody>
    <?php  if (!empty($output['order_refund'])) { ?>
    <tfoot>
    <tr>
        <td colspan="20"><div class="pagination"> <?php echo $output['show_page']; ?> </div></td>
    </tr>
    </tfoot>
    <?php } ?>
</table>
<script src="<?php echo resource_site_url;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo shop_resource_site_url;?>/js/store_goods_list.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo resource_site_url; ?>/js/jquery-ui/i18n/zh-cn.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo resource_site_url; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<script type="text/javascript" src="<?php echo resource_site_url; ?>/js/jquery-ui-timepicker-addon.js"></script>
<style type="text/css">
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
</style>