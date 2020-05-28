<?php
/**
 * 自定义属性模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class type_customModel extends Model {
    const STATE1 = 1;       // 开启
    const STATE0 = 0;       // 关闭

    public function __construct() {
        parent::__construct('type_custom');
    }

    /**
     * 自定义属性列表
     *
     * @param array $condition
     * @param string $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getTypeCustomList($condition, $field = '*', $order = 'custom_id asc') {
        return $this->field($field)->where($condition)->order($order)->select();
    }

    /**
     * 保存自定义属性
     *
     * @param array $insert
     * @return boolean
     */
    public function addTypeCustomAll($insert) {
        return $this->insertAll($insert);
    }

    /**
     * 编辑自定义属性
     * @param array $update
     * @param array $condition
     * @return array
     */
    public function editTypeCustom($update, $condition) {
        return $this->where($condition)->update($update);
    }

    /**
     * 删除自定义属性
     * @param array $condition
     * @return array
     */
    public function delTypeCustom($condition) {
        return $this->where($condition)->delete();
    }

}
