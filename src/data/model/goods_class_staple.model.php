<?php
/**
 * 常用商品分类模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined ( 'ByShopWWI' ) or exit ( 'Access Invalid!' );
class goods_class_stapleModel extends Model {
    public function __construct(){
        parent::__construct('goods_class_staple');
    }

    /**
     * 常用分类列表
     *
     * @param array $condition 条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 限制
     * @return array 二维数组
     */
    public function getStapleList($condition, $field = '*', $order = 'counter desc', $limit = 20) {
        $result = $this->field($field)->where($condition)->order($order)->limit($limit)->select();
        return $result;
    }

    /**
     * 一条记录
     *
     * @param array $condition 检索条件
     * @param object $page
     * @return array 一维数组结构的返回结果
     */
    public function getStapleInfo($condition, $field = '*') {
        $result = $this->field($field)->where($condition)->find();
        return $result;
    }

    /**
     * 添加常用分类，如果已存在计数器+1
     *
     * $param array $patam 参数
     * $param int $member_id 成员id
     */
    public function autoIncrementStaple($param, $member_id) {
        $where = array(
            'gc_id_1' => intval($param['gc_id_1']),
            'gc_id_2' => intval($param['gc_id_2']),
            'gc_id_3' => intval($param['gc_id_3']),
            'member_id' => $member_id
        );
        $staple_info = $this->getStapleInfo($where);
        if (empty($staple_info)) {
            $insert = array(
                    'staple_name' => $param['gc_tag_name'],
                    'gc_id_1' => intval($param['gc_id_1']),
                    'gc_id_2' => intval($param['gc_id_2']),
                    'gc_id_3' => intval($param['gc_id_3']),
                    'type_id' => $param['type_id'],
                    'member_id' => $member_id
                );
            $this->addStaple($insert);
        } else {
            $update = array('counter' => array('exp', 'counter + 1'));
            $where = array('staple_id' => $staple_info['staple_id']);
            $this->updateStaple($update, $where);
        }
        return true;
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return boolean 布尔类型的返回结果
     */
    public function addStaple($param) {
        $result = $this->insert($param);
        return $result;
    }

    /**
     * 更新
     *
     * @param array $update 更新内容
     * @param array $where 条件
     * @return boolean
     */
    public function updateStaple($update, $where) {
        $result = $this->where($where)->update($update);
        return $result;
    }

    /**
     * 删除常用分类
     *
     * @param array $condtion 条件
     * @return boolean
     */
    public function delStaple($condtion) {
        $result = $this->where($condtion)->delete();
        return $result;
    }
}
