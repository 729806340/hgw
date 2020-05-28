<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3><?php echo $lang['refund_manage'];?> - 查看“订单编号：<?php echo $output['order_info']['order_sn']; ?>”</h3>
                <h5><?php echo $lang['refund_manage_subhead'];?></h5>
            </div>
        </div>
    </div>

    <div class="explanation mt10" id="result" style="display:none;"></div>
    <div id="flexigrid"></div>
</div>
<div id="mask" style="position: fixed;top: 0;bottom: 0;left: 0;right: 0;background: #333;opacity: .3;z-index: 9999; display: none;">
</div>
<div id="loading" style="z-index:9999;position: fixed; top: 100px; width: 100%; text-align: center;display: none;">
    <p style="background:#FFF;margin: 100px auto; width: 300px; padding: 20px 30px; font-size: 16px;">正在处理，请勿关闭页面...</p>
</div>
<style>
    .handle{
        width:240px !important;
    }
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        $("#flexigrid").flexigrid({
            url: 'index.php?act=refund&op=get_view_detail_xml&order_sn=<?php echo $output['order_info']['order_sn']; ?>',
            colModel : [
                {display: '订单编号', name : 'order_sn', width : 130, sortable : false, align: 'center'},
                {display: '退单编号', name : 'refund_sn', width : 130, sortable : false, align: 'center'},
                {display: '渠道', name : 'fx_name', width : 130, sortable : false, align: 'center'},
                {display: '退款金额(元)', name : 'refund_amount', width : 70, sortable : true, align: 'left'},
                {display: '申请图片', name : 'pic_info', width : 70, sortable : false, align : 'left'},
                {display: '申请原因', name : 'buyer_message', width : 120, sortable : false, align: 'left'},
                {display: '申请时间', name : 'add_time', width: 120, sortable : true, align : 'center'},
                {display: '涉及商品', name : 'goods_name', width : 120, sortable : false, align: 'left'},
                {display: '商家处理', name : 'seller_state', width : 80, sortable : true, align: 'center'},
                {display: '平台处理', name : 'refund_state', width : 80, sortable : false, align: 'center'},
                {display: '商家处理备注', name : 'seller_message', width : 120, sortable : false, align: 'left'},
                {display: '平台处理备注', name : 'admin_message', width : 120, sortable : false, align: 'left'},
                {display: '商家审核时间', name : 'seller_time', width: 120, sortable : true, align : 'center'},
                {display: '商品图', name : 'goods_image', width : 40, sortable : true, align: 'center'},
                {display: '商品ID', name : 'goods_id', width : 80, sortable : true, align: 'center'},
                {display: '买家', name : 'buyer_name', width : 60, sortable : true, align: 'left'},
                {display: '买家ID', name : 'buyer_id', width : 40, sortable : true, align: 'center'},
                {display: '商家名称', name : 'store_name', width : 100, sortable : true, align: 'left'},
                {display: '商家ID', name : 'store_id', width : 40, sortable : true, align: 'center'},
                {display: '退款方式', name : 'refund_way', width : 80, sortable : true, align: 'center'},
                {display: '收款姓名', name : 'refund_name', width : 80, sortable : true, align: 'center'},
                {display: '收款帐号', name : 'refund_account', width : 80, sortable : true, align: 'center'},
                {display: '分销订单号', name : 'fx_order_id', width : 200, sortable : true, align: 'center'},
                {display: '退款操作人', name : 'admin_name', width : 200, sortable : true, align: 'center'}
            ],
            sortname: "seller_time",
            sortorder: "desc",
            title: '关联退单列表'
        });
    });

</script>