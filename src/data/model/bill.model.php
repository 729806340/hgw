<?php
/**
 * 结算模型
 * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
 
//以下是定义结算单状态
//默认
define('BILL_STATE_CREATE',1);
define('BILL_STATE_HANGO',10); // 汉购网商务审核中
define('BILL_STATE_FIRE_PHONIX',11); // 公司商务审核中
define('BILL_STATE_CEO',12); // 总经理审核中
define('BILL_STATE_PAYING',13); // 财务付款中
//店铺已确认
define('BILL_STATE_STORE_COFIRM',2);
//平台已审核
define('BILL_STATE_SYSTEM_CHECK',3);
//结算完成
define('BILL_STATE_SUCCESS',4);
//部分结算
define('BILL_STATE_PART_PAY',5);

class billModel extends Model {
    //退单推送状态
    const JDY_REFUND_STATE_NONE = 0;//未推送
    const JDY_REFUND_STATE_PARSED = 1;//已拆分
    const JDY_REFUND_STATE_PUSHING = 10;//推送中/部分推送
    const JDY_REFUND_STATE_PARSE_ERROR = 20;//解析错误
    const JDY_REFUND_STATE_PUSH_ERROR = 30;//推送错误
    const JDY_REFUND_STATE_PUSHED = 100;//推送完成

    const JDY_STATE_NEW = 0;
    const JDY_STATE_PARSED = 1;//已拆分
    const JDY_STATE_PUSHING = 10;//推送中/部分推送
    const JDY_STATE_PARSE_ERROR = 20;//推送中/部分推送
    const JDY_STATE_PUSH_ERROR = 30;//推送中/部分推送
    const JDY_STATE_PUSHED = 100;//推送完成

    public function getJdyState($state){
        $state_string = [
            0=>'未推送',
            1=>'已拆分',
            10=>'部分推送',
            20=>'解析错误',
            30=>'推送错误',
            100=>'推送完成',
        ];
        return $state_string[$state];
    }
    /**
     * 取得平台月结算单
     * @param unknown $condition
     * @param unknown $fields
     * @param unknown $pagesize
     * @param unknown $order
     * @param unknown $limit
     */
    public function getOrderStatisList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('order_statis')->where($condition)->field($fields)->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 取得平台月结算单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderStatisInfo($condition = array(), $fields = '*',$order = null) {
        return $this->table('order_statis')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得店铺月结算单列表
     * @param unknown $condition
     * @param string $fields
     * @param string $pagesize
     * @param string $order
     * @param string $limit
     */
    public function getOrderBillList($condition = array(), $fields = '*', $pagesize = null, $order = '', $limit = null) {
        return $this->table('order_bill')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 取得店铺月结算单单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getOrderBillInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('order_bill')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderBillCount($condition) {
        return $this->table('order_bill')->where($condition)->count();
    }

    public function addOrderStatis($data) {
        return $this->table('order_statis')->insert($data);
    }

    public function addOrderBill($data) {
        return $this->table('order_bill')->insert($data);
    }

    public function editOrderBill($data, $condition = array()) {
        return $this->table('order_bill')->where($condition)->update($data);
    }


    //查询结算单里的订单是否已全部推送SAP
    public function checkOrderStatus($ids)
    {
        if(empty($ids)) return true ;
        $condition['ob_id'] = array('in', $ids) ;
        $list = $this->table('order_bill')->where($condition)->select();
        $data = array() ;
        $order_model = Model('order') ;
        foreach ( $list as $bill_info ) {
            $order_condition = array();
            $order_condition['order_state'] = ORDER_STATE_SUCCESS;
            $order_condition['store_id'] = $bill_info['ob_store_id'];
            $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
            $order_condition['purchase_sap'] = array('in', array('0')) ;
            //全部推送，更新order_bill表推送状态
            if( $order_model->getOrderCount($order_condition) == 0 ) {
                $ob_condition = array() ;
                $ob_condition['ob_id'] = $bill_info['ob_id'] ;
                $this -> where ( $ob_condition ) -> update( array('ob_sap_order' => '1') ) ;
                //结算订单推送结束
//                $url = 'http://www2.hangowa.com/crontab/index.php?act=sap&code=sap503&ob_id='.$ob_condition['ob_id'];
//                header("Location:" . $url);
            } else {
                $ob_condition = array() ;
                $ob_condition['ob_id'] = $bill_info['ob_id'] ;
                $this -> where ( $ob_condition ) -> update( array('ob_sap_order' => '0') ) ;
            }
        }
    }
    
    //查询结算单里的退款单是否已全部推送SAP
    public function checkRefundStatus($ids)
    {
        if(empty($ids)) return true ;
        $condition['ob_id'] = array('in', $ids) ;
        $list = $this->table('order_bill')->where($condition)->select();
    
        $data = array() ;
        $model_refund = Model('refund_return');
        foreach ( $list as $bill_info ) {
    
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['store_id'] = $bill_info['ob_store_id'];
            $refund_condition['goods_id'] = array('gt',0);
            $refund_condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
            $refund_condition['purchase_sap'] = array('in', array('0')) ;
    
            //全部推送，更新order_bill表推送状态
            if( $model_refund->getRefundReturnCount($refund_condition) == 0 ) {
                $ob_condition = array() ;
                $ob_condition['ob_id'] = $bill_info['ob_id'] ;
                $this -> where ( $ob_condition ) -> update( array('ob_sap_refund' => '1') ) ;
            } else {
                $ob_condition = array() ;
                $ob_condition['ob_id'] = $bill_info['ob_id'] ;
                $this -> where ( $ob_condition ) -> update( array('ob_sap_refund' => '0') ) ;
            }
        }
    }
    
    //查询结算单里的店铺费用是否已全部推送SAP
    public function checkStorecostStatus($ids)
    { 
    	if(empty($ids)) return true ;
    	$condition['ob_id'] = array('in', $ids) ;
    	$list = $this->table('order_bill')->where($condition)->select();
    	 
    	$data = array() ;
    	$storecost_model = Model('store_cost') ;
    	foreach ( $list as $bill_info ) {
    		$cost_condition = array();
    		$cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
	    	$cost_condition['cost_price'] = array('gt', 0) ;
	    	$cost_condition['cost_state'] = 0;
	    	$cost_condition['cost_time'] = array(array('egt',$bill_info['ob_start_date']),array('elt',$bill_info['ob_end_date']),'and');
	    	$cost_condition['fx_order_id'] = array('gt', 0) ;
	    	$cost_condition['purchase_sap'] = array('in', array('0')) ;
    
    		//全部推送，更新order_bill表推送状态
    		if( $storecost_model->where($cost_condition)->count() == 0 ) {
    			$ob_condition = array() ;
    			$ob_condition['ob_id'] = $bill_info['ob_id'] ;
    			$this -> where ( $ob_condition ) -> update( array('ob_sap_storecost' => '1') ) ;
    		} else {
    			$ob_condition = array() ;
    			$ob_condition['ob_id'] = $bill_info['ob_id'] ;
    			$this -> where ( $ob_condition ) -> update( array('ob_sap_storecost' => '0') ) ;
    		}
    	}
    }
}
