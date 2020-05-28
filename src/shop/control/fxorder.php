<?php
/**
 * 分销订单接口文件
 * @author lijingquan
 * @category hangowa
 */
defined('ByShopWWI') or exit('Access Invalid!');
class fxorderControl extends BaseBuyControl {
    public function __construct() {
        parent::__construct();
    }
    
    public function indexOp(){
        header('Content-Type:application/json; charset=utf-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        $params = file_get_contents("php://input");
        $result = Model('order')->createFxOrder($params);
        die(JSON($result));
    }
}