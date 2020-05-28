<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 14:05
 */
require_once('Task.php');

class TaskEmptyCost extends Task
{
    public function getHandler()
    {
        return 'SELECT rec_id,a.order_id,b.order_sn as order_sn,goods_id,goods_name,gc_id,goods_cost,tax_input,tax_output 
FROM `shopwwi_order_goods` as a
RIGHT JOIN `shopwwi_orders` as b
on a.order_id = b.order_id
WHERE b.manage_type = \'co_construct\'
AND (a.tax_input NOT BETWEEN 1 AND 99
OR a.tax_output NOT BETWEEN 1 AND 99
OR a.goods_cost=0);';
    }

    public function getId()
    {
        return 'emptyCost';
    }

    public function getName()
    {
        return '未设置税率/成本的共建商家订单商品列表';
    }

    public function getTitle()
    {
        return date('Y-m-d').'未设置税率/成本的共建商家订单商品提醒';
    }

    public function getDescription($count=0)
    {
        return '系统监测到 <b>'.$count.'</b> 条订单商品为共建商家订单商品，但是未设置税率/成本，请立即登录后台调整相关数据；';
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
        $res = "<br />行ID\t|\t订单ID\t|\t订单编号\t\t|\t成本\t|\t进项税\t|\t销项税\t|\t商品名称";
        foreach ($this->_data as $item)
        {
            $res .= "<br />{$item['rec_id']}\t|\t{$item['order_id']}\t|\t{$item['order_sn']}\t|\t{$item['goods_cost']}\t|\t{$item['tax_input']}\t|\t{$item['tax_output']}\t|\t{$item['goods_name']} \t ";
        }
        return $res;
    }


}