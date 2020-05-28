<link href="<?php echo SHOP_TEMPLATES_URL?>/css/base.css" rel="stylesheet" type="text/css">
<link href="<?php echo SHOP_TEMPLATES_URL?>/css/seller_center.css" rel="stylesheet" type="text/css">
<style>
    body {
        min-width:800px;
    }
</style>
<form method="get" action="index.php?act=jdy_mapping&op=supplier">
    <input type="hidden" name="act" value="jdy_mapping">
    <input type="hidden" name="op" value="supplier">
    <table class="search-form">
        <tr>
            <td>&nbsp;</td>
            <th class="w80">
                <select name="query_supplier_key">
                    <option value="supplier_name" <?php if($_GET['query_supplier_key'] == 'supplier_name') {?>selected<?php }?>>供应商名称</option>
                    <option value="supplier_number" <?php if($_GET['query_supplier_key'] == 'supplier_number') {?>selected<?php }?>>供应商代码</option>
                </select>
            </th>
            <td class="w100"><input type="text" class="text"  name="query_supplier_value" value="<?php echo trim($_GET['query_supplier_value']);?>" /></td>
            <th class="w80">
                <select>
                    <option>供应商类别</option>
                </select>
            </th>
            <td class="w100">
                <input type="text" class="text" name="supplier_catetory_name" value="<?php echo trim($_GET['supplier_catetory_name']);?>" />
            </td>
            <th class="w80">
                <select name="query_link_key">
                    <option value="supplier_link_man" <?php if($_GET['query_link_key'] == 'supplier_link_man') {?>selected<?php }?>>联系人</option>
                    <option value="supplier_link_mobile" <?php if($_GET['query_link_key'] == 'supplier_link_mobile') {?>selected<?php }?>>联系人手机</option>
                </select>
            </th>
            <td class="w100"><input type="text" class="text" name="query_link_value" value="<?php echo trim($_GET['query_link_value']);?>" /></td>
            <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="搜索" /></label></td>
        </tr>
    </table>
</form>
<table class="ncsc-default-table">
    <thead>
    <tr>
        <th class="w10"></th>
        <th class="w60">供应商code</th>
        <th class="w120">供应商名称</th>
        <th class="w60">供应商类别</th>
        <th class="w60">联系人</th>
        <th class="w80">电话</th>
        <th class="w100">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php if(!empty($output['supplier_list'])) {?>
        <?php foreach ($output['supplier_list'] as $val) {?>
            <tr class="bd-line">
                <td></td>
                <td><?php echo $val['supplier_number'];?></td>
                <td><?php echo $val['supplier_name'];?></td>
                <td><?php echo $val['supplier_catetory_name'];?></td>
                <td><?php echo $val['supplier_link_man'];?></td>
                <td><?php echo $val['supplier_link_mobile'];?></td>
                <td class="ncgs-table-handle">
                    <span>
                        <a href="javascript:void(0)" data-name="<?php echo $val['supplier_name'];?>" data-id="<?php echo $val['supplier_unique_id'];?>" class="un-supplier hgbtn-mini hgbtn-bluejeansjeans mt10">选择</a>
                    </span>
                </td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="20" class="norecord"><div class="warning-option"><i class="icon-warning-sign"></i><span>暂无符合条件的数据记录</span></div></td>
        </tr>
    <?php }?>
    </tbody>
    <tfoot>
    <?php if(!empty($output['supplier_list'])) {?>
        <tr>
            <td colspan="20"><div class="pagination"><?php echo $output['show_page']; ?></div></td>
        </tr>
    <?php }?>
    </tfoot>
</table>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL?>/js/layer/layer.js"></script>
<script>
    $(function(){
        var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
        $(".un-supplier").click(function () {
            var supplier_name = $(this).attr('data-name');
            var supplier_unique_id = $(this).attr('data-id');
            parent.$('#layer_supplier_name').text(supplier_name);
            parent.$('#layer_supplier_unique_id').text(supplier_unique_id);
            parent.layer.close(index);
        })
    })
</script>
