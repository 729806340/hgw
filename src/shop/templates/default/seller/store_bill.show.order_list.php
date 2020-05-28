<?php defined('ByShopWWI') or exit('Access Invalid!');?>
  <form method="get" id="formSearch">
    <table class="search-form">
      <input type="hidden" id='act' name='act' value='store_bill' />
      <input type="hidden" id='op' name='op' value='show_bill' />
      <input type="hidden" name='ob_id' value='<?php echo $_GET['ob_id'];?>' />
      <input type="hidden" name='type' value='<?php echo $_GET['type'];?>' />
      <tr>
        <td>&nbsp;</td>
        <th>是否结算</th>
        <td class="w160">
          <select name="check_result">
            <option  value="">请选择</option>
            <option  value="已结算">已结算</option>
            <option  value="未结算">未结算</option>
            <option  value="未对账">未对账</option>
            <option  value="第1次结算">第1次结算</option>
            <option  value="第2次结算">第2次结算</option>
            <option  value="第3次结算">第3次结算</option>
          </select>
        </td>

        <th>订单编号</th>
        <td class="w180"><input type="text" class="text"  value="<?php echo $_GET['query_order_no'];?>" name="query_order_no" /></td>
        <th>成交时间</th>
        <td class="w180">
        	<input type="text" class="text w70" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>"/>
          &#8211;
          <input type="text" class="text w70" name="query_end_date" id="query_end_date" value="<?php echo $_GET['query_end_date']; ?>"/>
        </td>
        <td class="tc w200">
        <label class="submit-border"><input type="button" id="ncsubmit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label>
        <label class="submit-border"><input type="button" id="ncexport" class="submit" value="导出" /></label>
        </td>
    </table>
  </form>
<table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w10"></th>
        <th>订单编号</th>
        <th>是否结算</th>
        <th>下单时间</th>
        <th>成交时间</th>
        <th>订单金额</th>
        <th>运费</th>
        <th>佣金金额</th>
        <th>平台红包</th>
        <th><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (is_array($output['order_list']) && !empty($output['order_list'])) { ?>
      <?php foreach($output['order_list'] as $order_info) { ?>
      <tr class="bd-line">
        <td></td>
        <td class="w90"><?php echo $order_info['order_sn'];?></td>
        <td class="w90"><?php echo $order_info['check_result'];?></td>
        <td><?php echo date("Y-m-d",$order_info['add_time']);?></td>
        <td><?php echo date("Y-m-d",$order_info['finnshed_time']);?></td>
        <td><?php echo $output['bill_info']['ob_store_manage_type']=='platform'?ncPriceFormat($order_info['order_amount']):ncPriceFormat($order_info['cost_amount']);?></td>
        <td><?php echo ncPriceFormat($order_info['shipping_fee']);?></td>
        <td><?php echo $order_info['commis_amount'];?></td>
        <td><?php echo ncPriceFormat($order_info['rpt_bill']);?></td>
        <td>
       	<a target="_blank" href="index.php?act=store_order&op=show_order&order_id=<?php echo $order_info['order_id'];?>"><?php echo $lang['nc_view'];?></a>
          <?php if( $output['bill_info']['ob_state'] == BILL_STATE_CREATE) { ?>
       	<a target="_blank" href="javascript:ajax_form('edit-bill-data','调整结算信息','index.php?act=store_bill&op=edit_order&ob_id=<?php echo $output['bill_info']['ob_id'];?>&order_id=<?php echo $order_info['order_id'];?>',1020,0)">调整</a>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <?php if (is_array($output['order_list']) && !empty($output['order_list'])) { ?>
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