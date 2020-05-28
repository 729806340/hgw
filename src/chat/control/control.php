<?php
/**
 * 前台control父类
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

/********************************** 前台control父类 **********************************************/

class BaseControl {
	public function __construct(){
		Language::read('common');
	}
}
