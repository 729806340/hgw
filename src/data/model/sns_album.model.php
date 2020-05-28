<?php
/**
 * 买家相册模型
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class sns_albumModel extends Model {

    public function __construct(){
        parent::__construct('sns_albumpic');
    }

    public function getSnsAlbumClassDefault($member_id) {
        if(empty($member_id)) {
            return null;
        }

        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['is_default'] = 1;
        $info = $this->table('sns_albumclass')->where($condition)->find();

        if(!empty($info)) {
            return $info['ac_id'];
        } else {
            //新建买家秀相册
            $data = array(
                'member_id' => $member_id,
                'is_default' => 1,
                'ac_sort' => 1,
                'ac_name' => '买家秀',
                'ac_des' => '买家秀默认相册',
            );
            return $this->insert($data);
        }
    }
}
