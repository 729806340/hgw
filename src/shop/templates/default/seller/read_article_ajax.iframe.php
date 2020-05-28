<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<!doctype html>
<html>
<head>
    <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
    <link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo SHOP_TEMPLATES_URL?>/css/base.css" rel="stylesheet" type="text/css">
</head>
<body style="min-width:890px !important;">
<div class="eject_con">
    <form method="post" action="<?php echo urlShop('seller_center', 'read_article');?>" id="article_form">
        <input type="hidden" name="form_submit" value="ok" />
        <input type="hidden" name="article_id" value="<?php echo $output['article_data']['article_id']; ?>" />
        <div class="title"><?php echo $output['article_data']['article_title']; ?></div>
        <div class="inner-box"><?php echo $output['article_data']['article_content']; ?></div>
        <div class="bottom">
            <?php if($output['article_next']) {?>
                <label class="submit-border"><input type="button" id="article_next" class="submit" value="下一条通知"/></label>
            <?php } else {?>
                <label class="submit-border"><input type="button" id="article_button" class="submit" value="我已阅读完"/></label>
            <?php }?>
        </div>
    </form>
</div>
</body>
</html>
<style type="text/css">
    .eject_con{ width: 100%; height:600px; overflow-y: scroll; }
    .eject_con{float: none;}
    .eject_con .title{ font-size:24px; text-align: center; line-height: 40px; margin: 15px auto;}
    .inner-box{width: 90%; padding:20px 5%; height: auto;}
    .inner-box img{ width: 100%; margin:10px 0;}
    .inner-box{ line-height: 28px;}
</style>
<script>
    $('#article_button').click(function () {
        var form = $('#article_form');
        $.post(
            form.attr("action"),
            form.serialize(),
            function (result) {
                $(".dialog_close_button", parent.document).click();
            },
            'json'
        );
    });
    <?php if($output['article_next']) {?>
    $('#article_next').click(function () {
        var form = $('#article_form');
        $.post(
            form.attr("action"),
            form.serialize(),
            function (result) {
                ajax_form('ajax_jingle', '系统公告', 'index.php?act=seller_center&op=read_article_ajax&article_id=' + <?php echo $output['article_next']['article_id'];?>, 915);
            },
            'json'
        );
    });
    <?php }?>

</script>