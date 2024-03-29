<?php
/**
 * 满即送模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class p_mansongModel extends Model{

    const MANSONG_STATE_NORMAL = 1;
    const MANSONG_STATE_CLOSE = 2;
    const MANSONG_STATE_CANCEL = 3;

    private $mansong_state_array = array(
        0 => '全部',
        self::MANSONG_STATE_NORMAL => '正常',
        self::MANSONG_STATE_CLOSE => '已结束',
        self::MANSONG_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('p_mansong');
    }

    /**
     * 读取满即送列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getMansongList($condition, $page=null, $order='', $field='*', $limit = 0) {
        $mansong_list = $this->field($field)->where($condition)->limit($limit)->page($page)->order($order)->select();
        if(!empty($mansong_list)) {
            for($i =0, $j = count($mansong_list); $i < $j; $i++) {
                $mansong_list[$i] = $this->getMansongExtendInfo($mansong_list[$i]);
            }
        }
        return $mansong_list;
    }

    /**
     * 获取店铺新满即送活动开始时间限制
     *
     */
    public function getMansongNewStartTime($store_id) {
        if(empty($store_id)) {
            return null;
        }
        $condition = array();
        $condition['store_id'] = $store_id;
        $condition['state'] = self::MANSONG_STATE_NORMAL;
        $mansong_list = $this->getMansongList($condition, null, 'end_time desc');
        return $mansong_list[0]['end_time'];
    }

    /**
     * 根据条件读满即送信息
     * @param array $condition 查询条件
     * @return array 限时折扣信息
     *
     */
    public function getMansongInfo($condition) {
        $mansong_info = $this->where($condition)->find();
        $mansong_info = $this->getMansongExtendInfo($mansong_info);
        return $mansong_info;
    }

    /**
     * 根据满即送编号读取信息
     * @param array $mansong_id 限制折扣活动编号
     * @param int $store_id 如果提供店铺编号，判断是否为该店铺活动，如果不是返回null
     * @return array 限时折扣信息
     *
     */
    public function getMansongInfoByID($mansong_id, $store_id = 0) {
        if(intval($mansong_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['mansong_id'] = $mansong_id;
        $mansong_info = $this->getMansongInfo($condition);
        if($store_id > 0 && $mansong_info['store_id'] != $store_id) {
            return null;
        } else {
            return $mansong_info;
        }
    }

    /**
     * 获取店铺当前可用满即送活动
     * @param array $store_id 店铺编号
     * @param array $goods_id 商品ID
     * @return array 满即送活动
     *
     */
    public function getMansongInfoByStoreID($store_id,$goods_id=0) {
        if(intval($store_id) <= 0) {
            return array();
        }
        $info = $this->_rGoodsMansongCache($store_id);
        if (empty($info)) {
            $condition = array();
            $condition['state'] = self::MANSONG_STATE_NORMAL;
            $condition['store_id'] = $store_id;
            $condition['end_time'] = array('gt', TIMESTAMP);
            $mansong_list = $this->getMansongList($condition, null, 'start_time asc', '*', 1);

            $mansong_info = $mansong_list[0];

            if(!empty($mansong_info)) {
                $model_mansong_rule = Model('p_mansong_rule');
                $mansong_info['rules'] = $model_mansong_rule->getMansongRuleListByID($mansong_info['mansong_id']);
                if (empty($mansong_info['rules'])) {
                    $mansong_info = array(); // 如果不存在规则直接返回不记录缓存。
                } else {
                    // 规则数组序列化保存
                    $mansong_info['rules'] = serialize($mansong_info['rules']);
                }
            }
            $info['info'] = serialize($mansong_info);
            $this->_wGoodsMansongCache($store_id, $info);
        }
        $mansong_info = unserialize($info['info']);
        if (!empty($mansong_info) && $mansong_info['start_time'] > TIMESTAMP) {
            $mansong_info = array();
        }
        if (!empty($mansong_info)) {
            $mansong_info['rules'] = unserialize($mansong_info['rules']);
            if($goods_id>0){
                $rules = array();
                foreach ($mansong_info['rules'] as $rule){
                    if($rule['rule_range'] ==0){
                        $rules[] = $rule;
                    }else{
                        $sku = explode(',',$rule['rule_sku']);
                        if(in_array($goods_id,$sku)==($rule['rule_range'] ==1))$rules[] = $rule;
                    }
                }
                if(count($rules)>0){
                    $mansong_info['rules'] = $rules;
                }else{
                    $mansong_info = array();
                }
            }

        } else {
            $mansong_info = array();
        }
        return $mansong_info;
    }

    /**
     * 获取订单可用满即送规则
     * @param array $store_id 店铺编号
     * @param array $order_price 订单金额
     * @param array $store_cart_list 订单商品列表
     * @return array 满即送规则
     *
     */
    public function getMansongRuleByStoreID($store_id, $order_price,$store_cart_list) {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $mansong_info = $this->getMansongInfoByStoreID($store_id);
        if(empty($mansong_info)) {
            return null;
        }
        $rule_info = null;
        $rules = array_reverse($mansong_info['rules']);
        foreach ($rules as $k=>$value) {
            // 根据适用范围计算订单金额
            if($value['rule_range'] == 0) $amount = $order_price;
            else{
                $amount=0;
                $sku = explode(',',$value['rule_sku']);
                foreach ($store_cart_list as $goods){
                    if(in_array($goods['goods_id'],$sku) == ($value['rule_range']==1))
                        $amount += $goods['goods_price']*$goods['goods_num'];
                }
            }

            // 根据规则和商品，获取每种规则的最大优惠金额
            if($amount>= $value['price']) {
                if($value['goods_id']>0){
                    $gift = $goodsModel->getGoodsInfo(array('goods_id'=>$value['goods_id']));
                    if(empty($gift)) $rules[$k]['goods_price']=0;
                    else $rules[$k]['goods_price']=$gift['goods_price'];
                }else{
                    $rules[$k]['goods_price']=0;
                }
                $rules[$k]['discount_repeat']=1;
                if($value['rule_repeat']>0) {
                    $rules[$k]['discount_repeat'] =floor($amount/$value['price']);
                    $rules[$k]['discount_amount'] = ($rules[$k]['discount']+$rules[$k]['goods_price'])*$rules[$k]['discount_repeat'];
                } else $rules[$k]['discount_amount'] = $rules[$k]['discount']+$rules[$k]['goods_price'];
            }else{
                $rules[$k]['discount_amount'] = -1;
            }
            continue;
        }
        // 查找最大的优惠
        $max = -1;
        $rule_info=null;
        foreach ($rules as $rule){
            if($rule['discount_amount']>$max) {
                $max=$rule['discount_amount'];
                $rule_info=$rule;
            }
        }
        return $rule_info;
    }

    /**
     * 获取满即送状态列表
     *
     */
    public function getMansongStateArray() {
        return $this->mansong_state_array;
    }

    /**
     * 获取满即送扩展信息，包括状态文字和是否可编辑状态
     * @param array $mansong_info
     * @return string
     *
     */
    public function getMansongExtendInfo($mansong_info) {
        if($mansong_info['end_time'] > TIMESTAMP) {
            $mansong_info['mansong_state_text'] = $this->mansong_state_array[$mansong_info['state']];
        } else {
            $mansong_info['mansong_state_text'] = '已结束';
        }

        if($mansong_info['state'] == self::MANSONG_STATE_NORMAL && $mansong_info['end_time'] > TIMESTAMP) {
            $mansong_info['editable'] = true;
        } else {
            $mansong_info['editable'] = false;
        }

        return $mansong_info;
    }

    /**
     * 增加
     * @param array $param
     * @return bool
     *
     */
    public function addMansong($param){
        $param['state'] = self::MANSONG_STATE_NORMAL;
        $result = $this->insert($param);
        if ($result) {
            $this->_dGoodsMansongCache($param['store_id']);
        }
        return $result;
    }

    /**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editMansong($update, $condition){
        $mansong_list = $this->getMansongList($condition);
        if (empty($mansong_list)) {
            return true;
        }
        $result = $this->where($condition)->update($update);
        if ($result) {
            foreach ($mansong_list as $val) {
                $this->_dGoodsMansongCache($val['store_id']);
            }
        }
        return $result;
    }

    /**
     * 删除限时折扣活动，同时删除限时折扣商品
     * @param array $condition
     * @return bool
     *
     */
    public function delMansong($condition){
        $mansong_list = $this->getMansongList($condition);
        $mansong_id_string = '';
        if(!empty($mansong_list)) {
            foreach ($mansong_list as $value) {
                $mansong_id_string .= $value['mansong_id'] . ',';
                $this->_dGoodsMansongCache($value['store_id']);
            }
        }

        //删除满送规则
        $model_mansong_rule = Model('p_mansong_rule');
        $model_mansong_rule->delMansongRule($condition);

        return $this->where($condition)->delete();
    }

    /**
     * 取消满即送活动
     * @param array $condition
     * @return bool
     *
     */
    public function cancelMansong($condition){
        $update = array();
        $update['state'] = self::MANSONG_STATE_CANCEL;
        return $this->editMansong($update, $condition);
    }


    /**
     * 过期满送修改状态
     */
    public function editExpireMansong() {
        $updata = array();
        $update['state'] = self::MANSONG_STATE_CLOSE;

        $condition = array();
        $condition['end_time'] = array('lt', TIMESTAMP);
        $condition['state'] = self::MANSONG_STATE_NORMAL;
        $this->editMansong($update, $condition);
    }

    /**
     * 读取商品满即送缓存
     * @param int $store_id
     * @return array
     */
    private function _rGoodsMansongCache($store_id) {
        return rcache($store_id, 'goods_mansong');
    }

    /**
     * 写入商品满即送缓存
     * @param int $store_id
     * @param array $mansong_info
     * @return boolean
     */
    private function _wGoodsMansongCache($store_id, $mansong_info) {
        return wcache($store_id, $mansong_info, 'goods_mansong');
    }

    /**
     * 删除商品满即送缓存
     * @param int $store_id
     * @return boolean
     */
    private function _dGoodsMansongCache($store_id) {
        return dcache($store_id, 'goods_mansong');
    }
}
