<?php
/**
 * 控制台
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class appControl
{
    /**
     * ### 获取应用凭据
     * 应用通过此接口获取通信令牌，进行后续通信
     *
     * @param string $id 应用ID
     * @param string $secret 应用密钥
     * @return json
     */
    public function authOp()
    {
        if (empty($_POST['id'])) {
            jsonReturn(400, '缺少参数 id');
        }

        if (empty($_POST['secret'])) {
            jsonReturn(400, '缺少参数 secret');
        }

        try {
            $data = Model('open_app')->getToken($_POST['id'], $_POST['secret']);
        }
        catch (Exception $e) {
            jsonReturn($e->getCode(), $e->getMessage());
        }

        jsonReturn(200, $data);
    }
}
