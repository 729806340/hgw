<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=seckill_list&config_tuan_id=<?php echo $output['tuan_config_id']; php?>" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>社区团购秒杀活动商品</h3>
                <h5>社区团购秒杀活动商品管理</h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>

        </ul>
    </div>
    <div id="flexigrid"></div>
</div>

<script>
    $(function(){
        var flexUrl = 'index.php?act=tuan_config&op=tuan_seckill_goods_xml&xianshi_id=<?php echo $_GET['xianshi_id']?>&config_tuan_id=<?php echo $_GET['config_tuan_id']?>';
        $("#flexigrid").flexigrid({
            url: flexUrl,
            colModel: [
                {display: '编号', name: 'tuan_config_goods_id', width: 70, sortable: false, align: 'left'},
                {display: '商品名称', name: 'goods_name', width: 280, sortable: false, align: 'left'},
                {display: '商品价格', name: 'goods_price', width: 60, sortable: false, align: 'left'},
                {display: '秒杀价格', name: 'return_price', width: 60, sortable: false, align: 'left'},
                {display: '商品库存', name: 'return_price', width: 120, sortable: false, align: 'left'},
                {display: '已售数量', name: 'return_price', width: 120, sortable: false, align: 'left'},
            ],
            sortname: "tuan_config_goods_id",
            sortorder: "desc",
            rp: 10,
            title: '秒杀活动下的商品列表'
        });
    });
</script>
