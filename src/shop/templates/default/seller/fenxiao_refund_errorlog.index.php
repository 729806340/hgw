<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="fenxiao_refund_errorlog" />
    <input type="hidden" name="op" value="index" />
    <input type="hidden" name="oid" id="oid" value="<?php echo $_GET['oid'];?>" />
      <tr style="display:block">
          <td>&nbsp;</td>
          <th style="width: 72px;"></th>
          <td class="w160"><input type="text" class="text" placeholder="输入订单编号" name="oid" value="<?php echo $_GET['oid']; ?>"/></td>
          <th style="width: 72px;padding-left: 20px;"></th>
          <td class="w160"><input type="text" class="text" placeholder="信息过滤" name="keywords" value="<?php echo $_GET['keywords']; ?>"/></td>
          <th></th>
          <td class="w160">
              <select name="logtype" id="logtype" class="w150">
                  <option value="">--------</option>
                  <option value="order" <?php if ($_GET['logtype'] == 'order') echo 'selected'?>>导入订单</option>
                  <option value="ship" <?php if ($_GET['logtype'] == 'ship') echo 'selected'?>>发货</option>
                  <option value="refund" <?php if ($_GET['logtype'] == 'refund') echo 'selected'?>>退款</option>
              </select>
          </td>
          <td class="tc w70"><label class="submit-border">
                  <input type="submit" class="submit" value="搜索">
              </label></td>
      </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr nc_type="table_header">
      <th class="w180">日志ID</th>
      <th class="w180">分销订单号</th>
      <th class="w180">信息</th>
      <th class="w100">日志时间</th>
      <th class="w100">类型</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['errorlog'])) { ?>
    <?php foreach ($output['errorlog'] as $val) { ?>
            <tr>
                <td><?php echo $val['id'];?></td>
                <td><?php echo $val['orderno'];?></td>
                <td><?php echo $val['error'];?></td>
                <td><?php echo $val['logtime'];?></td>
                <td><?php
                    if ($val['log_type'] == 'order') {
                        echo '导入订单';
                    } elseif ($val['log_type'] == 'ship') {
                        echo '发货';
                    } elseif ($val['log_type'] == 'refund') {
                        echo '退货';
                    }
                    ?></td>
            </tr>
    <tr style="display:none;"><td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td></tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
    <?php  if (!empty($output['errorlog'])) { ?>
  <tfoot>
    <tr>
      <td colspan="20"><div class="pagination"> <?php echo $output['show_page']; ?> </div></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js"></script>
<script charset="utf-8" type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />

<style type="text/css">
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
</style>