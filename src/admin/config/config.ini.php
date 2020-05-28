<?php
defined('ByShopWWI') or exit('Access Invalid!');

$config['sys_log']              = true;
$config['order_create_key']     = 123456;
$config['send_payment']         = true; // 是否开启 （财务审核退款，在线交易原路退款）

$config['driver_config'] = array(
    '37' => array(
        'driver_id' => 37,
        'driver_name' => '黄博',
        'driver_phone' => '13667205982',
        'driver_car_number' => '鄂A3S1W5',
    ),
    '34' => array(
        'driver_id' => 34,
        'driver_name' => '万江',
        'driver_phone' => '13900008888',
        'driver_car_number' => '鄂A14235',
    ),
);
return $config;
