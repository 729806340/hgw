<?php
/***
 * 同步老平台用户数据
 */
defined('ByShopWWI') or exit('Access Invalid!');
class tbuserControl extends BaseHomeControl {
    public function userOp(){
       set_time_limit(0);
       echo date('Y-m-d H:i:s',time())."开始执行<br>";
       $model_olduers = ecModel('B2cMembers');
       $model_member = model('member');
       $flag = true;
       $i = 0;
       while($flag){
           $where = array();
           if($last_memberId) $where['member_id'] = array('gt', $last_memberId);
           $tm = $model_olduers->field('member_id')->where($where)->order('member_id ASC')->limit(1000)->select();
           $member_ids = array_column($tm, 'member_id');
           $last_memberId = end($member_ids);
           $user_list = $model_member->getMemberList(array('member_id'=>array('in',$member_ids)), 'member_id');
           $user_list = array_column($user_list, 'member_id');
           $no_user_list = array_diff($member_ids, $user_list);
           if(count($no_user_list)>0){
               foreach($no_user_list as $k=>$v){
                   $ec_member_info = $model_member->getOldMemberInfo(array('member_id'=>$v));
                   //var_dump($i);var_dump($ec_member_info);die();
                   if($ec_member_info){ // 若旧平台存在对应用户
                        $model_member->updateMemberByOld($ec_member_info);
                   }
                   $i++;
               }
           }
           if(count($member_ids)<1000) $flag= false;
       }
       echo $i."条记录<br>";
       echo date('Y-m-d H:i:s',time())."执行完成";
       
    }
    
    public function czkOp(){
        set_time_limit(0);
        echo date('Y-m-d H:i:s',time())."开始执行<br>";
        $cardold_model = ecModel('PamCzcard');
        $card_model = Model('rechargecard');
        $pc_list = $cardold_model->field('createtime')->group('createtime')->select();
        foreach($pc_list as $value){
            $card_list= $cardold_model->where(array('createtime'=>$value['createtime']))->order('card_id ASC')->select();
            foreach($card_list as $card){
                $data = array();
                $data['id'] = intval($card['card_id']);
                $data['sn'] = $card['bonus_sn'];
                $data['pwd'] = $card['bonus_pwd'];
                $data['denomination'] = ncPriceFormat($card['type_money']);
                $data['batchflag'] = date('Y-m-d',$value['createtime'])."领取卡";
                $data['admin_name'] = 'admin';
                $data['tscreated'] = $card['createtime'];
                $data['tsused']    = $card['settime']?$card['settime']:0;
                $data['actived']   = $card['activetime']==null?0:$card['activetime'];
                $data['state']     = !empty($card['status'])?$card['status']:0;
                $data['member_id']  = intval($card['member_id']);
                $data['member_name'] = '';
                if($card['member_id']>0){
                    $member = Model('member')->getMemberInfo(array('member_id'=>$data['member_id']) ,'member_name');
                    $data['member_name'] = $member['member_name']?$member['member_name']:'';
                }
                $data['isflag'] = 0;
                $data['disabled'] = $card['disabled']==true?1:0;
                $data['receiver'] = $card['receiver_name'];
                $data['memo']     = $card['memo'];
                $card_model->insert($data);
            }
        }
        
        echo date('Y-m-d H:i:s',time())."执行完成<br>";
       
    }
}