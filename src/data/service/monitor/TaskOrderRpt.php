<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 14:05
 */
require_once('Task.php');

class TaskOrderRpt extends Task
{
    public function getHandler()
    {
        $threshold = TIMESTAMP - 7*24*3600;
        return 'SELECT a.order_id,order_sn,a.rpt_bill,b.rpt_bill as goods_rpt_bill,a.rpt_amount,b.rpt_amount as goods_rpt_amount
FROM `shopwwi_orders` as a
RIGHT JOIN (SELECT order_id,SUM(rpt_bill) as rpt_bill,SUM(rpt_amount) as rpt_amount FROM `shopwwi_order_goods` GROUP BY `order_id`) as b
on a.order_id = b.order_id
WHERE a.rpt_bill != b.rpt_bill OR a.rpt_amount != b.rpt_amount;';
    }

    public function getId()
    {
        return 'orderRpt';
    }

    public function getName()
    {
        return '红包不一致订单列表';
    }

    public function getTitle()
    {
        return date('Y-m-d').'红包不一致订单提醒';
    }

    public function getDescription($count=0)
    {
        return '系统监测到 <b>'.$count.'</b> 条红包不一致订单，请立即登录后台调整相关数据；';
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
        $res = "<br />订单ID\t|\t订单编号\t\t|\t订单红包\t|\t商品行红包\t|\t承担订单红包\t|\t承担商品红包";
        foreach ($this->_data as $item)
        {
            $res .= "<br />{$item['order_id']}\t|\t{$item['order_sn']}\t|\t{$item['rpt_amount']}\t|\t{$item['goods_rpt_amount']}\t|\t{$item['rpt_bill']}\t|\t{$item['goods_rpt_bill']}";
        }
        return $res;
    }


}