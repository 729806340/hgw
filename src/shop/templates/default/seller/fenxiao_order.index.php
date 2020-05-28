<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
    <a href="javascript:void(0)" class="ncbtn ncbtn-aqua" nc_type="dialog" style="right:160px" dialog_title="导入订单" dialog_id="my_goods_brand_apply" dialog_width="480" uri="index.php?act=fenxiao_order&op=index&action=importorder">导入订单</a>
    <a title="导出订单" class="ncbtn ncbtn-mint eo-btn" href="javascript:;" style="right:80px">导出订单</a>
    <a title="下载模板" class="ncbtn ncap-btn-black" href="/shop/resource/ordertpl.rar">下载模板</a>
    <form method="post" action="index.php?act=fenxiao_order&op=export_excel" id="exportForm" >
        <input type="hidden" name="fenxiao_id" value="" id="fenxiao_id2">
        <input type="hidden" name="exp_starttime" value="" id="exp_starttime">
        <input type="hidden" name="exp_endtime" value="" id="exp_endtime">
        <input type="hidden" name="exp_istarttime" value="" id="exp_istarttime">
        <input type="hidden" name="exp_iendtime" value="" id="exp_iendtime">
        <input type="hidden" name="exp_status" value="" id="exp_status">
        <input type="hidden" name="exp_oid" value="" id="exp_oid">
        <input type="hidden" name="exp_fxoid" value="" id="exp_fxoid">
    </form>
</div>
<form method="get" action="index.php">
  <table class="search-form">
    <input type="hidden" name="act" value="fenxiao_order" />
    <input type="hidden" name="op" value="index" />
    <input type="hidden" name="status" id="status" value="<?php echo $_GET['status'];?>" />
    <input type="hidden" name="oid" id="oid" value="<?php echo $_GET['oid'];?>" />
    <input type="hidden" name="fxoid" id="fxoid" value="<?php echo $_GET['fxoid'];?>" />
      <tr style="display:block">
          <td>&nbsp;</td>
          <th>渠道：</th>
          <td class="w160">
              <select name="fenxiao_id" id="fenxiao_id" class="w150">
                  <option value="">请选择</option>
                  <?php foreach ($output['fenxiao_list'] as $k => $v) {?>
                      <option <?php if ($_GET['fenxiao_id'] == $v['id']) echo 'selected';?> value="<?php echo $v['id'];?>"><?php echo $v['member_cn_code'];?></option>
                  <?php }?>
              </select>
          </td>
          <th style="width: 72px;">订单编号：</th>
          <td class="w160"><input type="text" class="text" placeholder="输入订单编号" name="oid" value="<?php echo $_GET['oid']; ?>"/></td>
          <th style="width: 72px;padding-left: 20px;">分销订单号：</th>
          <td class="w160"><input type="text" class="text" placeholder="输入分销订单号" name="fxoid" value="<?php echo $_GET['fxoid']; ?>"/></td>
      </tr>
        <tr style="display:block; float:right">
          <th style="width: 72px;">下单时间：</th>
          <td class="w540">
              <input type="text" class="text w120" name="starttime" id="starttime" value="<?php echo $_GET['starttime']; ?>" />
              <label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;
              <input id="endtime" class="text w120" type="text" name="endtime" value="<?php echo $_GET['endtime']; ?>" />
              <label class="add-on"><i class="icon-calendar"></i></label>
          </td>
          <th style="width: 72px;">导入时间：</th>
          <td class="w540">
              <input type="text" class="text w120" name="istarttime" id="istarttime" value="<?php echo $_GET['istarttime']; ?>" />
              <label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;
              <input id="iendtime" class="text w120" type="text" name="iendtime" value="<?php echo $_GET['iendtime']; ?>" />
              <label class="add-on"><i class="icon-calendar"></i></label>
          </td>
          <td class="tc w70"><label class="submit-border">
                  <input type="submit" class="submit" value="搜索">
              </label></td>
      </tr>
  </table>
</form>
<table class="ncsc-default-table">
  <thead>
    <tr nc_type="table_header">
<!--      <th class="w180">商品图片</th>-->
      <th class="w180">分销订单号</th>
      <th class="w180">渠道</th>
      <th class="w180">分销商品名称</th>
      <th class="w100">商品单价</th>
      <th class="w100">商品数量</th>
      <th class="w100">状态</th>
      <th class="w100">商品总价</th>
