<?php
/**
 * 消费记录
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');

class consumeControl extends BaseMemberControl {
    public function __construct() {
        parent::__construct();
    }
    
    public function indexOp() {
        $model_consume = Model('consume');
        $consume_list = $model_consume->getConsumeList(array('member_id' => $_SESSION['member_id']), '*', 20);
        Tpl::output('show_page', $model_consume->showpage());
        Tpl::output('consume_list', $consume_list);
        Tpl::output('consume_type', $this->type);
        $this->profile_menu('consume', 'consume');
        Tpl::showpage('consume.list');
    }
    
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        $menu_array = array();
        switch ($menu_type) {
            case 'consume':
                $menu_array = array(
                1=>array('menu_key'=>'consume','menu_name'=>'消费记录',   'menu_url'=>'index.php?act=consume'));
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
