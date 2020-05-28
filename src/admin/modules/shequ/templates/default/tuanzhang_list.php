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
        <div class="item-title"><a class="back" href="index.php?act=tuan_config" title="返回活动列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3><?php echo '团长页面';?></h3>
                <h5><?php echo '团长列表管理';?></h5>
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
                        <dt>关键字搜索</dt>
                        <dd>
                            <label>
                                <select class="s-select" name="keyword_type">
                                    <option selected="selected" value="">-请选择-</option>
                                    <option value="tz_name">团长名称</option>
                                    <option value="tz_phone">团长电话</option>
                                </select>
                            </label>
                            <label>
                                <input type="text" value="" placeholder="请输入关键字" name="keyword" class="s-input-txt">
                            </label>
                            <label>
                                <input type="checkbox" id="jq_query" value="1" name="jq_query">精确

                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>地区</dt>
                        <dd>
                            <label>
                                <select class="s-select" name="address_list">
                                    <option selected="selected" value="">-请选择-</option>
                                    <option value="area">区</option>
                                    <option value="street">街道</option>
                                    <option value="community">社区</option>
                                </select>
                            </label>
                            <label>
                                <input type="text" value="" placeholder="请输入关键字" name="keyword_address" class="s-input-txt">
                            </label>
                            <label>
                                <input type="checkbox" id="jq_query_address" value="1" name="jq_query_address">精确

                            </label>
                        </dd>
                    </dl>
                    <dl>
                        <dt>状态</dt>
                        <dd>
                            <label>
                                <select name="order_state" class="s-select">
                                    <option value="">请选择</option>
                                    <option value="10">已下单</option>
                                    <option value="20">已成团</option>
                                    <option value="30">已分派司机</option>
                                    <option value="40">配送完成</option>
                                    <option value="0">成团失败</option>
                                </select>
                            </label>
                        </dd>
                    </dl>
                    <input type="hidden" name="config_tuan_id" id="config_tuan_id" value="<?php echo $_GET['config_tuan_id']?>">
                </div>
            </div>
            <div class="bottom">
                <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green">提交查询</a>
                <a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a>
            </div>
        </form>
    </div>

    <form method="get" action="index.php" target="_self">
        <tr>
            <td class="w70 tc" style="width:20%">
                <div class="upload-con-div">
                    <div class="ncsc-upload-btn"> <a href="javascript:void(0);"><span>
                     <!--<input type="file" style="width:130px;"  id="batch_file" hidefocus="true" size="1" class="input-file" name="file"/>-->
                    </span>
                            <!--<p style="width: 130px;"><i class="icon-upload-alt"></i><?php /*echo '批量打印';*/?></p>-->
                            <pstyle="width: 130px;"><i class="icon-upload-alt"></i> <a class="back" target="_blank" href="index.php?act=tuan_config&op=distribution_batch_print&tuan_config_id=<?php echo $output['tuan_config_id']; php?>" title="批量打印">批量打印</a></p>
                        </a> </div></div>
            </td>
        </tr>
    </form>
    <div id="flexigrid"></div>

</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script>
    $(function(){
        // 高级搜索提交
        $('#ncsubmit').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=tuan_config&op=tuanzhang_list_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
        });
        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=tuan_config&op=tuanzhang_list_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });
        var flexUrl = 'index.php?act=tuan_config&op=tuanzhang_list_xml&config_tuan_id=<?php echo $_GET['config_tuan_id']?>';
        $("#flexigrid").flexigrid({
            url: flexUrl,
            colModel: [
                {display: '操作', name: 'operation', width: 60, sortable: false, align: 'center', className: 'handle'},
                {display: '团长姓名', name: 'tz_name', width: 80, sortable: false, align: 'left'},
                {display: '团长电话', name: 'tz_phone', width: 80, sortable: false, align: 'left'},
                {display: '开始时间', name: 'start_time', width: 120, sortable: false, align: 'left'},
                {display: '结束时间', name: 'end_time', width: 120, sortable: false, align: 'left'},
                {display: '区', name: 'area', width: 80, sortable: true, align: 'left'},
                {display: '街道', name: 'street', width: 80, sortable: true, align: 'left'},
                {display: '社区', name: 'community', width: 80, sortable: true, align: 'left'},
                {display: '收货地址', name: 'address', width: 80, sortable: true, align: 'left'},
                {display: '门牌号', name: 'building', width: 80, sortable: true, align: 'left'},
                {display: '经度', name: 'longitude', width: 80, sortable: true, align: 'center'},
                {display: '纬度', name: 'latitude', width: 120, sortable: true, align: 'center'},
                {display: '状态', name: 'state', width: 80, sortable: true, align: 'center'},
              /*  {display: '总金额', name: 'total_amount', width: 80, sortable: true, align: 'center'},*/
                {display: '订单数量', name: 'order_num', width: 80, sortable: false, align: 'center'},
               /* {display: '佣金金额', name: 'commis_amount', width: 80, sortable: false, align: 'center'},*/
                {display: '退款金额', name: 'refund_amount', width: 80, sortable: false, align: 'center'},
                {display: '退款佣金', name: 'refund_commis_amount', width: 80, sortable: false, align: 'center'},
            ],
            searchitems: [
                {display: '团长名称', name: 'tz_name', isdefault: true},
                {display: '团长电话', name: 'tz_phone'}
            ],
            sortname: " ",
            sortorder: " ",
            title: '团长列表'
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

    // $('a.confirm-on-click').live('click', function() {
    //     return confirm('确定"'+this.innerText+'"?');
    // });
</script>
