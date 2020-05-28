<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/10/11
 * Time: 15:04
 */


defined('ByShopWWI') or exit('Access Invalid!');

class manualControl extends BaseCronControl {

    // foreground color control codes
    const FG_BLACK  = 30;
    const FG_RED    = 31;
    const FG_GREEN  = 32;
    const FG_YELLOW = 33;
    const FG_BLUE   = 34;
    const FG_PURPLE = 35;
    const FG_CYAN   = 36;
    const FG_GREY   = 37;
    // background color control codes
    const BG_BLACK  = 40;
    const BG_RED    = 41;
    const BG_GREEN  = 42;
    const BG_YELLOW = 43;
    const BG_BLUE   = 44;
    const BG_PURPLE = 45;
    const BG_CYAN   = 46;
    const BG_GREY   = 47;
    // fonts style control codes
    const RESET       = 0;
    const NORMAL      = 0;
    const BOLD        = 1;
    const ITALIC      = 3;
    const UNDERLINE   = 4;
    const BLINK       = 5;
    const NEGATIVE    = 7;
    const CONCEALED   = 8;
    const CROSSED_OUT = 9;
    const FRAMED      = 51;
    const ENCIRCLED   = 52;
    const OVERLINED   = 53;


    /**
     * 运行方式：php index.php manual rebuildBill
     * @return null|void
     */
    public function rebuildBillOp(){
        $this->stdout("功能说明：\n本操作重建结算单周期内的基础数据，并重新计算结算单！\n请依据提示进行操作\n");
        $this->stdout('请输入结算单号：',static::FG_GREEN);
        $bill_id = intval(trim(fgets(STDIN)));
        if(empty($bill_id)) return $this->stdout("结算单号不得为空！\n",static::BG_RED);
        /** @var billModel $billModel */
        $billModel = Model('bill');
        $billInfo = $billModel->getOrderBillInfo(array('ob_id'=>$bill_id));
        if(empty($billInfo)||$billInfo['ob_sap_order']||$billInfo['ob_sap_refund']||$billInfo['ob_sap_close']) return $this->stdout("结算单不存在或者已关闭！\n",static::BG_RED);
        $this->stdout('请新商户类型(1=>共建商家；2=>平台商家；其他/留空=>不修改)：',static::FG_GREEN);
        $manageType = trim(fgets(STDIN));
        $manageType = $manageType==1?'co_construct':($manageType==2?'platform':$billInfo['ob_store_manage_type']);
        $manageTypeMessage = $manageType == 'co_construct'?'共建商家':'平台商家';
        $manageTypeMessage .= $manageType == $billInfo['ob_store_manage_type']?'(未修改)':'(已修改)';
        $bill_cycle = date('Y-m-d',$billInfo['ob_start_date']). '-' . date('Y-m-d',$billInfo['ob_end_date']);
        $message = <<<MESSAGE
即将重建以下结算单：
商家名称: {$billInfo['ob_store_name']}
商家类型: {$manageTypeMessage}
结算周期: {$bill_cycle}
结算单号: {$bill_id}\n
MESSAGE;
        $this->stdout($message,static::FG_YELLOW);
        $this->stdout("确认请输入{$bill_id}:",static::FG_GREEN);
        $confirm = trim(fgets(STDIN));
        if($confirm!=$bill_id) return $this->stdout("重建已取消！\n",static::BG_RED);
        $billModel->beginTransaction();
        if($manageType != $billInfo['ob_store_manage_type']){
            if($this->rebuildManageType($billInfo,$manageType)==false){
                $billModel->rollback();
                return $this->stdout("重建失败！\n",static::BG_RED);
            }
            $billInfo['ob_store_manage_type'] = $manageType;

        }
        $this->stdout("开始重建基础数据！\n");
        if(!$this->_rebuildBill($billInfo)){
            $billModel->rollback();
            return $this->stdout("重建失败！\n",static::BG_RED);
        }
        $billModel->commit();
        $this->stdout("基础数据重建完毕！\n开始重建账单！\n");

        /** @var BillService $bill */
        $bill = Service('Bill');
        if($bill->calcRealBill($billInfo)) $this->stdout("账单重建成功！\n",static::FG_GREEN);
        else $this->stdout("账单重建失败！\n",static::FG_GREEN);
        return null;
    }

