<?php
/**
 * 采购商管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class purchaserControl extends SystemControl{
    public function __construct(){
        parent::__construct();
        Language::read('goods_class');
    }

    public function indexOp() {
        $this->purchaserOp();
    }

    public function purchaserOp(){
        $purchaser_model = Model('b2b_purchaser');
        $purchaser_list = $purchaser_model->getPurchaserList();

        Tpl::output('class_list',$purchaser_list);
        Tpl::setDirquna('b2b');
        Tpl::showpage('purchaser.index');
    }
}
