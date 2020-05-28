<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once('WorkflowHandler.php');

class OrderBillEdit extends WorkflowHandler
{

    public function getId()
    {
        return 12;
    }

    public function getConfig()
    {
        $startApprove = function ($model){
            //v($model);
            return '总经理';
        };
        return array(
            'name' => '未出账订单的成本、佣金变更审批流程',
            'description' => '',
            'model' => 'order_goods',
            'primary_key' => 'rec_id',
            'action' => 'action',
            'attributes' => array(
                array('name' => 'goods_pay_price', 'type' => 'text', 'label' => '销售价', 'on' => array('集采业务组'), 'notice' => '用户实际支付的总金额'),
                array('name' => 'goods_cost', 'type' => 'text', 'label' => '成本', 'on' => array('集采业务组'), 'notice' => '平台商品无需填写成本'),
                array('name' => 'commis_rate', 'type' => 'text', 'label' => '佣金比例', 'on' => array('集采业务组'), 'notice' => '共建商品无需填写佣金比例'),
                array('name' => 'rpt_bill', 'type' => 'text', 'label' => '红包金额', 'on' => array('集采业务组'), 'notice' => '由汉购网承担的红包金额'),
                array('name' => 'sign_ceo', 'type' => 'file', 'label' => '总裁签字', 'on' => array('集采业务组'), 'attachment' => true, 'notice' => '毛利小于5%需要上传总裁签字'),
                array('name' => 'sign_president', 'type' => 'file', 'label' => '董事长签字', 'on' => array('集采业务组'), 'attachment' => true, 'notice' => '负毛利需要上传董事长签字')
            ),
            /**
             * attributes 参数说明
             * name ：input名称/数据表字段名
             * type：input类型
             * label：input显示名称
             * on：显示条件
             * attachment：是否附件
             */
            'reference' => '/admin/modules/shop/index.php?act=order&op=show_order&rec_id={id}',
            'start' => array('集采业务组'),// 启动用户组
            'flow' => array(
                '集采业务组' => array(
                    'approve' => $startApprove,
                ),
                '公司商务' => array(
                    'timeout' => 3600,
                    'approve' =>'总经理',
                    'reject' => '',
                ),

                '总经理' => array(
                    'timeout' => 3600,
                    'approve' => function($model){
                        /** @var orderModel $orderModel */
                        $orderModel = Model('order');
                        $orderGoods = $orderModel->getOrderGoodsInfo(array('rec_id'=>$model['model_id']));
                        $newValue = is_array($model['new_value']) ? $model['new_value'] : json_decode($model['new_value'], true);
                        $data = array(
                            'goods_pay_price'=>'',
                            'goods_cost'=>'',
                            'commis_rate'=>'',
                            'rpt_bill'=>'',
                        );
                        foreach ($data as $k=>$v) {
                            if (isset($newValue[$k])) $data[$k] = $newValue[$k];
                            else unset($data[$k]);
                        }
                        $orderModel->table('order_goods')
                            ->where(array('rec_id' => $model['model_id']))
                            ->update($data);
                        // 更新订单
                        $orderModel->execute('
UPDATE shopwwi_orders as a INNER JOIN 
(
    SELECT SUM(goods_cost) as cost_amount, SUM(goods_pay_price) as goods_amount, order_id
    FROM shopwwi_order_goods
    WHERE order_id = "'.$orderGoods['order_id'].'"
    GROUP BY order_id
) as b
on a.order_id = b.order_id
SET a.cost_amount = b.cost_amount,
a.goods_amount = b.goods_amount,
a.order_amount = a.goods_amount+a.shipping_fee
WHERE a.order_id = "'.$orderGoods['order_id'].'";');
                        return 'closed';
                    },
                    'reject' => '',
                ),
            ),
        );
    }

    public function approve($post, $service)
    {
        if (in_array($service->getGroup(),(array)$this->getStartGroup())) {
            /** @var storeModel $storeModel */
            $storeModel = Model('store');
            /** @var orderModel $orderModel */
            $orderModel = Model('order');
            $model = $service->getModel();
            $newValue = $model['new_value'];
            $goods_new_cost = ncPriceFormat(floatval($post['goods_cost']));
            $goods_info = $orderModel->getOrderGoodsInfo(array('rec_id' => $model['model_id']));
            $storeInfo = $storeModel->getStoreInfo(array('store_id' => $goods_info['store_id']));
            if ($storeInfo['manage_type'] == 'co_construct') {
                if($goods_new_cost<=0) throw new Exception('共建商品成本必须大于0');
            }else{
                $orderInfo = $orderModel->getOrderInfo(array('order_id'=>$goods_info['order_id']),array('extend_order_goods'));
                if(!in_array($orderInfo['order_state'],array(20,30,40))||$orderInfo['send_sap'] != 0) throw new Exception('当前订单状态不允许修改该数据');
                $item_shipping_fee = $orderInfo['shipping_fee']/count($orderInfo['extend_order_goods']);
                $comprice = ncPriceFormat(($post['goods_pay_price']+$post['rpt_bill']) * $post['commis_rate'] / 100);
                $goods_new_cost =  ncPriceFormat($post['goods_pay_price'] + $item_shipping_fee - $comprice + $post['rpt_bill']);
            }
            $goods_price = ncPriceFormat($goods_info['goods_pay_price']);
            if ((($goods_price - $goods_new_cost) < $goods_new_cost * 0.05) && empty($post['sign_ceo'])) {
                throw new Exception( '请上传总裁签字！');
            }
            if (($goods_price < $goods_new_cost) && empty($post['sign_president'])) {
                throw new Exception( '请上传董事长签字！');
            }
        }
        return parent::approve($post, $service);
    }
}