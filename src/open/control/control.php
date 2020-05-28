<?php
/**
 * 父类
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class control
{
    protected $storeId;

    /**
     * 构造函数，所有请求先验证token
     *
     * @param string $token 应用访问令牌
     */
    public function __construct()
    {
        try {
            $app = Model('open_app')->checkToken($_POST['token']);
        }
        catch (Exception $e) {
            jsonReturn($e->getCode(), $e->getMessage());
        }

        $this->storeId = $app['store_id'];
    }
}
