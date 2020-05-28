<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
    <a href="javascript:void(0)" class="ncbtn ncbtn-aqua" nc_type="dialog" dialog_title="添加渠道" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_channel&op=index&action=add">添加渠道</a>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="fenxiao_channel" />
    <input type="hidden" name="op" value="index" />
      <tr>
          <td>&nbsp;</td>

          <th style="width: 72px;">分销渠道名称</th>
          <td class="w160"><input type="text" class="text" placeholder="输入分销渠道查询" name="channel_name" value="<?php echo $_GET['channel_name']; ?>"/></td>
          <td class="tc w70"><label class="submit-border">
                  <input type="submit" class="submit" value="搜索">
              </label></td>
      </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr nc_type="table_header">
      <th>分销渠道用户ID</th>
      <th>分销渠道名称</th>
      <th>是否贴标</th>
<!--      <th>结算模式</th>-->
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['channel_list'])) { ?>
    <?php foreach ($output['channel_list'] as $val) { ?>
    <tr>

    </tr>
    <tr>
      <td><span><?php echo $val['member_id']; ?></span></td>
      <td><span><?php echo $val['member_cn_code']; ?></span></td>
      <td><span><?php echo $val['is_sign'] == 1 ? '是' : '否'; ?></span></td>
<!--      <td><span>--><?php //if ($val['billing_mode'] == 1) {
//                echo '自己结算';
//              } elseif ($val['billing_mode'] == 2) {
//                echo '汉购网结算';
//              }; ?><!--</span></td>-->
        <td><span>
            <a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="编辑渠道" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_channel&op=index&action=edit&member_fenxiao_id=<?php echo $val['id'];?> ?>">编辑</a>
               </span></td>

    </tr>
    <tr style="display:none;"><td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td></tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
    <?php  if (!empty($output['goods_list'])) { ?>
  <tfoot>
    <tr>
      <td colspan="20"><div class="pagination"> <?php echo $output['show_page']; ?> </div></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js"></script> 
<script>
$(function(){
    //Ajax提示
    $('.tip').poshytip({
        className: 'tip-yellowsimple',
        showTimeout: 1,
        alignTo: 'target',
        alignX: 'center',
        alignY: 'top',
        offsetY: 5,
        allowTipHover: false
    });
});
</script>