<?php
/**
 * jdy采购单商品
 *
 */

defined('ByShopWWI') or exit('Access Invalid!');

class jdy_entryModel extends Model{

    const STATE_CREATED = 0;
    const STATE_MAPPED = 1;
    const STATE_SUCCESS = 100;
    const STATE_MAPPING = 10;
    const STATE_PUSH_ERROR = 20;

    const REFUND_STATE_CREATED = 0;
    const REFUND_STATE_PUSH_ERROR = 20;
    const REFUND_STATE_SUCCESS = 100;

    static $states = array(
        self::STATE_CREATED=>'待映射',
        self::STATE_MAPPED=>'待推送',
        self::STATE_MAPPING=>'映射错误',
        self::STATE_PUSH_ERROR=>'推送错误',
        self::STATE_SUCCESS=>'推送成功',
    );

    public function __construct() {
        parent::__construct('jdy_entry');
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
        return $this->add($param);
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
        return ArrayHelper::getValue(static::$states,$value,'待处理');
    }
}
