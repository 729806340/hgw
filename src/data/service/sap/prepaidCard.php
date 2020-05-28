<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-19
 * Time: 09:06
 */
class prepaidCard extends commons
{
    public function deal(){
        return Model('rechargecard')->getRechargeCardSapList();
    }

    public function deal_after($rel){
        $activate_cards = array();
        $recharge_cards = array();
        $orders = array();
        foreach($rel['results'] as $v){
            if($v['status'] == 0){
                switch($v['type']){
                    case 'activate':
                        $activate_cards[] = $v['tid'];
                        break;
                    case 'recharge':
                        $recharge_cards[] = $v['tid'];
                        break;
                    case 'cost':
                        $orders[] = $v['tid'];
                        break;
                }
            }
        }

        Model('rechargecard')->setCards($activate_cards,$recharge_cards,$orders);
        return true;
    }

}