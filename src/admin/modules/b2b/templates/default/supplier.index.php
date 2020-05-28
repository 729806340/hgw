<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style type="text/css">
    .flexigrid .bDiv tr:nth-last-child(2) span.btn ul {
        bottom: 0;
        top: auto
    }
</style>


<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>供应商管理</h3>
            </div>
            <?php echo $output['top_link']; ?> </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span']; ?>"></span>
        </div>
        <ul>
            <li>供应商管理</li>
        </ul>
    </div>
    <form method='post'>
        <input type="hidden" name="form_submit" value="ok"/>
        <input type="hidden" name="submit_type" id="submit_type" value=""/>
        <table class="flex-table">
            <thead>
            <tr>
                <th width="24" align="center" class="sign"><i class="ico-check"></i></th>
                <th width="150" class="handle" align="center"><?php echo $lang['nc_handle']; ?></th>
                <th width="70" align="center">供应商ID</th>
                <th width="100" align="center">供应商名称</th>
                <th width="100" align="center">供应商类型</th>

                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($output['class_list']) && is_array($output['class_list'])) { ?>
                <?php foreach ($output['class_list'] as $k => $v) { ?>
                    <tr data-id="<?php echo $v['gc_id']; ?>">
                        <td class="sign"><i class="ico-check"></i></td>
                        <td class="handle">
                            <a class="btn red" href="javascript:void(0);" onclick="fg_del(<?php echo $v['bc_id']; ?>);"><i class="fa fa-trash-o"></i><?php echo $lang['nc_del']; ?></a>
            <span class="btn"><em><i class="fa fa-cog"></i><?php echo $lang['nc_set']; ?><i class="arrow"></i></em>
            <ul>
                <li><a href="index.php?act=supplier&op=my_goods_list&supplier_id=<?php echo $v['supplier_id']; ?>">编辑供应商商品</a></li>
                <li><a href="index.php?act=supplier&op=supplier_edit&supplier_id=<?php echo $v['supplier_id']; ?>">编辑供应商信息</a></li>
            </ul>
            </span></td>
                        <td><?php echo $v['supplier_id']; ?></td>
                        <td><?php echo $v['company_name']; ?></td>
                        <td><?php if($v['manage_type'] == 'co_construct'){ echo '共建'; } else if($v['manage_type'] == 'platform'){ echo '平台';}  ?></td>
                        <td></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td class="no-data" colspan="100"><i class="fa fa-exclamation-circle"></i><?php echo $lang['nc_no_record']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL; ?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function () {
        $('.flex-table').flexigrid({
            height: 'auto',// 高度自动
            usepager: false,// 不翻页
            striped: false,// 不使用斑马线
            resizable: false,// 不调节大小
            title: '供应商管理',// 表格标题
            reload: false,// 不使用刷新
            columnControl: false,// 不使用列控制
            buttons: [
                {display: '<i class="fa fa-plus"></i>新增供应商', name: 'add', bclass: 'add', onpress: fg_operation}
//                {display: '<i class="fa fa-trash"></i>批量删除', name: 'del', bclass: 'del', title: '将选定行数据批量删除', onpress: fg_operation}
//                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name: 'csv', bclass: 'csv', title: '导出全部分类数据', onpress: fg_operation}
            ]
        });

        $('span[nc_type="inline_edit"]').inline_edit({act: 'goods_class', op: 'ajax'});
    });

    function fg_operation(name, bDiv) {
        if (name == 'add') {
            window.location.href = 'index.php?act=supplier&op=supplier_add';
        } else if (name == 'del') {
            if ($('.trSelected', bDiv).length == 0) {
                showError('请选择要操作的数据项！');
            }
            var itemids = new Array();
            $('.trSelected', bDiv).each(function (i) {
                itemids[i] = $(this).attr('data-id');
            });
            fg_del(itemids);
        } else if (name = 'csv') {
            window.location.href = 'index.php?act=goods_class&op=goods_class_export';
        }
    }
    function fg_del(ids) {
        if (typeof ids == 'number') {
            var ids = new Array(ids.toString());
        }
        ;
        id = ids.join(',');
        if (confirm('删除后将不能恢复，确认删除这项吗？')) {
            $.getJSON('index.php?act=goods_class&op=goods_class_del', {id: id}, function (data) {
                if (data.state) {
                    location.reload();
                } else {
                    showError(data.msg)
                }
            });
        }
    }
</script>