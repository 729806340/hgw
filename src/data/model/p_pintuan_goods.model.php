<?php
/**
 * 限时折扣活动商品模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class p_pintuan_goodsModel extends Model{

    const PINTUAN_GOODS_STATE_CANCEL = 0;
    const PINTUAN_GOODS_STATE_NORMAL = 1;

    public function __construct(){
        parent::__construct('p_pintuan_goods');
    }

    /**
     * 读取限时折扣商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 限时折扣商品列表
     *
     */
    public function getPintuanGoodsList($condition, $page=null, $order='', $field='*', $limit = 0) {
        return $pintuan_goods_list = $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    /**
     * 读取限时折扣商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 限时折扣商品列表
     *
     */
    public function getPintuanGoodsExtendList($condition, $page=null, $order='', $field='*', $limit = 0) {
        $pintuan_goods_list = $this->getPintuanGoodsList($condition, $page, $order, $field, $limit);
        if(!empty($pintuan_goods_list)) {
            for($i=0, $j=count($pintuan_goods_list); $i < $j; $i++) {
                $pintuan_goods_list[$i] = $this->getPintuanGoodsExtendInfo($pintuan_goods_list[$i]);
            }
        }
        return $pintuan_goods_list;
    }
	
	 /**
     * 读取限时折扣商品IDS
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 限时折扣商品列表
     *
     */
	public function getPintuanGoodsExtendIds($condition, $page=null, $order='', $field='goods_id', $limit = 0) {
        $pintuan_goods_id_list = $this->getPintuanGoodsList($condition, $page, $order, $field, $limit);
      
		if(!empty($pintuan_goods_id_list)){
			for($i=0;$i<count($pintuan_goods_id_list); $i++){
				
				$pintuan_goods_id_list[$i]=$pintuan_goods_id_list[$i]['goods_id'];
				 
			}
		}
		
        return $pintuan_goods_id_list;
	}

    /**
     * 根据条件读取限制折扣商品信息
     * @param array $condition 查询条件
     * @return array 限时折扣商品信息
     *
     */
    public function getPintuanGoodsInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

    /**
     * 根据限时折扣商品编号读取限制折扣商品信息
     * @param int $pintuan_goods_id
     * @return array 限时折扣商品信息
     *
     */
    public function getPintuanGoodsInfoByID($pintuan_goods_id, $store_id = 0) {
        if(intval($pintuan_goods_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['pintuan_goods_id'] = $pintuan_goods_id;
        $pintuan_goods_info = $this->getPintuanGoodsInfo($condition);

        if($store_id > 0 && $pintuan_goods_info['store_id'] != $store_id) {
            return null;
        } else {
            return $pintuan_goods_info;
        }
    }

    /**
     * 增加限时折扣商品
     * @param array $pintuan_goods_info
     * @return bool
     *
     */
    public function addPintuanGoods($pintuan_goods_info){
        $pintuan_goods_info['state'] = self::PINTUAN_GOODS_STATE_NORMAL;
        $pintuan_goods_id = $this->insert($pintuan_goods_info);

        // 删除商品限时折扣缓存
        $this->_dGoodsPintuanCache($pintuan_goods_info['goods_id']);

        $pintuan_goods_info['pintuan_goods_id'] = $pintuan_goods_id;
        $pintuan_goods_info = $this->getPintuanGoodsExtendInfo($pintuan_goods_info);
        return $pintuan_goods_info;
    }

    /**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editPintuanGoods($update, $condition){
        $result = $this->where($condition)->update($update);
        if ($result) {
            $pintuan_goods_list = $this->getPintuanGoodsList($condition, null, '', 'goods_id');
            if (!empty($pintuan_goods_list)) {
                foreach ($pintuan_goods_list as $val) {
                    // 删除商品限时折扣缓存
                    $this->_dGoodsPintuanCache($val['goods_id']);
                    // 插入对列 更新促销价格
                    QueueClient::push('updateGoodsPromotionPriceByGoodsId', $val['goods_id']);
                }
            }
        }
        return $result;
    }

    /**
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delPintuanGoods($condition){
        $pintuan_goods_list = $this->getPintuanGoodsList($condition, null, '', 'goods_id');
        $result = $this->where($condition)->delete();
        if ($result) {
            if (!empty($pintuan_goods_list)) {
                foreach ($pintuan_goods_list as $val) {
                    // 删除商品限时折扣缓存
                    $this->_dGoodsPintuanCache($val['goods_id']);
                    // 插入对列 更新促销价格
                    QueueClient::push('updateGoodsPromotionPriceByGoodsId', $val['goods_id']);
                }
            }
        }
        return $result;
    }

    /**
     * 获取限时折扣商品扩展信息
     * @param array $pintuan_info
     * @return array 扩展限时折扣信息
     *
     */
    public function getPintuanGoodsExtendInfo($pintuan_info) {
        $pintuan_info['goods_url'] = urlShop('goods', 'index', array('goods_id' => $pintuan_info['goods_id']));
        $pintuan_info['image_url'] = cthumb($pintuan_info['goods_image'], 60, $pintuan_info['store_id']);
        $pintuan_info['pintuan_price'] = ncPriceFormat($pintuan_info['pintuan_price']);
        return $pintuan_info;
    }

    /**
     * 获取推荐限时折扣商品
     * @param int $count 推荐数量
     * @return array 推荐限时活动列表
     *
     */
    public function getPintuanGoodsCommendList($count = 4) {
        $condition = array();
        $condition['state'] = self::PINTUAN_GOODS_STATE_NORMAL;
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $pintuan_list = $this->getPintuanGoodsExtendList($condition, null, 'pintuan_recommend desc', '*', $count);
        return $pintuan_list;
    }

    /**
     * 根据商品编号查询是否有可用限时折扣活动，如果有返回限时折扣活动，没有返回null
     * @param int $goods_id
     * @return array $pintuan_info
     *
     */
    public function getPintuanGoodsInfoByGoodsID($goods_id,$updateCache=false) {
        $info = $this->_rGoodsPintuanCache($goods_id);
        if(empty($info)||$updateCache) {
            $condition['state'] = self::PINTUAN_GOODS_STATE_NORMAL;
            $condition['end_time'] = array('gt', TIMESTAMP);
            $condition['start_time'] = array('lt', TIMESTAMP);
            $condition['goods_id'] = $goods_id;
            $pintuan_goods_list = $this->getPintuanGoodsExtendList($condition, null, 'start_time asc', '*', 1);
            $info['info'] = serialize($pintuan_goods_list[0]);
            $this->_wGoodsPintuanCache($goods_id, $info);
        }
        $pintuan_goods_info = unserialize($info['info']);
        if (!empty($pintuan_goods_info) && ($pintuan_goods_info['start_time'] > TIMESTAMP || $pintuan_goods_info['end_time'] < TIMESTAMP)) {
            $pintuan_goods_info = array();
        }else if($pintuan_goods_info){
            $pintuan_goods_info['now_time'] = time();
        }
        return $pintuan_goods_info;
    }

    /**
     * 根据商品编号查询是否有可用限时折扣活动，如果有返回限时折扣活动，没有返回null
     * @param string $goods_string 商品编号字符串，例：'1,22,33'
     * @return array $pintuan_goods_list
     *
     */
    public function getPintuanGoodsListByGoodsString($goods_string) {
        $pintuan_goods_list = $this->_getPintuanGoodsListByGoods($goods_string);
        $pintuan_goods_list = array_under_reset($pintuan_goods_list, 'goods_id');
        return $pintuan_goods_list;
    }

    /**
     * 根据商品编号查询是否有可用限时折扣活动，如果有返回限时折扣活动，没有返回null
     * @param string $goods_id_string
     * @return array $pintuan_info
     *
     */
    private function _getPintuanGoodsListByGoods($goods_id_string) {
        $condition = array();
        $condition['state'] = self::PINTUAN_GOODS_STATE_NORMAL;
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $condition['goods_id'] = array('in', $goods_id_string);
        $pintuan_goods_list = $this->getPintuanGoodsExtendList($condition, null, 'pintuan_goods_id desc', '*');
        return $pintuan_goods_list;
    }

    /**
     * 读取商品限时折扣缓存
     * @param int $goods_id
     * @return array/bool
     */
    private function _rGoodsPintuanCache($goods_id) {
        return rcache($goods_id, 'goods_pintuan');
    }

    /**
     * 写入商品限时折扣缓存
     * @param int $goods_id
     * @param array $info
     * @return boolean
     */
    private function _wGoodsPintuanCache($goods_id, $info) {
        return wcache($goods_id, $info, 'goods_pintuan');
    }

    /**
     * 删除商品限时折扣缓存
     * @param int $goods_id
     * @return boolean
     */
    private function _dGoodsPintuanCache($goods_id) {
        return dcache($goods_id, 'goods_pintuan');
    }
}
