<?php
/**
 * 社区团购活动秒杀商品模型
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_xianshi_goodsModel extends Model{

    protected $tuan_id = 0;

    public function __construct(){
        parent::__construct('shequ_xianshi_goods');
    }

    /**
     * 读取社区团购配置商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getXianshiGoodsList($condition, $page=null, $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }


    public function getXianshiGoodsInfo($condition) {
        return $this->where($condition)->find();
    }

    public function addXianshiGoods($param){
        return $this->insert($param);
    }

    public function editXianshiGoods($condition,$data){
        return $this->where($condition)->update($data);
    }

    public function getXianshiGoodsListInfoByGoodsIds($goods_ids, $tuan_id) {

        $condition = array(
            'tuan_config_id' => $tuan_id,
            'state' => 1,
            'end_time' => array('gt', TIMESTAMP),
            'start_time' => array('lt', TIMESTAMP),
        );
        /** @var shequ_xianshiModel $shequ_xianshi_model */
        $shequ_xianshi_model = Model('shequ_xianshi');
        $xianshi_list = $shequ_xianshi_model->getXianShiConfigList($condition, '', '', 'xianshi_id');
        if (empty($xianshi_list)) {
            return array();
        }
        $xianshi_ids = array_column($xianshi_list, 'xianshi_id');
        $goods_xianshi_condition = array(
            'xianshi_id' => array('in', $xianshi_ids),
            'tuan_config_id' => $tuan_id,
            'goods_id' => array('in', $goods_ids)
        );
        $xianshi_goods_list = $this->getXianshiGoodsList($goods_xianshi_condition);
        $xianshi_goods_list = array_under_reset($xianshi_goods_list, 'goods_id');
        return $xianshi_goods_list;
    }

    public function getShequXianshiGoodsInfoByGoodsID($goods_id) {
        if (!$this->tuan_id) {
            /** @var shequ_tuan_configModel $shequ_tuan_configModel */
            $shequ_tuan_configModel = Model('shequ_tuan_config');
            $shequ_tuan_config_info = $shequ_tuan_configModel->getTuanConfigInfo(array(
                'config_start_time' => array('lt', TIMESTAMP),
                'config_end_time' => array('gt', TIMESTAMP),
                'config_state' => 1
            ));
            if (empty($shequ_tuan_config_info)) {
                return array();
            }
            $this->tuan_id = $shequ_tuan_config_info['config_tuan_id'];
        }
        $tuan_id = $this->tuan_id;
        $condition = array(
            'tuan_config_id' => $tuan_id,
            'state' => 1,
            'end_time' => array('gt', TIMESTAMP),
            'start_time' => array('lt', TIMESTAMP),
        );
        /** @var shequ_xianshiModel $shequ_xianshi_model */
        $shequ_xianshi_model = Model('shequ_xianshi');
        $xianshi_list = $shequ_xianshi_model->getXianShiConfigList($condition, '', '', 'xianshi_id');
        if (empty($xianshi_list)) {
            return array();
        }
        $xianshi_ids = array_column($xianshi_list, 'xianshi_id');
        $goods_xianshi_condition = array(
            'xianshi_id' => array('in', $xianshi_ids),
            'tuan_config_id' => $tuan_id,
            'goods_id' => $goods_id
        );
        $xianshi_goods_info = $this->getXianshiGoodsInfo($goods_xianshi_condition);
        return empty($xianshi_goods_info) ? array() : $xianshi_goods_info;
    }



}