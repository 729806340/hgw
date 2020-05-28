<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/5
 * Time: 9:52
 */
/**
 * 获取拼多多开放平台api调用token
 * @author chenhao
 * @date 2017-12-5
 */
error_reporting(7);
$_GET['act']	= 'pddtoken';
$_GET['op']		= 'gettoken';
require_once(dirname(__FILE__).'/../../../index.php');
?>