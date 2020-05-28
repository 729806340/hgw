<?php
/**
 * 满即送活动规则模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class p_mansong_ruleModel extends Model{

    public function __construct(){
        parent::__construct('p_mansong_rule');
    }

    /**
     * 读取满即送规则列表
     * @param array $mansong_id 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 满即送套餐列表
     *
     */
    public function getMansongRuleListByID($mansong_id) {
        $condition = array();
        $condition['mansong_id'] = $mansong_id;
        $mansong_rule_list = $this->where($condition)->order('price asc')->select();
        if(!empty($mansong_rule_list)) {
            /** @var goodsModel $model_goods */
            $model_goods = Model('goods');

            for($i =0, $j = count($mansong_rule_list); $i < $j; $i++) {
                $goods_id = intval($mansong_rule_list[$i]['goods_id']);
                if(!empty($goods_id)) {
                    $goods_info = $model_goods->getGoodsOnlineInfoByID($goods_id);
                    if(!empty($goods_info)) {
                        if(empty($mansong_rule_list[$i]['mansong_goods_name'])) {
                            $mansong_rule_list[$i]['mansong_goods_name'] = $goods_info['goods_name'];
                        }
                        $mansong_rule_list[$i]['goods_image'] = $goods_info['goods_image'];
                        $mansong_rule_list[$i]['goods_image_url'] = cthumb($goods_info['goods_image'], $goods_info['store_id']);
                        $mansong_rule_list[$i]['goods_storage'] = $goods_info['goods_storage'];
                        $mansong_rule_list[$i]['goods_cost'] = $goods_info['goods_cost'];
                        $mansong_rule_list[$i]['tax_input'] = $goods_info['tax_input'];
                        $mansong_rule_list[$i]['tax_output'] = $goods_info['tax_output'];
                        $mansong_rule_list[$i]['goods_id'] = $goods_id;
                        $mansong_rule_list[$i]['goods_url'] = urlShop('goods', 'index', array('goods_id' => $goods_id));
                    }
                }
                $rule = $mansong_rule_list[$i];
                if($rule['rule_range']>0){
                    $sku = $rule['rule_sku'];
                    $goodsList = $model_goods->getGoodsList(array('goods_id'=>array('in',$sku)));
                    $mansong_rule_list[$i]['sku'] = $goodsList;
                }
            }
        }
        return $mansong_rule_list;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     *
     */
    public function addMansongRule($param){
        return $this->insert($param);
    }

    /*
     * 批量增加
     * @param array $array
     * @return bool
     *
     */
    public function addMansongRuleArray($array){
        return $this->insertAll($array);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delMansongRule($condition){
        return $this->where($condition)->delete();
    }
}
