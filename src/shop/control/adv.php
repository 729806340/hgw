<?php
/**
 * 广告展示
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class advControl {
    /**
     *
     * 广告展示
     */
    public function advshowOp(){
        import('function.adv');
        $ap_id = intval($_GET['ap_id']);
        echo advshow($ap_id,'js');
    }
    /**
     * 异步调用广告
     *
     */
    public function get_adv_listOp(){
        $ap_ids = $_GET['ap_ids'];
        $list = array();
        if (!empty($ap_ids) && is_array($ap_ids)) {
            import('function.adv');
            foreach ($ap_ids as $key => $value) {
                $ap_id = intval($value);//广告位编号
                $adv_info = advshow($ap_id,'array');
                if (!empty($adv_info) && is_array($adv_info)) {
                    $adv_info['adv_url'] = htmlspecialchars_decode($adv_info['adv_url']);
                    $list[$ap_id] = $adv_info;
                }
            }
        }
        echo $_GET['callback'].'('.json_encode($list).')';
        exit;
    }
}
