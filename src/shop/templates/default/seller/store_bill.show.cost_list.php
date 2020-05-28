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
            <td class="tc w180">
                <label class="submit-border"><input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" /></label>
                <label class="submit-border"><a class="submit" href="/index.php?act=store_bill&op=export_cost&ob_id=<?php echo $_GET['ob_id']?>" >导出</a></label>
            </td>
    </table>
</form>


<table class="ncsc-default-table">
    <thead>
      <tr>
        <th class="w10"></th>
        <th>促销名称</th>
        <th>促销费用</th>
        <th>分销订单号</th>
        <th>申请日期</th>
        <th>是否结算</th>
      </tr>
    </thead>
    <tbody>
      <?php if(is_array($output['cost_list']) && !empty($output['cost_list'])){?>
      <?php foreach($output['cost_list'] as $cost_info) { ?>
      <tr class="bd-line">
        <td></td>
        <td><?php echo $cost_info['cost_remark'];?></td>
        <td><?php echo ncPriceFormat($cost_info['cost_price']);?></td>
        <td><?php echo $cost_info['fx_order_id']>0?$cost_info['fx_order_id']:'无';?></td>
        <td><?php echo date('Y-m-d',$cost_info['cost_time']);?></td>
        <td><?php echo $cost_info['check_result'];?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <?php if (is_array($output['cost_list']) && !empty($output['cost_list'])) { ?>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
      </tr>
      <?php } ?>
    </tfoot>
  </table>