    /**
     * 运行方式：php index.php manual rebuildTax
     * @return null|void
     */
    public function rebuildTaxOp(){
        $this->stdout("功能说明：\n本操作重建结算单周期内的税率信息！\n请依据提示进行操作\n");
        $this->stdout('请输入结算单号：',static::FG_GREEN);
        $bill_id = intval(trim(fgets(STDIN)));
        if(empty($bill_id)) return $this->stdout("结算单号不得为空！\n",static::BG_RED);
        /** @var billModel $billModel */
        $billModel = Model('bill');
        $billInfo = $billModel->getOrderBillInfo(array('ob_id'=>$bill_id));
        if(empty($billInfo)||$billInfo['ob_sap_order']||$billInfo['ob_sap_refund']||$billInfo['ob_sap_close']) return $this->stdout("结算单不存在或者已关闭！\n",static::BG_RED);
        if($billInfo['ob_store_manage_type'] !='co_construct'){
            return $this->stdout("“非共建商家”不需要重建税率数据\n",static::FG_RED);
        }
        $bill_cycle = date('Y-m-d',$billInfo['ob_start_date']). '-' . date('Y-m-d',$billInfo['ob_end_date']);
        $message = <<<MESSAGE
即将重建以下结算单对应的订单商品税率数据：
商家名称: {$billInfo['ob_store_name']}
结算周期: {$bill_cycle}
结算单号: {$bill_id}\n
MESSAGE;
        $this->stdout($message,static::FG_YELLOW);
        $this->stdout("确认请输入{$bill_id}:",static::FG_GREEN);
        $confirm = trim(fgets(STDIN));
        if($confirm!=$bill_id) return $this->stdout("重建已取消！\n",static::BG_RED);
        $billModel->beginTransaction();
        $this->stdout("开始重建税率基础数据！\n");
        if(!$this->_rebuildTax($billInfo)){
            $billModel->rollback();
            return $this->stdout("重建失败！\n",static::BG_RED);
        }
        $billModel->commit();
        $this->stdout("税率数据重建完毕！\n",static::FG_GREEN);
        return null;
    }

