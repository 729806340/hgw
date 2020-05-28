<?php
/**
 * 系统文章
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class documentModel extends Model {
    /**
     * 查询所有系统文章
     */
    public function getList(){
        $param  = array(
            'table' => 'document'
        );
        return Db::select($param);
    }
    /**
     * 根据编号查询一条
     *
     * @param unknown_type $id
     */
    public function getOneById($id){
        $param  = array(
            'table' => 'document',
            'field' => 'doc_id',
            'value' => $id
        );
        return Db::getRow($param);
    }
    /**
     * 根据标识码查询一条
     *
     * @param unknown_type $id
     */
    public function getOneByCode($code){
        $param  = array(
            'table' => 'document',
            'field' => 'doc_code',
            'value' => $code
        );
        return Db::getRow($param);
    }
    /**
     * 更新
     *
     * @param unknown_type $param
     */
    public function updates($param){
        return Db::update('document',$param,"doc_id='{$param['doc_id']}'");
    }
}
