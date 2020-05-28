<?php defined('ByShopWWI') or exit('Access Invalid!');?>
  <form method="get" id="formSearch">
    <table class="search-form">
      <input type="hidden" id='act' name='act' value='store_bill' />
      <input type="hidden" id='op' name='op' value='show_bill' />
      <input type="hidden" name='ob_id' value='<?php echo $_GET['ob_id'];?>' />
      <input type="hidden" name='type' value='<?php echo $_GET['type'];?>' />
      <tr>
        <td>&nbsp;</td>
        <th>订单编号</th>
        <td class="w180"><input type="text" class="text"  value="<?php echo $_GET['query_order_no'];?>" name="query_order_no" /></td>
        <th>客服调整</th>
        <td class="w20">
        	<input type="checkbox" class="checkbox" name="query_log_role" id="query_log_role" value="1" <?php echo $_GET['query_log_role'] == '1'?'checked':''; ?>/>
        </td>
        <td class="tc w200">
        <label class="submit-border"><input type="button" id="ncsubmit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label>
        </td>
    </table>
  </form>
<table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w10"></th>
        <th>调整时间</th>
        <th class="w500">调整信息</th>
        <th>操作角色</th>
        <th>操作人</th>
      </tr>
    </thead>
    <tbody>
      <?php if (is_array($output['log_list']) && !empty($output['log_list'])) { ?>
      <?php foreach($output['log_list'] as $log_info) { ?>
      <tr class="bd-line">
        <td></td>
        <td><?php echo date("Y-m-d H:i",$log_info['log_time']);?></td>
        <td><?php echo "{$log_info['log_msg']}";?></td>
        <td><?php echo $log_info['log_role']==1?'客服':'商家';?></td>
        <td><?php echo $log_info['log_user'];?></td>
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