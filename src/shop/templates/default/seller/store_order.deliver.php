<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="tabmenu">
  <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10">
	<ul class="mt5">
		<li>1、可以对待发货的订单进行发货操作，发货时可以设置收货人和发货人信息，填写一些备忘信息，选择相应的物流服务，打印发货单。</li>
		<li>2、已经设置为发货中的订单，您还可以继续编辑上次的发货信息。</li>
		<li>3、如果因物流等原因造成买家不能及时收货，您可使用点击延迟收货按钮来延迟系统的自动收货时间。</li>
		<li>4、批量发货：请先下载<a href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=downBatchTemplate" style="color: blue;">模板文件</a>，按照order_id(订单编号)、logi_name(快递公司)、logi_no(快递编号),remark(发货备注)的顺序编辑excel文件。</li>
		<li>5、如果一个订单分多个快递包裹发货，请在“快递编号”之间，用英文半角逗号 , 来分隔多个快递编号</li>
        <li>6、电子面单批量发货：请先下载<a href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=downBatchTemplate&file_type=dzmd" style="color: blue;">模板文件</a>，按照格式编辑exlce表格的order_id(订单编号)，订单编号在原订单编号前加上*</li>
        <li>7、批量发货备注：请先下载<a href="<?php echo ADMIN_SITE_URL;?>/public/DeliveryRemarkTemplate.csv" style="color: blue;">模板文件</a>，按照order_id(订单编号)、remark(发货备注)的顺序编辑excel文件。</li>
        <li>8、批量分销发货备注：请先下载<a href="<?php echo ADMIN_SITE_URL;?>/public/DeliveryRemarkFenxiaoTemplate.csv" style="color: blue;">模板文件</a>，按照fx_order_id(分销订单号)、remark(发货备注)的顺序编辑excel文件。</li>
    </ul>
</div>
<form method="get" action="index.php" target="_self">
	<table class="search-form">
		<input type="hidden" name="act" value="store_deliver" />
		<input type="hidden" name="op" value="index" />
    <?php if ($_GET['state'] !='') { ?>
    <input type="hidden" name="state"
			value="<?php echo $_GET['state']; ?>" />
    <?php } ?>
    <tr>
			<th><?php echo $lang['store_order_add_time'];?></th>
			<td class="w240"><input type="text" class="text w70"
				name="query_start_date" id="query_start_date"
				value="<?php echo $_GET['query_start_date']; ?>" /><label
				class="add-on"><i class="icon-calendar"></i></label>
				&nbsp;&#8211;&nbsp; <input id="query_end_date" class="text w70"
				type="text" name="query_end_date"
				value="<?php echo $_GET['query_end_date']; ?>" /><label
				class="add-on"><i class="icon-calendar"></i></label></td>
			<th><?php echo $lang['store_order_buyer'];?></span></th>
			<td class="w100"><input type="text" class="text w80"
				name="buyer_name" value="<?php echo trim($_GET['buyer_name']); ?>" /></td>
			<th><?php echo $lang['store_order_order_sn'];?></th>
			<td class="w160"><input type="text" class="text w150" name="order_sn"
				value="<?php echo trim($_GET['order_sn']); ?>" /></td>
			<td class="w70 tc"><label class="submit-border"> <input type="submit"
					class="submit" value="<?php echo $lang['store_order_search'];?>" />
			</label></td>
    </tr>
        <tr>
			<td class="w70 tc" style="width:20%">
					<div class="upload-con-div">
                    <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                    <input type="file" style="width:130px;"  id="batch_file" hidefocus="true" size="1" class="input-file" name="file"/>
                    </span>
                    <p style="width: 130px;"><i class="icon-upload-alt"></i><?php echo $lang['store_order_deliverbyexcel'];?></p>
                   </a> </div></div>
			</td>
            <!----批量发货备注---->
            <td class="w70 tc" style="width:20%">
                <div class="upload-con-div">
                    <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                    <input type="file" style="width:130px;" id="upload_remark" hidefocus="true" size="1" class="input-file" name="file"/>
                    </span>
                    <p style="width:130px;"><i class="icon-upload-alt"></i>批量修改发货备注</p>
                    </a>
                    </div>
                </div>
            </td>

            <td style="width:20%;">
                <div class="upload-con-div">
                    <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                    <input type="file"  style="width:150px;" id="upload_fenxiao_remark" hidefocus="true" size="1" class="input-file" name="file"/>
                    </span>
                            <p style="width: 150px;"><i class="icon-upload-alt"></i>分销订单批量发货备注</p>
                        </a>
                    </div>
                </div>
            </td>

            <td>
            <div class="upload-con-div">
            <?php if($output['is_fx_send']){?>
                <div class="ncsc-upload-btn" > <a href="javascript:void(0);"><span>
                        <input  type="file" style="width:130px;" id="batch_fx_file" hidefocus="true" size="1" class="input-file" name="file"/>
                        </span>
                        <p style="width: 130px;margin-bottom:13px;"><i class="icon-upload-alt"></i>分销订单批量发货</p>
                </div>
            <?php }?>
            </div>
            </td>

            <td colspan="9">
                <a href="javascript:void(0)" class="ncbtn-mini ncbtn-bittersweet fr" uri="index.php?act=store_deliver&op=pirntship_more" dialog_width="480" dialog_title="电子面单批量发货" nc_type="dialog" dialog_id="seller_order_adjust_fee" id="order<?php echo $order['order_id']; ?>_action_adjust_fee" /><i class="icon-truck"></i>电子面单批量发货</a></td>
        </tr>
	</table>
