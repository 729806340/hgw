<?php
/**
 * jdy采购单商品
 *
 */

defined('ByShopWWI') or exit('Access Invalid!');

class payment_billModel extends Model{

    const STATE_CREATED = 0;
    const STATE_PUSHING = 10;
    const STATE_PUSHED = 100;
    const STATE_ERROR = 20;


    static $states = array(
        self::STATE_CREATED=>'待推送',
        self::STATE_PUSHING=>'推送中',
        self::STATE_PUSHED=>'已推送',
        self::STATE_ERROR=>'推送错误',
    );

    public function __construct() {
        parent::__construct('payment_bill');
    }

    /**
     * 读取列表
     * @param array $condition
     */
    public function getList($condition, $page='', $order='', $field='*',$limit=null) {
        return $this->field($field)->where($condition)->page($page)->limit($limit)->order($order)->select();
    }
    public function getCount($condition) {
        return $this->where($condition)->count();
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function addItem($param){
        return $this->insert($param);
    }

    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function editItem($data, $condition) {
        return $this->where($condition)->update($data);
    }

    /*
     * 查找单条记录
     * @param array $condition
     * @return array
     */
    public function getItemInfo($condition){
        return $this->where($condition)->find();
    }

    public static function getStateName($value){
        import('ArrayHelper');
        return ArrayHelper::getValue(static::$states,$value,'待推送');
    }
}
