<?php
/**
 * 平台充值卡
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class rechargecardModel extends Model
{
    public function __construct()
    {
        parent::__construct('rechargecard');
    }

    /**
     * 获取充值卡列表
     *
     * @param array $condition 条件数组
     * @param int $pageSize 分页长度
     *
     * @return array 充值卡列表
     */
    public function getRechargeCardList($condition, $pageSize = 20, $limit = null, $sort = 'id desc')
    {
        if ($condition) {
            $this->where($condition);
        }

        if ($sort) {
            $this->order($sort);
        }

        if ($limit) {
            $this->limit($limit);
        } else {
            $this->page($pageSize);
        }

        return $this->table('rechargecard')->select();
    }

    public function isReceiverExist($sn){
        return $this->table('receiver')->where('sn = '.$sn)->find();
    }

    public function addReceiver($data){
        return $this->table('receiver')->insert($data);
    }

    public function updateReceiver($sn,$data){
        return $this->table('receiver')->where('sn = '.$sn)->update($data);
    }


    public function getReceiverList(){
        return $this->table('receiver')->where('status = 1')->select();
    }

    public function getAllReceiverList(){
        return $this->table('receiver')->select();
    }

    //设置领卡人状态可用
    public function setReceiverListStatus($receiver_sn_array){

        $data0 = array();
        $data0['status'] = 0;
        $res1 = Db::update('receiver',$data0);

        $condition = array();
        $condition['sn'] = array('in',$receiver_sn_array);

        $data1 = array();
        $data1['status'] = 1;
        $res2 = $this->table('receiver')->where($condition)->update($data1);
        return $res2;
    }

    public function getRechargeCardSapList(){
        $receiver_list =  $this->getAllReceiverList();
        $receiver_list_sn = array_column($receiver_list,'sn');

        $condition = array();
        $condition['recharge_sap'] = 1;
        $condition['recharge_status'] = 1;
        $condition['receiver'] = array('in',$receiver_list_sn);
        $recharge_list = $this->table('rechargecard')->where($condition)->field('sn,recharge_time,receiver,denomination')->select();

        $data_out = array();
        foreach($recharge_list as $v){
            $item['type'] = 'recharge';
            $item['cardSn'] = $v['sn'];
            $item['money'] = $v['denomination'];
            $item['date'] = date('Y-m-d', $v['recharge_time']);
            $item['accountCode'] = $v['receiver'];
            $data_out[] = $item;
        }


        $condition = array();
        $condition['active_sap'] = 0;
        $condition['disabled'] = 1;
        $condition['receiver'] = array('in',$receiver_list_sn);
        $active_list = $this->table('rechargecard')->where($condition)->field('sn,actived,receiver,denomination')->limit(100)->order('id desc')->select();

        foreach($active_list as $v){
            $item['type'] = 'activate';
            $item['cardSn'] = $v['sn'];
            $item['money'] = $v['denomination'];
            $item['date'] = date('Y-m-d', $v['actived']);
            $item['accountCode'] = $v['receiver'];
            $data_out[] = $item;
        }

        $condition = array();
        $condition['chargecard_sap'] = 1;
        $consume_list = $this->table('orders')->where($condition)->field('order_sn,payment_time,rcb_amount')->select();
        foreach($consume_list as $v){
            $item['type'] = 'cost';
            $item['cardSn'] = $v['order_sn'];
            $item['money'] = $v['rcb_amount'];
            $item['date'] = date('Y-m-d', $v['payment_time']);
            $item['accountCode'] = 0;
            $data_out[] = $item;
        }
        return $data_out;
    }

    //设置充值卡消费未同步
    public function setRechargecardSap($order_sn,$chargecard_sap){
        $condition = array();
        $condition['order_sn'] = $order_sn;

        $data = array();
        $data['chargecard_sap'] = $chargecard_sap;
        return $this->table('orders')->where($condition)->update($data);
    }


    public function setCards($activate_cards, $recharge_cards, $orders){
        if(!empty($activate_cards)){
            $condition = array();
            $condition['sn'] = array('in',$activate_cards);
            $this->table('rechargecard')->where($condition)->update(array('active_sap' => 1));
        }

        if(!empty($recharge_cards)){
            $condition = array();
            $condition['sn'] = array('in',$recharge_cards);
            $this->table('rechargecard')->where($condition)->update(array('recharge_sap' => 2));
        }

        if(!empty($orders)){
            $condition = array();
            $condition['order_sn'] = array('in',$orders);
            $this->table('orders')->where($condition)->update(array('chargecard_sap' => 2));
        }
    }



    /**
     * 通过卡号获取单条充值卡数据
     *
     * @param string $sn 卡号
     *
     * @return array|null 充值卡数据
     */
    public function getRechargeCardBySN($sn)
    {
        return $this->table('rechargecard')->where(array(
            'sn' => (string) $sn,
        ))->find();
    }

    /**
     * 设置充值卡为已使用
     *
     * @param int $id 表字增ID
     * @param int $memberId 会员ID
     * @param string $memberName 会员名称
     *
     * @return boolean
     */
    public function setRechargeCardUsedById($id, $memberId, $memberName)
    {
        return $this->table('rechargecard')->where(array(
            'id' => (string) $id,
        ))->update(array(
            'tsused' => time(),
            'state' => 1,
            'member_id' => $memberId,
            'member_name' => $memberName,
        ));
    }

    /**
     * 通过ID删除充值卡（自动添加未使用标记）
     *
     * @param int|array $id 表字增ID(s)
     *
     * @return boolean
     */
    public function delRechargeCardById($id)
    {
        return $this->table('rechargecard')->where(array(
            'id' => array('in', (array) $id),
            'state' => 0,
        ))->delete();
    }

    /**
     * 通过给定的卡号数组过滤出来不能被新插入的卡号（卡号存在的）
     *
     * @param array $sns 卡号数组
     *
     * @return array
     */
    public function getOccupiedRechargeCardSNsBySNs(array $sns)
    {
        $array = $this->table('rechargecard')->field('sn')->where(array(
            'sn' => array('in', $sns),
        ))->select();

        $data = array();

        foreach ((array) $array as $v) {
            $data[] = $v['sn'];
        }

        return $data;
    }

    public function getRechargeCardCount($condition) {
        return $this->table('rechargecard')->where($condition)->count();
    }

    /***
     * 激活卡
     * @param array $card
     * @param unknown $pwd
     * @param unknown $memo
     */
    public function activeCard(array $card,$pwd,$is_flag,$memo,$receiver='',$disabled){
        if(!is_array($card) || empty($pwd) || strlen($pwd) < 4 || strlen($memo) > 150){
            return false;
        }
        
        $isUsed = $card['state'] == 1 && $card['member_id'] > 0 && $card['tsused'] > 0;
        $isActive = $card['disabled'] == 1 && $card['actived'] > 0;
        
        if($isUsed || $isActive){
            return false;
        }
        
        $condition = array();
        $update_arr = array();
        $condition['id'] = intval($card['id']);
        $update_arr['pwd'] = !empty($pwd)?$pwd:'8888';
        $update_arr['isflag'] = intval($is_flag) >0 ?intval($is_flag):1;
        $update_arr['memo'] = !empty($memo)?preg_replace('/\s/', '', $memo):'';
        $update_arr['receiver'] = $receiver;
        if($disabled==1){
            $update_arr['actived'] = TIMESTAMP;
        }
        $update_arr['disabled'] = intval($disabled);

        $res = $this->table('rechargecard')->where($condition)->update($update_arr);
        if(!$res){
            throw new Exception('平台充值卡更新失败');
        }
        return true;

    }

    public function consumeCardLog($order_sn,$money = 0){
        return $this->setRechargecardSap($order_sn,1);
    }

    public function chargeCardLog($sn){
        $res_info = $this->table('rechargecard')->where(array('sn' => $sn))->find();
        if(!$res_info) throw new Exception("激活记录不存在");

        $data = array();
        $data['recharge_time'] = TIMESTAMP;
        $data['recharge_status'] = 1;
        $data['recharge_sap'] = 1;

        $res = $this->table('rechargecard')->where(array('sn' => $sn))->update($data);
        if(!$res) throw new Exception("充值状态更新失败");
        return true;
    }




}