</form>
<div class="alert alert-info mt10" id="showBatchResult" style="display:none;">
	
</div>

<table class="ncsc-default-table order deliver">
  <?php if (is_array($output['order_list']) and !empty($output['order_list'])) { ?>
  <?php foreach($output['order_list'] as $order_id => $order) {?>
  <tbody>
		<tr>
			<td colspan="21" class="sep-row"></td>
		</tr>
		<tr>
			<th colspan="21"><span class="ml5"><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><strong><?php echo $order['order_sn']; ?></strong></span><span><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em
					class="goods-time"><?php echo date("Y-m-d H:i:s",$order['add_time']); ?></em></span>
        <?php if (!empty($order['extend_order_common']['shipping_time'])) {?>
        <span><?php echo '发货时间'.$lang['nc_colon'];?><em
					class="goods-time"><?php echo date("Y-m-d H:i:s",$order['extend_order_common']['shipping_time']); }?></em></span>
				<span class="fr mr10">
        <?php if ($order['shipping_code'] != ''){?>
        <a
					href="index.php?act=store_deliver&op=search_deliver&order_sn=<?php echo $order['order_sn']; ?>"
					class="ncbtn-mini"><i class="icon-compass"></i><?php echo $lang['store_order_show_deliver'];?></a>
        <?php }?>
        <a
					href="index.php?act=store_order&op=order_print&order_id=<?php echo $order['order_id'];?>"
					target="_blank" class="ncbtn-mini"
					title="<?php echo $lang['store_show_order_printorder'];?>" /><i
					class="icon-print"></i><?php echo $lang['store_show_order_printorder'];?></a>
			</span></th>
		</tr>
    <?php $i = 0; ?>
    <?php foreach($order['goods_list'] as $k => $goods) { ?>
    <?php $i++; ?>
    <tr>
			<td class="bdl w10"></td>
			<td class="w50"><div class="pic-thumb">
					<a href="<?php echo $goods['goods_url'];?>" target="_blank"><img
						src="<?php echo $goods['image_60_url']; ?>"
						onMouseOver="toolTip('<img 
						src=<?php echo $goods['image_240_url'];?>>')"
						onMouseOut="toolTip()" /></a>
				</div></td>
			<td class="tl"><dl class="goods-name">
					<dt>
						<a target="_blank" href="<?php echo $goods['goods_url'];?>"><?php echo $goods['goods_name']; ?></a>
					</dt>
					<dd>
						<strong>￥<?php echo ncPriceFormat($goods['goods_price']); ?></strong>&nbsp;x&nbsp;<em><?php echo $goods['goods_num']; ?></em>件
					</dd>
				</dl></td>

			<!-- S 合并TD -->
      <?php if (($order['goods_count'] > 1 && $k == 0) || ($order['goods_count'] == 1)){?>
      <td class="bdl bdr order-info w500"
				rowspan="<?php echo $order['goods_count'];?>"><dl>
					<dt><?php echo $lang['store_deliver_buyer_name'].$lang['nc_colon'];?></dt>
					<dd><?php echo $order['buyer_name']; ?> <span
							member_id="<?php echo $order['buyer_id'];?>"></span>
            <?php if(!empty($order['extend_member']['member_qq'])){?>
            <a target="_blank"
							href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $order['extend_member']['member_qq'];?>&site=qq&menu=yes"
							title="QQ: <?php echo $order['extend_member']['member_qq'];?>"><img
							border="0"
							src="http://wpa.qq.com/pa?p=2:<?php echo $order['extend_member']['member_qq'];?>:52"
							style="vertical-align: middle;" /></a>
            <?php }?>
            <?php if(!empty($order['extend_member']['member_ww'])){?>
            <a target="_blank"
							href="http://amos.im.alisoft.com/msg.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>"
							class="vm"><img border="0"
							src="http://amos.im.alisoft.com/online.aw?v=2&uid=<?php echo $order['extend_member']['member_ww'];?>&site=cntaobao&s=2&charset=<?php echo CHARSET;?>"
							alt="Wang Wang" style="vertical-align: middle;" /></a>
            <?php }?>
          </dd>
				</dl>
				<dl>
					<dt><?php echo '收货人'.$lang['nc_colon'];?></dt>
					<dd>
						<div class="alert alert-info m0">
							<p>
								<i class="icon-user"></i><?php echo $order['extend_order_common']['reciver_name']?><span
									class="ml30" title="<?php echo '电话';?>"><i class="icon-phone"></i><?php echo $order['extend_order_common']['reciver_info']['phone'];?></span>
							</p>
							<p class="mt5"
								title="<?php echo $lang['store_deliver_buyer_address'];?>">
								<i class="icon-map-marker"></i><?php echo $order['extend_order_common']['reciver_info']['address'];?></p>
              <?php if ($order['extend_order_common']['order_message'] != '') {?>
              <p class="mt5"
								title="<?php echo $lang['store_deliver_buyer_address'];?>">
								<i class="icon-map-marker"></i><?php echo $order['extend_order_common']['order_message'];?></p>
              <?php } ?>
            </div>
					</dd>
				</dl>
				<dl>
					<dt><?php echo $lang['store_deliver_shipping_amount'].$lang['nc_colon'];?> </dt>
					<dd>
            <?php if (!empty($order['shipping_fee']) && $order['shipping_fee'] != '0.00'){?>
            ￥<?php echo $order['shipping_fee'];?>
            <?php }else{?>
            <?php echo $lang['nc_common_shipping_free'];?>
            <?php }?>
            <?php if (empty($order['lock_state'])) {?>
            <?php if ($order['order_state'] == ORDER_STATE_PAY||$order['order_state'] == ORDER_STATE_PREPARE) {?>
                    <span>
                    <?php if($order['if_can_send']){?>
                         <a href="index.php?act=store_deliver&op=send&order_id=<?php echo $order['order_id'];?>" class="ncbtn-mini ncbtn-mint fr"><i class="icon-truck"></i><?php echo $lang['store_order_send'];?></a>
                    <?php }?>
                    <?php if($order['if_can_printship']){?>
                        <a style="margin: 0px 5px 0px 0px;" href="javascript:void(0)" class="ncbtn-mini ncbtn-bittersweet fr" nc_type="dialog" uri="index.php?act=store_printship&op=selectTemplate&order_sn=<?php echo $order['order_sn']; ?>&order_id=<?php echo $order['order_id']; ?>" dialog_title="电子面单发货" dialog_id="seller_order_print_ship" dialog_width="400"/><i class="icon-truck"></i>电子面单</a>
                    <?php }?>
                    </span>
            <?php } elseif ($order['order_state'] == ORDER_STATE_SEND){?>
            <span><a href="javascript:void(0)"
							class="ncbtn-mini ncbtn-bittersweet ml5 fr"
							uri="index.php?act=store_deliver&op=delay_receive&order_id=<?php echo $order['order_id']; ?>"
							dialog_width="480" dialog_title="延迟收货" nc_type="dialog"
							dialog_id="seller_order_delay_receive"
							id="order<?php echo $order['order_id']; ?>_action_delay_receive" /><i
							class="icon-time"></i></i>延迟收货</a> <a
							href="index.php?act=store_deliver&op=send&order_id=<?php echo $order['order_id'];?>"
							class="ncbtn-mini ncbtn-aqua fr"><i class="icon-edit"></i><?php echo $lang['store_deliver_modify_info'];?></a>
						</span>
            <?php }?>
            <?php }?>
          </dd>
				</dl></td>
      <?php } ?>
      <!-- E 合并TD -->
		</tr>

		<!-- S 赠品列表 -->
    <?php if (!empty($order['zengpin_list']) && $i == count($order['goods_list'])) { ?>
    <tr>
			<td class="bdl w10"></td>
			<td colspan="2" class="tl">
				<div class="ncsc-goods-gift">
					赠品：
					<ul>
    <?php foreach ($order['zengpin_list'] as $k => $zengpin_info) { ?>
    <li><a
							title="赠品：<?php echo $zengpin_info['goods_name'];?> * <?php echo $zengpin_info['goods_num'];?>"
							href="<?php echo $zengpin_info['goods_url'];?>" target="_blank"><img
								src="<?php echo $zengpin_info['image_60_url'];?>"
								onMouseOver="toolTip('<img 
								src=<?php echo $zengpin_info['image_240_url'];?>>')"
								onMouseOut="toolTip()"/></a></li>
    <?php } ?>
    </ul>
				</div>
			</td>
		</tr>
    <?php } ?>
    <!-- E 赠品列表 -->

    <?php } ?>
    <?php } } else { ?>
    <tr>
			<td colspan="21" class="norecord"><div class="warning-option">
					<i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span>
				</div></td>
		</tr>
    <?php } ?>
  </tbody>
	<tfoot>
    <?php if (!empty($output['order_list'])) { ?>
    <tr>
			<td colspan="21"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
		</tr>
    <?php } ?>
  </tfoot>
