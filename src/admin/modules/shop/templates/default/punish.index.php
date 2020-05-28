<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>店铺罚款</h3>
        <h5>包括店铺罚款和订单罚款</h5>
      </div>
      <?php //echo $output['top_link'];?>
    </div>
  </div>
    <div id="result"></div>
  <div id="flexigrid"></div>
    <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
    <div class="ncap-search-bar">
      <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
      <div class="title">
        <h3>高级搜索</h3>
      </div>
      <form method="get" name="formSearch" id="formSearch">
        <div id="searchCon" class="content">
          <div class="layout-box">
            <dl>
              <dt>店铺名称</dt>
              <dd>
                <input type="text" value="" name="store_name" id="store_name" class="s-input-txt">
              </dd>
            </dl>
            <dl>
              <dt>分销订单编号</dt>
              <dd>
                <input type="text" value="" name="fx_order_id" id="fx_order_id" class="s-input-txt">
              </dd>
            </dl>
            <dl>
              <dt>分销渠道</dt>
              <dd>
                  <select name="channel_id" id="channel_id" title="">
                      <option value="0">请选择渠道</option>
                      <?php
                      foreach ($output['member_fenxiao'] as $member){
                          echo "<option value='{$member['member_id']}'>{$member['member_cn_code']}</option>";
                      }
                      ?>
                  </select>
              </dd>
            </dl>
          </div>
        </div>
        <div class="bottom">
          <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green">提交查询</a>
          <a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a>
        </div>
      </form>
    </div>
</div>

<div id="mask" style="position: fixed;top: 0;bottom: 0;left: 0;right: 0;background: #333;opacity: .3;z-index: 9999; display: none;">
</div>
<div id="loading" style="z-index:9999;position: fixed; top: 100px; width: 100%; text-align: center;display: none;">
    <p style="background:#FFF;margin: 100px auto; width: 300px; padding: 20px 30px; font-size: 16px;">正在处理，请勿关闭页面...</p>
</div>
<div style="display: none;">
    <input type="file" id="data-file" name="file">
</div>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.iframe-transport.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.ui.widget.js"
        charset="utf-8"></script>
<script type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js"
        charset="utf-8"></script>
<script type="text/javascript">
$(function(){

    var file = $('#data-file'),mask = $('#mask'),loading = $('#loading'),resultPanel = $('#result'),uploaded=false;
    file.change(function (e) {
        console.log(e,this);
        if(this.files.length>0){
            console.log(e,this.files);
            var file = this.files[0],filename = file.name;
            if(/.*\.csv$/i.test(filename) === false) return alert('仅支持CSV文件');
            mask.fadeIn();loading.fadeIn();
        }
    });

    file.fileupload({
        dataType: 'json',
        url: 'index.php?act=punish&op=import',
        done: function (e,data) {
            mask.fadeOut();loading.fadeOut();
            uploaded=true;
            var param = data.result;
            if(param.state==false){
                showError(param.msg);
                return false;
            }
            showSucc('导入完成。请核对导入结果，且不要重复导入。');
            var tips = '<div class="title"><h4>文件上传成功</h4> </div>';
            tips += "<ul class=\"mt5\">";
            tips +="<li>批量罚款的订单有<font color=\"red\">"+param.result.total+"</font>个；</li>";
            tips +="<li>罚款成功的有<font color=\"red\">"+param.result.success+"</font>个；</li>";
            tips +="<li>罚款失败的有<font color=\"red\">"+param.result.fail.length+"</font>个；</li>";
            if(param.result.fail.length>0){
                tips +="<li>罚款的失败的编号有：</li>";
                for(var i = 0 ; i<param.result.fail.length; i++){
                    tips +=param.result.fail[i]+"、";
                }
                tips +="<li>失败原因：</li>";
                for(var i = 0 ; i<param.result.errorMsg.length; i++){
                    tips +=	"<li>"+param.result.errorMsg[i]+"</li>";
                }
            }
            tips +="</ul>";
            resultPanel.html(tips).fadeIn('fast');
        },
        fail : function () {
            mask.fadeOut();loading.fadeOut();
            uploaded=true;
            showError('上传失败');
        }
    });

    $("#flexigrid").flexigrid({
        url: 'index.php?act=punish&op=get_xml',
        colModel : [
            {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
            {display: '店铺ID', name : 'cost_store_id', width : 40, sortable : true, align: 'center'},
            {display: '店铺名称', name : 'store_name', width : 150, sortable : false, align: 'left'},
            {display: '罚款金额', name : 'member_id', width : 120, sortable : true, align: 'left'},
            {display: '罚款原因', name : 'seller_name', width : 120, sortable : false, align: 'left'},
            {display: '关联订单', name : 'store_avatar', width: 60, sortable : false, align : 'center'},
            {display: '关联渠道', name : 'store_label', width: 60, sortable : false, align : 'center'},
            {display: '罚款时间', name : 'grade_id', width : 80, sortable : true, align: 'center'},
            {display: 'send_sap', name : 'store_time', width : 100, sortable : true, align: 'center'},
            {display: 'purchase_sap', name : 'store_end_time', width : 100, sortable : true, align: 'center'},
            {display: 'errInf', name : 'store_state', width : 80, sortable : true, align: 'center'},
            {display: 'check_result', name : 'sc_id', width : 80, sortable : true, align: 'left'},
            {display: 'check_status', name : 'area_info', width : 150, sortable : false, align : 'left'}
            ],
        buttons : [
            //{display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出CVS文件', onpress : fg_operation }	,
            {display: '<i class="fa fa-file-excel-o"></i><a href="/admin/public/punishtpl.csv">下载模板</a>', name : 'csv', bclass : 'csv', title : '下载模板' }	,
			{display: '<i class="fa fa-plus"></i>导入数据', name : 'import', bclass : 'add', title : '批量导入新数据到列表', onpress : fg_operations},
			{display: '<i class="fa fa-plus"></i>导出数据', name : 'csv',  title : '导出数据', onpress : fg_operation}
        ],
        searchitems : [
            {display: '店铺名称', name : 'store_name', isdefault: true},
            ],
        sortname: "cost_id",
        sortorder: "desc",
        title: '罚款列表'
    });

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=punish&op=get_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });

    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: 'index.php?act=punish&op=get_xml'}).flexReload();
        $("#formSearch")[0].reset();
    });
});

function fg_operation(name, bDiv) {
    if (name == 'csv') {
        if ($('.trSelected', bDiv).length == 0) {
            if (!confirm('您确定要下载全部数据吗？')) {
                return false;
            }
        }
        var itemids = new Array();
        $('.trSelected', bDiv).each(function(i){
            itemids[i] = $(this).attr('data-id');
        });
        fg_csv(itemids);
    }
}
function fg_operations(name, bDiv) {
    if (name === 'import') {
        $('#data-file').click();
    }
}

function fg_csv(ids) {
    id = ids.join(',');
    window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_csv&id=' + id;
}
</script>