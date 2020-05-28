<?php
/**
 * 验证码
 *
 * @copyright  Copyright (c) 2007-2015 SHOPWWI Inc. 
 * by汉购网破解提供！
 * www.hangowa.com
 * 请勿随意分享 以免你的网站受到安全的威胁，汉购网概不负责。人概不负责。
 */

defined('ByShopWWI') or exit('Access Invalid!');

class seccodeControl{

	public function __construct(){
	}
	/**
	 * 产生验证码
	 *
	 */
	public function makecodeOp(){
		$refererhost = parse_url($_SERVER['HTTP_REFERER']);
		$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

		$seccode = makeSeccode($_GET['nchash']);

		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");

		$code = new seccode();
		$code->code = $seccode;
		$code->width = 90;
		$code->height = 26;
		$code->background = 1;
		$code->adulterate = 1;
		$code->scatter = '';
		$code->color = 1;
		$code->size = 0;
		$code->shadow = 1;
		$code->animator = 0;
		$code->datapath =  BASE_DATA_PATH.'/resource/seccode/';
		$code->display();
	}

	/**
	 * AJAX验证
	 *
	 */
	public function checkOp(){
		if (checkSeccode($_GET['nchash'],$_GET['captcha'])){
			exit('true');
		}else{
			exit('false');
		}
	}
}

?>
