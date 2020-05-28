<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>无标题文档</title>
        <link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
    </head>

    <body>
        <div class="top-bar public-bg clear">
            <h1 class="fl">汉购网分销系统1.0</h1>
            <div class="user-info fr">
                <ul class="clear">
                    <li>
                        <div>
                            <p>信用额度</p>
                            <p class="gold"><?php echo $advance ? $advance : 0;?></p>
                        </div>
                    </li>
                    <li>
                        <div>
                            <p>订单累计</p>
                            <p class="gold"><?php echo $totalamount;?></p>
                        </div>
                    </li>
                    <li class="user-name">
                        <div>
                            <p>欢迎您，<?php echo session('username');; ?>！
                            </p>
                        </div>
                    </li>
                    <li>
                        <div>
                            <p><a href="<?php echo  U('index.php/login/logout');?>" target="_top">退出</a></p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </body>
</html>
