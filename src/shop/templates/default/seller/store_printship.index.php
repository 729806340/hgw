<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10">
    <ul class="mt5">
        <li>1、请先注册快递鸟账号，<a href="http://www.kdniao.com/" target="_blank">点击前往</a></li>
        <li>2、申请开通电子面单业务（免费开通）</li>
        <li>3、将快递鸟用户ID和API key提交汉购网（汉购网对您的信息完全保密）</li>
        <li>4、创建电子面单模板（目前仅支持邮政包裹、顺丰、快捷快递物流公司）</li>
        <li>5、申请电子面单发货的订单可以在快递鸟后台的订单列表批量打印,请自己准备快递打印机和热敏打印纸</li>
        <li>6、<a href="http://www.hangowa.com/member/article-43.html" target="_blank">帮助指南</a></li>
    </ul>
</div>
<table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w30"></th>
        <th class="w120 tl">模板名称</th>
        <th class="w120 tl">物流公司</th>
        <th class="tl">发货区域</th>
        <th class="tl">发货地址</th>
        <th class="w120 tl">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(!empty($output['template_list']) && is_array($output['template_list'])){?>
        <?php foreach($output['template_list'] as $key => $value){?>
            <tr class="bd-line">
                <td></td>
                <td class="tl"><?php echo $value['template_name'];?></td>
                <td class="tl"><?php echo $value['express_name'];?></td>
                <td class="tl"><?php echo $value['region']?></td>
                <td class="tl"><?php echo $value['address'];?></td>
                <td>
                    <a href="index.php?act=store_printship&op=editTemplate&id=<?php echo $value['id'];?>" class="btn-bluejeans"><i class="icon-edit"></i><p>编辑</p></a>
                </td>
            </tr>
        <?php }?>
    <?php }else{?>
        <tr>
            <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span><?php echo $lang['no_record'];?></span></div></td>
        </tr>
    <?php }?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
    </tr>
    </tfoot>
</table>
<form id="del_form" action="<?php echo urlShop('store_waybill', 'waybill_del');?>" method="post">
    <input type="hidden" id="del_waybill_id" name="waybill_id">
</form>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
