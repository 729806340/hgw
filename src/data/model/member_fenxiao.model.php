<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-05-24
 * Time: 16:43
 */
defined('ByShopWWI') or exit('Access Invalid!');
class member_fenxiaoModel extends Model{
    public static $refund_way = array(
        'predeposit' => '预存款',
        'alipay' => '支付宝',
        'offline' => '线下',
        'yeepay' => '易宝',
        'fenxiao' => '分销',
        'offline_wxpay' => '微信线下退款',
        'offline_alipay' => '支付宝线下退款',
    );

    public function __construct(){
        parent::__construct('member_fenxiao');
    }

    public function addMemberFenxiao($insert){
        $res = $this->table('member_fenxiao')->insert($insert);
        return $res;
    }

    public function  getMembeFenxiaoList($condition=array(),$limit=''){
        return  $this->where($condition)->limit($limit)->select();
    }

    public function getMembeFenxiaoList2($condition = array(), $field = '*', $page = null, $order = 'member_id desc', $limit = '') {
        return $this->table('member_fenxiao')->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    public function  getMembeFenxiaoInfo($condition=array()){
        return  $this->where($condition)->find();
    }

    public function getMemberFenxiaoCount($condition=array()){
        return   $this->where($condition)->limit()->count();
    }

    public function getMemberFenxiao($force = false){
        $member_fenxiao_list = rkcache('member_fenxiao');
        if(empty($member_fenxiao_list)||$force){
            $this->writeCache();
        }
        return $member_fenxiao_list;
    }

    public function getMemberIdByCode($en_code){
        $res = $this->table('member_fenxiao')->field('member_id')->where(array('member_en_code' => $en_code))->find();
        return $res['member_id'];
    }


    private function writeCache(){
        $member_fenxiao_list = $this->table('member_fenxiao')->order('sort desc')->limit(false)->master(true)->select();
        wkcache('member_fenxiao' , $member_fenxiao_list);
    }

    public function updates($condition,$data){
        return $this->where($condition)->update($data);
    }

    public function addFenxiao($post_data, $store_id = 0){

        try {
            $this->beginTransaction();
            $model_member = Model('member');
            $insert_array = array();
            $insert_array['member_name']    = trim($post_data['member_en_code']);
            $insert_array['member_passwd']  = md5(trim($post_data['member_passwd']));
            $insert_array['member_email']   = trim($post_data['member_email']);
            $insert_array['member_truename']= trim($post_data['member_truename']);
            $insert_array['member_sex']     = trim($post_data['member_sex']);
            $insert_array['member_qq']      = trim($post_data['member_qq']);
            $insert_array['member_ww']      = trim($post_data['member_ww']);
            //默认允许举报商品
            $insert_array['inform_allow']   = '1';
            $insert_array['member_type'] = 'fenxiao';
            $member_rel = $model_member->addMember($insert_array);
            if(!$member_rel){
                throw new Exception('添加分销会员失败1！');
            }

            $fenxiao_array = array();
            $fenxiao_array['member_cn_code'] = trim($post_data['member_cn_code']);
            $fenxiao_array['member_en_code'] = trim($post_data['member_en_code']);
            $fenxiao_array['is_sign'] = trim($post_data['is_sign']);
            $fenxiao_array['billing_mode'] = trim($post_data['billing_mode']);
            $fenxiao_array['member_id'] = $member_rel;
            $fenxiao_array['filter_store_id'] = $store_id;
            $fenxiao_rel = $this->addMemberFenxiao($fenxiao_array);
            if(!$fenxiao_rel){
                throw new Exception('添加分销会员失败2！');
            }
            $this->commit();
            $this->writeCache();
            return true;
        } catch(Exception $e){
            $this->rollback();
            return false;
        }
    }
}