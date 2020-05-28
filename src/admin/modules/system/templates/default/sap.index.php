<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title">
            <div class="subject">
                <h3>SAP接口日志</h3>
                <h5>查看或删除SAP接口日志</h5>
            </div>
        </div>
    </div>

    <!-- 操作说明 -->
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span>
        </div>
        <ul>
            <li>
                日志开关在data/config/sap.config.php配置文件中设置；目前状态为：<strong><?php echo $output['sap_setting']['log'] ? '开启' : '关闭'; ?></strong>
            </li>
            <li>
                消息推送状态：<strong><?php echo $output['sap_setting']['notice']['send'] ? '开启' : '关闭'; ?></strong>；推送邮箱：<strong><?php echo $output['sap_setting']['notice']['email']; ?></strong>
            </li>
            <li>sap101添加物料&nbsp;&nbsp;sap102更新物料&nbsp;&nbsp;sap201添加商户&nbsp;&nbsp;sap202更新商户&nbsp;&nbsp;sap301推送已确认收货的订单&nbsp;&nbsp;sap401推送收款单&nbsp;&nbsp;sap402退款单</li>
            <li>如果存在返回结果异常的数据，可点击“重置状态”按钮，重新推送数据</li>
        </ul>
    </div>
    <div id="table"></div>
</div>

<!--高级搜索-->
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
                    <dt>日志编号</dt>
                    <dd>
                        <input type="text" value="" name="log_id" id="log_id" class="s-input-txt">
                    </dd>
                </dl>
                <dl>
                    <dt>数据</dt>
                    <dd>
                        <input type="text" value="" name="sap_data" id="sap_data" class="s-input-txt">
                    </dd>
                </dl>
                <dl>
                    <dt>结果</dt>
                    <dd>
                        <input type="text" value="" name="sap_rel" id="sap_rel" class="s-input-txt">
                    </dd>
                </dl>
                <dl>
                    <dt>交易码</dt>
                    <dd>
                        <select class="s-select" name="sap_code">
                            <option value="">-请选择-</option>
                            <option value="sap101">sap101</option>
                            <option value="sap102">sap102</option>
                            <option value="sap201">sap201</option>
                            <option value="sap202">sap202</option>
                            <option value="sap301">sap301</option>
                            <option value="sap401">sap401</option>
                            <option value="sap402">sap402</option>
                            <option value="sap403">sap403</option>
                            <option value="sap404">sap404</option>
                            <option value="sap405">sap405</option>
                            <option value="sap406">sap406</option>
                            <option value="sap407">sap407</option>
                            <option value="sap501">sap501</option>
                            <option value="sap502">sap502</option>
                            <option value="sap503">sap503</option>
                            <option value="sap504">sap504</option>
                            <option value="sap505">sap505</option>
                            <option value="sap601">sap601</option>
                            <option value="sap602">sap602</option>
                            <option value="sap603">sap603</option>
                            <option value="sap701">sap701</option>
                        </select>
                    </dd>
                </dl>
                <dl>
                    <dt>类型</dt>
                    <dd>
                        <select class="s-select" name="sap_method">
                            <option value="">-请选择-</option>
                            <option value="api">api</option>
                            <option value="after">after</option>
                            <option value="callback">callback</option>
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
<script>
    $(function () {
        // 高级搜索提交
        $('#ncsubmit').click(function(){
            $("#table").flexOptions({url: 'index.php?act=sap&op=get_xml&sap_search=gsearch&'+$("#formSearch").serialize(),query:'',qtype:'',sap_search:'gsearch'}).flexReload();
        });

        // 高级搜索重置
        $('#ncreset').click(function(){
            $("#table").flexOptions({url: 'index.php?act=sap&op=get_xml'}).flexReload();
            $("#formSearch")[0].reset();
        });

        $("#table").flexigrid({
            url: 'index.php?act=sap&op=get_xml',
            colModel: [
                {display: '操作', name: 'operation', width: 200, sortable: false, align: 'center'},
                {display: '日志ID', name: 'log_id', width: 50, sortable: true, align: 'center'},
                {display: '交易码', name: 'code', width: 100, sortable: true, align: 'center'},
                {display: '类型', name: 'method', width: 100, sortable: true, align: 'center'},
                {display: '数据', name: 'data', width: 300, sortable: true, align: 'center'},
                {display: '结果', name: 'rel', width: 300, sortable: true, align: 'center'},
                {display: '错误信息', name: 'error', width: 200, sortable: true, align: 'center'},
                {display: '时间', name: 'add_time', width: 150, sortable: true, align: 'center'}
            ],
            buttons: [
                {
                    display: '<i class="fa fa-trash"></i>清理日志',
                    name: 'clear',
                    bclass: 'del',
                    title: '删除一月以前的日志信息',
                    onpress: fg_operation
                },
                {
                    display: '<i class="fa fa-trash"></i>批量删除',
                    name: 'del',
                    bclass: 'del',
                    title: '将选定行数据删除',
                    onpress: fg_operation
                },
                {
                    display: '<i class="fa fa-list-alt"></i>重置状态',
                    name: 'reset',
                    bclass: 'del',
                    title: '将推送中状态的数据重置为未推送',
                    onpress: fg_operation
                }/*,
                {
                    display: '<i class="fa fa-list-alt"></i>重推所有数据',
                    name: 'resend',
                    bclass: 'del',
                    title: '将已推送状态的数据重置为未推送',
                    onpress: fg_operation
                }*/
            ],
            searchitems: [
                {display: '日志编号', name: 'log_id'},
                {display: '交易码', name: 'code'},
                {display: '类型', name: 'method'},
                {display: '数据', name: 'data'},
                {display: '结果', name: 'rel'}
            ],
            sortname: "log_id",
            sortorder: "desc",
            title: '日志列表'
        });

    });

    function fg_operation(name, bDiv) {
        if (name == 'clear') {
            window.location.href = 'index.php?act=sap&op=clear';
        }
        if (name == 'reset') {
            if (confirm('确定将所有 推送中 状态的数据重置为 未推送 ，重新进行推送吗？')) {
                window.location.href = 'index.php?act=sap&op=reset';
            }
        }
        if (name == 'resend') {
            if (confirm('确定将所有 已推送 状态的数据重置为 未推送 ，重新进行推送吗？')) {
                window.location.href = 'index.php?act=sap&op=resend';
            }
        }
        if (name == 'del') {
            if ($('.trSelected', bDiv).length == 0) {
                if (!confirm('您确定要删除这些数据吗？')) {
                    return false;
                }
            }
            var itemids = new Array();
            $('.trSelected', bDiv).each(function (i) {
                itemids[i] = $(this).attr('data-id');
            });
            submit_delete(itemids);
        }
    }

    function submit_delete(id) {
        if (typeof id == 'number') {
            id = new Array(id.toString());
        }

        if (confirm('删除后将不能恢复，确认删除这 ' + id.length + ' 项吗？')) {
            id = id.join(',');
            window.location.href = 'index.php?act=sap&op=log_del&log_id=' + id;
        }
    }
</script>

