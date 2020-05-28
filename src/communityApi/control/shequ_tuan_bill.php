<?php
/**
 * 社区团购结算单
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_billControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
        $access = MD5($_REQUEST['member_id'] . "654123");
        if ($access != $_REQUEST['access_token']) {
            output_error('access_token错误');
        };
    }

    public function indexOp()
    {
        /** @var shequ_billModel $model_shequ_bill */
        $model_shequ_bill = Model('shequ_bill');
        $condition = $this->_get_condition();
        $bill_list_array = $model_shequ_bill->getList($condition, $this->page, 'ob_id desc');
        $bill_list = array();
        foreach ($bill_list_array as $value) {
            $bill_list[] = array(
                'ob_no' => $value['ob_no'],
                'ob_create_date' => date('Y-m-d', $value['ob_create_date']),
                'ob_start_date' => date('Y-m-d', $value['ob_start_date']),
                'ob_end_date' => date('Y-m-d', $value['ob_end_date']),
                'ob_order_totals' => $value['ob_order_totals'],
                'ob_order_return_totals' => $value['ob_order_return_totals'],
                'ob_result_totals' => $value['ob_result_totals'],
                'ob_state' => shequBillState($value['ob_state']),
            );
        }
        $page_count = $model_shequ_bill->gettotalpage();
        if (intval($_POST['curpage']) > $page_count) $bill_list = array();
        output_data(array('bill_list' => $bill_list), mobile_page($page_count));
    }

    private function _get_condition() {
        $member_id = intval($_REQUEST['member_id']);
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $tuanzhang_model = Model('shequ_tuanzhang');
        $tuanzhang_info = $tuanzhang_model->getOne(array('member_id' => $member_id));
        $condition = array();
        if (empty($tuanzhang_info)) {
            $condition['ob_store_id'] = -1;
        } else {
            $condition['ob_store_id'] = $tuanzhang_info['id'];
        }
        return $condition;
    }
}
