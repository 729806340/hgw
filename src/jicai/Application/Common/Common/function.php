<?php

//echo $order->getLastSql();  获取最后一条sql语句

/**
 * 格式化打印函数
 * @param  [type] $arr [数组]
 * @return [type]      [description]
 */
function p($arr) {
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
}

/**
 * 下载文件函数
 * @param  [type] $file 字符串  文件路径
 * @return [type]      [description] mime_content_type
 */
function downloadFile($file) {
    if (is_file($file)) {
        $length = filesize($file);
//        $finfo = mime_content_type(FILEINFO_MIME);
        $finfo = finfo_open(FILEINFO_MIME);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
        $showname = ltrim(strrchr($file, '/'), '/');
        header("Content-Description: File Transfer");
        header('Content-type: ' . $type);
        header('Content-Length:' . $length);
        if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
            header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $showname . '"');
        }
        readfile($file);
        exit;
    } else {
        exit('文件已被删除！');
    }
}


