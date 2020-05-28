<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>ERP</title>
    <link href="http://apps.bdimg.com/libs/bootstrap/3.3.4/css/bootstrap.css" rel=stylesheet type="text/css">
    <link href="http://apps.bdimg.com/libs/fontawesome/4.4.0/css/font-awesome.css" rel=stylesheet type="text/css">
    <link href="<?php echo ERP_TEMPLATES_URL;?>/css/erp.css" rel=stylesheet type="text/css">
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.js"></script>
</head>

<body>
<div class="wrap">
    <div class="container" id="mainContent">
        <?php require_once($tpl_file);?>
    </div>
</div>

<script src="http://apps.bdimg.com/libs/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery.cookie/1.4.1/jquery.cookie.js"></script>
<script type="text/javascript">
    $(function () {
        console.log('init');
    });
</script>
</body>
</html>
