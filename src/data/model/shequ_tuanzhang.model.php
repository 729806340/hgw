<?php
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_tuanzhangModel extends Model
{
    public function __construct() {
        parent::__construct('shequ_tuanzhang');
    }

    static $state = array(
        '0' => '待审核',
        '1' => '已审核',
        '-1'=>'审核未通过'
    );
    static $type = array(
      '1'=>'社区工作人员',
        '2'=>'个体商户',
        '3'=>'自由职业者',
        '4'=>'公司员工'
    );
    static $cate = array(
        '1'=>'餐饮',
        '2'=>'超市便利店'
    );
    public static function getState($id){
        import('ArrayHelper');
        return ArrayHelper::getValue(static::$state,$id,' ');
    }
    public static function getType($id){
        import('ArrayHelper');
        return ArrayHelper::getValue(static::$type,$id,' ');
    }
    public static function getCate($id){
        import('ArrayHelper');
        return ArrayHelper::getValue(static::$cate,$id,' ');
    }


    public function getList($condition, $page='', $order='', $field='*',$limit=null)
    {
        return $this->field($field)->where($condition)->page($page)->limit($limit)->order($order)->select();
    }
    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition){
        $result = $this->where($condition)->find();
        return $result;
    }
    /**
     * 更新
     * @param $data
     * @param $condition
     * @return bool
     */
    public function edit($condition, $data) {
        return $this->where($condition)->update($data);
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function addItem($param){
        return $this->insert($param);
    }


}