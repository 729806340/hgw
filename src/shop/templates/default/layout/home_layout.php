<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php include template('layout/common_layout');?>
<?php include template('layout/cur_local');?>
<link href="<?php echo MEMBER_TEMPLATES_URL;?>/css/member.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/member.js"></script>

<?php require_once($tpl_file);?>
<?php /*echo $tpl_file;*/?>
<?php require_once template('footer');?>
</body>
</html>
