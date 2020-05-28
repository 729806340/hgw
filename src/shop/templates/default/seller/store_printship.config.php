<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="alert alert-block mt10">
    <ul class="mt5">
        <li>1、请先注册快递鸟账号，<a href="http://www.kdniao.com/" target="_blank">点击前往</a></li>
        <li>2、申请开通电子面单业务（免费开通）</li>
        <li>3、将快递鸟用户ID和API key提交汉购网（汉购网对您的信息完全保密）</li>
        <li>4、创建电子面单模板（目前仅支持邮政包裹、顺丰、快捷快递物流公司）</li>
        <li>5、申请电子面单发货的订单可以在快递鸟后台的订单列表批量打印,请自己准备快递打印机和热敏打印纸</li>
        <li>6、<a href="http://www.hangowa.com/member/article-43.html" target="_blank">帮助指南</a></li>
    </ul>
</div>
<div class="ncsc-form-default">
    <form id="add_form" action="<?php echo urlShop('store_printship', 'setconfig');?>" method="post" enctype="multipart/form-data">
        <dl>
            <dt><i class="required">*</i>用户ID<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text"  name="userId"  id="userId"  value="<?php echo $output['kdn_config']['userId'];?>" class="text">
                <span></span>
                <p class="hint">请输入快递鸟接口用户ID</p>
            </dd>
        </dl>

        <dl>
            <dt><i class="required">*</i>API key<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input type="text"  id="userKey" name="userKey" size="35" value="<?php echo $output['kdn_config']['userKey']; ?>" class="text">
                <span></span>
                <p class="hint">请输入快递鸟Api key</p>
            </dd>
        </dl>

        <div class="bottom">
            <label class="submit-border">
                <input type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>">
            </label>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script language="javascript">
    $(function () {
        $('#add_form').validate({
            rules : {
                userId: {
                    required:true,
                    number:true
                },
                userKey:{
                    required: true,
                },
            },
            messages : {
                userId: {
                    required : "<i class=\"icon-exclamation-sign\"></i>快递鸟用户ID不能为空",
                    number : "<i class=\"icon-exclamation-sign\"></i>快递鸟用户ID格式不正确"
                },
                userKey:{
                    required : "<i class=\"icon-exclamation-sign\"></i>快递鸟API key不能为空",
                }
            }
        });
    })
</script>
