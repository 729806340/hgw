<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script>
  SITEURL = '<?php echo ADMIN_SITE_URL;?>';
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="<?php echo getReferer();?>" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>团长结算管理 - 账单明细 </h3>
        <h5>实物商品订单结算索引及团长账单表</h5>
      </div>
    </div>
  </div>
  <?php if (floatval($output['bill_info']['ob_order_book_totals']) > 0) { ?>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>未退定金金额是预定订单中已经被取消，但系统未退定金的总金额</li>
      <li>默认未退定金金额会累加到平台应付金额中</li>
    </ul>
  </div>
  <?php } ?>
  <div class="ncap-form-default">
    <div class="title">
      <h3>团长 - <?php echo $output['bill_info']['ob_store_name'];?>（ID：<?php echo $output['bill_info']['ob_store_id'];?>） 结算单</h3>

    </div>
    <dl class="row">
      <dt class="tit"><?php echo $lang['order_time_from'];?>结算单号</dt>
      <dd class="opt"><?php echo $output['bill_info']['ob_id'];?>&emsp;<?php echo $output['bill_info']['ob_no'] ? '(原结算单号：'.$output['bill_info']['ob_no'].')' : null;?>
      </dd>
    </dl>
    <dl class="row">
      <dt class="tit">起止日期</dt>
      <dd class="opt"><?php echo date('Y-m-d',$output['bill_info']['ob_start_date']);?> &nbsp;至&nbsp; <?php echo date('Y-m-d',$output['bill_info']['ob_end_date']);?></dd>
    </dl>
    <dl class="row">
      <dt class="tit">出账日期</dt>
      <dd class="opt"><?php echo date('Y-m-d',$output['bill_info']['ob_create_date']);?></dd>
    </dl>
    <dl class="row">
      <dt class="tit">平台应付金额</dt>
      <dd class="opt">
          <?php echo $output['platform_amount']['payable_amount']; ?>
      </dd>
    </dl>
    <dl class="row">
      <dt class="tit">结算状态</dt>
      <dd class="opt"><?php echo shequBillState($output['bill_info']['ob_state']);?>
        <?php if ($output['bill_info']['ob_state'] == BILL_STATE_SUCCESS){?>
        &emsp;结算日期<?php echo $lang['nc_colon'];?><?php echo date('Y-m-d',$output['bill_info']['ob_pay_date']);?>，结算备注<?php echo $lang['nc_colon'];?><?php echo $output['bill_info']['ob_pay_content'];?>
        <?php }?>
      </dd>
    </dl>
    <div class="bot">

        <?php if ($output['bill_info']['ob_state'] == BILL_STATE_STORE_COFIRM){?>
      <a class="ncap-btn-big ncap-btn-green mr10" onclick="if (confirm('审核后将无法撤销，进入下一步付款环节，确认审核吗?')){return true;}else{return false;}" href="index.php?act=shequ_bill&op=bill_check&ob_id=<?php echo $_GET['ob_id'];?>">审核</a>
      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_HANGO&&in_array($output['gname'],array('汉购网商务','超级管理员'))){?>
            <a class="ncap-btn-big ncap-btn-green mr10" style="display: none;" onclick="ajax_get_confirm('确定要重建账单吗?重建账单后若有数据变更，将返回商家重新审核。','modules/shop/index.php?act=shequ_bill&op=rebuild_bill&ob_id=<?php echo $_GET['ob_id'];?>')">重建账单</a>
        <a class="ncap-btn-big ncap-btn-green mr10" onclick="ajax_get_confirm('提交后将无法撤销，进入下一步财务支付，确认提交吗?','modules/shop/index.php?act=shequ_bill&op=approve_hango&ob_id=<?php echo $_GET['ob_id'];?>')">发送到财务支付</a>

      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_FIRE_PHONIX&&$output['gname']=='公司商务'){?>
        <a class="ncap-btn-big ncap-btn-green mr10" onclick="ajax_get_confirm('确定要重建账单吗?重建账单后若有数据变更，将返回商家重新审核。?','modules/shop/index.php?act=shequ_bill&op=rebuild_bill&ob_id=<?php echo $_GET['ob_id'];?>')">重建账单</a>
            <a class="ncap-btn-big ncap-btn-green mr10" href="javascript:;" onclick="ajax_form('upload','批量修改','index.php?act=shequ_bill&op=batch_edit&ob_id=<?php echo $_GET['ob_id'];?>',640,0);">批量修改</a>
            <a class="ncap-btn-big ncap-btn-green mr10" href="javascript:;" onclick="ajax_form('upload','上传附件','index.php?act=shequ_bill&op=add_attachment&ob_id=<?php echo $_GET['ob_id'];?>',640,0);">上传附件</a>

        <a class="ncap-btn-big ncap-btn-green mr10" onclick="ajax_get_confirm('打回前请上传需要补齐凭证的订单数据附件（建议使用Excel），确认打回吗?','modules/shop/index.php?act=shequ_bill&op=reject_com&ob_id=<?php echo $_GET['ob_id'];?>')">打回补齐凭证</a>
        <a class="ncap-btn-big ncap-btn-green mr10" onclick="ajax_get_confirm('审核后将无法撤销，进入下一步付款环节，确认审核吗?','modules/shop/index.php?act=shequ_bill&op=approve_com&ob_id=<?php echo $_GET['ob_id'];?>')">审核完成</a>
      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_FIRE_PHONIX&&$output['gname']=='超级管理员'){?>
      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_CEO&&$output['gname']=='总经理'){?>
        <a class="ncap-btn-big ncap-btn-green mr10" onclick="ajax_get_confirm('同意后将无法撤销，进入下一步付款环节，确认审核吗?','modules/shop/index.php?act=shequ_bill&op=approve_pay&ob_id=<?php echo $_GET['ob_id'];?>')">同意支付</a>
      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_SYSTEM_CHECK || $output['bill_info']['ob_state'] == BILL_STATE_PART_PAY){?>
      <a target="_blank" class="ncap-btn-big ncap-btn-blue mr10" href="index.php?act=shequ_bill&op=bill_pay&ob_id=<?php echo $_GET['ob_id'];?>"><?php echo $lang['nc_exdport'];?>付款完成</a>
<!--      <a target="_blank" class="ncap-btn-big ncap-btn-blue mr10" href="index.php?act=shequ_bill&op=bill_part_pay&ob_id=--><?php //echo $_GET['ob_id'];?><!--">--><?php //echo $lang['nc_exdport'];?><!--部分付款</a>-->
      <?php }elseif ($output['bill_info']['ob_state'] == BILL_STATE_SUCCESS){?>
      <a class="ncap-btn-big" target="_blank" href="index.php?act=shequ_bill&op=bill_print&ob_id=<?php echo $_GET['ob_id'];?>">打印</a>
      <?php }?>
    </div>
  </div>
  <div class="homepage-focus" nctype="sellerTplContent">
    <div class="title">
      <ul class="tab-base nc-row">
        <li><a href="index.php?act=shequ_bill&op=show_bill&query_type=order&ob_id=<?php echo $_GET['ob_id'] ;?>" class="<?php echo ($_GET['query_type'] == '' || $_GET['query_type'] == 'order') ? 'current' : '';?>">订单列表</a></li>
        <li><a href="index.php?act=shequ_bill&op=show_bill&query_type=refund&ob_id=<?php echo $_GET['ob_id'] ;?>" class="<?php echo $_GET['query_type'] == 'refund' ? 'current' : '';?>">退单列表</a></li>
        <li><a href="index.php?act=shequ_bill&op=show_bill&query_type=pay_log&ob_id=<?php echo $_GET['ob_id'] ;?>" class="<?php echo $_GET['query_type'] == 'pay_log' ? 'current' : '';?>">结算记录</a></li>
      </ul>
    </div>
    <?php include template($output['tpl_name'], 'shop');?>
  </div>
</div>
