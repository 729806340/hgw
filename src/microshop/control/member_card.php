<?php
/**
 * The AJAX call member information
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

class member_cardControl extends MircroShopControl{
    public function mcard_infoOp(){
        $uid    = intval($_GET['uid']);
        if($uid <= 0) {
            echo 'false';exit;
        }
        $model_micro_member_info = Model('micro_member_info');
        $micro_member_info = $model_micro_member_info->getOneById($uid);
        if(empty($micro_member_info)){
            echo 'false';exit;
        }
        echo json_encode($micro_member_info);exit;
    }
}
