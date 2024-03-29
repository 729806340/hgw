<?php
/**
 * 店铺模型管理
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class store_plateModel extends Model {
    public function __construct(){
        parent::__construct('store_plate');
    }

    /**
     * 版式列表
     * @param array $condition
     * @param string $field
     * @param int $page
     * @return array
     */
    public function getStorePlateList($condition, $field = '*', $page = 0) {
        return $this->field($field)->where($condition)->page($page)->select();
    }

    /**
     * 版式详细信息
     * @param array $condition
     * @return array
     */
    public function getStorePlateInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getStorePlateInfoByID($plate_id, $fields = '*') {
        $info = $this->_rStorePlateCache($plate_id, $fields);
        if (empty($info)) {
            $info = $this->getStorePlateInfo(array('plate_id' => $plate_id));
            $this->_wStorePlateCache($plate_id, $info);
        }
        return $info;
    }

    /**
     * 添加版式
     * @param unknown $insert
     * @return boolean
     */
    public function addStorePlate($insert) {
        return $this->insert($insert);
    }

    /**
     * 更新版式
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editStorePlate($update, $condition) {
        $list = $this->getStorePlateList($condition, 'plate_id');
        if (empty($list)) {
            return true;
        }
        $result = $this->where($condition)->update($update);
        if ($result) {
            foreach ($list as $val) {
                $this->_dStorePlateCache($val['plate_id']);
            }
        }
        return $result;
    }

    /**
     * 删除版式
     * @param array $condition
     * @return boolean
     */
    public function delStorePlate($condition) {
        $list = $this->getStorePlateList($condition, 'plate_id');
        if (empty($list)) {
            return true;
        }
        $result = $this->where($condition)->delete();
        if ($result) {
            foreach ($list as $val) {
                $this->_dStorePlateCache($val['plate_id']);
            }
        }
        return $result;
    }

    /**
     * 读取店铺关联板式缓存缓存
     * @param int $plate_id
     * @param string $fields
     * @return array
     */
    private function _rStorePlateCache($plate_id, $fields) {
        return rcache($plate_id, 'store_plate', $fields);
    }

    /**
     * 写入店铺关联板式缓存缓存
     * @param int $plate_id
     * @param array $info
     * @return boolean
     */
    private function _wStorePlateCache($plate_id, $info) {
        return wcache($plate_id, $info, 'store_plate');
    }

    /**
     * 删除店铺关联板式缓存缓存
     * @param int $plate_id
     * @return boolean
     */
    private function _dStorePlateCache($plate_id) {
        return dcache($plate_id, 'store_plate');
    }
}
