<?php
/**
 * 文章管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class open_appModel extends Model
{
    public function getToken($store_id, $secret) {
        $app = $this->where(array('store_id' => $store_id, 'status' => 1))->find();
        if (empty($app['secret']) || $app['secret'] != $secret) {
            throw new Exception('密钥错误', 400);
        }

        $token = md5(uniqid());
        $expires_in = time() + 7200;
        $result = $this->where(array('store_id' => $app['store_id']))->update(array('token' => $token, 'expires_in' => $expires_in));

        if (!$result) {
            throw new Exception('密钥创建失败', 400);
        }

        return array('token' => $token, 'expires_in' => $expires_in);
    }

    public function checkToken($token)
    {
        $app = $this->where(array('token' => $token))->find();

        if (empty($app) || $app['expires_in'] < time()) {
            throw new Exception('token 不正确', 400);
        }

        return $app;
    }
}
