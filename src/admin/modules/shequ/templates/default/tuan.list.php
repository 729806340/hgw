<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<style>
    .nscs-table-handle { font-size: 0; *word-spacing:-1px/*IE6、7*/;}
    .nscs-table-handle span { vertical-align: middle; letter-spacing: normal; word-spacing: normal; text-align: center; display: inline-block; padding: 0 4px; border-left: solid 1px #E6E6E6;}
    .nscs-table-handle span { *display: inline/*IE6,7*/;}
    .nscs-table-handle span:first-child { border-left: none 0;}
    .nscs-table-handle span a { color: #777; background-color: #FFF; display: block; padding: 3px 7px; margin: 1px;}
    .nscs-table-handle span a i { font-size: 14px; line-height: 16px; height: 16px; display: block; clear: both; margin: 0; padding: 0;}
    .nscs-table-handle span a p { font: 12px/16px arial; height: 16px; display: block; clear: both; margin: 0; padding: 0;}
    .nscs-table-handle span a:hover { text-decoration: none; color: #FFF; margin: 0; border-style: solid; border-width: 1px;}

    .ncsc-upload-btn { vertical-align: top; display: inline-block; *display: inline/*IE7*/; width: 80px; height: 30px; margin: 5px 5px 0 0; *zoom:1;}
    .ncsc-upload-btn a { display: block; position: relative; z-index: 1;}
    .ncsc-upload-btn span { width: 80px; height: 30px; position: absolute; left: 0; top: 0; z-index: 2; cursor: pointer;}
    .ncsc-upload-btn .input-file { width: 80px; height: 30px; padding: 0; margin: 0; border: none 0; opacity:0; filter: alpha(opacity=0); cursor: pointer; }
    .ncsc-upload-btn p { font-size: 12px; line-height: 20px; background-color: #F5F5F5; color: #999; text-align: center; color: #666; width: 78px; height: 20px; padding: 4px 0; border: solid 1px; border-color: #DCDCDC #DCDCDC #B3B3B3 #DCDCDC; position: absolute; left: 0; top: 0; z-index: 1;}
    .ncsc-upload-btn p i {vertical-align: middle;margin-right: 4px;}
    .ncsc-upload-btn a:hover p { background-color: #E6E6E6; color: #333; border-color: #CFCFCF #CFCFCF #B3B3B3 #CFCFCF;}
</style>
<div class="page">
  <!-- 页面导航 -->
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo '接龙页面';?></h3>
        <h5><?php echo '接龙列表管理';?></h5>
      </div>
        <?php echo $output['top_link'];?>
    </div>
  </div>

  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo '';?>"><?php echo'';?></h4>
      <span id="explanationZoom" title="<?php echo '';?>"></span> </div>
    <ul>
      <li><?php echo '';?></li>
    </ul>
  </div>
    <form method="get" action="index.php" target="_self">
    <tr>
        <td class="w70 tc" style="width:20%">
            <div class="upload-con-div">
                <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                    <input type="file" style="width:130px;"  id="batch_file" hidefocus="true" size="1" class="input-file" name="file"/>
                    </span>
                        <p style="width: 130px;"><i class="icon-upload-alt"></i><?php echo '上传批量发货的excel';?></p>
                    </a> </div></div>
        </td>
    </tr>
        </form>
  <div id="flexigrid"></div>

   <!-- <div class="ncap-search-ban-s" id="searchBarOpen"><i class="fa fa-search-plus"></i>高级搜索</div>
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
              <dt>代金券名称</dt>
              <dd>
                <input type="text" name="voucher_t_title" class="s-input-txt" placeholder="请输入代金券名称关键字" />
              </dd>
            </dl>
            <dl>
              <dt>店铺名称</dt>
              <dd>
                <input type="text" name="voucher_t_storename" class="s-input-txt" placeholder="请输入店铺名称关键字" />
              </dd>
            </dl>
            <dl>
              <dt>修改时间</dt>
              <dd>
                  <label>
                    <input type="text" name="sdate" data-dp="1" class="s-input-txt" placeholder="请选择筛选时间段起点" />
                  </label>
                  <label>
                    <input type="text" name="edate" data-dp="1" class="s-input-txt" placeholder="请选择筛选时间段终点" />
                  </label>
              </dd>
            </dl>
            <dl>
              <dt>领取方式</dt>
              <dd>
                <select name="voucher_t_gettype" class="s-select">
                    <option value="0" selected>全部</option>
                    <?php /*if ($output['gettype_arr']){ */?>
                    <?php /*foreach ($output['gettype_arr'] as $k=>$v){ */?>
                    <option value="<?php /*echo $v['sign'];*/?>"><?php /*echo $v['name'];*/?></option>
                    <?php /*} */?>
                    <?php /*} */?>
                </select>
              </dd>
            </dl>
            <dl>
              <dt>状态</dt>
              <dd>
                <select name="voucher_t_state" class="s-select">
                    <option value="0" selected>全部</option>
                    <?php /*if ($output['templateState']){ */?>
                    <?php /*foreach ($output['templateState'] as $k=>$v){ */?>
                    <option value="<?php /*echo $v[0];*/?>"><?php /*echo $v[1];*/?></option>
                    <?php /*} */?>
                    <?php /*} */?>
                </select>
              </dd>
            </dl>
            <dl>
              <dt>推荐</dt>
              <dd>
                <select name="voucher_t_recommend" class="s-select">
                    <option value="" selected>全部</option>
                    <option value="1" >是</option>
                    <option value="0" >否</option>
                </select>
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
          <a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php /*echo $lang['nc_cancel_search'];*/?></a>
        </div>
      </form>
    </div>-->

</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script>
$(function(){
    var flexUrl = 'index.php?act=tuan_list&op=tuan_order_xml';
    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '操作', name: 'operation', width: 60, sortable: false, align: 'center', className: 'handle'},
            {display: '姓名', name: 'name', width: 80, sortable: false, align: 'left'},
            {display: '开始时间', name: 'phone', width: 120, sortable: false, align: 'left'},
            {display: '结束时间', name: 'sn', width: 120, sortable: false, align: 'left'},
            {display: '配送方式', name: 'sn_image1', width: 80, sortable: true, align: 'left'},
            {display: '收货地址', name: 'sn_image2', width: 80, sortable: true, align: 'left'},
            {display: '经度', name: 'type', width: 80, sortable: true, align: 'center'},
            {display: '纬度', name: 'category', width: 120, sortable: true, align: 'center'},
            {display: '总金额', name: 'zhandui', width: 120, sortable: true, align: 'center'},
            {display: '订单数量', name: 'area', width: 80, sortable: false, align: 'center'},
            {display: '佣金金额', name: 'street', width: 80, sortable: false, align: 'center'},
            {display: '退款金额', name: 'community', width: 80, sortable: false, align: 'center'},
            {display: '退款佣金', name: 'address', width: 80, sortable: false, align: 'center'},
        ],
        searchitems: [
            {display: '', name: '', isdefault: true},
   /*         {display: '', name: 'voucher_t_storename'}*/
        ],
        sortname: " ",
        sortorder: " ",
        title: '社区接龙列表'
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


    $('#batch_file').fileupload({
        dataType:'json',
        url:"index.php?act=tuan_list&op=upload",
        done: function (e,data){
            if(data.result.state==false){
                showError(data.result.msg);
                return false;
            }else{
                var answer = confirm(data.result.msg);
                if(answer){
                    window.location.href="index.php?act=tuan_list&op=export_excel_result&key_name=" + data.result.key_name;
                }
            }
        }
    });

});

$('a.confirm-on-click').live('click', function() {
    return confirm('确定"'+this.innerText+'"?');
});
</script>
