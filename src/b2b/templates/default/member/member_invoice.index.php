<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
    <a href="javascript:void(0)" class="ncbtn ncbtn-bittersweet" nc_type="dialog" dialog_title="<?php echo $lang['member_address_new_address'];?>" dialog_id="my_address_edit"  uri="index.php?act=member_invoice&op=address&type=add&invoice_type=1" dialog_width="550" title="<?php echo $lang['member_address_new_address'];?>"><i class="icon-map-marker"></i><?php echo '新增普通发票';?></a>
    <a href="javascript:void(0)" class="ncbtn ncbtn-bittersweet" style="right: 130px;" nc_type="dialog" dialog_title="<?php echo $lang['member_address_new_address'];?>" dialog_id="my_address_edit"  uri="index.php?act=member_invoice&op=address&type=add&invoice_type=2" dialog_width="550" title="<?php echo $lang['member_address_new_address'];?>"><i class="icon-map-marker"></i><?php echo '新增增值税发票';?></a>
    <?php if (C('delivery_isuse')) { ?>
    <!--<a href="javascript:void(0)" class="ncbtn ncbtn-bittersweet" style="right: 100px;" nc_type="dialog" dialog_title="使用代收货（自提）" dialog_id="daisou"  uri="index.php?act=member_address&op=delivery_add" dialog_width="900" title="使用自提服务站"><i class="icon-flag"></i>使用自提服务站</a>-->
    <?php } ?>
  </div>
  <div class="alert alert-success">
    <h4>操作提示：</h4>
    <ul>
      <li>最多可保存20个有效地址</li>
    </ul>
  </div>
  <table class="ncm-default-table" >
    <thead>
      <tr>
        <th class="w80"><?php echo '发票抬头';?></th>
        <th class="w80"><?php echo '单位名称';?></th>
        <th class="w150"><?php echo '发票类型';?></th>

        <th class="w120"><?php echo $lang['member_address_phone'];?>/<?php echo $lang['member_address_mobile'];?></th>
        <th class="w110"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <?php if(!empty($output['invoice_list']) && is_array($output['invoice_list'])){?>
    <tbody>
      <?php foreach($output['invoice_list'] as $key => $value){?>
      <tr class="bd-line">
        <td><?php echo $value['inv_title'];?></td>
        <td><?php echo $value['inv_company'];?></td>
        <td><?php echo $value['inv_state'];?></td>

        <td><?php if ($address['is_default'] == '1') {?>
          <i class="icon-ok-sign green" style="font-size: 18px;"></i>默认地址
          <?php } ?></td>
        <td class="ncm-table-handle"><span>
          <?php if (intval($address['dlyp_id'])) { ?>
          <a href="javascript:void(0);" class="btn-bluejeans" dialog_id="daisou" dialog_width="900" dialog_title="<?php echo $lang['member_address_edit_address'];?>" nc_type="dialog" uri="<?php echo B2B_SITE_URL;?>/index.php?act=member_purchase&op=delivery_add&id=<?php echo $address['inv_id'];?>"><i class="icon-edit"></i>
          <p><?php echo $lang['nc_edit'];?></p>
          </a>
          <?php } else { ?>
          <a href="javascript:void(0);" class="btn-bluejeans" dialog_id="my_address_edit" dialog_width="550" dialog_title="<?php echo $lang['member_address_edit_address'];?>" nc_type="dialog" uri="<?php echo B2B_SITE_URL;?>/index.php?act=member_invoice&op=address&type=edit&id=<?php echo $value['inv_id'];?>"><i class="icon-edit"></i>
          <p><?php echo $lang['nc_edit'];?></p>
          </a>
          <?php } ?>
          </span> <span><a href="javascript:void(0)" class="btn-grapefruit" onclick="ajax_get_confirm('<?php echo $lang['nc_ensure_del'];?>', '<?php echo B2B_SITE_URL;?>/index.php?act=member_invoice&op=address&id=<?php echo $value['inv_id'];?>');"><i class="icon-trash"></i>
          <p><?php echo $lang['nc_del'];?></p>
          </a></span></td>
      </tr>
      <?php }?>
      <?php }else{?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></div></td>
      </tr>
      <?php }?>
    </tbody>
  </table>
</div>
<?php if (C('delivery_isuse')) { ?>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js" type="text/javascript"></script>
<?php } ?>
