<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<link rel="stylesheet" href="<?php echo ADMIN_RESOURCE_URL; ?>/js/treeTable/vsStyle/jquery.treeTable.css" type="text/css">
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
                <h3>自定义分类管理</h3>
                <h5>用于前台分类展示，与系统商品分类无关</h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span']; ?>"></span>
        </div>
        <ul>
            <li>用于前台分类展示，与系统商品分类无关</li>
            <li>对分类作任何更改后，都需要到 平台 -> 设置 -> 清理缓存 清理 <a href="/admin/modules/system/index.php?act=cache"><span style="color: red;">自定义分类</span></a>，新的设置才会生效</li>
            <li>只有<span style="color: red;">顶级</span>分类才能编辑分类各种导航信息</li>
            <li>建议最多分成<span style="color: red;">3级</span>分类</li>
        </ul>
    </div>
    <div class="bDiv" style="height: auto;">
        <table class="flex-table autoht" cellpadding="0" cellspacing="0" border="0" id="treeTable1">
            <thead>
            <tr>
                <th width="180" align="left">分类名称</th>
                <th width="50" align="center"></th>
                <th width="100" align="center">分类ID</th>
                <th width="50" align="center">排序</th>
                <th width="350" align="center">分类链接</th>
                <th width="50" align="center">隐藏</th>
                <th width="100" align="center">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($output['cats'] as $k=>$v){?>
                <tr id="<?php echo $v['cat_id'];?>" pId="<?php echo $v['parent_id'];?>">
                    <td style="width:180px;"><?php echo $v['cat_name'];?></td>
                    <td style="width:50px;"><i class="ico-check"></i></td>
                    <td style="width:100px;"><?php echo $v['cat_id'];?></td>
                    <td style="width:50px;"><?php echo $v['cat_sort'];?></td>
                    <td style="width:350px;"><input type="text" value="<?php echo $v['cat_link'];?>" style="width: 300px;"></td>
                    <td style="width:50px;"><?php echo $v['disable'] == 'true' ? '<span class="yes"><i class="fa fa-check-circle"></i>'.$lang['nc_yes'].'</span>' : '<span class="no"><i class="fa fa-ban"></i>'.$lang['nc_no'].'</span>';?></td>
                    <td class="handle">
                        <a class="btn red" href="javascript:void(0);" onclick="fg_del(<?php echo $v['cat_id'];?>);"><i class="fa fa-trash-o"></i>删除</a>
                        <span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em>
                            <ul>
                                <li><a href="index.php?act=goods_category&op=goods_category_edit&cat_id=<?php echo $v['cat_id'];?>">编辑分类信息</a></li>
                                <li><a href="index.php?act=goods_category&op=goods_category_add&parent_id=<?php echo $v['cat_id'];?>">新增下级分类</a></li>
                                <?php if($v['parent_id'] == 0){?>
                                <li><a href="index.php?act=goods_category&op=category_nav_edit&cat_id=<?php echo $v['cat_id'];?>">编辑分类导航</a></li>
                                <?php }?>
                            </ul>
                        </span>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <div class="iDiv" style="display: none;"></div>
    </div>
</div>

<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL; ?>/js/treeTable/jquery.treeTable.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL; ?>/js/jquery.edit.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function(){
        var option = {
            theme:'vsStyle',
            expandLevel : 2,
            onSelect : function($treeTable, id) {
                window.console && console.log('onSelect:' + id);
            }
        };
        $('#treeTable1').treeTable(option);
    });
    $(function () {
        $('.flex-table').flexigrid({
            height: 'auto',// 高度自动
            usepager: false,// 不翻页
            striped: false,// 不使用斑马线
            resizable: false,// 不调节大小
            title: '自定义分类列表',// 表格标题
            reload: false,// 不使用刷新
            columnControl: true,// 不使用列控制
            buttons: [
                {display: '<i class="fa fa-plus"></i>新增数据', name: 'add', bclass: 'add', onpress: fg_operation},
                /*{display: '<i class="fa fa-trash"></i>批量删除', name: 'del', bclass: 'del', title: '将选定行数据批量删除', onpress: fg_operation},*/
                /*{display: '<i class="fa fa-file-excel-o"></i>导出数据', name: 'csv', bclass: 'csv', title: '导出全部分类数据', onpress: fg_operation}*/
            ]
        });

        $('span[nc_type="inline_edit"]').inline_edit({act: 'goods_class', op: 'ajax'});
    });

    function fg_operation(name, bDiv) {
        if (name == 'add') {
            window.location.href = 'index.php?act=goods_category&op=goods_category_add';
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
            $.getJSON('index.php?act=goods_category&op=goods_category_del', {cat_id: id}, function (data) {
                if (data.state) {
                    location.reload();
                } else {
                    showError(data.msg)
                }
            });
        }
    }
</script>