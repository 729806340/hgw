<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo MEMBER_TEMPLATES_URL;?>/css/member.css" rel="stylesheet" type="text/css">
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3>领卡人</h3>
        <h5>领卡人维护</h5>
      </div>
      <ul class="tab-base nc-row">
        <li><a href="JavaScript:void(0);" class="current">列表</a></li>
      </ul>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>平台发布充值卡，用户可在会员中心通过输入正确充值卡号的形式对其充值卡账户进行充值。</li>
      <li>已经被领取的平台充值卡不能被删除。</li>
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
              <dt>充值卡卡号</dt>
              <dd>
                <input type="text" name="sn" class="s-input-txt" placeholder="请输入充值卡卡号关键字" />
              </dd>
            </dl>
            <dl>
              <dt>批次标识</dt>
              <dd>
                <input type="text" name="batchflag" class="s-input-txt" placeholder="请输入批次标识关键字" />
              </dd>
            </dl>
            <dl>
              <dt>发布管理员</dt>
              <dd>
                <input type="text" name="admin_name" class="s-input-txt" placeholder="请输入发布管理员关键字" />
              </dd>
            </dl>
            <dl>
              <dt>领取人</dt>
              <dd>
                <input type="text" name="member_name" class="s-input-txt" placeholder="请输入领取人" />
              </dd>
            </dl>
            <dl>
              <dt>状态</dt>
              <dd>
                <select name="state" class="s-select">
                    <option value="">全部</option>
                    <option value="1">已被领取</option>
                    <option value="0">未被领取</option>
                </select>
              </dd>
            </dl>
            <dl>
              <dt>激活状态</dt>
              <dd>
                <select name="disabled" class="s-select">
                    <option value="">全部</option>
                    <option value="1">已激活</option>
                    <option value="0">未激活</option>
                </select>
              </dd>
            </dl>
            <dl>
              <dt>发布时间</dt>
              <dd>
              <label>
                <input type="text" name="sdate" data-dp="1" class="s-input-txt" placeholder="请输入起始时间" />
              </label>
              <label>
                <input type="text" name="edate" data-dp="1" class="s-input-txt" placeholder="请输入终止时间" />
              </label>
              </dd>
            </dl>
            <dl>
              <dt>开卡时间</dt>
              <dd>
              <label>
                <input type="text" name="sdate1" data-dp="1" class="s-input-txt" placeholder="请输入起始时间" />
              </label>
              <label>
                <input type="text" name="edate1" data-dp="1" class="s-input-txt" placeholder="请输入终止时间" />
              </label>
              </dd>
            </dl>
            <dl>
              <dt>领取时间</dt>
              <dd>
              <label>
                <input type="text" name="sdate2" data-dp="1" class="s-input-txt" placeholder="请输入起始时间" />
              </label>
              <label>
                <input type="text" name="edate2" data-dp="1" class="s-input-txt" placeholder="请输入终止时间" />
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

$(function() {
    var flexUrl = 'index.php?act=receiver&op=index_xml';

    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '操作', name: 'operation', width: 150, sortable: false, align: 'center'},
            {display: '领卡人标识', name: 'sn', width: 200, sortable: false, align: 'left'},
            {display: '领卡人名称', name: 'receiver', width: 250, sortable: false, align: 'left'},
        ],
        buttons: [
            {
                display: '<i class="fa fa-plus"></i>新增领卡人',
                name: 'add',
                bclass: 'add',
                title: '添加新数据到列表',
                onpress: function() {
                    location.href = '<?php echo urlAdminShop('receiver', 'add_receiver'); ?>';
                }
            },
        ],
        searchitems: [
            {display: '领卡人标识', name: 'sn', isdefault: true},
            {display: '领卡人名称', name: 'receiver'}
        ],
        sortname: "id",
        sortorder: "desc",
        title: '领卡人列表'
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

    $("input[data-dp='1']").datepicker({dateFormat: 'yy-mm-dd'});

});

$('a[data-href]').live('click', function() {
    if ($(this).hasClass('confirm-del-on-click') && !confirm('确定删除?')) {
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
