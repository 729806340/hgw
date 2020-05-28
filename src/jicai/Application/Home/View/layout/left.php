<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
<link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="left-sidebar public-bg fl">
      <ul>
        <li><a href="#" class="nav01"><b class="l-nav-icon01"></b><i>|</i>系统首页</a></li>
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon04"></b><i>|</i>商品管理</a>
       		<dl>
            	<dd><a href="<?php echo U('index.php/goods/goodslist');?>" target="main">商品列表</a></dd>
                <dd><a href="<?php echo U('index.php/goods/add');?>" target="main">新增商品</a></dd>
            </dl>
        </li>
        <li class="cur"><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon05"></b><i>|</i>订单管理</a>
        	<dl>
            	<!--<dd><a href="#">订单</a></dd>-->
                <dd><a href="<?php echo U("index.php/order/orderlist");?>" target="main">订单列表</a></dd>
                <dd><a href="<?php echo U("index.php/order/cart");?>" target="main">购物车</a></dd>
            </dl>
        </li>
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon06"></b><i>|</i>系统管理</a>
        <dl>
                <dd><a href="<?php echo U('index.php/system/modifypwd');?>" target="main">修改密码</a></dd>
            </dl>
        </li>
      </ul>
    </div>
</body>
</html>
<script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>
<script src="__PUBLIC__/js/menu.js"></script>
<script>navList(12);</script>