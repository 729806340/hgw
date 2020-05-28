<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-14
 * Time: 10:41
 */
/**
 * 分销系统错误日志
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class b2c_order_fenxiao_errorModel extends Model
{
    public function __construct()
    {
        parent::__construct('b2c_order_fenxiao_error');
    }

    //保存错误日志
    public function addLog($orderno, $error, $member_fenxiao_s, $log_type = "order")
    {
        $data['orderno'] = $orderno;
        $data['error'] = $error;
        $data['order_time'] = 0;
        $data['log_time'] = time();
        $data['sourceid'] = $member_fenxiao_s['member_id'];
        $data['source'] = $member_fenxiao_s['member_en_code'];
        $data['log_type'] = $log_type;
        $this->table('b2c_order_fenxiao_error')->insert($data);
    }

    /**
     * 获取订单错误数据
     * @return mixed
     */
    public function getOrderErrorData()
    {
        $field = 'distinct(orderno),error,sourceid,log_time';
        $order = 'log_time desc';
        $beg = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
        $condition = array('log_type' => 'order', 'log_time' => array('between', "$beg" . ',' . "$end"));
        return $this->table('b2c_order_fenxiao_error')->field($field)->where($condition)->order($order)->select();

    }

    /**
     * @param $conditions
     * @param int $page
     * @param string $limit
     * @return mixed
     */
    public function getLogList($conditions, $page = 10, $limit = '')
    {
        return $this->where($conditions)
            ->field('*,FROM_UNIXTIME(log_time,"%Y-%m-%d %H:%i:%S") as logtime')
            ->order('id desc')->limit($limit)->page($page)->select();
    }
}