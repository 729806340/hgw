<?php
/**
 * cms管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */

//use Shopwwi\Tpl;

defined('ByShopWWI') or exit('Access Invalid!');
class cms_special_widgetplControl extends SystemControl{

    public function __construct(){
        parent::__construct();
        Language::read('cms');
    }

    public function indexOp() {
        $this->widgetpl_listOp();
    }

    public function widgetpl_listOp()
    {
    	$condition = array();
    	$model = Model("cms_special_widgtpl") ;
    	$list = $model -> where( $condition ) -> select();
    	
    	Tpl::output('list', $list);
    	Tpl::setDirquna('cms');
    	Tpl::showpage('cms_special_widgetpl.list');
    }

	function widgetpl_editOp()
	{
		$condition = array();
		$condition = array('id' => intval($_REQUEST['id'])) ;
		$model = Model("cms_special_widgtpl") ;
		$detail = $model -> where( $condition ) -> find();
		
		Tpl::output('detail', $detail);
		Tpl::setDirquna('cms');
		Tpl::showpage('cms_special_widgetpl.add');
	}
	
	function widgetpl_saveOp()
	{
		$data = array(
				'name' => $_POST['tpl_name'],
				'content' => $_POST['tpl_content'],
		) ;
		if( $_POST['id'] ) {
			$where = array('id' => intval($_POST['id'])) ;
			Model("cms_special_widgtpl")->where($where)->update($data) ;
		} else {
			Model("cms_special_widgtpl")->insert($data) ;
		}
		$this->widgetpl_listOp();
	}
	
	function widgetpl_addOp()
	{
		Tpl::setDirquna('cms');
		Tpl::showpage('cms_special_widgetpl.add');
	}
}
