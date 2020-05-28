<?php
/**
 * Author: Shen.L
 * Date: 2016/7/19
 * Time: 16:25
 */


/**
 * Class StoreService
 */
class StoreService
{
    /** @var  array */
    private $_store;
    /** @var storeModel  */
    private $_model;

    public function __construct()
    {
        $this->_model = Model('store');
    }

    /**
     * 获取店铺类型
     * @param $store array
     * @return string
     */
    public function getManageType($store)
    {
        /** 若新类型或者未到生效期，则返回旧类型 */
        if(empty($store['manage_type_new'])||$store['manage_type_validate']>TIMESTAMP){
            return $store['manage_type'];
        }
        $update = array(
            'manage_type' => $store['manage_type_new'],
            'manage_type_new' => '',
            //'manage_type_validate' => 0,
            );
        $this->_model->editStore($update,array('store_id'=>$store['store_id']));
        $this->_store['manage_type'] = $update['manage_type'];
        return $update['manage_type'];
    }
    public function getManageTypeById($id)
    {
        $store = $this->getStoreById($id);
        if(empty($store)) return null;
        return $this->getManageType($store);
    }

    public function getStoreById($id){
        if($this->_store== null || $this->_store['store_id'] != $id){
            $this->_store =  $this->_model->getStoreInfo(array('store_id'=>$id));
        }
        return $this->_store;
    }
}