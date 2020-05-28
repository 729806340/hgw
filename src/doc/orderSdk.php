<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Content-Type:application/json; charset=utf-8');
$url = "http://www2.hangowa.com/shop/api/fenxiao/order.php";
$data['order_sn'] = 'E2016073112464502371'; //分销系统订单编号
$data['buy_id'] = 194664; //分销商用户编号
$data['receiver']='Judith';//收件人
$data['provine'] = '上海';
$data['city'] ='上海市';
$data['area'] = '长宁区';
$data['address'] = '镇宁路55号c 101';
$data['mobile']='13601819020'; //手机号码
$data['remark'] = '订单留言';
$data['amount'] = 103.60;
$data['payment_code'] = 'fenxiao';//订单来源  fenxiao,jicai
$data['order_time']=strtotime("2016-07-31 00:46:45");//下单时间，时间戳
$data['item'] = array(
    array(
        'goods_id'=>100012, //对应b2c_category的pid
        'num'=>1, //数量
        'price'=>23.90, //单价
		'oid' => 4900999, //分销子订单号，无子订单可以不传
    ),
    
    array(
        'goods_id'=>100011, //对应b2c_category的pid
        'num'=>1, //数量
        'price'=>32.90,
		'oid' => 4901001, //分销子订单号
    ),
    
    array(
        'goods_id'=>100014, //对应b2c_category的pid
        'num'=>1, //数量
        'price'=>19.90,
		'oid' => 4901000, //分销子订单号
    ),
	
	array(
        'goods_id'=>100013, //对应b2c_category的pid
        'num'=>1, //数量
        'price'=>26.90,
		'oid' => 4900998, //分销子订单号
    ),
);
$data['discount'] = '' ; //订单优惠金额 addzxj
$data['save_type'] = 'insert' ; //可不传 传save时为更新b2c_order_fenxiao表（适用于拼多多）

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