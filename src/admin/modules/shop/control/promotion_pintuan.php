<?php
/**
 * 拼团管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class promotion_pintuanControl extends SystemControl{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 默认Op
     */
    public function indexOp() {

        $this->pintuan_listOp();

    }

    /**
     * 店铺拼团列表
     */
    public function pintuan_listOp()
    {
        $model_pintuan = Model('p_pintuan');
        Tpl::output('pintuan_state_array', $model_pintuan->getPintuanStateArray());

        $this->show_menu('pintuan_list');
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('promotion_pintuan.list');
    }

    /**
     * 商家活动列表
     */
    public function pintuan_list_xmlOp()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['pintuan_name']))) {
                $condition['pintuan_name'] = array('like', '%' . $q . '%');
            }
            if (strlen($q = trim((string) $_REQUEST['store_name']))) {
                $condition['store_name'] = array('like', '%' . $q . '%');
            }
            if (($q = (int) $_REQUEST['state']) > 0) {
                $condition['state'] = $q;
            }

            $pdates = array();
            if (strlen($q = trim((string) $_REQUEST['pdate1'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "end_time >= {$q}";
            }
            if (strlen($q = trim((string) $_REQUEST['pdate2'])) && ($q = strtotime($q . ' 00:00:00'))) {
                $pdates[] = "start_time <= {$q}";
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
                    case 'pintuan_name':
                        $condition['pintuan_name'] = array('like', '%'.$q.'%');
                        break;
                    case 'store_name':
                        $condition['store_name'] = array('like', '%'.$q.'%');
                        break;
                }
            }
        }

        $model_pintuan = Model('p_pintuan');
        $pintuan_list = (array) $model_pintuan->getPinTuanList($condition, $_REQUEST['rp'], 'state desc, end_time desc');

        $flippedOwnShopIds = array_flip(Model('store')->getOwnShopIds());

        $data = array();
        $data['now_page'] = $model_pintuan->shownowpage();
        $data['total_num'] = $model_pintuan->gettotalnum();

        foreach ($pintuan_list as $val) {
            $o  = '<a class="btn red confirm-on-click" href="javascript:;" data-href="' . urlAdminShop('promotion_pintuan', 'pintuan_del', array(
                'pintuan_id' => $val['pintuan_id'],
            )) . '"><i class="fa fa-trash-o"></i>删除</a>';

            $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';

            if ($val['editable']) {
                $o .= '<li><a class="confirm-on-click" href="javascript:;" data-href="' . urlAdminShop('promotion_pintuan', 'pintuan_cancel', array(
                    'pintuan_id' => $val['pintuan_id'],
                )) . '">取消活动</a></li>';
            }

            $o .= '<li><a class="confirm-on-click" href="' . urlAdminShop('promotion_pintuan', 'pintuan_detail', array(
                'pintuan_id' => $val['pintuan_id'],
            )) . '">活动详细</a></li>';

            $o .= '</ul></span>';

            $i = array();
            $i['operation'] = $o;
            $i['pintuan_id'] = $val['pintuan_id'];
            $i['pintuan_name'] = $val['pintuan_name'];
            $i['store_name'] = '<a target="_blank" href="' . urlShop('show_store', 'index', array(
                'store_id'=>$val['store_id'],
            )) . '">' . $val['store_name'] . '</a>';

            if (isset($flippedOwnShopIds[$val['store_id']])) {
                $i['store_name'] .= '<span class="ownshop">[自营]</span>';
            }

            $i['start_time_text'] = date('Y-m-d H:i', $val['start_time']);
            $i['end_time_text'] = date('Y-m-d H:i', $val['end_time']);
            $i['limit_time'] = $val['limit_time']/3600;
            $i['limit_user'] = $val['limit_user'];
            $i['minimum_user'] = $val['minimum_user'];

            $i['limit_floor'] = $val['limit_floor'];
            $i['limit_ceilling'] = $val['limit_ceilling'];
            $i['limit_total'] = $val['limit_total'];
            $i['pintuan_state_text'] = $val['pintuan_state_text'];

            $data['list'][$val['pintuan_id']] = $i;
        }

        echo Tpl::flexigridXML($data);
        exit;
    }

    /**
     * 拼团活动取消
     **/
    public function pintuan_cancelOp() {
        $pintuan_id = intval($_REQUEST['pintuan_id']);
        $model_pintuan = Model('p_pintuan');
        $result = $model_pintuan->cancelPinTuan(array('pintuan_id' => $pintuan_id));
        if($result) {
            $this->log('取消拼团活动，活动编号'.$pintuan_id);

            $this->jsonOutput();
        } else {
            $this->jsonOutput('操作失败');
        }
    }

    /**
     * 拼团活动删除
     **/
    public function pintuan_delOp() {
        $pintuan_id = intval($_REQUEST['pintuan_id']);
        $model_pintuan = Model('p_pintuan');
        $result = $model_pintuan->delPinTuan(array('pintuan_id' => $pintuan_id));
        if($result) {
            $this->log('删除拼团活动，活动编号'.$pintuan_id);

            $this->jsonOutput();
        } else {
            $this->jsonOutput('操作失败');
        }
    }

    /**
     * 活动详细信息
     **/
    public function pintuan_detailOp() {
        $pintuan_id = intval($_GET['pintuan_id']);

        $model_pintuan = Model('p_pintuan');
        $model_pintuan_goods = Model('p_pintuan_goods');

        $pintuan_info = $model_pintuan->getPinTuanInfoByID($pintuan_id);
        if(empty($pintuan_info)) {
            showMessage(L('param_error'));
        }
        Tpl::output('pintuan_info', $pintuan_info);

        //获取拼团商品列表
        $condition = array();
        $condition['pintuan_id'] = $pintuan_id;
        $pintuan_goods_list = $model_pintuan_goods->getPinTuanGoodsExtendList($condition);
        Tpl::output('list', $pintuan_goods_list);

        $this->show_menu('pintuan_detail');
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('promotion_pintuan.detail');
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
            'pintuan_list'=>array('menu_type'=>'link','menu_name'=>'拼团列表','menu_url'=>'index.php?act=promotion_pintuan&op=index'),
            'pintuan_detail'=>array('menu_type'=>'link','menu_name'=>'拼团','menu_url'=>'index.php?act=promotion_pintuan&op=pintuan_detail'),
        );
        if($menu_key != 'pintuan_detail') unset($menu_array['pintuan_detail']);
        $menu_array[$menu_key]['menu_type'] = 'text';
        Tpl::output('menu',$menu_array);
    }

}
