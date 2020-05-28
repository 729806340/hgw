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
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon02"></b><i>|</i>供应商管理</a>
        	<dl>
            	<!--<dd><a href="#" target="main">添加供应商</a></dd>-->
                <!--<dd><a href="<?php // echo U('index.php/supplier/supplierlist');?>" target="main">供应商列表</a></dd>-->
            </dl>
        </li>
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon03"></b><i>|</i>分销商管理</a>
        	<dl>
            	<!--<dd><a href="<?php // echo U('index.php/distributor/distributoradd');?>"  target="main">添加分销商</a></dd>-->
                <!--<dd><a href="<?php // echo U('index.php/distributor/distributorlist');?>" target="main">分销商列表</a></dd>-->
                <!--<dd><a href="<?php // echo U('index.php/distributor/productlist');?>" target="main">商品列表</a></dd>-->
            </dl>
        </li>
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon04"></b><i>|</i>产品管理</a>
       		<dl>
            	<dd><a href="<?php echo U('index.php/goods/goodslist');?>" target="main">商品列表</a></dd>
                <dd><a href="<?php echo U('index.php/goods/distributorgoods');?>" target="main">我分销的商品</a></dd>
            </dl>
        </li>
        <li class="cur"><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon05"></b><i>|</i>订单管理</a>
        	<dl>
            	<!--<dd><a href="#">订单</a></dd>-->
                <dd><a href="<?php echo U("index.php/order/orderlist");?>" target="main">订单列表</a></dd>
				<dd><a href="<?php echo U("index.php/refund/refundlist");?>" target="main">退款列表</a></dd>
				<dd><a href="<?php echo U("index.php/refund/errorlog");?>" target="main">错误日志</a></dd>
            </dl>
        </li>
        <li><a href="javascript:void(0);" class="nav01"><b class="l-nav-icon06"></b><i>|</i>系统管理</a>
        <dl>
<!--                <dd><a href="<?php echo U('index.php/credit/addcredit');?>" target="main">添加额度</a></dd>
                <dd><a href="<?php echo U('index.php/credit/addcreditlist');?>" target="main">额度日志</a></dd>-->
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