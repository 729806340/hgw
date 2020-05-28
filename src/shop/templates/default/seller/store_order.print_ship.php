<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php defined('ByShopWWI') or exit('Access Invalid!');?>
    <link href="<?php echo SHOP_TEMPLATES_URL;?>/css/base.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SHOP_TEMPLATES_URL;?>/css/seller_center.css" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        body { background: #FFF none;
        }
    </style>
    <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>
    <script type="text/javascript" src="<?php echo SHOP_RESOURCE_SITE_URL;?>/js/jquery.printarea.js" charset="utf-8"></script>
    <title><?php echo $lang['member_printorder_print'];?>--<?php echo $output['store_info']['store_name'];?><?php echo $lang['member_printorder_title'];?></title>
</head>

<body>
<div class="print-layout">
    <div class="print-btn" id="printbtn" title="<?php echo $lang['member_printorder_print_tip'];?>"><i></i><a href="javascript:void(0);"><?php echo $lang['member_printorder_print'];?></a></div>
    <div class="a5-size"></div>
    <dl class="a5-tip">
        <dt>
        <h1>A5</h1>
        <em>Size: 210mm x 148mm</em></dt>
        <dd><?php echo $lang['member_printorder_print_tip_A5'];?></dd>
    </dl>
    <div class="a4-size"></div>
    <dl class="a4-tip">
        <dt>
        <h1>A4</h1>
        <em>Size: 210mm x 297mm</em></dt>
        <dd><?php echo $lang['member_printorder_print_tip_A4'];?></dd>
    </dl>
    <div class="print-page">
        <div id="printarea">
            <div id="print_area" style=" width: 375px; margin: auto"><?php echo $output['content'];?></div>
        </div>
</div>
</body>
<script>
    $(function(){
        $("#printbtn").click(function(){
            $("#print_area").printArea();
        });
    });

</script>
</html>