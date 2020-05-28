<?php
/**
 * 限时折扣活动模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class p_pintuanModel extends Model{

    const PINTUAN_STATE_NORMAL = 1;
    const PINTUAN_STATE_CLOSE = 2;
    const PINTUAN_STATE_CANCEL = 3;

    private $pintuan_state_array = array(
        0 => '全部',
        self::PINTUAN_STATE_NORMAL => '正常',
        self::PINTUAN_STATE_CLOSE => '已结束',
        self::PINTUAN_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('p_pintuan');
    }

    /**
     * 读取限时折扣列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getPintuanList($condition, $page=null, $order='', $field='*') {
        $pintuan_list = $this->table('p_pintuan')->field($field)->where($condition)->page($page)->order($order)->select();
        if(!empty($pintuan_list)) {
            for($i =0, $j = count($pintuan_list); $i < $j; $i++) {
                $pintuan_list[$i] = $this->getPintuanExtendInfo($pintuan_list[$i]);
            }
        }
        return $pintuan_list;
    }

    /**
     * 读取限时折扣配置列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getPintuanConfigList($condition, $page=null, $order='', $field='*') {
        $pintuan_list = $this->table('p_pintuan_config')->field($field)->where($condition)->page($page)->order($order)->select();
        if(!empty($pintuan_list)) {
            for($i =0, $j = count($pintuan_list); $i < $j; $i++) {
                $pintuan_list[$i] = $this->getPintuanExtendInfo($pintuan_list[$i]);
            }
        }
        return $pintuan_list;
    }

    /**
     * 根据条件读取限制折扣信息
     * @param array $condition 查询条件
     * @return array 限时折扣信息
     *
     */
    public function getPintuanInfo($condition) {
        $pintuan_info = $this->table('p_pintuan')->where($condition)->find();
        $pintuan_info = $this->getPintuanExtendInfo($pintuan_info);
        return $pintuan_info;
    }

    public function getPintuanConfigInfo($condition) {
        $pintuan_info = $this->table('p_pintuan_config')->where($condition)->find();
        return $pintuan_info;
    }

    /**
     * 根据限时折扣编号读取限制折扣信息
     * @param array $pintuan_id 限制折扣活动编号
     * @param int $store_id 如果提供店铺编号，判断是否为该店铺活动，如果不是返回null
     * @return array 限时折扣信息
     *
     */
    public function getPintuanInfoByID($pintuan_id, $store_id = 0) {
        if(intval($pintuan_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['pintuan_id'] = $pintuan_id;
        $pintuan_info = $this->getPintuanInfo($condition);
        if($store_id > 0 && $pintuan_info['store_id'] != $store_id) {
            return null;
        } else {
            return $pintuan_info;
        }
    }

    /**
     * 限时折扣状态数组
     *
     */
    public function getPintuanStateArray() {
        return $this->pintuan_state_array;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     *
     */
    public function addPintuan($param){
        $param['state'] = self::PINTUAN_STATE_NORMAL;
        return $this->table('p_pintuan')->insert($param);
    }

    public function addPintuanConfig($param){
        return $this->table('p_pintuan_config')->insert($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editPintuan($update, $condition){
        return $this->table('p_pintuan')->where($condition)->update($update);
    }

    /*
     * 删除限时折扣活动，同时删除限时折扣商品
     * @param array $condition
     * @return bool
     *
     */
    public function delPintuan($condition){
        $pintuan_list = $this->getPintuanList($condition);
        $pintuan_id_string = '';
        if(!empty($pintuan_list)) {
            foreach ($pintuan_list as $value) {
                $pintuan_id_string .= $value['pintuan_id'] . ',';
            }
        }

        //删除限时折扣商品
        if($pintuan_id_string !== '') {
            $model_pintuan_goods = Model('p_pintuan_goods');
            $model_pintuan_goods->delPintuanGoods(array('pintuan_id'=>array('in', $pintuan_id_string)));
        }

        return $this->table('p_pintuan')->where($condition)->delete();
    }

    public function delPintuanConfig($config_pintuan_id) {
        // 查看是否有店铺使用该活动
        $w = array('config_pintuan_id' => $config_pintuan_id);
        $pintuan = $this->getPintuanList($w);
        if (!empty($pintuan)) return false;

        return $this->table('p_pintuan_config')->where($w)->delete();
    }

    /*
     * 取消限时折扣活动，同时取消限时折扣商品
     * @param array $condition
     * @return bool
     *
     */
    public function cancelPintuan($condition){
        $pintuan_list = $this->getPintuanList($condition);
        $pintuan_id_string = '';
        if(!empty($pintuan_list)) {
            foreach ($pintuan_list as $value) {
                $pintuan_id_string .= $value['pintuan_id'] . ',';
            }
        }

        $update = array();
        $update['state'] = self::PINTUAN_STATE_CANCEL;

        //删除限时折扣商品
        if($pintuan_id_string !== '') {
            $model_pintuan_goods = Model('p_pintuan_goods');
            $model_pintuan_goods->editPintuanGoods($update, array('pintuan_id'=>array('in', $pintuan_id_string)));
        }

        return $this->editPintuan($update, $condition);
    }

    /**
     * 获取限时折扣扩展信息，包括状态文字和是否可编辑状态
     * @param array $pintuan_info
     * @return string
     *
     */
    public function getPintuanExtendInfo($pintuan_info) {
        if($pintuan_info['end_time'] > TIMESTAMP) {
            $pintuan_info['pintuan_state_text'] = $this->pintuan_state_array[$pintuan_info['state']];
        } else {
            $pintuan_info['pintuan_state_text'] = '已结束';
        }

        if($pintuan_info['state'] == self::PINTUAN_STATE_NORMAL && $pintuan_info['end_time'] > TIMESTAMP) {
            $pintuan_info['editable'] = true;
        } else {
            $pintuan_info['editable'] = false;
        }

        return $pintuan_info;
    }

    /**
     * 过期修改状态
     */
    public function editExpirePintuan($condition) {
        $condition['end_time'] = array('lt', TIMESTAMP);

        // 更新商品促销价格
        $pintuangoods_list = Model('p_pintuan_goods')->getPintuanGoodsList($condition);
        if (!empty($pintuangoods_list)) {
            $goodsid_array = array();
            foreach ($pintuangoods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            // 更新商品促销价格，需要考虑团购是否在进行中
            QueueClient::push('updateGoodsPromotionPriceByGoodsId', $goodsid_array);
        }
        $condition['state'] = self::PINTUAN_STATE_NORMAL;

        $updata = array();
        $update['state'] = self::PINTUAN_STATE_CLOSE;
        $this->editPintuan($update, $condition);
        return true;
    }

}
