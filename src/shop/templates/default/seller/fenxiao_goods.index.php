<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="fenxiao_goods" />
    <input type="hidden" name="op" value="index" />
      <tr>
          <td>&nbsp;</td>

          <th style="width: 72px;">分销商品名称</th>
          <td class="w160"><input type="text" class="text" placeholder="输入分销商品名称查询" name="goods_name" value="<?php echo $_GET['goods_name']; ?>"/></td>
          <th>店铺名称</th>
          <td class="w160"><input type="text" class="text" placeholder="输入店铺名称查询" name="store_name" value="<?php echo $_GET['store_name']; ?>"/></td>
          <td class="tc w70"><label class="submit-border">
                  <input type="submit" class="submit" value="搜索">
              </label></td>
      </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr nc_type="table_header">
      <th><?php echo $lang['store_goods_album_goods_pic'];?></th>
      <th><?php echo $lang['store_goods_index_goods_name'];?></th>
      <th class="w180"><?php echo $lang['store_goods_index_price'];?></th>
      <th class="w100"><?php echo $lang['store_goods_index_store_name'];?></th>
      <th class="w100"><?php echo $lang['store_goods_index_huopin_id'];?></th>
      <th class="w100"><?php echo $lang['store_goods_index_state'];?></th>
      <th class="w100"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['goods_list'])) { ?>
    <?php foreach ($output['goods_list'] as $val) { ?>
    <tr>

    </tr>
    <tr>
      <td class="tl"><dl class="goods-name">
              <div class="pic-thumb">
                  <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['goods_id']));?>" target="_blank"><img src="<?php echo thumb($val, 60);?>"/></a>
              </div>
      </td>
      <td><a target="_blank" href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['goods_id']));?>"><?php echo $val['goods_name'];?></a></td>
      <td><span><?php echo $lang['currency'].ncPriceFormat($val['goods_price']); ?></span></td>
      <td><span><?php echo $val['store_name']; ?></span></td>
      <td><span><?php echo $val['goods_id']; ?></span></td>
      <td><span><?php echo $lang['store_goods_index_show'];?></span></td>
        <td><span>
                 <a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="选择渠道" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_goods&op=index&action=add&goods_name=<?php echo $val['goods_name'];?>&pid=<?php echo $val['goods_id']; ?>&gid=<?php echo $val['goods_commonid']; ?>">添加为分销商品</a>
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