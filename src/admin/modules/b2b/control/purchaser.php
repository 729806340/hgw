<?php
/**
 * �ɹ��̹���
 *
 *
 *
 *
 * @�������ṩ����֧�� ��Ȩ�빺��shopnc��Ȩ
 * @license    http://www.hangowa.com
 * @link       ����Ⱥ�ţ�
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
