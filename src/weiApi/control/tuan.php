<?php
/**
 * 商品拼团列表
 *
 */


defined('ByShopWWI') or exit('Access Invalid!');

class tuanControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 拼团列表
     */
    public function get_listOp()
    {
        $condition = array();
        $condition['state'] = 1;
        $condition['end_time'] = array('gt', TIMESTAMP);
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['limit_user'] = array('gt', 0);

        /** @var p_pintuan_goodsModel $p_pintuan_goods_model */
        $p_pintuan_goods_model = Model('p_pintuan_goods');
        $goods_list = $p_pintuan_goods_model->getPintuanGoodsList($condition,$this->page);
        $page_count = $p_pintuan_goods_model->gettotalpage();
        if (intval($_GET['curpage']) > $page_count) $goods_list = array();
        $goods_list_new = array();
        foreach ($goods_list as $val) {
            $val['goods_image'] = thumb($val);
            $goods_list_new[] = $val;
        }
        $bottom_image = '';
        if (intval($_GET['curpage']) <= 1 && empty($goods_list_new)) {
            $bottom_image = SHOP_SITE_URL.DS.'resource'.DS.'img'.DS.'spsc_w_img@2x.png';
        }
        output_data(array('goods_list' => $goods_list_new, 'bottom_image' => $bottom_image), mobile_page($page_count));
    }

    public function infoOp()
    {
        $tuan_id = intval($_POST['tuan_id']);
        if ($tuan_id <= 0) {
            output_error('参数错误');
        }
        /** @var p_pintuan_memberModel $p_pintuan_member_model */
        $p_pintuan_member_model = Model('p_pintuan_member');
        $tuan_user_list = $p_pintuan_member_model->getMemberList(array('tuan_id' => $tuan_id));
        if (empty($tuan_user_list)) {
            output_error('参数错误');
        }
        output_data(array('user_list' => $tuan_user_list));
    }

}
