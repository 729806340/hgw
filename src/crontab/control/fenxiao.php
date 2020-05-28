<?php
/**
 * 分销系统错误订单定时发送
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined ( 'ByShopWWI' ) or exit ( 'Access Invalid!' );
class fenxiaoControl extends BaseCronControl{
    public function indexOp(){
        $data=$this->getErrorData();
        if(!empty($data)){
            $text=$this->dealData($data);
            $email = new Email ();
            $email->send_sys_email ( 'wanglijuan@hansap.com', '订单日志发送错误列表', $text );
            $email->send_sys_email ( 'qianli@hansap.com', '订单日志发送错误列表', $text );
            $email->send_sys_email ( 'lijingquan@hansap.com', '订单日志发送错误列表', $text );
            $email->send_sys_email ( 'wanjiang@hansap.com', '订单日志发送错误列表', $text );

        }
    }

    /**
     * 获取数据库订单错误数据以及上一次产生的附件名
     * @return array
     */
    private function getErrorData()
    {
        $fenxiao_error = Model('b2c_order_fenxiao_error');
        return $fenxiao_error->getOrderErrorData();
    }

    /**
     * 处理字符串
     */
    private function dealData($data){
        //日志数组转换成字符串
        $text="";
        $fenxiao_list=Model('member_fenxiao')->getMembeFenxiaoList(array(),false);
        $fenxiao_list = array_under_reset($fenxiao_list ,'member_id');
        foreach($data as $v) {
            $text .= $v['orderno'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;来自:&nbsp;".$fenxiao_list[$v['sourceid']]['member_cn_code'] ."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;错误原因:&nbsp;". $v['error'] . "<br>";
        }
        return $text;
    }
}