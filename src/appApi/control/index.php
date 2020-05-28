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
        $res = array();
        foreach ($items as $item) {
            foreach ($item as $k => $v) {
                if ($k == 'home3')
                    $res['banner'] = $v;
                elseif ($k == 'goods')
                    $res['goods'] = $v;
                elseif ($k == 'home1')
                    $res['special'][] = $v;
            }
        }
        output_data($res);

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

        //历史搜索
        if (cookie('his_sh') != '') {
            $his_search_list = explode('~', cookie('his_sh'));
        }

        $data['list'] = $list;
        $data['his_list'] = is_array($his_search_list) ? $his_search_list : array();
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
}
