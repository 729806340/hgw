<?php
/**
 * mobile公共方法
 *
 * 公共方法
 *
 * @package    function
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net/
 * @link       http://www.shopnc.net/
 * @author     ShopNC Team
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

function output_data($datas, $extend_data = array(), $error = false) {
    $data = array();
    $data['code'] = 200;
    if($error) {
        $data['code'] = 400;
    }

    if(!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    $data['datas'] = $datas;

    $jsonFlag = 0 && C('debug') && version_compare(PHP_VERSION, '5.4.0') >= 0
        ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        : 0;

    if ($jsonFlag) {
        header('Content-type: text/plain; charset=utf-8');
    }
    if (!empty($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($data, $jsonFlag).')';die;
    } else {
        header("Access-Control-Allow-Origin:*");
        echo JSON($data, $jsonFlag);die;
    }
}

function output_data_new($datas, $extend_data = array(), $error = false) {
    $data = array();
    $data['code'] = 200;
    if($error) {
        $data['code'] = 400;
    }

    if(!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    $data['datas'] = $datas;

    //将数组中值为null 转为 ''
    /*array_walk_recursive($data, function (& $val, $key ) {
        if ($val === null) {
            $val = '';
        }
    });*/

    $jsonFlag = 0 && C('debug') && version_compare(PHP_VERSION, '5.4.0') >= 0
        ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        : 0;

    if ($jsonFlag) {
        header('Content-type: text/plain; charset=utf-8');
    }
    if (!empty($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($data, $jsonFlag).')';die;
    } else {
        header('content-type:application/json');
        header("Access-Control-Allow-Origin:*");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die;
        //echo JSON($data, $jsonFlag);die;
    }
}

function output_error($message, $extend_data = array()) {
    $datas = array('error' => $message);
    output_data($datas, $extend_data, true);
}

function mobile_page($page_count) {
    //输出是否有下一页
    $extend_data = array();
    $current_page = intval($_GET['curpage'])?intval($_GET['curpage']):intval($_POST['curpage']);
    if($current_page <= 0) {
        $current_page = 1;
    }
    if($current_page >= $page_count) {
        $extend_data['hasmore'] = 0;
    } else {
        $extend_data['hasmore'] = 1;
    }
    $extend_data['page_total'] = $page_count;
    return $extend_data;
}

function get_server_ip() {
    if (isset($_SERVER)) {
        if($_SERVER['SERVER_ADDR']) {
            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {
            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }
    return $server_ip;
}

function http_get($url) {
    return file_get_contents($url);
}

function http_post($url, $param) {
    $postdata = http_build_query($param);

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}

function http_postdata($url, $postdata) {
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context  = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}
