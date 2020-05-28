<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
  <a href="javascript:void(0)" class="ncbtn ncbtn-mint" nc_type="dialog" dialog_title="添加资质认证文件" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=store_info&op=certification_add">添加资质认证文件</a>
  </div>


<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w20"></th>
      <th>名称</th>
      <th>描述</th>
      <th>详情</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['store_certifications'])) { ?>
    <?php foreach($output['store_certifications'] as $val) { ?>
    <tr class="bd-line">
      <td></td>
      <td class="w180 tc"><?php echo $val['name']; ?></td>
      <td class="w180 tc"><?php echo $val['class_2_name'] ? '>' : null; ?>&emsp;<?php echo $val['description']; ?></td>
      <td class="tl"><img src="<?php echo $val['content']; ?>" alt=""></td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

