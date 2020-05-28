<?php
/**
*积分商城
*by wansyb QQ群：111731672
*你正在使用的是由网店 运 维提供S2.0系统！保障你的网络安全！ 购买授权请前往shopnc
*/
defined('ByShopWWI') or exit('Access Invalid!');
class pointsControl extends mobileControl{

    public function __construct() {
        parent::__construct();
    }
    
    /***
     * 积分兑换礼品列表
     */
    public function gift_listOp(){

        $model_gp = Model('pointprod');
        $condition = array();
        $condition['pgoods_show'] = 1;
        $condition['pgoods_state'] = 0;
        $field = 'pgoods_id , pgoods_name ,pgoods_price,pgoods_points,pgoods_image';
        $glist = $model_gp->getPointProdList($condition , $field,'pgoods_id desc' , '',$this->page);
        $page_count = $model_gp->gettotalpage();
        foreach($glist as $k=>$v){
            unset($glist[$k]['pgoods_image']);
            unset($glist[$k]['pgoods_image_old']);
            unset($glist[$k]['ex_state']);
        }
        output_data(array('glist' => $glist), mobile_page($page_count));
    }
    /***
     * 积分兑换礼品详情
     */
    public function gift_detailOp(){
        //$_POST['pgoods_id'] = 3;
        $model_gp = Model('pointprod');
        $condition = array();
        $condition['pgoods_id'] = intval($_POST['pgoods_id']);
        $field = 'pgoods_id,pgoods_name,pgoods_price,pgoods_points,pgoods_image,pgoods_tag,pgoods_serial,pgoods_storage,
            pgoods_show,pgoods_commend,pgoods_add_time,pgoods_keywords,pgoods_description,pgoods_body,pgoods_state,pgoods_view,
            pgoods_salenum';
        $pgoods_info= $model_gp->getPointProdInfo($condition,$field);
        if(empty($pgoods_info['pgoods_id'])){
            output_error('兑换商品信息不存在');
        }
        unset($pgoods_info['pgoods_image_old']);
        unset($pgoods_info['ex_state']);
        $pgoods_info['pgoods_add_time']=date('Y-m-d H:i:s' , $pgoods_info['pgoods_add_time']);
        output_data(array('pgoods_info'=>$pgoods_info));
    }
    /***
     * 积分兑换红包列表
     */
    public function redpacket_listOp(){
        /** @var redpacketModel $model_rpt */
        $model_rpt = Model('redpacket');
        $list = $model_rpt->getRecommendRpt(10);
        $rpt_list = array();
        foreach($list as $k=>$v){
            $rpt_list[$k]['rpacket_t_id']=intval($v['rpacket_t_id']);
            $rpt_list[$k]['rpacket_t_title'] = $v['rpacket_t_title'];
            $rpt_list[$k]['rpacket_t_desc'] = $v['rpacket_t_desc'];
            $rpt_list[$k]['rpacket_t_start_date'] = date('Y-m-d',$v['rpacket_t_start_date']);
            $rpt_list[$k]['rpacket_t_end_date'] = date('Y-m-d',$v['rpacket_t_end_date']);
            $rpt_list[$k]['rpacket_t_price'] = $v['rpacket_t_price'];
            $rpt_list[$k]['rpacket_t_limit'] = $v['rpacket_t_limit'];
            $rpt_list[$k]['rpacket_t_points'] = $v['rpacket_t_points'];
            $rpt_list[$k]['rpacket_t_customimg_url'] = $v['rpacket_t_customimg_url'];
            $rpt_list[$k]['rpacket_t_mgradelimittext'] = $v['rpacket_t_mgradelimittext'];
        }
        output_data(array('rpt_list'=>$rpt_list));
    }
    
    /**
     * 积分兑换代金券列表
     */
    public function voucher_listOp(){
        /** @var voucherModel $model_voucher */
        $model_voucher = Model('voucher');
        $list= $model_voucher->getRecommendTemplate(10);
        $voucher_list = array();
        foreach($list as $k=>$v){
            $voucher_list[$k]['voucher_t_id'] = intval($v['voucher_t_id']);
            $voucher_list[$k]['voucher_t_title'] = $v['voucher_t_title'];
            $voucher_list[$k]['voucher_t_desc'] = $v['voucher_t_desc'];
            $voucher_list[$k]['voucher_t_storename'] = $v['voucher_t_storename'];
            $voucher_list[$k]['voucher_t_price'] = $v['voucher_t_price'];
            $voucher_list[$k]['voucher_t_limit'] = $v['voucher_t_limit'];
            $voucher_list[$k]['voucher_t_points'] = $v['voucher_t_points'];
            $voucher_list[$k]['voucher_t_start_date'] = date('Y-m-d',$v['voucher_t_start_date']);
            $voucher_list[$k]['voucher_t_end_date'] = date('Y-m-d',$v['voucher_t_end_date']);
            $voucher_list[$k]['voucher_t_customimg'] = $v['voucher_t_customimg'];
        }
        output_data(array('voucher_list'=>$voucher_list));
        
        
    }
        
    
}