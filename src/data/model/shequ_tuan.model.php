<?php
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_tuanModel extends Model
{

    const STATE_FAILED = 0;
    const STATE_CREATING = 10;
    const STATE_SUCCESS = 20;
    const STATE_SHIPPING = 30;
    const STATE_DONE = 40;

   public static $type = array(
       '1'=>'物流',
       '2'=>'自提'
   );
    public function __construct()
    {
        parent::__construct('shequ_tuan');
    }


    public static function getType($id){
        import('ArrayHelper');
        return ArrayHelper::getValue(static::$type,$id,' ');
    }


    public function getList($condition, $page='', $order='', $field='*',$limit=null)
    {

        return $this->where($condition)->page($page)->limit($limit)->order($order)->select();

    }

    public function getListTuan($condition = array(), $fields = '*', $order='id desc',$group = '', $page = null) {
        return $this->where($condition)->page($page)->field($fields)->limit(false)->group($group)->order($order)->select();
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

    /**
     * 获取每个团的参与人数
     * @param array  $v
     * 社区团的信息
     */
    public function getJoinNum($v){

    }
}