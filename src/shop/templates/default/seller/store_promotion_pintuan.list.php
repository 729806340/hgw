<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <a class="ncbtn ncbtn-mint" href="<?php echo urlShop('store_promotion_pintuan', 'pintuan_add');?>"><i class="icon-plus-sign"></i>新增拼团</a>
</div>
<div class="alert alert-block mt10">
  <ul>
    <li>1、点击添加活动按钮可以添加拼团活动，点击管理按钮可以对拼团活动内的商品进行管理</li>
    <li>2、点击删除按钮可以删除拼团活动</li>
  </ul>
</div>
<form method="get">
  <table class="search-form">
    <input type="hidden" name="act" value="store_promotion_pintuan" />
    <input type="hidden" name="op" value="index" />
    <tr>
      <td>&nbsp;</td>
      <th>状态</th>
      <td class="w100"><select name="state">
          <?php if(is_array($output['pintuan_state_array'])) { ?>
          <?php foreach($output['pintuan_state_array'] as $key=>$val) { ?>
          <option value="<?php echo $key;?>" <?php if(intval($key) === intval($_GET['state'])) echo 'selected';?>><?php echo $val;?></option>
          <?php } ?>
          <?php } ?>
        </select></td>
      <th class="w110">拼团名称</th>
      <td class="w160"><input type="text" class="text w150" name="pintuan_name" value="<?php echo $_GET['pintuan_name'];?>"/></td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" />
        </label></td>
    </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr>
      <th class="w30"></th>
      <th class="tl">编号</th>
      <th class="tl">拼团名称</th>
      <th class="w180">开始时间</th>
      <th class="w180">结束时间</th>
      <th class="w180">成团时限（单位：小时）</th>
      <th class="w180">成团人数</th>
      <th class="w180">凑团人数</th>
      <th class="w80">购买下限</th>
      <th class="w80">购买上限</th>
      <th class="w80">累计购买上限</th>
      <th class="w80">状态</th>
      <th class="w160"><?php echo $lang['nc_handle'];?></th>
    </tr>
  </thead>
  <?php if(!empty($output['list']) && is_array($output['list'])){?>
  <?php foreach($output['list'] as $key=>$val){?>
  <tbody id="pintuan_list">
    <tr class="bd-line">
      <td></td>
      <td class="tl">
          <?php echo $val['pintuan_id'];?>
        </td>
        <td class="tl"><dl class="goods-name">
          <dt><?php echo $val['pintuan_name'];?></dt>
        </dl></td>
      <td class="goods-time"><?php echo date("Y-m-d H:i",$val['start_time']);?></td>
      <td class="goods-time"><?php echo date("Y-m-d H:i",$val['end_time']);?></td>
        <td><?php echo intval($val['limit_time']/3600)?></td>
        <td><?php echo $val['limit_user']?></td>
        <td><?php echo $val['minimum_user']?></td>
      <td><?php echo $val['limit_floor'];?></td>
      <td><?php echo $val['limit_ceilling'];?></td>
      <td><?php echo $val['limit_total'];?></td>
      <td><?php echo $val['pintuan_state_text'];?></td>
      <td class="nscs-table-handle tr"><?php if($val['editable']) { ?>
        <span> <a href="index.php?act=store_promotion_pintuan&op=pintuan_edit&pintuan_id=<?php echo $val['pintuan_id'];?>" class="btn-bluejeans"> <i class="icon-edit"></i>
        <p><?php echo $lang['nc_edit'];?></p>
        </a> </span>
        <?php } ?>
        <span> <a href="index.php?act=store_promotion_pintuan&op=pintuan_manage&pintuan_id=<?php echo $val['pintuan_id'];?>" class="btn-mint"> <i class="icon-cog"></i>
        <p><?php echo $lang['nc_manage'];?></p>
        </a> </span> <span> <a href="javascript:;" nctype="btn_del_pintuan" data-pintuan-id=<?php echo $val['pintuan_id'];?> class="btn-grapefruit"> <i class="icon-trash"></i>
        <p><?php echo $lang['nc_delete'];?></p>
        </a> </span></td>
    </tr>
    <?php }?>
    <?php }else{?>
    <tr id="pintuan_list_norecord">
      <td class="norecord" colspan="20"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php }?>
  </tbody>
  <tfoot>
    <?php if(!empty($output['list']) && is_array($output['list'])){?>
    <tr>
      <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    <?php } ?>
  </tfoot>
</table>
<form id="submit_form" action="" method="post" >
  <input type="hidden" id="pintuan_id" name="pintuan_id" value="">
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('[nctype="btn_del_pintuan"]').on('click', function() {
            if(confirm('<?php echo $lang['nc_ensure_del'];?>')) {
                var action = "<?php echo urlShop('store_promotion_pintuan', 'pintuan_del');?>";
                var pintuan_id = $(this).attr('data-pintuan-id');
                $('#submit_form').attr('action', action);
                $('#pintuan_id').val(pintuan_id);
                ajaxpost('submit_form', '', '', 'onerror');
            }
        });
    });
</script> 
