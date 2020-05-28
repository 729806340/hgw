
<?php
/**
 * 签收码
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_get_codeControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     *
     */
    public function indexOp() {
        $code = '';
        //获取农猫速达的小程序配置生成小程序码
        output_data($code);
    }

}

