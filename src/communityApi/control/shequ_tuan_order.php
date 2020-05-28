<?php
/**
 * 社区团购订单详情
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_orderControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
        $exclude_port = array('new_upload_pic');
        if (!in_array($_GET['op'], $exclude_port)) {
            $this->checkLogin();
        }
    }

    protected function checkLogin()
    {
        $access = MD5($_REQUEST['member_id'] . "654123");
        if ($access != $_REQUEST['access_token']) {
            output_error('access_token错误');
        };
    }

    public function indexOp()
    {
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $condition = $this->_get_condition();

        $field = "order_id,tuan_id,order_type,order_sn,chain_code,pay_sn,store_id,store_name,goods_amount,order_amount,rcb_amount,pd_amount,shipping_fee,add_time,
            payment_code,payment_time,finnshed_time,lock_state,refund_state,order_state,evaluation_state,shipping_code,chain_code,shequ_return_amount,refund_amount";
        $order_list_array = $model_order->getOrderList($condition, $this->page, $field, 'order_id desc', '', array('order_common', 'order_goods'));
        /** @var refund_returnModel $model_refund_return */
        $model_refund_return = Model('refund_return');
        $order_list_array = $model_refund_return->getGoodsRefundList($order_list_array, 1);//订单商品的退款退货显示
        $need_goods_fields = array('rec_id', 'goods_id', 'goods_name', 'goods_price', 'goods_spec', 'goods_num', 'goods_image', 'refund', 'goods_type', 'xianshi_num');
        $res = array();
        foreach ($order_list_array as $value) {

            $value['if_pay'] = $value['order_state'] == '10' ? '1' : '0';
            $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $value);
            //显示收货
            $value['if_receive'] = $model_order->getOrderOperateState('receive', $value);
            //显示锁定中
            $value['if_lock'] = $model_order->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $model_order->getOrderOperateState('deliver', $value);
            $value['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $value);
            $value['if_evaluation_again'] = $model_order->getOrderOperateState('evaluation_again', $value);
            $value['if_refund_cancel'] = $model_order->getOrderOperateState('refund_cancel', $value);
            if ($value['if_refund_cancel'] && (TIMESTAMP - $value['finnshed_time']) > C('shequ_refund_time')) {
                $value['if_refund_cancel'] = false;
            }
            $value['if_chain_receive'] = $model_order->getOrderOperateState('chain_receive', $value);
            $value['if_chain_receive'] = false;
            //$value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示删除订单(放入回收站)
            $value['if_delete'] = $model_order->getOrderOperateState('delete', $value);
            //显示永久删除
            $value['if_drop'] = $model_order->getOrderOperateState('drop', $value);
            //显示团购分享
            $value['if_pin_share'] = $model_order->getOrderOperateState('pin_share', $value);
            //$value['pin_share_member_name'] = $this->member_info['member_name'];
            $value['goods_count'] = 0;

            if($value['lock_state'] > 0 ) {
                $value['state_desc'] = '退款中';
            } else if ($value['refund_state'] > 0 ) {
                $value['state_desc'] = '退款完成' ;
            }

            if ($value['order_state'] == ORDER_STATE_PAY && $value['chain_code'] > 0) {
                $value['state_desc'] = '待自提';
            }

            if ($value['order_state'] == ORDER_STATE_CANCEL && $value['shequ_tuan_id'] > 0) {
                $value['state_desc'] = '拼团失败';
            }

            //商品图
            foreach ($value['extend_order_goods'] as $k => $goods_info) {
                foreach ($goods_info as $goods_param => $goods_value) {
                    if (!in_array($goods_param, $need_goods_fields)) {
                        unset($goods_info[$goods_param]);
                    }
                }

                $goods_info['is_zengpin'] = 0;
                $goods_info['is_miaosao'] = 0;
                $goods_info['is_pin'] = 0;

                //empty($value['extend_order_goods'][$k]['refund']) and $value['extend_order_goods'][$k]['refund']=0;
                if ($goods_info['goods_type'] == 5) {
                    $goods_info['is_zengpin'] = 1;
                } elseif ($goods_info['goods_type'] == 3) {
                    $goods_info['is_miaosao'] = 1;
                    $goods_info['xianshi_num'] = $goods_info['xianshi_num'] > 0 ? $goods_info['xianshi_num'] : $goods_info['goods_num'];
                } elseif ($goods_info['goods_type'] == 10) {
                    $goods_info['is_pin'] = 1;
                }
                $value['extend_order_goods'][$k] = $goods_info;
                $value['extend_order_goods'][$k]['goods_image'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
                $value['goods_count'] += $goods_info['goods_num'];
            }
            $value['shipping_code'] = trim($value['shipping_code']);
            $value['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $value['shipping_type'] = '买家自提';
            unset($value['refund_list']);
            $value['extend_order_shipping'] = array(
                'reciver_name' => $value['extend_order_common']['reciver_name'],
                'reciver_address' => $value['extend_order_common']['reciver_info']['address'],
                'reciver_phone' => $value['extend_order_common']['reciver_info']['mob_phone'],
            );
            unset($value['extend_order_common']);
            $value['shequ_commis'] = ($value['refund_state'] == 0) ? ncPriceFormat($value['shequ_return_amount']) : 0;
            $res[] = $value;
        }
        $page_count = $model_order->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $res = array();
        output_data(array('order_list' => $res), mobile_page($page_count));
    }

    private function _get_condition() {
        $tuan_id = intval($_REQUEST['tuan_id']);
        $condition = array();
        $condition['delete_state'] = 0;
        $condition['order_state'] = array('egt', ORDER_STATE_PAY);
        $condition['shequ_tuan_id'] = $tuan_id;
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        if(!$tuan_id||intval($tuan_id)<0){
            output_error('参数错误');
        }
        $member_id = $_REQUEST['member_id'];
        $tuan_info = $shequ_tuan_model->getOne(array('id' => $tuan_id, 'member_id' => $member_id));
        if (empty($tuan_info)) {
            output_error('参数错误');
        }
        $condition['shequ_tuan_id'] = $tuan_id;
        $order_state = intval($_POST['order_state']);
        if ($order_state) {
            if ($order_state == 41) {
                $condition['refund_state'] = array('in', array(1, 2));
            }
            $condition['order_state'] = $order_state;
        }
        $order_sn = trim($_POST['order_sn']);
        if ($order_sn) {
            $condition['order_sn'] =  array('like', '%' . $order_sn . '%');
        }
        $chain_code = trim($_POST['chain_code']);
        if ($chain_code) {
            $condition['chain_code'] =  array('like', '%' . $chain_code . '%');
        }
        $link_name = trim($_POST['link_name']);
        $link_phone = trim($_POST['link_phone']);

        if ($link_phone) {
            $condition['buyer_phone'] = $link_phone;
        } elseif ($link_name) {
            $link_condition = $condition;
            $link_condition['reciver_name'] = $link_name;
            $order_list = Model()->table('orders,order_common')->join('inner')->on('orders.order_id=order_common.order_id')->where($link_condition)->field('orders.order_id')->select();
            if (empty($order_list)) {
                $condition['order_id'] = -1;
            } else {
                $condition['order_id'] = array('in', array_column($order_list, 'order_id'));
            }
        }

        return $condition;
    }

    public function exprot_csvOp() {
        $condition = $this->_get_condition();
        /** @var orderModel $model_order */
        $model_order = Model('order');
        $order_list_array = $model_order->getOrderList($condition, '', '*', 'order_id,order_amount', '', array('order_goods','order_common','member'));
        $this->createExcel($order_list_array);
    }

    /**
     * 生成excel  常规导出
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        $all_data = array();
        $i = 1;
        foreach ($data as $k => $val) {
            $all_data[$val['order_id']] = array();
            foreach ($val['extend_order_goods'] as $v) {
                $tmp = array(
                    $i,
                    $val['extend_member']['wx_nick_name'],
                    $v['goods_name'],
                    $v['goods_num'],
                    $v['goods_price'],
                    $val['order_amount'],
                    $val['extend_order_common']['reciver_name'],
                    $val['extend_order_common']['reciver_info']['phone']
                );
                $all_data[$val['order_id']][] = $tmp;
            }
            $i++;
        }
        $all_data = array_values($all_data);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $objPHPExcel = new \PHPExcel();
        $obj = $objPHPExcel->setActiveSheetIndex(0);
        $obj->setCellValue('A1','接龙id');
        $obj->setCellValue('B1','微信名称');
        $obj->setCellValue('C1','商品名称');
        $obj->setCellValue('D1','数量');
        $obj->setCellValue('E1','商品金额');
        $obj->setCellValue('F1','订单总金额');
        $obj->setCellValue('G1','收货人');
        $obj->setCellValue('H1','联系电话');
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
        $row = 2;
        foreach ($all_data as $k => $v) {
            $start_row = $row;
            $end_row = $row;
            $A_value = '';
            $B_value = '';
            $F_value = '';
            $G_value = '';
            $H_value = '';
            foreach ($v as $vv) {
                $nn = 0;
                foreach ($vv as $vvv) {
                    $col = chr(65 + $nn); //列
                    if ($nn == 0) $A_value = $vvv;
                    if ($nn == 1) $B_value = $vvv;
                    if ($nn == 5) $F_value = $vvv;
                    if ($nn == 6) $G_value = $vvv;
                    if ($nn == 7) $H_value = $vvv;
                    $obj->setCellValue($col . $row, $vvv); //列,行,值
                    $objPHPExcel->getActiveSheet()->getStyle($col . $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
                    $nn++;
                }
                $end_row ++;
                $row ++;
            }
            $end_row = $end_row - 1;
            $obj->mergeCells( 'A'.$start_row. ':A'. $end_row);
            $obj->setCellValue('A' . $start_row, $A_value);
            $objPHPExcel->getActiveSheet()->getStyle('A'. $start_row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
            $obj->mergeCells( 'B'.$start_row. ':B'. $end_row);
            $obj->setCellValue('B' . $start_row, $B_value);
            $objPHPExcel->getActiveSheet()->getStyle('B'. $start_row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
            $obj->mergeCells( 'F'.$start_row. ':F'. $end_row);
            $obj->setCellValue('F' . $start_row, $F_value);
            $objPHPExcel->getActiveSheet()->getStyle('F'. $start_row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
            $obj->mergeCells( 'G'.$start_row. ':G'. $end_row);
            $obj->setCellValue('G' . $start_row, $G_value);
            $objPHPExcel->getActiveSheet()->getStyle('G'. $start_row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
            $obj->mergeCells( 'H'.$start_row. ':H'. $end_row);
            $obj->setCellValue('H' . $start_row, $H_value);
            $objPHPExcel->getActiveSheet()->getStyle('H'. $start_row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
        }

        $objPHPExcel->getActiveSheet()->setTitle('sheet1'); //题目
        $objPHPExcel->setActiveSheetIndex(0); //设置当前的sheet
        header('Content-Type: application/vnd.ms-excel');
        $fileName = '订单列表';
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"'); //文件名称
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); //Excel5 Excel2007
        $objWriter->save('php://output');
        exit;
    }





}
