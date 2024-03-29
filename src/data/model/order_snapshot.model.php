<?php
/**
 * 交易快照
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class order_snapshotModel extends Model {

    public function __construct() {
        parent::__construct('order_snapshot');
    }

    /**
     * 由订单商品表主键取得交易快照信息
     * @param int $rec_id
     * @param int $goods_id
     * @return array
     */
    public function getSnapshotInfoByRecid($rec_id,$goods_id) {
        $info = $this->where(array('rec_id'=>$rec_id))->find();
        if (!$info) {
            return $this->createSphot($rec_id, $goods_id);
        }
        return $info;
    }

    public function createSphot($rec_id,$goods_id) {
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfo(array('goods_id'=>$goods_id),'goods_serial,goods_body,goods_commonid');
        $goods_common_info = $model_goods->getGoodsCommonInfo(array('goods_commonid'=>$goods_info['goods_commonid']),'brand_name,goods_attr,goods_custom,goods_body,plateid_top,plateid_bottom');
        $goods_common_info['goods_attr'] = unserialize($goods_common_info['goods_attr']);
        $goods_common_info['goods_custom'] = unserialize($goods_common_info['goods_custom']);
        $_attr = array();
        $_attr['货号'] = $goods_info['goods_serial'];
        $_attr['品牌'] = $goods_common_info['brand_name'];
        if (is_array($goods_common_info['goods_attr']) && !empty($goods_common_info['goods_attr'])) {
            foreach($goods_common_info['goods_attr'] as $v) {
                $_attr[$v['name']] = end($v);
            }            
        }
        if (is_array($goods_common_info['goods_custom']) && !empty($goods_common_info['goods_custom'])) {
            foreach($goods_common_info['goods_custom'] as $v) {
                $_attr[$v['name']] = $v['value'];
            }
        }

        $info = array();
        $info['rec_id'] = $rec_id;
        $info['goods_id'] = $goods_id;
        $info['create_time'] = time();
        $info['goods_attr'] = serialize($_attr);
        $info['goods_body'] = $goods_info['goods_body'] == '' ? $goods_common_info['goods_body'] : $goods_info['goods_body'];
        $model_plate = Model('store_plate');
        // 顶部关联版式
        if ($goods_common_info['plateid_top'] > 0) {
            $plate_top = $model_plate->getStorePlateInfoByID($goods_common_info['plateid_top']);
            $info['plate_top'] = $plate_top['plate_content'];
        }
        // 底部关联版式
        if ($goods_common_info['plateid_bottom'] > 0) {
            $plate_bottom = $model_plate->getStorePlateInfoByID($goods_common_info['plateid_bottom']);
            $info['plate_bottom'] = $plate_bottom['plate_content'];
        }
        $this->insert($info);
        return $info;
    }

}
