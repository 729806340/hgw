<?php
defined('ByShopWWI') or exit('Access Invalid!');

function jsonReturn($status = 0, $data = '')
{
    $result = array(
        'status' => $status,
        'data'   => $data,
    );

    $jsonResult = json_encode($result);

    header('Expires: -1');
    header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
    header('Pragma: no-cache');
    header('Content-type: application/json');
    exit($jsonResult);
}
