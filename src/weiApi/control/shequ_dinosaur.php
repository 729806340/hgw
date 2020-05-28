<?php
/**
 * 接龙详情页
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_dinosaurControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 接龙详情
     */
    public function indexOp() {

        $tuan_id = intval($_POST['dinosaur_id']);
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $tuan_info = $shequ_tuan_model->getOne(array('id' => $tuan_id));
        $tuan_info['add_time_text'] = date('Y-m-d H:i:s', $tuan_info['add_time']);
        if (empty($tuan_info)) {
            output_error('该接龙并不存在');
        }
        $config_tuan_id = $tuan_info['config_id'];
        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $tuan_config_info = $shequ_tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        if (empty($tuan_config_info)) {
            output_error('该接龙并不存在!');
        }
        $tuan_info['config_pic'] = str_replace(array("\r","\n"),'', $this->_base64EncodeImage(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$tuan_config_info['config_pic_er']));
        $tuan_info['config_pic_new'] = WEI_UPLOAD_URL. DS.ATTACH_COMMON.DS.$tuan_config_info['config_pic_er'];

        $member_id = $tuan_info['member_id'];
        $zt_address_id = $tuan_info['address_id'];
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_info = $member_model->getMemberInfoByID($member_id);
        /** @var shequ_tuanzhangModel $tuan_zhang_model */
        $tuan_zhang_model = Model('shequ_tuanzhang');
        $tuan_zhang_info = $tuan_zhang_model->getOne(array('member_id' => $member_id));
        $zt_address_info = array();
        if ($zt_address_id) {
            /** @var shequ_addressModel $shequ_address_model */
            $shequ_address_model = Model('shequ_address');
            $zt_address_info = $shequ_address_model->getOne(array('id' => $zt_address_id));
        }
        //获取团购商品
        /** @var shequ_tuan_config_goodsModel $config_tuan_goods_model */
        $config_tuan_goods_model = Model('shequ_tuan_config_goods');
        $config_tuan_goods_list = $config_tuan_goods_model->getTuanConfigGoodsList(array('tuan_config_id' => $config_tuan_id));
        $goods_ids = array_column($config_tuan_goods_list, 'goods_id');
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsOnlineList(array('goods_id' => array('in', $goods_ids)));
        $goods_commonids = array_column($goods_list, 'goods_commonid');
        $goods_common_list = $goods_model->getGoodsCommonList(array('goods_commonid' => array('in', $goods_commonids)));
        $goods_arr = array();
        foreach ($goods_list as $gv) {
            $goods_arr[$gv['goods_commonid']][] = $gv;
        }
        $goods_new_list = array();

        $share_goods_max = 0;
        $share_goods_min = 0;
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $order_goods_list = $order_model->getOrderList(array('shequ_tuan_id' => $tuan_id, 'order_state' => array('egt', ORDER_STATE_PAY), 'refund_state' => 0), '', '*', 'order_id desc', '', array('order_goods'));
        $new_order_goods_list = array();
        foreach ($order_goods_list as $order) {
            foreach ($order['extend_order_goods'] as $order_good) {
                $new_order_goods_list[$order_good['goods_id']] += $order_good['goods_num'];
            }
        }

        foreach ($goods_common_list as $goods_common) {
            $goods_list = $goods_arr[$goods_common['goods_commonid']];
            $goods_sales = 0;
            $goods_min_price = 0;
            $goods_max_price = 0;
            $goods_id = 0;
            $goods_storage = 0;
            foreach ($goods_list as $g_k=>$g_v) {
                $goods_id = $g_v['goods_id'];
                $goods_storage = $g_v['goods_storage'];
                $goods_list[$g_k]['goods_new_spec'] = '';
                $goods_list[$g_k]['goods_goods_image'] = thumb($g_v,360);
                //处理详情
                $mobile_body = $goods_common['goods_body'];
                $mobile_body = str_replace(array("\r\n", "\r", "\n", "\t"), "", $mobile_body);
                $mobile_body = str_replace('"', "'", $mobile_body);
                $goods_list[$g_k]['mobile_body'] = '';
                preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $mobile_body, $img_match);
                if (!empty($img_match[0])) {
                    foreach ($img_match[0] as $img) {
                        $goods_list[$g_k]['mobile_body'] .= $img;
                    }
                }
                $_tmp_value = unserialize($g_v['goods_spec']);
                $_tmp_name = unserialize($g_v['spec_name']);
                if (is_array($_tmp_name) && is_array($_tmp_value)) {
                    $_tmp_name = array_values($_tmp_name);$_tmp_value = array_values($_tmp_value);
                    foreach ($_tmp_name as $sk => $sv) {
                        $goods_list[$g_k]['goods_new_spec'] .= $sv.'：'.$_tmp_value[$sk].'，';
                    }
                    $goods_list[$g_k]['goods_new_spec'] = rtrim($goods_list[$g_k]['goods_new_spec'],'，');
                }
                $goods_sales += $new_order_goods_list[$goods_id];// $g_v['goods_salenum'];
                if ($goods_min_price == 0) {
                    $goods_min_price = $g_v['goods_price'];
                    $goods_max_price = $g_v['goods_price'];
                }
                if ($share_goods_min == 0) {
                    $share_goods_max = $g_v['goods_price'];
                    $share_goods_min = $g_v['goods_price'];
                }
                if ($g_v['goods_price'] < $share_goods_min) {
                    $share_goods_min = $g_v['goods_price'];
                }
                if ($share_goods_max < $g_v['goods_price']) {
                    $share_goods_max = $g_v['goods_price'];
                }

                if ($g_v['goods_price'] < $goods_min_price) {
                    $goods_min_price = $g_v['goods_price'];
                }
                if ($goods_max_price < $g_v['goods_price']) {
                    $goods_max_price = $g_v['goods_price'];
                }
            }
            $goods_new_list[$goods_common['goods_commonid']] = array(
                'goods_list' => $goods_list,
                'goods_name' => $goods_common['goods_name'],
                'goods_min_price' => $goods_min_price,
                'goods_max_price' => $goods_max_price,
                'goods_image' => thumb($goods_common,360),
                'goods_sales' => $goods_sales,
                'goods_id' => count($goods_list) == 1 ? $goods_id : 0,
                'goods_storage' => count($goods_list) == 1 ? $goods_storage : 0
            );
        }

        $share_goods_price = $share_goods_max == $share_goods_min ? $share_goods_min : $share_goods_min . '~'. $share_goods_max;
        $tuan_info['share_goods_price'] = $share_goods_price;
        //评论列表
        $eval_list = array();//$this->_get_comments($tuan_id, 1);
        $new_order_list = $this->_get_order_list($tuan_id);
        /*$pregRule = "/<[img|IMG].*?src=[\'|\"][\/](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        $tuan_config_info['config_tuan_description'] = preg_replace($pregRule, '<img src="'.SHOP_SITE_URL.'${1}" >', $tuan_config_info['config_tuan_description']);
        */

        $mobile_body = $tuan_config_info['config_tuan_description'];
        $mobile_body = str_replace(array("\r\n", "\r", "\n", "\t"), "", $mobile_body);
        $mobile_body = str_replace('"', "'", $mobile_body);
        $tuan_config_info['config_tuan_description'] = '';
        preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $mobile_body, $img_match);
        if (!empty($img_match[0])) {
            foreach ($img_match[0] as $img) {
                $tuan_config_info['config_tuan_description'] .= $img;
            }
        }
        //$tuan_zhang_info['avatar'] = UPLOAD_SITE_URL . '/'. $tuan_zhang_info['avatar'];

        /** @var wx_small_appLogic $wxSmallApp */
        $wxSmallApp = Logic('wx_small_app');
        $tuan_info['config_send_time']  = date('Y-m-d',$tuan_config_info['send_product_date']);
        $tuan_zhang_info['name'] = $this->getMemberWxNickName($tuan_zhang_info['name'], $tuan_zhang_info['member_id']);
        output_data_new(array('info' => array(
            'tuan_info' => $tuan_info,
            'zt_address_info' => $zt_address_info,
            'tuan_zhang_info' => $tuan_zhang_info,
            'deliver_type' => $tuan_config_info['type'] == 1 ? '物流发货' : '门店自提',
            'tuan_title' => $tuan_info['name'],
            'tuan_description' => $tuan_config_info['config_tuan_description'],
            'start_time' => $tuan_config_info['config_start_time'],
            'end_time' => $tuan_config_info['config_end_time'],
            'goods_list' => array_values($goods_new_list),
            'eval_list' => $eval_list,
            'order_total_num' => $new_order_list['count_num'],
            'order_list' => $new_order_list['list'],
            'erweima' => str_replace(array("\r","\n"),'', $wxSmallApp->getQrBase64('pages/community/community',$tuan_id)),
            'erweima_new' =>  $wxSmallApp->getQrHttp('pages/community/community',$tuan_id),
        )));
    }

    public function commitOp() {

        //获取用户id
        $dinosaur_id = intval($_POST['dinosaur_id']);
        $member_id = $_POST['member_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_info = $member_model->getMemberInfoByID($member_id);

        $insert = array(
            'member_id' => $member_id,
            'member_name' => $member_info['member_name'],
            'member_avatar' => getMemberAvatar($member_info['member_avatar']),
            'title' => $title,
            'content' => $content,
            'dinosaur_id' => $dinosaur_id,
            'add_time' => TIMESTAMP,
            'update_time' => TIMESTAMP,
        );
        /** @var shequ_tuan_pinglunModel $model_tuan_pinglun */
        $model_tuan_pinglun = Model('shequ_tuan_pinglun');
        $result = $model_tuan_pinglun->addTuanPinglun($insert);
        if(!$result) {
            output_error('失败');
        }
        output_data('成功');
    }

    private function _get_order_list($tuan_id)
    {
        if ($tuan_id <=0)  $tuan_id = -1;
        /** @var orderModel $order_model */
        $order_model = Model('order');
        $order_list = $order_model->getOrderList(array('shequ_tuan_id' => $tuan_id, 'order_state' => array('egt', ORDER_STATE_PAY), 'refund_state' => 0), $this->page, '*', 'order_id desc', '', array('order_goods'));
        $order_total_num = $order_model->gettotalnum();
        $i = 0;
        $new_order_list = array();
        /** @var memberModel $member_model */
        $member_model = Model('member');
        $member_list = array();
        if (!empty($order_list)) {
            $buyer_ids = array_column($order_list, 'buyer_id');
            $member_list = $member_model->getMemberList(array('member_id' => array('in',$buyer_ids)));
            $member_list = array_under_reset($member_list, 'member_id');
        }
        foreach ($order_list as $order) {
            if (TIMESTAMP - $order['add_time'] < 120) {
                $add_time_str = '1分钟前';
            } elseif (TIMESTAMP - $order['add_time'] < 360) {
                $add_time_str = '5分钟前';
            } elseif (TIMESTAMP - $order['add_time'] < 1860) {
                $add_time_str = '30分钟前';
            } elseif (TIMESTAMP - $order['add_time'] < 3660) {
                $add_time_str = '1个小时前';
            } else {
                $add_time_str = date('Y-m-d H:i:s', $order['add_time']);
            }
            $new_order_list[] = array(
                'number' => $order_total_num - ($_POST['curpage'] - 1) * $this->page - $i,
                'member_avatar' => $member_list[$order['buyer_id']]['wx_user_avatar'],
                'buyer_name' => $member_list[$order['buyer_id']]['wx_nick_name'],
                'add_time_str' => $add_time_str,
                'order_goods' => $order['extend_order_goods']
            );
            $i ++;
        }

        return array('list' => $new_order_list, 'count_num' => $order_total_num);
    }

    private function _get_comments($tuan_id, $page)
    {
        $condition = array();
        $condition['dinosaur_id'] = $tuan_id;
        //查询商品评分信息
        /** @var shequ_tuan_pinglunModel $model_shequ_tuan_pinglun */
        $model_shequ_tuan_pinglun = Model("shequ_tuan_pinglun");
        $pinglun_list = $model_shequ_tuan_pinglun->getTuanPinglunList($condition, $page);
        $list = array();
        foreach ($pinglun_list as $key => $value) {
            if (empty($value)) continue;
            $value['add_time_str'] =  date('Y-m-d', $value['geval_addtime']);
            $list[] = $value;
        }
        return $list;
    }

    public function get_comment_listOp() {
        $tuan_id = $_POST['tuan_id'];
        output_data($this->_get_comments($tuan_id, $_POST['curpage']));
    }

    public function get_order_listOp() {
        $tuan_id = $_POST['tuan_id'];
        output_data($this->_get_order_list($tuan_id));
    }

    private function _base64EncodeImage ($image_file) {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }


}

