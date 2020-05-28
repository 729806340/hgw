<?php
/**
 * 门店接口
 *
 */


defined('ByShopWWI') or exit('Access Invalid!');

class chainControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 门店列表
     */
    public function chain_listOp()
    {
        $store_id = intval($_POST['store_id']);
        $lat_x = trim($_POST['lay_x']);
        $lat_y = trim($_POST['lay_y']);
        /** @var storeModel $model_store */
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_id' => $store_id));
        if (empty($store_info)) {
            output_error('参数错误');
        }
        /** @var chainModel $model_chain */
        $model_chain = Model('chain');
        $distance_field = '';
        $order = 'chain_id desc';
        if ($lat_x && $lat_y) {
            $distance_field = ",(st_distance (point (longitude,latitude),point({$lat_x},{$lat_y}) ) * 111195) as distance";
            $order = 'distance asc';
        }
        $field = '*'. $distance_field;
        $chain_list = $model_chain->field($field)->where(array('store_id' => $store_id))->page($this->page)->order($order)->select();
        foreach ($chain_list as $k=>$v) {
            unset($chain_list[$k]['chain_pwd']);
            //$chain_list[$k]['chain_img'] = UPLOAD_SITE_URL.DS.'chain'.DS.$v['store_id'].DS.$v['chain_img'];
            $chain_list[$k]['chain_img'] = SHOP_SITE_URL.DS.'resource'.DS.'img'.DS.'chain_default5.png';
            $chain_list[$k]['distance'] = !isset($v['distance']) ? '' : $this->dealDistance($v['distance']);
        }
        $page_count = $model_chain->gettotalpage();
        output_data(array('chain_list' => $chain_list), mobile_page($page_count));
    }

    /**
     * 门店详情
     */
    public function chain_infoOp() {
        $chain_id = intval($_POST['chain_id']);
        /** @var chainModel $model_chain */
        $model_chain = Model('chain');
        $chain_info = $model_chain->getChainInfo(array('chain_id' => $chain_id));
        if (empty($chain_info)) {
            output_error('参数错误');
        }
        $chain_info['chain_img'] = UPLOAD_SITE_URL.DS.'chain'.DS.$chain_info['store_id'].DS.$chain_info['chain_img'];

        $res = array(
            'chain_info' => $chain_info,
            'class_list' => array(),
            'goods_list' => array(),
            'current_gc_id' => 0
        );
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $gc_list = $goods_model->getGoodsChainList(array('chain_id' => $chain_id), '`goods`.gc_id_2');

        if (empty($gc_list)) {
            output_data($res);
        }
        $gc_list = array_unique(array_column($gc_list, 'gc_id_2'));
        /** @var goods_classModel $goods_class_model */
        $goods_class_model = Model('goods_class');
        $goods_class_list = $goods_class_model->getCache();
        $goods_class_list = $goods_class_list['data'];
        foreach ($gc_list as $gc_id) {
            if (array_key_exists($gc_id, $goods_class_list)) {
                $gc_data = $goods_class_list[$gc_id];
                $res['class_list'][] = array(
                    'gc_id' => $gc_data['gc_id'],
                    'gc_name' => $gc_data['gc_name']
                );
            }
        }

        if (empty($res['class_list'])) {
            output_data($res);
        }

        $all_c = array(
            'gc_id' => 0,
            'gc_name' => '全部',
        );

        array_unshift($res['class_list'], $all_c);

        $gc_goods_list = $this->get_gc_goods_list($chain_id, 0);
        $res['goods_list'] = $gc_goods_list['goods_list'];
        output_data($res, mobile_page($gc_goods_list['page_count']));

    }

    /**
     * 门店商品列表
     */
    public function get_goods_listOp() {
        $gc_id = intval($_POST['gc_id']);
        $chain_id = intval($_POST['chain_id']);
        if ($gc_id < 0 || !$chain_id) {
            output_error('参数错误');
        }
        $result = $this->get_gc_goods_list($chain_id, $gc_id);
        output_data(array('goods_list' => $result['goods_list']), mobile_page($result['page_count']));
    }

    //获取门店分类下的商品列表
    private function get_gc_goods_list($chain_id, $gc_id) {
        if ($chain_id <0 || $gc_id<0) {
            return array();
        }
        $condition = array(
            'chain_id' => $chain_id,
            'gc_id' => $gc_id
        );

        if ($gc_id == 0) {
            unset($condition['gc_id']);
        }

        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsChainList($condition, 'goods.*,chain_stock.stock', 7);
        $page_count = $goods_model->gettotalpage();
        /** @var goodsLogic $logic_goods */
        $logic_goods = Logic('goods');
        $goods_list = $logic_goods->deal_goods_list($goods_list);
        return array('goods_list' => $goods_list, 'page_count' => $page_count);
    }

    //处理距离
    private function dealDistance($distance) {
        if ($distance <= 0) {
            return '';
        }

        if ($distance < 1000) {
            return round($distance, 2) . '米';
        }

        return round($distance/1000, 2). '千米';
    }

}
