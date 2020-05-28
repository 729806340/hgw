<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script>
    SITEURL = '<?php echo ADMIN_SITE_URL;?>';
</script>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="<?php echo getReferer();?>" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>渠道结算管理 - 账单明细 </h3>
                <h5>实物商品订单结算索引及商家账单表</h5>
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
            <h3>渠道 - <?php echo $output['bill_info']['ob_channel_name'];?>（ID：<?php echo $output['bill_info']['ob_channel_id'];?>） 结算单</h3>

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
                <?php
                    echo ncPriceFormat($output['bill_info']['ob_result_totals']);?> = <?php echo ncPriceFormat($output['bill_info']['ob_order_totals']);?> (订单成本金额)
                    - <?php echo ncPriceFormat($output['bill_info']['ob_order_return_totals']);?> (退单成本金额)
                    <?php if (floatval($output['bill_info']['ob_order_book_totals']) > 0) { ?>
                        + <?php echo ncPriceFormat($output['bill_info']['ob_order_book_totals']);?> (未退定金金额)
                    <?php }
                ?>
            </dd>
        </dl>
        <div class="bot">
            <a class="ncap-btn-big" target="_blank" href="index.php?act=channel_bill&op=export_profit&ob_id=<?php echo $_GET['ob_id'];?>">导出低/负毛利订单</a>
        </div>
    </div>
    <div class="homepage-focus" nctype="sellerTplContent">
        <div class="title">
            <ul class="tab-base nc-row">
                <li><a href="index.php?act=channel_bill&op=show_bill&query_type=order&ob_id=<?php echo $_GET['ob_id'] ;?>" class="<?php echo ($_GET['query_type'] == '' || $_GET['query_type'] == 'order') ? 'current' : '';?>">订单列表</a></li>
                <li><a href="index.php?act=channel_bill&op=show_bill&query_type=refund&ob_id=<?php echo $_GET['ob_id'] ;?>" class="<?php echo $_GET['query_type'] == 'refund' ? 'current' : '';?>">退单列表</a></li>
            </ul>
        </div>
        <?php include template($output['tpl_name'], 'shop');?>
    </div>
</div>
