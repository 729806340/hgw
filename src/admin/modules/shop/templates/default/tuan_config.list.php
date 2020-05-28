<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>社区团购</h3>
        <h5>社区团购活动设置及管理</h5>
      </div>
      <ul class="tab-base nc-row">
        <?php   foreach($output['menu'] as $menu) {  if($menu['menu_type'] == 'text') { ?>
        <li><a href="JavaScript:void(0);" class="current"><?php echo $menu['menu_name'];?></a></li>
        <?php }  else { ?>
        <li><a href="<?php echo $menu['menu_url'];?>" ><?php echo $menu['menu_name'];?></a></li>
        <?php  } }  ?>
      </ul>
    </div>
  </div>

  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
        <li>平台发布的社区团购活动列表</li>
        <li>活动发布后可以供用户选用</li>
    </ul>
  </div>

  <div id="flexigrid"></div>

    <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
    <div class="ncap-search-bar">
      <div class="handle-btn" id="searchBarClose"><i class="fa fa-search-minus"></i>收起边栏</div>
      <div class="title">
        <h3>高级搜索</h3>
      </div>
      <form method="get" name="formSearch" id="formSearch">
        <input type="hidden" name="advanced" value="1" />
        <div id="searchCon" class="content">
          <div class="layout-box">
            <dl>
              <dt>活动名称</dt>
              <dd>
                <input type="text" name="xianshi_name" class="s-input-txt" placeholder="请输入活动名称关键字" />
              </dd>
            </dl>
            <dl>
              <dt>活动时期筛选</dt>
              <dd>
                <label>
                    <input type="text" name="pdate1" data-dp="1" class="s-input-txt" placeholder="结束时间不晚于" />
                </label>
                <label>
                    <input type="text" name="pdate2" data-dp="1" class="s-input-txt" placeholder="开始时间不早于" />
                </label>
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

<script>
$(function(){
    var flexUrl = 'index.php?act=tuan_config&op=config_tuan_list_xml';

    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '操作', name: 'operation', width: 150, sortable: false, align: 'center', className: 'handle'},
            {display: '编号', name: 'config_xianshi_id', width: 70, sortable: false, align: 'left'},
            {display: '活动名称', name: 'config_xianshi_name', width: 280, sortable: false, align: 'left'},
            {display: '开始时间', name: 'config_start_time_text', width: 120, sortable: false, align: 'center'},
            {display: '结束时间', name: 'config_end_time_text', width: 120, sortable: false, align: 'center'},
            {display: '发货时间', name: 'send_product_date', width: 120, sortable: false, align: 'center'},
            {display: '类型', name: 'config_type', width: 120, sortable: false, align: 'center'},
        ],
        searchitems: [
            {display: '活动名称', name: 'config_xianshi_name', isdefault: true},
        ],
        buttons: [
            {
                display: '<i class="fa fa-plus"></i>新增社区团购',
                name: 'add',
                bclass: 'add',
                title: '新增社区团购',
                onpress: function() {
                    location.href = '<?php echo urlAdminShop('tuan_config', 'config_tuan_add'); ?>';
                }
            },
        ],
        sortname: "config_xianshi_id",
        sortorder: "desc",
        rp : 10,
        title: '社区团购活动列表'
    });

    // 高级搜索提交
    $('#ncsubmit').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl + '&' + $("#formSearch").serialize(),query:'',qtype:''}).flexReload();
    });

    // 高级搜索重置
    $('#ncreset').click(function(){
        $("#flexigrid").flexOptions({url: flexUrl}).flexReload();
        $("#formSearch")[0].reset();
    });

    $('[data-dp]').datepicker({dateFormat: 'yy-mm-dd'});

});

$('a[data-href]').live('click', function() {
    if ($(this).hasClass('confirm-on-click') && !confirm('确定"'+$(this).text()+'"?')) {
        return false;
    }

    $.getJSON($(this).attr('data-href'), function(d) {
        if (d && d.result) {
            $("#flexigrid").flexReload();
        } else {
            alert(d && d.message || '操作失败！');
        }
    });
});

</script>
