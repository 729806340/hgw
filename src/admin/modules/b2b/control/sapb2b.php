<?php
defined('ByShopWWI') or exit('Access Invalid!');

class sapb2bControl extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }

    public function indexOp()
    {
        Tpl::output('sap_setting', base::load_config('sap', 'setting'));
        Tpl::setDirquna('b2b');
        Tpl::showpage('sapb2b.index');
    }

    public function get_xmlOp()
    {
        $model_log = Model('sapb2b_log');
        $where = array();
        $input_name = $_POST['qtype'];
        $input_value = $_POST['query'];
        $sort_name = $_POST['sortname'];
        $sort_order = $_POST['sortorder'] == 'desc' ? 'desc' : '';

        if (!empty($input_value)) {
            if ($input_name == 'code' || $input_name == 'method' || $input_name == 'log_id') {
                $where[$input_name] = $input_value;
            } elseif ($input_name == 'data' || $input_name == 'rel') {
                $where[$input_name] = array('like', "%{$input_value}%");
            }
        }
        
        //排序
        $order = 'log_id desc';
        if (in_array($sort_name, array('log_id', 'code', 'method'))) {
            $order = $sort_name . ' ' . $sort_order;
        }

        if(!empty($_GET['sap_search'])&&$_GET['sap_search']=="gsearch"){
            $where=$this->_get_sap_condition($where);
        }
        $page = $_POST['rp'];
        $list = $model_log->where($where)->page($page)->order($order)->select();
        $data = array();
        $data['now_page'] = $model_log->shownowpage();
        $data['total_num'] = $model_log->gettotalnum();
        foreach ($list as $value) {
            $param = array();
            $param['operation'] = "<a class='btn green' href='index.php?act=sap&op=log_show&log_id=" . $value['log_id'] . "'><i class='fa fa-list-alt'></i>查看</a>
            <a href='javascript:submit_delete(" . $value['log_id'] . ");' class='btn red'><i class='fa fa-trash-o'></i>删除</a>";
            $param['log_id'] = $value['log_id'];
            $param['code'] = $value['code'];
            $param['method'] = $value['method'];
            $param['data'] = '<pre>' . mb_substr($value['data'], 0, 30) . '</pre>';
            $param['rel'] = '<pre>' . mb_substr(htmlspecialchars($value['rel']), 0, 30) . '</pre>';
            $param['error'] = '<pre>' . $value['error'] . '</pre>';
            $param['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $data['list'][$value['log_id']] = $param;
        }
        echo Tpl::flexigridXML($data);
        exit();
    }

    function  _get_sap_condition($condition){
        if(!empty($_GET['log_id']) && trim($_GET['log_id'])!=''){
            $condition['log_id']=trim($_GET['log_id']);
        }
        if(!empty($_GET['sap_data']) && trim($_GET['sap_data'])!=''){
            $condition['data']= array('like', '%'.$_GET['sap_data'].'%');
        }
        if(!empty($_GET['sap_rel']) && trim($_GET['sap_rel'])!=''){
            $condition['rel']= array('like', '%'.$_GET['sap_rel'].'%');
        }
        if(!empty($_GET['sap_code']) && trim($_GET['sap_code'])!=''){
            $condition['code']=trim($_GET['sap_code']);
        }
        if(!empty($_GET['sap_method']) && trim($_GET['sap_method'])!=''){
            $condition['method']=trim($_GET['sap_method']);
        }
        return $condition;
    }

    public function log_delOp()
    {
        if (!empty($_GET['log_id'])) {
            $ids = explode(',', $_GET['log_id']);
            Model('sap_log')->where(array('log_id' => array('in', $ids)))->delete();
            showMessage('删除成功', 'index.php?act=sap');
        } else {
            showMessage('删除失败', 'index.php?act=sap');
        }
    }

    public function log_showOp()
    {
        $condition['log_id'] = intval($_GET['log_id']);
        $log_info = Model('sap_log')->where($condition)->find();
        Tpl::output('log_info', $log_info);
        Tpl::setDirquna('system');
        Tpl::showpage('sapb2b.show');
    }

    //清理日志
    public function clearOp()
    {
        Model('sap_log')->clearLog();
        showMessage('清理成功', 'index.php?act=sap');
    }

    //重置状态
    public function resetOp()
    {
        //重置sap101
        Model('goods')->where(array('send_sap' => '1'))->update(array('send_sap' => '0'));
        //重置sap102
        Model('goods')->where(array('edit_sap' => '1'))->update(array('edit_sap' => '0'));
        //重置sap201
        Model('store')->where(array('send_sap' => '1'))->update(array('send_sap' => '0'));
        //重置sap202
        Model('store')->where(array('edit_sap' => '1'))->update(array('edit_sap' => '0'));
        //重置sap301
        Model('orders')->where(array('send_sap' => '1'))->update(array('send_sap' => '0'));

        showMessage('重置成功', 'index.php?act=sap');
    }

    //重推  测试用  上线后必须删除该方法
    public function resendOp()
    {
        //重置sap101
        Model('goods')->where(array('send_sap' => '2'))->update(array('send_sap' => '0'));
        //重置sap201
        Model('store')->where(array('send_sap' => '2'))->update(array('send_sap' => '0'));
        //重置sap301
        Model('orders')->where(array('send_sap' => '2'))->update(array('send_sap' => '0'));
        //重置sap401
        Model('orders')->where('make_send_time > 0')->update(array('make_send_time' => '0'));
        //重置sap402
        Model('refund_return')->where(array('send_sap' => '2'))->update(array('send_sap' => '0'));

        showMessage('重置成功', 'index.php?act=sap');
    }
}