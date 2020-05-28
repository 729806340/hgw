<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w10"></th>
        <th>结算日志id</th>
        <th class="w500">付款日期</th>
        <th>支付备注</th>
      </tr>
    </thead>
    <tbody>
      <?php if (is_array($output['log_list']) && !empty($output['log_list'])) { ?>
      <?php foreach($output['log_list'] as $log_info) { ?>
      <tr class="bd-line">
        <td></td>
        <td><?php echo "{$log_info['obl_id']}";?></td>
        <td><?php echo date("Y-m-d H:i",$log_info['obl_pay_date']);?></td>
        <td><?php echo $log_info['obl_pay_content'];?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <?php if (is_array($output['log_list']) && !empty($output['log_list'])) { ?>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
      </tr>
      <?php } ?>
    </tfoot>
  </table>
<script type="text/javascript">
$(function(){
    $('#ncexport').click(function(){
    	$('input[name="op"]').val('export_order');
    	$('#formSearch').submit();
    });
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('show_bill');
    	$('#formSearch').submit();
    });
});
</script>