<!--      <th class="w100">物流公司</th>-->
<!--      <th class="w100">发货单号</th>-->
      <th class="w100">订单总额</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($output['goods_list'])) { ?>
    <?php foreach ($output['goods_list'] as $val) { ?>
            <tr>
                <th class="tc"><input type="checkbox" class="checkitem tc" value="104022"></th>
                <th colspan="20"><span>平台订单号: <?php echo $val['order_sn'];?> (编号：<?php echo $val['order_id'];?>)</span> <span>总金额:<?php echo $val['order_amount'];?></span> <span>下单时间：<?php echo $val['datetime'];?></span> <span>导入时间： <?php echo $val['import_time'] == 0 ? '未记录' : date('Y-m-d H:i:s', $val['import_time']); ?></span> <span>收货人:<?php echo $val['reciver_name'];?></span> <span>电话:<?php echo $val['reciver_info']['phone']?></span><span>收货地址：<?php echo $val['reciver_info']['address'];?></span></th>
            </tr>

        <?php foreach ($val['suborder'] as $val2) { ?>
                <tr>
<!--      <td class="tl"><dl class="goods-name">-->
<!--              <div class="pic-thumb">-->
<!--                  <a href="--><?php //echo urlShop('goods', 'index', array('goods_id' => $val2['goods_id']));?><!--" target="_blank"><img src="--><?php //echo thumb($val2['goods_image'], 60);?><!--"/></a>-->
<!--              </div>-->
<!--      </td>-->
      <td><span><?php echo $val['fx_order_id'];?></span></td>
      <td><span><?php echo $output['fenxiao_list'][$val['buyer_id']]['member_cn_code'];?></span></td>
      <td><span><?php echo $val2['goods_name'];?></span></td>
      <td><span><?php echo $lang['currency'].ncPriceFormat($val2['goods_price']); ?></span></td>
      <td><span><?php echo $val2['goods_num']; ?></span></td>
      <td><span>
          <?php if($val['order_state']=='0'){
              echo '已取消';
          }else if($val['order_state']=='10'){
              echo '未支付';
          }else if($val['order_state']=='20'){
              echo '已付款';
          }else if($val['order_state']=='30'){
              echo '已发货';
              echo '<p>
                    <a href="index.php?act=fenxiao_order&op=search_deliver&order_sn='.$val['order_sn'].'">查看物流</a>
                  </p>';
          }else if($val['order_state']=='40'){
              echo '已完成';
              echo '<p>
                    <a href="index.php?act=fenxiao_order&op=search_deliver&order_sn='.$val['order_sn'].'">查看物流</a>
                  </p>';
          }
          $expressId='';
          $expressName='';
          if($val['shipping_code']!=null&&$val['shipping_code']!=''){
              $expressId=trim($val['shipping_code']);
          }
          if($val['express_name']!=null&&$val['express_name']!=''){
              $expressName=trim($val['express_name']);
          }
          ?>
          </span></td>
                    <td><span><?php echo $lang['currency'].ncPriceFormat($val2['goods_price']*$val2['goods_num']); ?></span></td>
<!--        <td><span>--><?php //echo $expressName;?><!--</span></td>-->
<!--        <td><span>--><?php //echo $expressId;?><!--</span></td>-->
        <td><span><?php echo $val['order_amount'];?></span></td>
                </tr>
        <?php } ?>


    <tr style="display:none;"><td colspan="20"><div class="ncsc-goods-sku ps-container"></div></td></tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
      <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
    </tr>
    <?php } ?>
  </tbody>
    <?php  if (!empty($output['goods_list'])) { ?>
  <tfoot>
    <tr>
      <td colspan="20"><div class="pagination"> <?php echo $output['show_page']; ?> </div></td>
    </tr>
  </tfoot>
  <?php } ?>
</table>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/store_goods_list.js"></script>
<script charset="utf-8" type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon.js"></script>
<style type="text/css">
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
    </style>
<script>
$(function(){
    $('#starttime').datetimepicker({
        showSecond: true,
        showMillisec: true,
        timeFormat: 'hh:mm:ss'
    });
    $('#endtime').datetimepicker({
        showSecond: true,
        showMillisec: true,
        timeFormat: 'hh:mm:ss'
    });
    $('#istarttime').datetimepicker({
        showSecond: true,
        showMillisec: true,
        timeFormat: 'hh:mm:ss'
    });
    $('#iendtime').datetimepicker({
        showSecond: true,
        showMillisec: true,
        timeFormat: 'hh:mm:ss'
    });
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

    $(".eo-btn").click(function() {
        var fenxiao_id = $("#fenxiao_id").val();
        var starttime = $("#starttime").val();
        var endtime = $("#endtime").val();
        var istarttime = $("#istarttime").val();
        var iendtime = $("#iendtime").val();
        var status = $("#status").val();
        var oid = $("#oid").val();
        var fxoid = $("#fxoid").val();
        if(fenxiao_id==""&&starttime==""&&endtime==""&&istarttime==""&&iendtime==""&&oid==""&&fxoid==""){
            alert("必须输入【筛选条件】");
            return false;
        }
        // alert(status)
        if(starttime!==""&&endtime==""){
            alert("必须输入【下单时间】的结束时间");
            return false;
        }
        if(starttime==""&&endtime!==""){
            alert("必须输入【下单时间】的开始时间");
            return false;
        }
        if(istarttime!==""&&iendtime==""){
            alert("必须输入【导入时间】的结束时间");
            return false;
        }
        if(istarttime==""&&iendtime!==""){
            alert("必须输入【导入时间】的开始时间");
            return false;
        }
        d1=new Date(Date.parse(starttime));
        d2=new Date(Date.parse(endtime));
        d3=new Date(Date.parse(istarttime));
        d4=new Date(Date.parse(iendtime));
        if(d1>=d2){
            alert('【下单时间】的结束时间必须不小于开始时间');
            return false;
        }
        if(d3>=d4){
            alert('【导入时间】的结束时间必须不小于开始时间');
            return false;
        }
        $("#fenxiao_id2").val( fenxiao_id );
        $("#exp_starttime").val( starttime );
        $("#exp_endtime").val( endtime );
        $("#exp_istarttime").val( istarttime );
        $("#exp_iendtime").val( iendtime );
        $("#exp_status").val( status );
        $("#exp_oid").val( oid );
        $("#exp_fxoid").val( fxoid );
        $("#exportForm").submit();
    })
});
</script>