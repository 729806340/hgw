<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['refund_manage'];?></h3>
        <h5><?php echo $lang['refund_manage_subhead'];?></h5>
      </div>
      <?php echo $output['top_link'];?>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span>
    </div>
    <ul>
      <li>买家提交申请，商家同意并经平台确认后，退款金额以预存款的形式返还给买家（充值卡部分只能退回到充值卡余额）。</li>
        <li>客服人员可进行撤销退款的操作，会改变退款状态为商家已拒绝，并对订单做相应处理。</li>
    </ul>
  </div>

    <div class="explanation mt10" id="result" style="display:none;"></div>
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
            <dt>关键字搜索</dt>
            <dd>
              <label>
                <select class="s-select" name="keyword_type">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="refund_sn">退单编号</option>
                  <option value="goods_name">商品名称</option>
                  <option value="buyer_name">买家账号</option>
                  <option value="store_name">店铺名称</option>
                  <option value="order_sn">订单编号</option>
                    <option value="member_fenxiao_name">渠道名称</option>
                    <option value="fx_order_id">分销订单编号</option>
                </select>
              </label>
              <label>
                <input type="text" value="" placeholder="请输入关键字" name="keyword" class="s-input-txt">
              </label>
              <label>
                <input type="checkbox" value="1" name="jq_query">精确
              </label>
            </dd>
          </dl>
          <dl>
            <dt>日期筛选</dt>
            <dd>
              <label>
                <select class="s-select" name="qtype_time">
                  <option selected="selected" value="">-请选择-</option>
                  <option value="add_time">买家申请时间</option>
                  <option value="seller_time">商家处理时间</option>
                  <option value="admin_time">平台处理时间 </option>
                </select>
              </label>
              <label>
                <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
            <dl>
                <dt>是否分销订单</dt>
                <dd>
                    <label>
                        <select class="s-select" name="fenxiao_type">
                            <option selected="selected" value="">-请选择-</option>
                            <option value="hango">来自平台</option>
                            <option value="fenxiao">来自分销</option>
                        </select>
                    </label>
                </dd>
            </dl>

            <dl>
            <dt>退款金额</dt>
            <dd>
              <label>
                <input placeholder="请输入起始金额" name=query_start_amount value="" type="text" class="s-input-txt" />
              </label>
              <label>
                <input placeholder="请输入结束金额" name="query_end_amount" value="" type="text" class="s-input-txt" />
              </label>
            </dd>
          </dl>
		  <dl>
            <dt>平台处理状态</dt>
            <dd>
              <label>
                <select class="s-select" name="refund_state">
                  <option selected="selected" value="">-请选择-</option>
				  <option value="1">商家处理中</option>
                  <option value="2">待平台处理</option>
                  <option value="3">已完成</option>
                </select>
              </label>
            </dd>
          </dl>
            <input type="hidden" name="shequ_tuan_id" id="shequ_tuan_id" value="">
            <input type="hidden" name="shequ_tz_id" id="shequ_tz_id" value="">
        </div>
      </div>
      <div class="bottom"> <a href="javascript:void(0);" id="ncsubmit" class="ncap-btn ncap-btn-green mr5">提交查询</a><a href="javascript:void(0);" id="ncreset" class="ncap-btn ncap-btn-orange" title="撤销查询结果，还原列表项所有内容"><i class="fa fa-retweet"></i><?php echo $lang['nc_cancel_search'];?></a></div>
    </form>
  </div>
