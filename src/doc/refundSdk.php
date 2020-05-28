<?php
/**
 * todo
 *@author Administrator
 *@date 2016-8-1
 */
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Content-Type:application/json; charset=utf-8');
$url = "http://www2.hangowa.com/shop/api/fenxiao/refund.php";
$data['reason_id'] = 99; //退款退货理由 整型
$data['refund_type'] = 1; //申请类型 1. 退款  2.退货
$data['return_type'] = 1; //退货情况 1. 不用退货  2.需要退货
$data['seller_state'] = 1; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1
$data['refund_amount'] = 100.00;//退款金额
$data['goods_num'] = 1;//商品数量
$data['buyer_message'] = '用户留言';  //用户留言信息
$data['ordersn'] = '160801130994523001';  //汉购网订单编号
$data['goods_id'] = 100011; //商品编号
//添加订单信息结束
$params = JSON($data);
$output = curl_url($url, $params);
print_r($output);
/**
 * 请求接口通用函数
 *
 * @param array $params
 *            请求参数（有值为post请求方式）
 * @param array $headers
 *            头部信息
 * @return string $ret 返回信息
*/
function curl_url($url, $params = array(), $headers = array())
{
	$ch = curl_init();
	$ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if (! empty($params)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	}
	if (! empty($headers))
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	if ($ssl) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
	static $recursive_counter = 0;
	if (++ $recursive_counter > 10000) {
		die('possible deep recursion attack');
	}
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}
		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter --;
}

function JSON($array)
{
	arrayRecursive($array, 'urlencode', true);
	$json = json_encode($array);
	return urldecode($json);
}