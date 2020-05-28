<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 14:05
 */
require_once('Task.php');

class TaskManageType extends Task
{
    public function getHandler()
    {
        $threshold = TIMESTAMP - 7*24*3600;
        return 'SELECT a.order_id,order_sn,a.manage_type,b.manage_type as store_manage_type,b.store_name,a.add_time
FROM `shopwwi_orders` as a
RIGHT JOIN `shopwwi_store` as b
on a.store_id = b.store_id
WHERE a.manage_type != b.manage_type
AND a.add_time>'.$threshold.';';
    }

    public function getId()
    {
        return 'manageType';
    }

    public function getName()
    {
        return '商家类型不正确订单列表';
    }

    public function getTitle()
    {
        return date('Y-m-d').'商家类型不正确订单提醒';
    }

    public function getDescription($count=0)
    {
        return '系统监测到 <b>'.$count.'</b> 条商家类型不正确订单，请立即登录后台调整相关数据；';
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
        $res = "<br />订单ID\t|\t订单编号\t\t|\t下单时间\t\t|\t订单商家类型\t|\t商家类型\t|\t商家名称";
        foreach ($this->_data as $item)
        {
            $addTime = date('Y-m-d H:i:s',$item['add_time']);
            $res .= "<br />{$item['order_id']}\t|\t{$item['order_sn']}\t|\t{$addTime}\t|\t{$item['manage_type']}\t|\t{$item['store_manage_type']}\t|\t{$item['store_name']} \t ";
        }
        return $res;
    }


}