</div>
<div id="mask" style="position: fixed;top: 0;bottom: 0;left: 0;right: 0;background: #333;opacity: .3;z-index: 9999; display: none;">
</div>
<div id="loading" style="z-index:9999;position: fixed; top: 100px; width: 100%; text-align: center;display: none;">
    <p style="background:#FFF;margin: 100px auto; width: 300px; padding: 20px 30px; font-size: 16px;">正在处理，请勿关闭页面...</p>
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
        $('#query_start_date').datepicker();
        $('#query_end_date').datepicker();
        // 高级搜索提交
        $('#ncsubmit').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=refund&op=get_all_xml&'+$("#formSearch").serialize(),query:'',qtype:''}).flexReload();
        });
        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#flexigrid").flexOptions({url: 'index.php?act=refund&op=get_all_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });
        //社区团订单详情
        var url = 'index.php?act=refund&op=get_all_xml';
        var  shequ_tuan_id  = '<?php echo $_GET['shequ_tuan_id']; ?>'
        console.log(shequ_tuan_id);
        if(shequ_tuan_id){
            $("#shequ_tuan_id").val(<?php echo $_GET['shequ_tuan_id']; ?>);
            $("#shequ_tz_id").val(<?php echo $_GET['shequ_tz_id']; ?>);
            url = url+"&"+$("#formSearch").serialize();
        }
        $("#flexigrid").flexigrid({
           // url: 'index.php?act=refund&op=get_all_xml',
              url: url,
            colModel : [
                {display: '操作', name : 'operation', width : 150, sortable : false, align: 'center', className: 'handle'},
                {display: '订单编号', name : 'order_sn', width : 130, sortable : false, align: 'center'},
                {display: '退单编号', name : 'refund_sn', width : 130, sortable : false, align: 'center'},
                {display: '渠道', name : 'fx_name', width : 130, sortable : false, align: 'center'},
                {display: '退款金额(元)', name : 'refund_amount', width : 70, sortable : true, align: 'left'},
                {display: '申请图片', name : 'pic_info', width : 70, sortable : false, align : 'left'},
                {display: '类型', name : 'is_aftersale', width : 70, sortable : false, align : 'left'},
                {display: '申请原因', name : 'buyer_message', width : 120, sortable : false, align: 'left'},
                {display: '申请时间', name : 'refund_id', width: 120, sortable : true, align : 'center'},
                {display: '涉及商品', name : 'goods_name', width : 120, sortable : false, align: 'left'},
                {display: '商家处理', name : 'seller_state', width : 80, sortable : true, align: 'center'},
                {display: '平台处理', name : 'refund_state', width : 80, sortable : false, align: 'center'},
                {display: '商家处理备注', name : 'seller_message', width : 120, sortable : false, align: 'left'},
                {display: '平台处理备注', name : 'admin_message', width : 120, sortable : false, align: 'left'},
                {display: '商家申核时间', name : 'seller_time', width: 120, sortable : true, align : 'center'},
                {display: '商品图', name : 'goods_image', width : 40, sortable : true, align: 'center'},
                {display: '商品ID', name : 'goods_id', width : 80, sortable : true, align: 'center'},
                {display: '买家', name : 'buyer_name', width : 60, sortable : true, align: 'left'},
                {display: '买家ID', name : 'buyer_id', width : 40, sortable : true, align: 'center'},
                {display: '商家名称', name : 'store_name', width : 100, sortable : true, align: 'left'},
                {display: '商家ID', name : 'store_id', width : 40, sortable : true, align: 'center'},
                {display: '退款方式', name : 'refund_way', width : 80, sortable : true, align: 'center'},
                {display: '收款姓名', name : 'refund_name', width : 80, sortable : true, align: 'center'},
                {display: '收款帐号', name : 'refund_account', width : 80, sortable : true, align: 'center'},
                {display: '分销订单号', name : 'fx_order_id', width : 200, sortable : true, align: 'center'},
                {display: '操作来源', name : 'operation_type', width : 200, sortable : true, align: 'center'},
                {display: '操作人', name : 'admin_name', width : 200, sortable : true, align: 'center'}
            ],
            searchitems : [
                {display: '退单编号', name : 'refund_sn', isdefault: true},
                {display: '商品名称', name : 'goods_name'},
                {display: '买家账号', name : 'buyer_name'},
                {display: '店铺名称', name : 'store_name'},
                {display: '订单编号', name : 'order_sn'},
                {display: '分销订单编号', name : 'fx_order_id'}
            ],
            buttons : [
                {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出csv文件,如果不选中行，将导出列表所有数据', onpress : fg_operate }
            ],
            sortname: "refund_id",
            sortorder: "desc",
            title: '线上实物交易订单退款列表'
        });
    });
    function fg_operate(name, grid) {
        if (name == 'csv') {
            var itemlist = new Array();
            if($('.trSelected',grid).length>0){
                $('.trSelected',grid).each(function(){
                    itemlist.push($(this).attr('data-id'));
                });
            }
            fg_csv(itemlist);
        }
    }
    function fg_csv(ids) {
        id = ids.join(',');
        window.location.href = $("#flexigrid").flexSimpleSearchQueryString()+'&op=export_step1&refund_id=' + id;
    }
    var refundFile = $('#refund-file'),mask = $('#mask'),loading = $('#loading'),resultPanel = $('#result'),uploaded=false;
    function importRefund() {
        if(uploaded){return showError('已经成功上传文件，若需要再次上传请刷新页面');}
        refundFile.trigger('click');
    }
    refundFile.fileupload({
        dataType: 'json',
        url: 'index.php?act=refund&op=import',
        done: function (e,data) {
            mask.fadeOut();loading.fadeOut();
            uploaded=true;
            var param = data.result;
            if(param.state==false){
                showError(param.msg);
                return false;
            }
            var tips = '<div class="title"><i class="fa fa-lightbulb-o"></i> <h4>文件上传成功</h4> </div>';
            tips += "<ul class=\"mt5\">";
            tips +="<li>批量退款的订单有<font color=\"red\">"+param.result.total+"</font>个；</li>";
            tips +="<li>退款成功的订单有<font color=\"red\">"+param.result.success+"</font>个；</li>";
            tips +="<li>退款失败的订单有<font color=\"red\">"+param.result.fail.length+"</font>个；</li>";
            if(param.result.fail.length>0){
                tips +="<li>退款的失败的订单编号有：</li>";
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

    refundFile.change(function (e) {
        console.log(e,this);
        if(this.files.length>0){
            console.log(e,this.files);
            var file = this.files[0],filename = file.name;
            if(/.*\.csv$/i.test(filename) === false) return alert('仅支持CSV文件');
            mask.fadeIn();loading.fadeIn();
            /*setTimeout(function () {
                mask.fadeOut();loading.fadeOut();
            },3000)*/
        }
    });
</script>

