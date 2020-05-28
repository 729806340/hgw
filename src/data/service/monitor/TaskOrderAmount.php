<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 14:05
 */
require_once('Task.php');

class TaskOrderAmount extends Task
{
    public function getHandler()
    {
        return 'SELECT a.order_id,order_sn,a.order_amount,b.goods_pay_price as goods_pay_price,a.shipping_fee 
FROM `shopwwi_orders` as a
RIGHT JOIN (SELECT order_id,SUM(goods_pay_price) as goods_pay_price FROM `shopwwi_order_goods` GROUP BY `order_id`) as b
on a.order_id = b.order_id
WHERE a.order_amount != b.goods_pay_price+a.shipping_fee;';
    }

    public function getId()
    {
        return 'orderAmount';
    }

    public function getName()
    {
        return '金额不一致订单列表';
    }

    public function getTitle()
    {
        return date('Y-m-d').'金额不一致订单提醒';
    }

    public function getDescription($count=0)
    {
        return '系统监测到 <b>'.$count.'</b> 条金额不一致订单，请立即登录后台调整相关数据；';
    }

    public function getEmailReceiver()
    {
        return array('guiyajun@hansap.com');
    }

    public function getSmsReceiver()
    {
        return array('18571593115');
    }

    public function renderData()
    {
        if(empty($this->_data)) return '';
        $res = "<br />订单ID\t|\t订单编号\t\t|\t订单金额\t|\t商品行金额\t|\t运费";
        foreach ($this->_data as $item)
        {
            $res .= "<br />{$item['order_id']}\t|\t{$item['order_sn']}\t|\t{$item['order_amount']}\t|\t{$item['goods_pay_price']}\t|\t{$item['shipping_fee']}";
        }
        return $res;
    }


}