<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="fenxiao_goods2" />
    <input type="hidden" name="op" value="index" />
    <input type="hidden" name="action" value="getlist" />
      <tr>
          <td>&nbsp;</td>
          <th>渠道：</th>
          <td class="w160">
              <select name="fenxiao_id" class="w150">
                  <option value="">请选择</option>
                  <?php foreach ($output['fenxiao_list'] as $k => $v) {?>
                  <option <?php if ($_GET['fenxiao_id'] == $v['id']) echo 'selected';?> value="<?php echo $v['id'];?>"><?php echo $v['member_cn_code'];?></option>
                  <?php }?>
              </select>
          </td>
          <th style="width: 72px;">商品名称：</th>
          <td class="w160"><input type="text" class="text" placeholder="输入商品名称查询" name="goods_name" value="<?php echo $_GET['goods_name']; ?>"/></td>
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
      <th class="w100">渠道</th>
      <th class="w100">一个包裹几个商品</th>
      <th class="w100">运费</th>
      <th class="w100">快递公司</th>
      <th class="w100">供应商ID</th>
      <th class="w100">添加时间</th>
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
                  <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['pid']));?>" target="_blank"><img src="<?php echo thumb($val, 60);?>"/></a>
              </div>
      </td>
      <td><a target="_blank" href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['pid']));?>"><?php echo $val['catename'];?></a></td>
      <td><span><?php echo $output['fenxiao_list'][$val['uid']]['member_cn_code'];?></span></td>
      <td><span><?php echo $val['package_count'];?></span></td>
      <td><span><?php echo $lang['currency'].ncPriceFormat($val['freight_cost']);?></span></td>
      <td><span><?php echo $output['express'][$val['express_id']] ? $output['express'][$val['express_id']]['e_name'] : '未设置';?></span></td>
      <td><span><?php echo $output['store_supplier'][$val['store_supplier_id']] ? $output['store_supplier'][$val['store_supplier_id']]['sup_name'] : '未设置';?></span></td>
      <td><span><?php echo date('Y-m-d H:i:s', $val['ctime']);?></span></td>
        <td><span>
                 <a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="设置" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_goods2&op=save_good_category&id=<?php echo $val['id']; ?>">设置</a>
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
    function delete_fenxiao(id, uid) {
        var url = 'index.php?act=fenxiao_goods&op=index&action=del';
        showDialog('确认要删除吗？', 'confirm', '', function(){
            $.post(url, {id:id,uid:uid}, function (data) {
                location.reload();
            }, 'json');
            // ajaxpost('store_certification_form', '', '', 'onerror')
        });
    }
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