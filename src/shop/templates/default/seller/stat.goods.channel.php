<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<div class="tabmenu">
    <?php include template('layout/submenu'); ?>
</div>
<div class="alert mt10">
    <ul class="mt5">
        <li>1、分销渠道数据统计</li>
    </ul>
</div>
<form method="get" action="index.php" target="_self">
    <table class="search-form">
        <input type="hidden" name="act" value="statistics_goods"/>
        <input type="hidden" name="op" value="channel"/>
        <input type="hidden" name="goods_id" value="<?php
        echo $output['goods_info']['goods_id']; ?>"/>
        <tr>
            <td class="tr">
                <div class="fr">
                    <label class="submit-border"><input type="submit" class="submit"
                                                        value="<?php echo $lang['nc_common_search']; ?>"/></label>
                </div>
                <div class="fr">
                    <div class="fl" style="margin-right:3px;">
                        <select name="search_type" id="search_type" class="querySelect">
                            <option value="day" <?php echo $output['search_arr']['search_type'] == 'day' ? 'selected' : ''; ?>>
                                按照天统计
                            </option>
                            <option value="week" <?php echo $output['search_arr']['search_type'] == 'week' ? 'selected' : ''; ?>>
                                按照周统计
                            </option>
                            <option value="month" <?php echo $output['search_arr']['search_type'] == 'month' ? 'selected' : ''; ?>>
                                按照月统计
                            </option>
                        </select>
                    </div>
                    <div id="searchtype_day" style="display:none;" class="fl">
                        <input type="text" class="text w70" name="search_time" id="search_time"
                               value="<?php echo @date('Y-m-d', $output['search_arr']['day']['search_time']); ?>"/><label
                                class="add-on"><i class="icon-calendar"></i></label>
                    </div>
                    <div id="searchtype_week" style="display:none;" class="fl">
                        <select name="searchweek_year" class="querySelect">
                            <?php foreach ($output['year_arr'] as $k => $v) { ?>
                                <option value="<?php echo $k; ?>" <?php echo $output['search_arr']['week']['current_year'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php } ?>
                        </select>
                        <select name="searchweek_month" class="querySelect">
                            <?php foreach ($output['month_arr'] as $k => $v) { ?>
                                <option value="<?php echo $k; ?>" <?php echo $output['search_arr']['week']['current_month'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php } ?>
                        </select>
                        <select name="searchweek_week" class="querySelect">
                            <?php foreach ($output['week_arr'] as $k => $v) { ?>
                                <option value="<?php echo $v['key']; ?>" <?php echo $output['search_arr']['week']['current_week'] == $v['key'] ? 'selected' : ''; ?>><?php echo $v['val']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="searchtype_month" style="display:none;" class="fl">
                        <select name="searchmonth_year" class="querySelect">
                            <?php foreach ($output['year_arr'] as $k => $v) { ?>
                                <option value="<?php echo $k; ?>" <?php echo $output['search_arr']['month']['current_year'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php } ?>
                        </select>
                        <select name="searchmonth_month" class="querySelect">
                            <?php foreach ($output['month_arr'] as $k => $v) { ?>
                                <option value="<?php echo $k; ?>" <?php echo $output['search_arr']['month']['current_month'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>

<div class="alert alert-info mt10" style="clear:both;">
    <ul class="mt5">
        <li>
            <?php
            $ordergamount = array_column($output['stat_ordergoods'], 'ordergamount');
            $ordernum = array_column($output['stat_ordergoods'], 'ordernum');
            $ordergoodsnum = array_column($output['stat_ordergoods'], 'ordergoodsnum');
            ?>
            <span class="w210 fl h30" style="display:block;">
    		<i title="该商品符合搜索条件的订单总金额" class="tip icon-question-sign"></i>
    		总下单金额：<strong><?php echo array_sum($ordergamount) . $lang['currency_zh']; ?></strong>
    	</span>
            <span class="w210 fl h30" style="display:block;">
			<i title="该商品符合搜索条件的订单数量" class="tip icon-question-sign"></i>
			总下单量：<strong><?php echo array_sum($ordernum); ?></strong>
		</span>
            <span class="w210 fl h30" style="display:block;">
			<i title="该商品符合搜索条件的销售数量" class="tip icon-question-sign"></i>
			总销售数量：<strong><?php echo array_sum($ordergoodsnum); ?></strong>
		</span>
        </li>
    </ul>
    <div style="clear:both;"></div>
</div>

<div id="statlist" class="">
    <table class="ncsc-default-table">
        <thead>
        <tr class="sortbar-array">
            <th class="align-center">渠道名称</th>
            <th class="align-center">销售数量</th>
            <th class="align-center">订单数量</th>
            <th class="align-center">订单总额</th>
        </tr>
        </thead>
        <tbody id="datatable">
        <?php foreach ($output['stat_ordergoods'] as $stat_ordergoods) { ?>

            <tr class="bd-line">
                <td class="align-center"><?php echo orderFrom(3, $output['fenxiaoMemebrs'][$stat_ordergoods['buyer_id']]); ?></td>
                <td class="align-center"><?php echo $stat_ordergoods['ordergoodsnum']; ?></td>
                <td class="align-center"><?php echo $stat_ordergoods['ordernum']; ?></td>
                <td class="align-center"><?php echo ncPriceFormat($stat_ordergoods['ordergamount']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script charset="utf-8" type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/highcharts/highcharts.js"></script>
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL; ?>/js/ui.core.js"></script>
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL; ?>/js/ui.tabs.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.ajaxContent.pack.js"></script>
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL; ?>/js/statistics.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.poshytip.min.js"></script>

<script type="text/javascript">
    //展示搜索时间框
    function show_searchtime() {
        s_type = $("#search_type").val();
        $("[id^='searchtype_']").hide();
        $("#searchtype_" + s_type).show();
    }

    $(function () {
        //Ajax提示
        $('.tip').poshytip({
            className: 'tip-yellowsimple',
            showTimeout: 1,
            alignTo: 'target',
            alignX: 'center',
            alignY: 'top',
            offsetY: 5,
            allowTipHover: false
        });

        //切换登录卡
        $('#stat_tabs').tabs();

        //统计数据类型
        var s_type = $("#search_type").val();
        $('#search_time').datepicker({dateFormat: 'yy-mm-dd'});

        show_searchtime();
        $("#search_type").change(function () {
            show_searchtime();
        });

    });
</script>