</table>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});

    //显示上传div
    $('#deliver').click(function(){
        var display =$('#deliverDiv').css('display');
        if(display == 'none'){
            $('#deliverDiv').show('fast');
        }else{
            $('#deliverDiv').hide('fast');
        }
    });

    //上传前进行判断
    $('#upload').click(function (){
        var file = $('#uploadsfile');
        var fileType = getFiletype(file.val());
        var allowtype = ['CSV','XLS','XLSX'];
        if($.trim(file.val())==''){
            showError('请选择文件');return false;
        }
        if ($.inArray(fileType,allowtype) == -1)
        {
            showError('请选择正确的文件类型');return false;
        }
    });

    //ajax上传文件
    $('#batch_fx_file').fileupload({
        dataType: 'json',
        url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload_fx',
        done: function (e,data) {
            var param = data.result;
            if(param.state==false){
            	showError(param.msg);
                return false;
            }else {
                var answer = confirm(data.result.msg);
                if (answer) {
                    window.location.href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=export_fx_result&key_name=" + data.result.key_name;
                }
            }
            //showSucc(tips);
            //setTimeout("window.location.reload()", 3000);
        }
    });

    //订单批量发货
    $('#batch_file').fileupload({
        dataType: 'json',
        url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload',
        done: function (e,data){
            if(data.result.state==false){
                showError(data.result.msg);
                return false;
            }else {
                var answer = confirm(data.result.msg);
                if (answer) {
                    window.location.href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=export_excel_result&key_name=" + data.result.key_name;
                }
            }
        }
    });

    //订单批量修改备注
    $('#upload_remark').fileupload({
        dataType: 'json',
        url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload_remark',
        done: function (e,data){
            if(data.result.state==false){
                showError(data.result.msg);
                return false;
            }else {
                var answer = confirm(data.result.msg);
                if (answer) {
                    window.location.href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=export_excel_shipremarkt&key_name=" + data.result.key_name;
                }
            }
        }
    });
    /*分销订单号批量修改备注*/
    $('#upload_fenxiao_remark').fileupload({
        dataType: 'json',
        url: '<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=upload_fenxiao_remark',
        done: function (e,data){
            if(data.result.state==false){
                showError(data.result.msg);
                return false;
            }else {
                var answer = confirm(data.result.msg);
                if (answer) {
                    window.location.href="<?php echo SHOP_SITE_URL;?>/index.php?act=store_deliver&op=export_excel_shiprefenxiaomarkt&key_name=" + data.result.key_name;
                }
            }
        }
    });

    //获取上传文件类型
    function getFiletype(filePath)
    {
        var extStart  = filePath.lastIndexOf(".")+1;
        return filePath.substring(extStart,filePath.length).toUpperCase();
    }


});
</script>
