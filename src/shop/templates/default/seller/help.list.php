<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w10"></th>
      <th class="w160 tl">标题</th>
      <th class="w100 tl"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($output['list'])>0) { ?>
    <?php foreach($output['list'] as $val) { ?>
    <tr class="bd-line">
      <td class="tl"><?php /*echo $val['article_id'];*/?></td>
      <td class="tl"><?php echo $val['article_title'];?></td>
      <td class="tl">
        <a href="index.php?act=store_help&op=show&article_id=<?php echo $val['article_id'];?>" class="btn-bittersweet"><i class="icon-eye-open"></i>
            <p>查看</p>
        </a>
      </td>
    </tr>
    <?php }?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <?php if (count($output['list'])>0) { ?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page'];?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<script>
	$(function(){
	    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
	    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
	});
</script>
