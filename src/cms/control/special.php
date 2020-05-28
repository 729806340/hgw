<?php
/**
 * cms专题
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class specialControl extends CMSHomeControl{

    public function __construct() {
        parent::__construct();
        Tpl::output('index_sign','special');
    }

    public function indexOp() {
        $this->special_listOp();
    }

    /**
     * 专题列表
     */
    public function special_listOp() {
        $conition = array();
        $conition['special_state'] = 2;
        $model_special = Model('cms_special');
        $special_list = $model_special->getCMSList($conition, 10, 'special_id desc');
        Tpl::output('show_page', $model_special->showpage(2));
        Tpl::output('special_list', $special_list);
        Tpl::showpage('special_list');
    }

    /**
     * 专题详细页
     */
    public function special_detailOp() {
        $special_file = getCMSSpecialHtml($_GET['special_id']);
        if($special_file) {
            Tpl::output('special_file', $special_file);
            Tpl::output('index_sign', 'special');
            Tpl::showpage('special_detail');
        } else {
            showMessage('专题不存在', '', '', 'error');
        }
    }
}
