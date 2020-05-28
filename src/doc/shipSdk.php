<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Content-Type:application/json; charset=utf-8');
$url = "http://www2.hangowa.com/shop/index.php?act=autotask&op=pushship";
$arr = array(
	'source' => 'renrendian', //分销平台名称
	'orderno' => 'E2016072201354538208', //分销平台订单号
	'logi_no' => '211237231126',  //快递单号
	'oid' => '4627712',  //shopwwi_b2c_order_fenxiao_sub表子订单号，拼多多不用传
	'num' => '1', //商品数量
	'logi_name' => '申通快递', //快递名称
	'full_ship' => '0' //是否全单发货  0：否；1：是 拼多多是整单发货，传1
) ;

$url .= '&' . http_build_query($arr);
//echo $url;die;


$res = file_get_contents( $url ) ;
var_dump($res);

?>



返回格式：

失败:
{"succ":"0","msg":"some error info"}

成功：
{"succ":"1","msg":"\u53d1\u8d27\u6d4b\u8bd5\u6210\u529f","params":{"access_token":"65038f47fd8e0ce7245d447ca358a21f","appid":"5356714791a4f20a","logis_code":"shentong","logis_no":"211237231126","method":"weiba.wxrrd.trade.send","order_sn":"E2016072201354538208","secret":"3f935b4b5356714791a4f20a934aa5d9","sub_order_info":"[{\"oid\":\"4627712\",\"quantity\":1}]","timestamp":"2016-07-22 16:54:23","sign":"003A80C466CF3AC50712356C3E4FDB0B"}}