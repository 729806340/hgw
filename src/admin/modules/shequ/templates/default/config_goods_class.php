<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style type="text/css">
    .flexigrid .bDiv tr:nth-last-child(2) span.btn ul {
        bottom: 0;
        top: auto
    }
</style>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config" title="返回活动列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>活动列表分类</h3>
                <h5>活动分类设置管理</h5>
            </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="<?php echo $lang['nc_prompts_title']; ?>"><?php echo $lang['nc_prompts']; ?></h4>
            <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span']; ?>"></span>
        </div>
        <ul>
            <li>分类导航信息设置完成后，需要更新“首页及频道”缓存。</li>
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
                <th width="70" align="center">分类ID</th>
                <th width="60" align="center"><?php echo $lang['nc_sort']; ?></th>
                <th width="300" align="left"><?php echo $lang['goods_class_index_name']; ?></th>
                <th width="60" align="center"><?php echo '类型名称'; ?></th>
                <th width="80" align="center">分类图片</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($output['class_list']) && is_array($output['class_list'])) { ?>
                <?php foreach ($output['class_list'] as $k => $v) { ?>
                    <tr data-id="<?php echo $v['config_gc_id']; ?>">
                        <td class="sign"><i class="ico-check"></i></td>
                        <td class="handle">
                           <!-- <a class="btn red" href="javascript:void(0);" onclick="fg_del(<?php /*echo $v['gc_id']; */?>);"><i class="fa fa-trash-o"></i><?php /*echo $lang['nc_del']; */?></a>-->
                            <span class="btn"><em><i class="fa fa-cog"></i><?php echo $lang['nc_set']; ?><i class="arrow"></i></em>
            <ul>
                <li><a href="index.php?act=tuan_config&op=goods_class_edit&config_gc_id=<?php echo $v['config_gc_id']; ?>&tuan_config_id=<?php echo $v['tuan_config_id']; ?>">编辑分类信息</a></li>
            </ul>
            </span></td>
                        <td><?php echo $v['config_gc_id']; ?></td>
                        <td class="sort"><span  title="<?php echo $lang['nc_editable']; ?>" column_id="<?php echo $v['config_gc_id']; ?>" fieldname="gc_sort" nc_type="inline_edit" class="editable "><?php echo $v['gc_sort']; ?></span></td>
                        <td class="name"><span title="<?php echo $lang['nc_editable']; ?>" column_id="<?php echo $v['config_gc_id']; ?>" fieldname="gc_name" nc_type="inline_edit" class="editable "><?php echo $v['gc_name']; ?></span></td>
                        <td class="wuliu_name" ><?php echo $v['type_name'] ?></td>
                        <td><a href='javascript:void(0);' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip("<?php echo $v['app_img']?"<img src=".UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.$v['app_img'].">":'';?>")'><i class='fa fa-picture-o'></i></a></td>
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
            title: '活动分类列表',// 表格标题
            reload: false,// 不使用刷新
            columnControl: false,// 不使用列控制
        /*buttons: [
            {display: '<i class="fa fa-plus"></i>新增数据', name: 'add', bclass: 'add', onpress: fg_operation},
              {display: '<i class="fa fa-trash"></i>批量删除', name: 'del', bclass: 'del', title: '将选定行数据批量删除', onpress: fg_operation},
              {display: '<i class="fa fa-file-excel-o"></i>导出数据', name: 'csv', bclass: 'csv', title: '导出全部分类数据', onpress: fg_operation}
            ]*/
        });

        $('span[nc_type="inline_edit"]').inline_edit({act: 'tuan_config', op: 'ajax_config'});
    });

   /* function fg_operation(name, bDiv) {
        if (name == 'add') {
            window.location.href = 'index.php?act=goods_class&op=goods_class_add';
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
    }*/
   /* function fg_del(ids) {
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
    }*/
</script>