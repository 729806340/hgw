<?php

$config = array(
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'hango_b2b2c', // 数据库名
    'DB_USER'   => 'hango', // 用户名
    'DB_PWD'    => 'hango#@!', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'shopwwi_', // 数据库表前缀
    'DB_CHARSET' => 'utf8',
    'DB_CONFIG1' => array(
        'db_type' => 'mysql',
        'db_user' => 'hango',
        'db_pwd' => 'hango#@!',
        'db_host' => '192.168.11.98',
        'db_port' => '3306',
        'db_name' => 'hango_b2c',
        'db_charset' => 'utf8',
    ),
    'OLD_STATUS'=>false,/** 配置同步旧数据 */
    'EC_API_HOST'=>'http://www2.hangowa.com/v2/index.php/api?', /** ecStore API 接口地址，请以http://开头 */
    'EC_SECRET'=>'YWCzFV3z48IacWYJh7HZVVkkL1dyC', /** ecStore 加密密钥，可以自行修改，但请与ecStore 的app/b2c/setting.php里的b2c_system_secret项保持一致 */
    'EC_SIGN'=>'eff90f9f07d591ac969dfc4750674ce2', /** ecStore Sign，不可自行修改 */
    'YEEPAY'=>array(
        'mer_id'=>'',
        'mer_key'=>'',
    ),

    'small_wx'=>array(
        'app_id'=>'wx66d8e5f039ce2822',
        'mch_id'=>'10022177',
        'key'=>'42010219890110402x18627173588mwy',
        'secret'=>'0312929405daaedda4d1956da8c247c6',
    ),
);

$config['shop_site_url']        = 'http://www2.hangowa.com/shop';
$config['b2b_site_url']        = 'http://www2.hangowa.com/b2b';
$config['cms_site_url']         = 'http://www2.hangowa.com/cms';
$config['microshop_site_url']   = 'http://www2.hangowa.com/microshop';
$config['circle_site_url']      = 'http://www2.hangowa.com/circle';
$config['admin_site_url']       = 'http://www2.hangowa.com/admin';
$config['mobile_site_url']      = 'http://www2.hangowa.com/mobile';
$config['wap_site_url']         = 'http://www2.hangowa.com/wap';
$config['chat_site_url']        = 'http://www2.hangowa.com/chat';
$config['wechat_site_url']      = 'http://www2.hangowa.com/wechat/ems/';
$config['node_site_url']        = 'http://www2.hangowa.com:8091';
$config['delivery_site_url']    = 'http://www2.hangowa.com/delivery';
$config['chain_site_url']       = 'http://www2.hangowa.com/chain';
$config['member_site_url']      = 'http://www2.hangowa.com/member';
$config['upload_site_url']      = 'http://www2.hangowa.com/data/upload';
$config['resource_site_url']    = 'http://www2.hangowa.com/data/resource';
$config['cms_modules_url']      = 'http://www2.hangowa.com/admin/modules/cms';
$config['microshop_modules_url']= 'http://www2.hangowa.com/admin/modules/microshop';
$config['circle_modules_url']   = 'http://www2.hangowa.com/admin/modules/circle';
$config['admin_modules_url']    = 'http://www2.hangowa.com/admin/modules/shop';
$config['mobile_modules_url']   = 'http://www2.hangowa.com/admin/modules/mobile';
$config['version']              = '201602150687S';
$config['setup_date']           = '2016-07-18 14:46:53';
$config['gip']                  = 0;
$config['dbdriver']             = 'mysql';
$config['tablepre']             = 'shopwwi_';
$config['db']['1']['dbhost']       = '192.168.11.98';
$config['db']['1']['dbport']       = '3306';
$config['db']['1']['dbuser']       = 'hango';
$config['db']['1']['dbpwd']        = 'hango#@!';
$config['db']['1']['dbname']       = 'hango_b2b2c';
$config['db']['1']['dbcharset']    = 'UTF-8';
$config['db']['slave']                  = $config['db']['master'];
$config['session_expire']   = 3600;
$config['lang_type']        = 'zh_cn';
$config['cookie_pre']       = 'hango_';
$config['cache_open'] = false;
//$config['redis']['prefix']        = 'nc_';
//$config['redis']['master']['port']        = 6379;
//$config['redis']['master']['host']        = '127.0.0.1';
//$config['redis']['master']['pconnect']    = 0;
//$config['redis']['slave']             = array();
//$config['fullindexer']['open']      = false;
//$config['fullindexer']['appname']   = 'shopnc';
$config['debug']            = false;
$config['url_model'] = false;
$config['subdomain_suffix'] = '';
//$config['session_type'] = 'redis';
//$config['session_save_path'] = 'tcp://127.0.0.1:6379';
$config['node_chat'] = true;
//流量记录表数量，为1~10之间的数字，默认为3，数字设置完成后请不要轻易修改，否则可能造成流量统计功能数据错误
$config['flowstat_tablenum'] = 3;
$config['sms']['gwUrl'] = 'http://sdk4report.eucp.b2m.cn:8080/sdk/SDKService';
$config['sms']['serialNumber'] = '';
$config['sms']['password'] = '';
$config['sms']['sessionKey'] = '';
$config['queue']['open'] = false;
$config['queue']['host'] = '127.0.0.1';
$config['queue']['port'] = 6379;
$config['process']['allowed_ip'] = array('127.0.*','192.168.*','172.16.30.17','27.17.54.74');
//$config['oss']['open'] = false;
//$config['oss']['img_url'] = '';
//$config['oss']['api_url'] = '';
//$config['oss']['bucket'] = '';
//$config['oss']['access_id'] = '';
//$config['oss']['access_key'] = '';
$config['https'] = false;
return $config;