    /**
     * 重建店铺管理类型
     * @param $billInfo
     * @param $manageType
     * @return bool
     */
    protected function rebuildManageType($billInfo,$manageType)
    {
        // 更新结算周期内的订单商户类型
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $billInfo['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}");
        $updateOrder = $orderModel->table('orders')->where($order_condition)->update(array('manage_type'=>$manageType));
        $orderIds = $orderModel->table('orders')->where($order_condition)->getfield();
        $updateOrderGoods =$orderModel->table('order_goods')->where(array('order_id'=>array('in',$orderIds)))->update(array('manage_type'=>$manageType));
        return $updateOrder&&$updateOrderGoods;
    }

    /**
     * 重建结算单
     * @param $billInfo
     * @return bool|mixed
     */
    protected function _rebuildBill($billInfo)
    {
        $manageType = $billInfo['ob_store_manage_type'];
        if(method_exists($this,'_rebuildBill'.$manageType)){
            return call_user_func(array($this,'_rebuildBill'.$manageType),$billInfo);
        };
        return false;
    }

    /**
     * 重建共建结算单
     * @param $billInfo
     * @return bool
     */
    protected function _rebuildBillCo_construct($billInfo){
        $orderModel = Model('order');
        $model = new Model();
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $billInfo['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}");
        $orderIds = $orderModel->table('orders')->where($order_condition)->getfield();
        //$this->stdout("订单ID：".implode(',',$orderIds)."\n");

        // 查找订单商品成本，税率未填写的商品
        $errorGoods = $model->query(
            'SELECT DISTINCT  a.goods_id,a.goods_name,a.goods_cost,a.tax_input,a.tax_output
    FROM shopwwi_goods as a INNER JOIN shopwwi_order_goods as b
    on a.goods_id = b.goods_id
    WHERE b.order_id IN ('.implode(',',$orderIds).') AND a.goods_cost = "0";'
        );
        if(!empty($errorGoods)) {
            $this->stdout("下列商品成本非法\n",static::FG_GREEN);
            $this->stdout("ID\t成本\t名称\n",static::FG_YELLOW);
            foreach ($errorGoods as $goods){
                $this->stdout("{$goods['goods_id']}\t{$goods['goods_cost']}\t{$goods['goods_name']}\n",static::FG_YELLOW);
            }
            $this->stdout("以上商品成本非法，继续重建请输入YES：",static::FG_GREEN);

            $confirm = strtolower(trim(fgets(STDIN)));
            if($confirm!='yes') {
                return false;
            }
        }
        // 更新订单商品成本，税率；
        $updateOrderGoodsCost = $model->execute(
            "UPDATE shopwwi_order_goods as a INNER JOIN shopwwi_goods as b
    on a.goods_id = b.goods_id
    SET a.goods_cost = b.goods_cost * a.goods_num
    WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
        // 重建共建订单商品佣金
        $updateOrderGoodsComm = $model->execute(
            "UPDATE shopwwi_order_goods as a 
INNER JOIN shopwwi_store_bind_class as b
on a.store_id = b.store_id AND a.gc_id = b.class_3
SET a.commis_rate = b.commis_rate
WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
        // 重建共建订单成本
        $updateOrderCost = $model->execute(
            "UPDATE shopwwi_orders as a INNER JOIN 
(
SELECT SUM(goods_cost)as cost_amount, order_id
FROM shopwwi_order_goods
WHERE order_id IN (".implode(',',$orderIds).")
GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.cost_amount = b.cost_amount
WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
        //  重建共建退款成本比例
        $updateRefundRate = $model->execute(
            "UPDATE shopwwi_refund_return as a 
INNER JOIN shopwwi_order_goods as b
on a.goods_id = b.goods_id
SET a.cost_rate = b.goods_cost*100/b.goods_pay_price,
a.commis_rate = b.commis_rate
WHERE a.goods_id > 0 AND a.seller_state = 2
AND a.store_id =  {$billInfo['ob_store_id']}
AND a.admin_time BETWEEN {$billInfo['ob_start_date']} AND {$billInfo['ob_end_date']};"
        );
        return $updateOrderGoodsCost&&$updateOrderGoodsComm&&$updateOrderCost&&$updateRefundRate;
    }

    /**
     * 重建平台结算单
     * @param $billInfo
     * @return bool
     */
    protected function _rebuildBillPlatform($billInfo){
        $orderModel = Model('order');
        $model = new Model();
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $billInfo['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}");
        $orderIds = $orderModel->table('orders')->where($order_condition)->getfield();
        // 重建平台订单商品佣金
        $updateOrderGoodsComm = $model->execute(
            "UPDATE shopwwi_order_goods as a 
INNER JOIN shopwwi_store_bind_class as b
on a.store_id = b.store_id AND a.gc_id = b.class_3
SET a.commis_rate = b.commis_rate
WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
        // 更新订单商品成本，税率；
        $updateOrderGoodsCost = $model->execute(
            "UPDATE shopwwi_order_goods
    SET goods_cost = goods_pay_price - goods_pay_price * commis_rate /100,
    tax_input = 200,
    tax_output = 200
    WHERE order_id IN (".implode(',',$orderIds).");"
        );
        // 重建平台订单成本
        $updateOrderCost = $model->execute(
            "UPDATE shopwwi_orders as a INNER JOIN 
(
SELECT SUM(goods_cost)as cost_amount, order_id
FROM shopwwi_order_goods
WHERE order_id IN (".implode(',',$orderIds).")
GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.cost_amount = b.cost_amount
WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
        //  重建平台退款成本比例，佣金比例
        $updateRefundRate = $model->execute(
            "UPDATE shopwwi_refund_return as a 
INNER JOIN shopwwi_order_goods as b
on a.goods_id = b.goods_id
SET a.cost_rate = b.goods_cost*100/b.goods_pay_price,
a.commis_rate = b.commis_rate
WHERE a.goods_id > 0 AND a.seller_state = 2
AND a.store_id =  {$billInfo['ob_store_id']}
AND a.admin_time BETWEEN {$billInfo['ob_start_date']} AND {$billInfo['ob_end_date']};"
        );

        return $updateRefundRate&&$updateOrderCost&&$updateOrderGoodsCost&&$updateOrderGoodsComm;
    }

    protected function _rebuildTax($billInfo){
        $orderModel = Model('order');
        $model = new Model();
        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $billInfo['ob_store_id'];
        $order_condition['finnshed_time'] = array('between',"{$billInfo['ob_start_date']},{$billInfo['ob_end_date']}");
        $orderIds = $orderModel->table('orders')->where($order_condition)->getfield();
        //$this->stdout("订单ID：".implode(',',$orderIds)."\n");

        // 查找订单商品税率未填写的商品
        $errorGoods = $model->query(
            'SELECT DISTINCT  a.goods_id,a.goods_name,a.goods_cost,a.tax_input,a.tax_output
    FROM shopwwi_goods as a INNER JOIN shopwwi_order_goods as b
    on a.goods_id = b.goods_id
    WHERE b.order_id IN ('.implode(',',$orderIds).') AND (a.tax_input>99 OR a.tax_output>99);'
        );
        if(!empty($errorGoods)) {
            $this->stdout("下列商品税率非法\n",static::FG_GREEN);
            $this->stdout("ID\t进项税\t销项税\t名称\n",static::FG_YELLOW);
            foreach ($errorGoods as $goods){
                $this->stdout("{$goods['goods_id']}\t{$goods['tax_input']}\t{$goods['tax_output']}\t{$goods['goods_name']}\n",static::FG_YELLOW);
            }
            $this->stdout("以上商品税率非法，继续重建请输入YES：",static::FG_GREEN);

            $confirm = strtolower(trim(fgets(STDIN)));
            if($confirm!='yes') {
                return false;
            }
        }
        // 更新订单商品税率；
        return $model->execute(
            "UPDATE shopwwi_order_goods as a INNER JOIN shopwwi_goods as b
    on a.goods_id = b.goods_id
    SET a.tax_input = b.tax_input,
    a.tax_output = b.tax_output
    WHERE a.order_id IN (".implode(',',$orderIds).");"
        );
    }


    /**
     * @param $string
     */
    protected function stdout($string)
    {
        $args = func_get_args();
        array_shift($args);
        $code = implode(';', $args);
        $string =  "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $string . "\033[0m";
        fwrite(STDOUT , $string);
    }


    public function updateCrmIdOp()
    {
        $offset = 0;
        $pageSize = 1000;
        $memberFenxiao = ecModel('b2c_member_fenxiao');
        $jsUsers = ecModel('js_users');
        $count=0;
        do{
            $members = $memberFenxiao->limit($offset,$pageSize)->select();
            $this->stdout("找到".count($members).'个用户',static::FG_GREEN);

            foreach ($members as $member){
                $user = $jsUsers->where(array('member_id'=>$member['member_id'],'u_mobile'=>$member['mobile']))->find();
                if(empty($user)) {
                    $this->stdout("未找到用户\n",static::FG_RED);
                    continue;
                }
                $this->stdout("更新：{$member['mobile']}::{$user['u_uid']}\n",static::FG_GREEN);
                $memberFenxiao->where(array('member_id'=>$member['member_id'],'mobile'=>$member['mobile']))->save(array('crm_member_id'=>$user['u_uid']));
                $count++;
            }
            $offset+=$pageSize;
        }while(count($members)>=$pageSize);
        $this->stdout("累积处理{$count}个用户！\n",static::FG_RED);

    }


    public function fixFenxiaoSubOp()
    {
        // 查询4月14日起的订单，补充oid
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        $subModel = Model("b2c_order_fenxiao_sub");
        $skuModel = Model("b2c_category");
        $map = array(
            'import_time'=>array('between',strtotime('2018-04-14').','.time()),
        );
        $orderList = $orderModel->table('orders')->where($map)->limit(999999)->select();
        foreach ($orderList as $order)
        {
            if(empty($order['fx_order_id'])) continue;
            $orderGoods = $orderModel->getOrderGoodsList(array('order_id'=>$order['order_id']));
            $fxOrderId = $order['fx_order_id'];
            $fxSub = $subModel->where(array('orderno'=>$fxOrderId))->find();
            if($fxSub) continue;

            $order_sub = array();
            $order_sub['orderno'] = $order['fx_order_id'];
            $order_sub['pay_sn'] = $order['pay_sn'];
            foreach ($orderGoods as $goods){
                $sku = $skuModel->where(array(
                    'uid'=>$order['buyer_id'],
                    'pid'=>$goods['goods_id']
                ))->find();
                $order_sub['product_id'] = $goods['goods_id'];
                $order_sub['oid'] = $sku['fxpid'];
                $order_sub['num'] = $goods['goods_num'];
                $subModel->insert($order_sub);
                echo "插入数据".$order['fx_order_id']."\n";
            }
        }
    }

    /**
     * 运行方式：php index.php manual updateGoodsQr
     * @return null|void
     */
    public function updateGoodsQrOp(){
        ini_set("memory_limit","4G");
        $this->stdout("重建商品二维码\n");

        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $where=array();
        //$count=$model_goods->getGoodsCount($where);
        $lst=$model_goods->getGoodsList($where,'store_id,goods_id','','',999999);

        foreach($lst as $k=>$v)
        {
            goodsQRCode($v,true,true);
            $this->stdout("生成二维码：{$v['goods_id']}\n");

        }


    }


}