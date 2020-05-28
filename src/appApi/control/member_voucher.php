<?php
/**
 * 我的代金券
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */


defined('ByShopWWI') or exit('Access Invalid!');

class member_voucherControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 代金券列表
     */
    public function voucher_listOp()
    {
        if ($this->member_info['member_id']) {
            $model_voucher = Model('voucher');
            $field = 'voucher_id,voucher_t_id,voucher_title , voucher_desc ,voucher_price,voucher_limit,voucher_start_date , voucher_end_date,voucher_active_date,voucher_state,voucher_order_id';
            $voucher_list = $model_voucher->getVoucherList(array('voucher_owner_id' => $this->member_info['member_id']), $field, '', $this->page, 'voucher_id DESC');
            $template_ids = array();
            foreach ($voucher_list as $key => $value) {
                $voucher_list[$key]['voucher_end_date'] = date('Y-m-d', $value['voucher_end_date']);
                $voucher_list[$key]['voucher_start_date'] = date('Y-m-d', $value['voucher_start_date']);
                $voucher_list[$key]['voucher_active_date'] = date('Y-m-d', $value['voucher_active_date']);
                //$voucher_list[$key]['store_avatar_url'] = $value['voucher_t_customimg'];
                $template_ids[] = $value['voucher_t_id'];
            }
            $page_count = $model_voucher->gettotalpage();
            $template_ids = array_unique($template_ids);
            if (count($template_ids) > 0) {
                $condition = array();
                $condition['voucher_t_id'] = array('in', $template_ids);
                $template_ids_arr = Model('voucher_template')->where($condition)->field('voucher_t_id,voucher_t_store_id,voucher_t_customimg')->select();
                $template_ids_arr = array_under_reset($template_ids_arr, 'voucher_t_id');
                //var_dump($template_ids_arr);
                foreach ($template_ids_arr as $key => $val) {
                    if (empty($val['voucher_t_customimg']) || !file_exists(BASE_UPLOAD_PATH . DS . ATTACH_VOUCHER . DS . $val['voucher_t_store_id'] . DS . $val['voucher_t_customimg'])) {
                        $template_ids_arr[$key]['voucher_t_customimg'] = UPLOAD_SITE_URL . DS . defaultGoodsImage(60);
                    } else {
                        $template_ids_arr[$key]['voucher_t_customimg'] = UPLOAD_SITE_URL . DS . ATTACH_VOUCHER . DS . $val['voucher_t_store_id'] . DS . str_ireplace('.', '_small.', $val['voucher_t_customimg']);
                    }
                }

            }
            foreach ($voucher_list as $key => $value) {
                $voucher_list[$key]['store_avatar_url'] = $template_ids_arr[$value['voucher_t_id']]['voucher_t_customimg'];
            }

            $get_frees = $model_voucher->getFreesByMemberId($this->member_info['member_id'], intval($_POST['free_num']));
            $free_list = array();
            foreach ($get_frees as $k => $row) {
                $free_list[$k]['voucher_t_id'] = $row['voucher_t_id'];
                $free_list[$k]['voucher_t_title'] = $row['voucher_t_title'];
                $free_list[$k]['voucher_t_desc'] = $row['voucher_t_desc'];
                $free_list[$k]['voucher_t_end_date'] = date('Y-m-d', $row['voucher_t_end_date']);
                $free_list[$k]['voucher_t_price'] = $row['voucher_t_price'];
            }
            if($_GET['curpage']>$page_count)$voucher_list=array();
            $output['voucher_list'] = $voucher_list;
            $output['free_list'] = $free_list;
            output_data($output, mobile_page($page_count));
        } else {
            output_error('请登录！');
        }
    }


    /**
     * 领取免费代金券
     */
    public function voucher_freeexOp()
    {
        //$_POST['tid'] = 6;
        $t_id = intval($_POST['tid']);
        if ($t_id <= 0) {
            output_error('代金券信息错误');
        }
        $model_voucher = Model('voucher');
        //验证是否可领取代金券
        $data = $model_voucher->getCanChangeTemplateInfo($t_id, intval($this->member_info['member_id']), intval($this->member_info['store_id']));
        //var_dump($data);
        if ($data['state'] == false) {
            output_error($data['msg']);
        }
        try {
            $model_voucher->beginTransaction();
            //添加代金券信息
            $data = $model_voucher->exchangeVoucher($data['info'], $this->member_info['member_id'], $this->member_info['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            output_data('代金券领取成功');
        } catch (Exception $e) {
            $model_voucher->rollback();
            output_error($e->getMessage());
        }

    }

    /**
     * 领取密码代金券
     */
    public function voucher_pwexOp()
    {
        if ($this->member_info['member_id']) {
// 			if(!$this->check()){
// 				output_error('验证码错误！');
// 			}
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array(
                    "input"   => $_POST["pwd_code"],
                    "require" => "true",
                    "message" => '请输入代金券卡密'
                )
            );
            $error = $obj_validate->validate();
            if ($error != '') {
                output_error($error);
            }
            // 查询代金券
            $model_voucher = Model('voucher');
            $where = array();
            $where['voucher_pwd'] = md5($_POST["pwd_code"]);
            $voucher_info = $model_voucher->getVoucherInfo($where);
            if (!$voucher_info) {
                output_error('代金券卡密错误');
            }
            if (intval($_SESSION['store_id']) == $voucher_info['voucher_store_id']) {
                output_error('不能领取自己店铺的代金券');
            }
            if ($voucher_info['voucher_owner_id'] > 0) {
                output_error('该代金券卡密已被使用，不可重复领取');
            }
            $where = array();
            $where['voucher_id'] = $voucher_info['voucher_id'];
            $update_arr = array();
            $update_arr['voucher_owner_id'] = $this->member_info['member_id'];
            $update_arr['voucher_owner_name'] = $this->member_info['member_name'];
            $update_arr['voucher_active_date'] = time();
            $result = $model_voucher->editVoucher($update_arr, $where, $this->member_info['member_id']);
            if ($result) {
                // 更新代金券模板
                $update_arr = array();
                $update_arr['voucher_t_giveout'] = array(
                    'exp',
                    'voucher_t_giveout+1'
                );
                $model_voucher->editVoucherTemplate(array(
                    'voucher_t_id' => $voucher_info['voucher_t_id']
                ), $update_arr);
                output_data('代金券领取成功');
            } else {
                output_error('代金券领取失败');
            }
        } else {
            output_error('请登录！');
        }
    }

    /**
     * AJAX验证
     *
     */
    protected function check()
    {
        if (checkSeccode($_POST['nchash'], $_POST['captcha'])) {
            return true;
        } else {
            return false;
        }
    }


}
