<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
#gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
  <form method="post" action="index.php?act=fenxiao_goods&op=index&action=add" target="_parent" name="store_certification_form" id="store_certification_form" enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <input name="gid" id="gid" type="hidden" value="<?php echo $output['gid'];?>">
    <input name="pid" id="pid" type="hidden" value="<?php echo $output['pid'];?>">
    <input name="catename" id="catename" type="hidden" value="<?php echo $output['catename'];?>">
      <ul style="padding: 30px 50px;">
    <?php foreach ($output['member_fenxiao'] as $k => $v) { ?>
            <li><input type="checkbox" name="uid" value="<?php echo $v['member_id']?>"><?php echo $v['member_cn_code']?></li>
      <?php }?>
      </ul>
    <div class="bottom">
      <label class="submit-border"><input type="button" id="btn_add_certification" class="submit" value="<?php echo $lang['nc_submit'];?>" /></label>
    </div>
  </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
	//页面输入内容验证
    $('#btn_add_certification').on('click', function() {
        var url = 'index.php?act=fenxiao_goods&op=index&action=add';
        var gid = $('#gid').val();
        var pid = $('#pid').val();
        var goods_name = $('#catename').val();
        var uid = $('input[name="uid"]:checked').val();
        var uids = new Array();
        $('input[name="uid"]:checked').each(function(){
            uids.push($(this).val());//向数组中添加元素
        });
        var uids = uids.join(',');
        if (uids == undefined) {
            alert('请选择一个分销聚到');return;
        }
        showDialog('确认要添加吗？', 'confirm', '', function(){
            $.post(url, {gid:gid,pid:pid,goods_name:goods_name,uid:uids}, function (data) {
                alert(data.msg);
                location.href = data.url;
            }, 'json');
        	// ajaxpost('store_certification_form', '', '', 'onerror')
        });
    });

    $('input[nc_type="logo"]').change(function(){
        var src = getFullPath($(this)[0]);
        $('img[nc_type="logo1"]').attr('src', src);
    });
});
</script>
