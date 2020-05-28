<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/home_goods.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/mz-packed.js" charset="utf-8"></script>
<div class="wrapper pr" style="padding: 50px 80px; padding-bottom: 200px;">
    <div class="lic_wrapper" style="padding: 20px 30px;">
        <form method="post" id="qcform" action="<?php echo urlShop('store_license', 'index');?>">
            <div style="padding-bottom: 30px;">
                <h1 style="font-size:20px;padding-bottom: 30px;">汉购网经营者营业执照信息</h1>
                <p>根据国家工商局《网络交易管理方法》要求对经营者营业执照信息公示如下：</p>
            </div>
            <div class="qc-wrapper" style="padding-bottom: 15px;">
                <h3 class="qc-title" style="padding-bottom: 15px;">请输入验证码后查看：</h3>
                <span class="qc-int">
                        <input type="text" name="captcha" class="qc_cd" placeholder="请输入验证码">
                        <input type="hidden" name="store_id" value="<?php echo $output['store_id']?>">
                    <span class="qc_img"><img class="img-responsive" id="qc_img" src="index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getNchash();?>" title="<?php echo $lang['login_index_change_checkcode'];?>" name="codeimage" id="sms_codeimage"><a class="makecode" href="javascript:void(0);" onclick="javascript:document.getElementById('sms_codeimage').src='index.php?act=seccode&op=makecode&type=50,120&nchash=<?php echo getNchash();?>&t=' + Math.random();"><?php echo $lang['login_password_change_code']; ?></a></span>
                    </span>
                <p class="seInfo"></p>
            </div>
            <div class="submit-div">
                <input type="submit" value="确定" class="submit" title="确定" />
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.charCount.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js" type="text/javascript"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/sns.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.F_slider.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.raty/jquery.raty.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
</script>
