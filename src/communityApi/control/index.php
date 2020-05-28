<?php
/**
 * 手机端首页控制
 *
 *by wansyb QQ群：111731672
 *你正在使用的是由网店 运 维提供S2.0系统！保障你的网络安全！ 购买授权请前往shopnc
 */


defined('ByShopWWI') or exit('Access Invalid!');

class indexControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function indexOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getAppSpecialIndex();
        $this->_output_special($data, $_GET['type']);
    }

    public function index2Op()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getAppSpecialIndex();
        $result = $this->dealSpecial($items);
        output_data($result);

    }

    public function index_specialOp() {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getAppSpecialIndex($_POST['special_id']);
        $datas['special_desc'] = $model_mb_special->getMbSpecialdesc($_POST['special_id']);
        $result = $this->dealSpecial($items);
        output_data($result);
    }

    public function index_special2Op() {
        $datas = array();
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getAppSpecialIndex($_POST['special_id']);
        $special_info = $model_mb_special->getMbSpecialInfo($_POST['special_id']);
        $datas['special_desc'] = $special_info['special_desc'];
        $datas['special_background'] = $special_info['special_background'];
        $datas['list'] = $this->dealSpecial($items);
        output_data_new($datas);
    }

    private function dealSpecial($items) {
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'adv_list') {
                    $res[] = array(
                        'type' => 'adv_list',
                        'list' => $v['item']
                    );
                } elseif ($k == 'icon') {
                    $res[] = array(
                        'type' => 'icon',
                        'list' => $v['item']
                    );
                } elseif ($k == 'home1') {
                    $res[] = array(
                        'type' => 'home1',
                        'list' => $v
                    );
                } elseif ($k == 'goods3') {
                    $res[] = array(
                        'type' => 'goods3',
                        'list' => $v['item'],
                        'lcurl' => $v['lcurl'],
                    );
                } elseif ($k == 'goods') {
                    $res[] = array(
                        'type' => 'goods',
                        'list' => $v['item'],
                        'lcurl' => $v['lcurl'],
                    );
                } elseif ($k == 'explode2') {
                    $res[] = array(
                        'type' => 'explode2',
                        'list' => $v['item']
                    );
                } elseif ($k == 'explode2pic') {
                    $res[] = array(
                        'type' => 'explode2pic',
                        'list' => $v['item'],
                        'title'=> $v['title'],
                        'stitle'=> $v['stitle'],
                        'title1'=> $v['title1'],
                        'stitle1'=> $v['stitle1'],
                    );
                } elseif ($k == 'explode3pic') {
                    $res[] = array(
                        'type' => 'explode3pic',
                        'list' => $v['item'],
                        'title'=> $v['title'],
                        'stitle'=> $v['stitle'],
                        'title1'=> $v['title1'],
                        'stitle1'=> $v['stitle1'],
                        'title2' => $v['title2'],
                        'stitle2'=> $v['stitle2'],
                    );
                } elseif ($k == 'home2') {
                    $res[] = array(
                        'type' => 'home2',
                        'list' => $v
                    );
                } elseif ($k == 'home4') {
                    $res[] = array(
                        'type' => 'home4',
                        'list' => $v
                    );
                } elseif ($k == 'miaosha') {
                    if ($v['item']['info']['end_time'] > time()) {
                        $res[] = array(
                            'type' => 'miaosha',
                            'list' => $v
                        );
                    }
                } elseif ($k == 'explode3') {
                    $res[] = array(
                        'type' => 'explode3',
                        'list' => $v['item']
                    );
                } elseif ($k == 'explode4') {
                    $res[] = array(
                        'type' => 'explode4',
                        'list' => $v['item']
                    );
                } elseif ($k == 'home3') {
                    $res[] = array(
                        'type' => 'home3',
                        'list' => $v['item']
                    );
                } elseif ($k == 'miaosha_more') {
                    $v['item']['now_time'] = TIMESTAMP;
                    $res[] = array(
                        'type' => 'miaosha_more',
                        'list' => $v['item']
                    );
                } elseif ($k == 'home6') {
                    if (!empty($v['item'])) {
                        $res[] = array(
                            'type' => 'home6',
                            'goods_image' => $v['item']['image'],
                            'current_time' => TIMESTAMP,
                            'xian_shi' => $v['item']['xian_shi'],
                            'back_img' => SHOP_SITE_URL.DS.'resource'.DS.'img'.DS.'simple_goods_time.jpg',
                        );
                    }
                } elseif ($k == 'layer') {
                        $res[] = array(
                            'type' => 'layer',
                            'list' => $v
                        );
                }
            }
        }

        return $res;
    }

    public function categoryOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_CATEGORY_SPECIAL_ID);
        $this->_output_special($data, $_GET['type']);
    }

    public function category2Op()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_CATEGORY_SPECIAL_ID);
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'home3') {
                    if (!isset($res['category'])) $res['category'] = $v;
                    if (!isset($res['function'])) $res['function'] = $v;
                    else $res['special'] = $v;
                }
            }
        }
        //添加分类显示信息
        $cat_list = Model('goods_class')->getGoodsClassList(array('app_show' => '1'), 'gc_id,gc_name,app_img');
        $cat_arr = array();
        foreach ((array)$cat_list as $v) {
            if (empty($v['gc_id'])) continue;
            if (!empty($v['app_img'])) $v['app_img'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . $v['app_img'];
            $cat_arr[] = $v;
        }
        $res['gc_info'] = $cat_arr;
        output_data($res);
    }

    public function shihuaOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_SHIHUA_SPECIAL_ID);
        $this->_output_special($data, $_GET['type']);
    }

    public function shihua2Op()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_SHIHUA_SPECIAL_ID);
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'home3') {
                    $res['home3'] = $v;
                }
                if ($k == 'home1') {
                    $res['home1'] = $v;
                }
                if ($k == 'article') {
                    $res['article'] = $v;
                }
            }
        }
        output_data($res);
    }

    public function shilvOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_SHILV_SPECIAL_ID);
        $this->_output_special($data, $_GET['type']);
    }

    public function faxianOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_FAXIAN_SPECIAL_ID);
        $this->_output_special($data, $_GET['type']);
    }

    public function faxian2Op()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_FAXIAN_SPECIAL_ID);
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'home3') $res = $v['item'];
            }
        }
        output_data($res);
    }

    public function zhidemai1Op()
    {
        $id = $_POST['id'];
        if (!in_array($id, array(1, 2, 3, 4))) $id = 1;
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $types = array(
            1 => $model_mb_special::APP_ZHIDEMAI1_SPECIAL_ID,
            2 => $model_mb_special::APP_ZHIDEMAI2_SPECIAL_ID,
            3 => $model_mb_special::APP_ZHIDEMAI3_SPECIAL_ID,
            4 => $model_mb_special::APP_ZHIDEMAI4_SPECIAL_ID,
        );
        $items = $model_mb_special->getMbSpecialItemUsableListByID($types[$id]);
        $res = array(
            'goods' => array(),
        );
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'home1') $res['home1'] = $v;
                if ($k == 'home2') $res['home2'] = $v;
                if ($k == 'goods') $res['goods'] = $v['item'];
            }
        }
        output_data($res);
    }

    public function zhidemai2Op()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $items = $model_mb_special->getMbSpecialItemUsableListByID($model_mb_special::APP_ZHIDEMAI2_SPECIAL_ID);
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'adv_list') $res['banner'] = $v['item'];
                if ($k == 'home2') $res['home2'] = $v;
                if ($k == 'goods') $res['goods'] = $v;

            }
        }
        output_data($res);
    }

    /**
     * 专题
     */
    public function specialOp()
    {
        /** @var mb_specialModel $model_mb_special */
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($_POST['special_id']);
        $datas['special_desc'] = $model_mb_special->getMbSpecialdesc($_POST['special_id']);
        $this->_output_special($data, 'json', $_POST['special_id'], $datas);
    }

    /**
     * 输出专题
     */
    private function _output_special($data, $type = 'json', $special_id = 900000001, $datas = array())
    {
        $datas['list'] = $data;
        $datas['special_id'] = $special_id;
        output_data($datas);
    }

    /**
     * 热门搜索词列表
     */
    public function search_hotOp()
    {
        //热门搜索
        $list = @explode(',', C('hot_search'));
        if (!$list || !is_array($list)) {
            $list = array();
        }

        $data['list'] = $list;
        output_data($data);
    }

    /**
     * 热门搜索列表
     */
    public function search_keyOp()
    {
        $rec_value = array();
        if (C('rec_search') != '') {
            $rec_search_list = @unserialize(C('rec_search'));
            $rec_value = array();
            foreach ($rec_search_list as $v) {
                $rec_value[] = $v['value'];
            }

        }
        output_data($rec_value);
    }

    /**
     * 高级搜索
     */
    public function search_advOp()
    {
        $area_list = Model('area')->getAreaList(array('area_deep' => 1), 'area_id,area_name');
        if (C('contract_allow') == 1) {
            $contract_list = Model('contract')->getContractItemByCache();
            $_tmp = array();
            $i = 0;
            foreach ($contract_list as $k => $v) {
                $_tmp[$i]['id'] = $v['cti_id'];
                $_tmp[$i]['name'] = $v['cti_name'];
                $i++;
            }
        }
        output_data(array('area_list' => $area_list ? $area_list : array(), 'contract_list' => $_tmp));
    }

    /**
     * android客户端版本号
     */
    public function apk_versionOp()
    {
        $versionNo = C('mobile_apk_version_no');
        $apkNo = intval($_POST['version_no']);
        $version = C('mobile_apk_version_name');
        $force = C('mobile_apk_force');
        $url = C('mobile_apk_url');
        if($apkNo>=$versionNo||empty($version)||empty($url)){
            output_data(array('new'=>'0','version' => '', 'url' => '', 'force' => ''));
        }
        if (empty($force)) {
            $force = 0;
        }
        output_data(array('new'=>'1','version' => $version, 'url' => $url, 'force' => $force));
    }

    /**
     *  退款上传图片
     */
    public function upload_picOp() {
        $upload_type = $_POST['upload_type'];
        if ($upload_type != 'refund_pic') {
            output_error('非法请求');
        }
        $upload = new UploadFile();
        $upload->set('default_dir', ATTACH_PATH.DS.'refund'.DS);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        $upload->set('max_size',1024*8);
        $thumb_width	= '32';
        $thumb_height	= '32';
        $upload->set('thumb_width', $thumb_width);
        $upload->set('thumb_height', $thumb_height);
        if ($_FILES['file']['name']) {
            $result = $upload->upfile('file');
            if ($result) {
                output_data(array('src'=>UPLOAD_SITE_URL . DS . ATTACH_PATH.DS .'refund' . DS . $upload->file_name));
            }
        }
        output_error('error #');

    }

    public function new_upload_picOp() {
        $upload_type = $_POST['upload_type'];
        if ($upload_type != 'refund_pic') {
            output_error('非法请求');
        }
        $upload = new UploadFile();
        $upload->set('default_dir', ATTACH_PATH.DS.'refund'.DS);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        $upload->set('max_size',1024*8);
        $thumb_width	= '32';
        $thumb_height	= '32';
        $upload->set('thumb_width', $thumb_width);
        $upload->set('thumb_height', $thumb_height);
        if ($_FILES['file']['name']) {
            $result = $upload->upfile('file');
            if ($result) {
                $file_name = $upload->file_name;
                $pic = UPLOAD_SITE_URL . DS . ATTACH_PATH.DS .'refund' . DS . $file_name;
                output_data(array(
                    'pic' => $file_name,
                    'http_pic' => $pic
                ));
            }
        }
        output_error('error #');

    }

    /**
     * 秒杀列表
     */
    public function second_killOp()
    {
        if (C('default_xianshi_pic')) {
            $top_img = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.C('default_xianshi_pic');
        } else {
            $top_img = SHOP_SITE_URL . '/img/hgms.jpg';
        }

        $data = array(
            'kill_list' => array(),
            'top_img' => $top_img
        );
        $config_ids = trim($_POST['config_ids']);
        $config_ids = json_decode($config_ids, true);
        if (empty($config_ids)) {
            output_error('参数错误');
        }
        /** @var p_xianshiModel $p_xianshi_model */
        $p_xianshi_model = Model('p_xianshi');
        $xian_shi_list = $p_xianshi_model->getXianshiList(array('config_xianshi_id' => array('in', $config_ids)), null, 'start_time ASC');
        if (empty($xian_shi_list)) {
            output_data($data);
        }
        $exit_config_ids = array();
        $end_num = 1;
        foreach ($xian_shi_list as $k1=>$v1) {
            if (!in_array($v1['config_xianshi_id'], $exit_config_ids)) {
                $v1['start_time_text'] = date('H:i', $v1['start_time']);
                if ($v1['end_time'] < TIMESTAMP) {
                    $v1['xianshi_state_text'] = '已结束';
                    if ($end_num > 1) {
                        continue;
                    }
                    $end_num ++;
                } elseif ($v1['start_time'] > TIMESTAMP) {
                    $v1['xianshi_state_text'] = '即将开始';
                } else {
                    $v1['xianshi_state_text'] = '进行中';
                }
                $data['kill_list'][] = $v1;
            }
            $exit_config_ids[] = $v1['config_xianshi_id'];
        }
        output_data($data);
    }

    /**
     * 秒杀单列商品列表
     */
    public function second_goods_listOp()
    {

        $goods_list = array();
        $current_config_id = intval($_POST['current_config_id']);
        if ($current_config_id <= 0) {
            output_error('参数错误');
        }
        /** @var p_xianshiModel $p_xianshi_model */
        $p_xianshi_model = Model('p_xianshi');
        /** @var p_xianshi_goodsModel $p_xinashi_goods_model */
        $p_xinashi_goods_model = Model('p_xianshi_goods');
        $current_xian_shi_list = $p_xianshi_model->getXianshiList(array('config_xianshi_id' => $current_config_id), null, 'start_time ASC', 'xianshi_id');
        if (empty($current_xian_shi_list)) {
            output_data(array('goods_list' => $goods_list, 'now_time' => TIMESTAMP));
        }
        $current_xian_shi_ids = array_column($current_xian_shi_list, 'xianshi_id');
        $xian_goods_data = $p_xinashi_goods_model->getXianshiGoodsList(array('xianshi_id' => array('in', $current_xian_shi_ids)));

        if (empty($xian_goods_data)) {
            output_data(array('goods_list' => $goods_list, 'now_time' => TIMESTAMP));
        }

        $member_carts = array();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            /** @var cartModel $model_cart */
            $model_cart = Model('cart');
            $condition = array('buyer_id' => $member_id);
            $cart_list = $model_cart->listCart('db', $condition);
            if (!empty($cart_list)) {
                $member_carts = array_column($cart_list, 'goods_num', 'goods_id');
            }
        }

        $goods_ids = array_column($xian_goods_data, 'goods_id');
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_arr = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)), 'goods_id,goods_storage,goods_spec');
        $goods_arr = array_column($goods_arr, 'goods_storage', 'goods_id');
        $goods_spec_arr = array_column($goods_arr, 'goods_spec', 'goods_id');
        foreach ($xian_goods_data as $m) {
            $goods_spec = array();
            $goods_spec_name = '';
            if (array_key_exists($m['goods_id'], $goods_spec_arr)) {
                $goods_spec = unserialize($goods_spec_arr[$m['goods_id']]);
            }
            if (!empty($goods_spec)) {
                $goods_spec_name = current($goods_spec);
            }
            $m['goods_image'] = cthumb($m['goods_image'], 240, $m['store_id']);
            $m['cart_num'] = array_key_exists($m['goods_id'], $member_carts) ? $member_carts[$m['goods_id']] : 0;
            $m['goods_storage'] = array_key_exists($m['goods_id'], $goods_arr) ? $goods_arr[$m['goods_id']] : 0;
            $m['goods_spec'] = $goods_spec_name;
            $goods_list[] = $m;
        }
        output_data(array('goods_list' => $goods_list, 'now_time' => TIMESTAMP));
    }

    /**
     * 进行中的秒杀商品列表
     */
    public function current_second_goods_listOp() {
        $goods_list = array();
        /** @var p_xianshi_goodsModel $p_xinashi_goods_model */
        $p_xinashi_goods_model = Model('p_xianshi_goods');
        $xian_condition = array(
            'start_time' => array('lt', TIMESTAMP),
            'end_time' => array('gt', TIMESTAMP),
            'state'      => 1,
            'xianshi_storage > xianshi_sold'
        );
        $xian_goods_data = $p_xinashi_goods_model->getXianshiGoodsList($xian_condition, 10, 'end_time ASC');
        if (empty($xian_goods_data)) {
            output_data(array('goods_list' => $goods_list));
        }
        $member_carts = array();
        $member_id = $this->getMemberIdIfExists();
        if ($member_id) {
            /** @var cartModel $model_cart */
            $model_cart = Model('cart');
            $condition = array('buyer_id' => $member_id);
            $cart_list = $model_cart->listCart('db', $condition);
            if (!empty($cart_list)) {
                $member_carts = array_column($cart_list, 'goods_num', 'goods_id');
            }
        }
        $goods_ids = array_column($xian_goods_data, 'goods_id');
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_arr = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)), 'goods_id,goods_storage,goods_spec');
        $goods_arr = array_column($goods_arr, 'goods_storage', 'goods_id');
        $goods_spec_arr = array_column($goods_arr, 'goods_spec', 'goods_id');
        foreach ($xian_goods_data as $m) {
            $goods_spec = array();
            $goods_spec_name = '';
            if (array_key_exists($m['goods_id'], $goods_spec_arr)) {
                $goods_spec = unserialize($goods_spec_arr[$m['goods_id']]);
            }
            if (!empty($goods_spec)) {
                $goods_spec_name = current($goods_spec);
            }
            $m['goods_image'] = cthumb($m['goods_image'], 240, $m['store_id']);
            $m['cart_num'] = array_key_exists($m['goods_id'], $member_carts) ? $member_carts[$m['goods_id']] : 0;
            $m['goods_storage'] = array_key_exists($m['goods_id'], $goods_arr) ? $goods_arr[$m['goods_id']] : 0;
            $m['goods_spec'] = $goods_spec_name;
            $goods_list[] = $m;
        }
        output_data(array('goods_list' => $goods_list));
    }

    //新版汉购网首页
    public function ne_ss_ggOp() {
        $result_home_goods = array(
            'hgtc' => array(105207,101887,105765,105543,105234),
            'lyfs' => array(104885,105769,100332,105714,105713),
            'spyl' => array(105880,105874,105597,103465,105532)
        );

        $goods_ids = array();
        foreach ($result_home_goods as $k=>$v) {
            $goods_ids = array_merge($goods_ids, $v);
        }
        $goods_ids = array_unique($goods_ids);
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)));
        $goods_list = array_under_reset($goods_list, 'goods_id');
        $result = array();

        foreach ($result_home_goods as $m=>$n){
            $result[$m] = array();
            foreach ($n as $goods_id) {
                $goods_info = $goods_list[$goods_id];
                $result[$m][] = array(
                    'goods_name' => $goods_info ? $goods_info['goods_name'] : '商品名称',
                    'goods_price' => $goods_info ? $goods_info['goods_price'] : 200,
                    'goods_marketprice' => $goods_info ? $goods_info['goods_marketprice'] : 300,
                    'goods_image' => thumb($goods_info),
                    'w_image' => $goods_info ? UPLOAD_SITE_URL . '/'. ATTACH_STORE . '/'. $goods_info['store_id'] . '/'. $goods_id . '.png' : ''
                );
            }
        }
        output_data($result);
    }
}
