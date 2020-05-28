<?php
/**
 * 社区团购管理
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');
class tuan_configControl extends SystemControl{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 默认Op
     */
    public function indexOp() {
        $this->config_tuan_listOp();
    }

    /**
     * 社区团购活动列表
     */
    public function config_tuan_listOp()
    {
        $this->show_menu('config_tuan_list');
        Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.list');
    }

    public function config_tuan_addOp()
    {
        if (chksubmit()) {
            $config_xianshi_name = trim($_POST['config_xianshi_name']);
            $config_xianshi_title = trim($_POST['config_xianshi_title']);
            $config_xianshi_explain = trim($_POST['article_content']);
            $config_start_time = strtotime($_POST['query_start_date']);
            $config_end_time = strtotime($_POST['query_end_date']);
            $send_product_date = strtotime($_POST['send_product_date']);
            $type = intval($_POST['type']);
            $config_pic = '';
            $config_pic_er = '';
            if (!empty($_FILES['member_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo');
                if ($result) {
                    $config_pic = $upload->file_name;
                } else {
                    showMessage($upload->error,'','','error');
                }
            } else {
                showMessage('请上传海报');
            }
            if (!empty($_FILES['member_logo_er']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir', ATTACH_COMMON);
                $result = $upload->upfile('member_logo_er');
                if ($result) {
                    $config_pic_er = $upload->file_name;
                } else {
                    showMessage($upload->error,'','','error');
                }
            } else {
                showMessage('请上传海报');
            }

            if(empty($config_xianshi_name)) {
                showMessage('活动名称不能为空！');
            }
            if($config_start_time >= $config_end_time) {
                showMessage('开始时间不能大于结束时间！');
            }
            if (!$config_xianshi_explain) {
                showMessage('描述不能为空！');
            }
            if (!$type) {
                showMessage('请选择类型！');
            }
            if ($config_start_time >= $send_product_date) {
                showMessage('开始时间不能大于发货时间！');
            }
            //生成活动
            /** @var shequ_tuan_configModel $model_tuan_config */
            $model_tuan_config = Model('shequ_tuan_config');
            $param = array();
            $param['config_tuan_name'] = $config_xianshi_name;
            $param['config_tuan_title'] = $config_xianshi_title;
            $param['config_tuan_description'] = $config_xianshi_explain;
            $param['config_start_time'] = $config_start_time;
            $param['config_end_time'] = $config_end_time;
            $param['send_product_date'] = $send_product_date;
            $param['config_pic'] = $config_pic;
            $param['config_pic_er'] = $config_pic_er;
            $param['type'] = $type;
            $result = $model_tuan_config->addTuanConfig($param);
            if ($result) {
                // 添加计划任务
                showMessage('新增成功！', 'index.php?act=tuan_config&op=config_tuan_list');
            } else {
                showMessage('新增失败！');
            }
        }
        Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.add');
    }

    /**
     * 平台活动列表
     */
    public function config_tuan_list_xmlOp()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['config_xianshi_name']))) {
                $condition['config_tuan_name'] = array('like', '%' . $q . '%');
            }

            $pdates = array();
            if (strlen($q = trim((string) $_REQUEST['pdate1'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "config_end_time >= {$q}";
            }
            if (strlen($q = trim((string) $_REQUEST['pdate2'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "config_start_time <= {$q}";
            }
            if ($pdates) {
                $condition['pdates'] = array(
                    'exp',
                    implode(' or ', $pdates),
                );
            }

        } else {
            if (strlen($q = trim($_REQUEST['query']))) {
                switch ($_REQUEST['qtype']) {
                    case 'config_tuan_name':
                        $condition['config_tuan_name'] = array('like', '%'.$q.'%');
                        break;
                }
            }
        }

        /** @var shequ_tuan_configModel $model_tuan_config */
        $model_tuan_config = Model('shequ_tuan_config');

        $config_list = (array) $model_tuan_config->getTuanConfigList($condition, $_REQUEST['rp'], 'config_end_time desc');
        $data = array();
        $data['now_page'] = $model_tuan_config->shownowpage();
        $data['total_num'] = $model_tuan_config->gettotalnum();

        foreach ($config_list as $val) {
            $o = '';

            $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';

            $o .= '<li><a class="confirm-on-click" href="' . urlAdminShop('tuan_config', 'config_tuan_detail', array(
                    'config_tuan_id' => $val['config_tuan_id'],
                )) . '">活动详细</a></li>';
            $o .= '<li><a class="confirm-on-click" href="' . urlAdminShop('tuan_config', 'config_add_goods', array(
                    'config_tuan_id' => $val['config_tuan_id'],
                )) . '">添加活动商品</a></li>';
            $o .= '<li><a class="confirm-on-click" href="' . urlAdminShop('tuan_config', 'config_tuan_goods', array(
                    'config_tuan_id' => $val['config_tuan_id'],
                )) . '">查看活动下的商品</a></li>';

            $o .= '</ul></span>';

            $i = array();
            $i['operation'] = $o;
            $i['config_xianshi_id'] = $val['config_tuan_id'];
            $i['config_xianshi_name'] = $val['config_tuan_name'];
            $i['config_start_time'] = date('Y-m-d H:i', $val['config_start_time']);
            $i['config_end_time'] = date('Y-m-d H:i', $val['config_end_time']);
            $i['send_product_date'] = $val['send_product_date']  ? date('Y-m-d H:i', $val['send_product_date']) : '';
            $i['config_type'] = !$val['type'] ? '' : ($val['type'] == 1 ? '物流' : '自提');
            $data['list'][$val['config_tuan_id']] = $i;
        }

        echo Tpl::flexigridXML($data);
        exit;
    }

    public function config_tuan_detailOp() {
        $config_xianshi_id = intval($_GET['config_tuan_id']);
        $config_xianshi_info = Model('shequ_tuan_config')->getTuanConfigInfo(array('config_tuan_id' => $config_xianshi_id));
        if(empty($config_xianshi_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('config_xianshi_info', $config_xianshi_info);
        Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.detail');
    }

    /**
     * 活动下的商品
     */
    public function config_tuan_goodsOp() {
		Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.goods');
    }

    /**
     * 添加活动商品
     */
    public function config_add_goodsOp() {

        $config_tuan_id = intval($_GET['config_tuan_id']);
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');
        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        $condition['tuan_config_id'] = $config_tuan_id;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $list = $tuan_config_goods_model->getTuanConfigGoodsList($condition, null, 'tuan_config_goods_id desc');
        $goods_ids = array_column($list, 'goods_id');
        $return_goods_rate_list = array();
        $return_goods_list = array();
        if (!empty($goods_ids)) {
            /** @var goodsModel $goods_model */
            $goods_model = Model('goods');
            $return_goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)));
            $return_goods_list = array_under_reset($return_goods_list, 'goods_id');
            /** @var shequ_return_goodsModel $return_goods_model */
            $return_goods_model = Model('shequ_return_goods');
            $return_goods_rate_list = $return_goods_model->getReturnGoodsList(array('return_goods_id' => array('in', $goods_ids)));
            $return_goods_rate_list = array_under_reset($return_goods_rate_list, 'return_goods_id');
        }

        Tpl::output('show_page', $tuan_config_goods_model->showpage());
        $return_arr = array();
        foreach ($list as $val) {
            $i = array();
            $i['tuan_config_goods_id']  = $val['tuan_config_goods_id'];
            $i['xianshi_name'] = $tuan_config_info['config_tuan_name'];
            $i['goods_name'] = $val['goods_name'];
            $i['goods_price'] = $return_goods_list[$val['goods_id']]['goods_price'];
            $i['return_price'] = isset($return_goods_rate_list[$val['goods_id']]) ? $return_goods_rate_list[$val['goods_id']]['return_money_rate'] * $return_goods_list[$val['goods_id']]['goods_price']: '';
            $i['start_time_text'] = date("Y-m-d H:i:s", $tuan_config_info['config_start_time']);
            $i['end_time_text'] = date("Y-m-d H:i:s", $tuan_config_info['config_end_time']);
            $return_arr[$val['tuan_config_goods_id']] = $i;
        }
        Tpl::output('goods_list', $return_arr);
        Tpl::output('tuan_config_id', $config_tuan_id);
        Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.goods_add');
    }

    /**
     * 活动下的商品
     */
    public function config_tuan_goods_xmlOp() {
        $config_tuan_id = intval($_GET['config_tuan_id']);
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model = Model('shequ_tuan_config');
        $tuan_config_info = $tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $config_tuan_id));
        $condition['tuan_config_id'] = $config_tuan_id;
        /** @var shequ_tuan_config_goodsModel $tuan_config_goods_model */
        $tuan_config_goods_model = Model('shequ_tuan_config_goods');
        $list = $tuan_config_goods_model->getTuanConfigGoodsList($condition, null, 'tuan_config_goods_id desc');
        $goods_ids = array_column($list, 'goods_id');
        $return_goods_rate_list = array();
        $return_goods_list = array();
        if (!empty($goods_ids)) {
            /** @var goodsModel $goods_model */
            $goods_model = Model('goods');
            $return_goods_list = $goods_model->getGoodsList(array('goods_id' => array('in', $goods_ids)));
            $return_goods_list = array_under_reset($return_goods_list, 'goods_id');
            /** @var shequ_return_goodsModel $return_goods_model */
            $return_goods_model = Model('shequ_return_goods');
            $return_goods_rate_list = $return_goods_model->getReturnGoodsList(array('return_goods_id' => array('in', $goods_ids)));
            $return_goods_rate_list = array_under_reset($return_goods_rate_list, 'return_goods_id');
        }

        $data = array();
        $data['now_page'] = $tuan_config_goods_model->shownowpage();
        $data['total_num'] = $tuan_config_goods_model->gettotalnum();
        foreach ($list as $val) {
            $i = array();
            $i['tuan_config_goods_id']  = $val['tuan_config_goods_id'];
            $i['xianshi_name'] = $tuan_config_info['config_tuan_name'];
            $i['goods_name'] = $val['goods_name'];
            $i['goods_price'] = $return_goods_list[$val['goods_id']]['goods_price'];
            $i['return_price'] = isset($return_goods_rate_list[$val['goods_id']]) ? $return_goods_rate_list[$val['goods_id']]['return_money_rate'] * $return_goods_list[$val['goods_id']]['goods_price']: '';
            $i['start_time_text'] = date("Y-m-d H:i:s", $tuan_config_info['config_start_time']);
            $i['end_time_text'] = date("Y-m-d H:i:s", $tuan_config_info['config_end_time']);
            $data['list'][$val['tuan_config_goods_id']] = $i;
        }
        echo '<pre>';
        print_r($data);exit;
        echo Tpl::flexigridXML($data);
        exit;
    }


    /**
     * ajax修改团购信息
     */
    public function ajaxOp(){
        $result = true;
        $update_array = array();
        $where_array = array();

        switch ($_GET['branch']){
         case 'recommend':
            $model= Model('p_xianshi_goods');
            $update_array['xianshi_recommend'] = $_GET['value'];
            $where_array['xianshi_goods_id'] = $_GET['id'];
            $result = $model->editXianshiGoods($update_array, $where_array);
            break;
        }

        if($result) {
            echo 'true';exit;
        } else {
            echo 'false';exit;
        }
    }

    public function goods_selectOp() {
        $tuan_config_id = intval($_GET['tuan_config_id']);
        /** @var shequ_tuan_config_goodsModel $model_tuan_config_goods */
        $model_tuan_config_goods = Model('shequ_tuan_config_goods');
        $config_goods_list = $model_tuan_config_goods->getTuanConfigGoodsList(array('tuan_config_id' => $tuan_config_id));
        $config_goods_ids = array_column($config_goods_list, 'goods_id');
        /** @var shequ_return_goodsModel $model_return_goods */
        $model_return_goods = Model('shequ_return_goods');
        $return_goods_list = $model_return_goods->getReturnGoodsList(array('return_goods_id' => array('not in', $config_goods_ids)),0,'', 'return_goods_id, return_money_rate');
        $goods_ids = array_column($return_goods_list, 'return_goods_id');
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $condition = array();
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        if (empty($goods_ids)) {
            $condition['goods_id'] = array('lt', 0);
        } else {
            $condition['goods_id'] = array('in', $goods_ids);
        }
        $return_goods_list = array_under_reset($return_goods_list, 'return_goods_id');

        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $config_info = $shequ_tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $tuan_config_id));

        if ($config_info['type'] == 2) {
            /** @var storeModel $store_model */
            $store_model = Model('store');
            $store_ids = $store_model->getStoreList(array('is_shequ_tuan' => 1));
            if (!empty($store_ids)) {
                $condition['store_id'] = array('in', array_column($store_ids, 'store_id'));
            } else {
                $condition['store_id'] = -1;
            }
        } else {
            //限制商品类型
            /** @var storeModel $store_model */
            $store_model = Model('store');
            $store_ids = $store_model->getStoreList(array('is_shequ_tuan' => 1));
            if (!empty($store_ids)) {
                $condition['store_id'] = array('not in', array_column($store_ids, 'store_id'));
            }
        }

        $goods_list = $model_goods->getGeneralGoodsOnlineList($condition, '*', 10);
        foreach ($goods_list as $goods_key=>$goods) {
            $goods_list[$goods_key]['goods_return_price'] = 0;
            if (isset($return_goods_list[$goods['goods_id']])) {
                $goods_list[$goods_key]['goods_return_price'] = $return_goods_list[$goods['goods_id']]['return_money_rate'] * $goods['goods_price'];
            }
        }
        Tpl::output('goods_list', $goods_list);
        Tpl::output('tuan_config_id', $tuan_config_id);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::setDirquna('shop');
        Tpl::showpage('tuan_config.goods_add_list', 'null_layout');
    }

    public function save_tuan_config_goodsOp() {
        $goods_id = intval($_GET['goods_id']);
        $tuan_config_id = intval($_GET['tuan_config_id']);
        /** @var shequ_return_goodsModel $model_return_goods */
        $model_return_goods = Model('shequ_return_goods');
        $return_goods_info = $model_return_goods->getReturnGoodsInfo(array('return_goods_id' => $goods_id));
        if (!$return_goods_info) {
            showMessage('该商品还不能分销');
        }
        //检测改团里是否存在
        /** @var shequ_tuan_config_goodsModel $model_tuan_config_goods */
        $model_tuan_config_goods = Model('shequ_tuan_config_goods');
        $exist_info = $model_tuan_config_goods->getTuanConfigGoodsInfo(array('tuan_config_id' => $tuan_config_id, 'goods_id' => $goods_id));
        if ($exist_info) {
            showMessage('已经添加过了');
        }
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfo(array('goods_id' => $goods_id));
        $insert_data = array(
            'tuan_config_id' => $tuan_config_id,
            'goods_id' => $goods_info['goods_id'],
            'store_id' => $goods_info['store_id'],
            'goods_name' => $goods_info['goods_name'],
            'goods_image' => $goods_info['goods_image'],
            'gc_id' => $goods_info['gc_id'],
        );
        $model_tuan_config_goods->addTuanConfigGoods($insert_data);
        showMessage('成功！');
    }


    /**
     * 页面内导航菜单
     *
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function show_menu($menu_key) {
        $menu_array = array(
            'config_tuan_list'=>array('menu_type'=>'link','menu_name'=>'社区团购活动列表','menu_url'=>'index.php?act=tuan_config&op=config_tuan_list'),
        );
        $menu_array[$menu_key]['menu_type'] = 'text';
        Tpl::output('menu',$menu_array);
    }

}
