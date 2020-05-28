<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Class cpsControl
 * CPS接口控制器
 * cps接口着陆页统一为/shop/index.php?act=cps其余参数不变
 * cps查询接口统一为/shop/index.php?act=cps&op=orders
 * 增加联盟识别参数与着陆页联盟识别参数一致，其余参数不变
 */
class cpsControl {
    public function indexOp()
    {
        /** @var CpsService $service */
        $service = Service('Cps');
        $redirect = $service->accept();
        //@header('Location: '.$redirect);
        redirect($redirect);
        //var_dump($_COOKIE);
    }

    public function ordersOp()
    {
        /** @var CpsService $service */
        $service = Service('Cps');
        exit($service->query());
    }

    public function cartOp()
    {
        $_SESSION['member_id'] = 202204;
        require_once 'cart.php';
        $cart = new cartControl();
        $cart->indexOp();
    }
    public function buyOp()
    {
        require_once 'control.php';

        /**
        vat_hash:
         */
        $_POST = array(
            'jjg'=>array('%jjgId%|%jjgLevel%|%id%'),
            'cart_id'=>array('100012|1','100076|1'),
            'goods_id'=>array('100012|1','100076|1'),
            'pay_message'=>array(),
            'ifcart'=>'0',
            'pay_name'=>'online',
            'vat_hash'=>'5q5jxPVcjba9J2apF-KDLGsBlxR6uBrr6NX',
            'address_id'=>'24',
            'buy_city_id'=>'258',
            'chain'=>array(
                'id'=>'',
                'buyer_name'=>'',
                'tel_phone'=>'',
                'mob_phone'=>'',
            ),
            'allow_offpay'=>'0',
            'allow_offpay_batch'=>'1:1',
            'offpay_hash'=>'0NIcAKn3owYerw91RM7yVXAdQFJNNvn6Lvc0cQt',
            'offpay_hash_batch'=>'ciuWoyNMEvWERsiK33hgbjFXT4TK702945zrKW1OvTff0ef',
            'invoice_id'=>'',
            'ref_url'=>'',
        );
        $_SESSION['member_id'] = 202204;
        $_SESSION['member_name'] = 'shenlei';
        $_SESSION['member_email'] = 'shen@shenl.com';
        /** @var buyLogic $logic_buy */
        $logic_buy = Logic('buy');
        $result = $logic_buy->buyStep2($_POST, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);
        var_dump($result);
    }


    //根据订单号，重新推送数据到cps渠道
    public function pushOp()
    {
        $oids = explode(',',$_GET['oid']);
        $items = Model('cps')->where(array('order_sn' => array('in', $oids)))->select();

        empty($items[0]['types']) && exit('invalid oid');
        foreach ($items as $item) {
            $union = Service('Cps')->getUnion($item['types']);
            $res = $union->push($item['id']);
            v($res, 0);
        }
        
    }
}
