<?php
/**
 * 浏览历史
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */



defined('ByShopWWI') or exit('Access Invalid!');

class member_goodsbrowseControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 我的浏览历史
     */
    public function browse_listOp() {
		$model_browse = Model('goods_browse');
		$viewed_goods = $model_browse->getViewedGoodsList($this->member_info['member_id'],20);
		foreach($viewed_goods as $key=>$value){
			$viewed_goods[$key]['goods_image_url'] = thumb($value, 360);
		}
		//$page_count = $model_browse->gettotalpage();
		output_data(array('goodsbrowse_list'=>$viewed_goods));
    }
	
    public function browse_addOp(){
        //$_POST['goods_id'] = 100080;
        $model_browse= Model('goods_browse');
        $member_id = $this->member_info['member_id'];
        $result=$model_browse->addViewedGoods($_POST['goods_id'] , $member_id);
        output_data(1);
    }
	
	/**
     * 清空浏览历史
     */
	public function browse_clearallOp() {
		$model_browse = Model('goods_browse');
		$return = $model_browse->delGoodsbrowse(array('member_id'=>$this->member_info['member_id']));
		if($return){
		    if(C('cache_open')){
		        //删除缓存
		        dcache($this->member_info['member_id'] , 'goodsbrowse');
		    }
			output_data('清空成功');
		}else{
			output_error('清空失败');
		}
	}
		

	protected function getMemberAndGradeInfo($is_return = false){
        $member_info = array();
        //会员详情及会员级别处理
        if($_SESSION['member_id']) {
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
            if ($member_info){
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info,$member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true){//返回会员信息
            return $member_info;
        } else {//输出会员信息
            Tpl::output('member_info',$member_info);
        }
    }

}
