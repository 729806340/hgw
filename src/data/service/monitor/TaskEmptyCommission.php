<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 14:05
 */
require_once('Task.php');

class TaskEmptyCommission extends Task
{
    public function getHandler()
    {
        return 'SELECT rec_id,a.order_id,b.order_sn as order_sn,goods_id,goods_name,goods_price,goods_num,goods_pay_price,gc_id 
FROM `shopwwi_order_goods` as a
RIGHT JOIN `shopwwi_orders` as b
on a.order_id = b.order_id
WHERE b.manage_type = \'platform\'
AND commis_rate=0;';
    }

    public function getId()
    {
        return 'emptyCommission';
    }

    public function getName()
    {
        return '佣金为0的平台商家订单商品列表';
    }

    public function getTitle()
    {
        return date('Y-m-d').'佣金为0的平台商家订单商品提醒';
    }

    public function getDescription($count=0)
    {
        return '系统监测到 <b>'.$count.'</b> 条订单商品为平台商家订单商品，但是商品佣金为0，请立即登录后台补充完整相关数据；';
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
        $res = "<br />行ID\t|\t订单ID\t|\t订单编号\t\t|\t分类ID\t|\t商品名称";
        foreach ($this->_data as $item)
        {
            $res .= "<br />{$item['rec_id']}\t|\t{$item['order_id']}\t|\t{$item['order_sn']}\t|\t{$item['gc_id']}\t|\t{$item['goods_name']} \t ";
        }
        return $res;
    }


}