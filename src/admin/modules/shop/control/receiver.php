<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-14
 * Time: 17:22
 */
/**
 * 领卡人
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');

class receiverControl extends SystemControl{

    public function __construct()
    {
        parent::__construct();
    }

    public function indexOp()
    {
        Tpl::setDirquna('shop');
        Tpl::showpage('receiver.index');
    }

    protected function getConditionAndSort()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            foreach (array('sn', 'batchflag', 'admin_name', ) as $sk) {
                if (strlen($q = trim((string) $_REQUEST[$sk]))) {
                    $condition[$sk] = array('like', '%' . $q . '%');
                }
            }
            if (strlen($q = trim((string) $_REQUEST['member_name']))) {
                $condition['member_name'] = $q;
            }
            if (strlen($q = trim((string) $_REQUEST['state']))) {
                $condition['state'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['disabled']))) {
                $condition['disabled'] = (int) $q;
            }

            $sdate = $_GET['sdate'] ? strtotime($_GET['sdate'] . ' 00:00:00') : 0;
            $edate = $_GET['edate'] ? strtotime($_GET['edate'] . ' 00:00:00') : 0;
            if ($sdate > 0 || $edate > 0) {
                $condition['tscreated'] = array('time', array($sdate, $edate));
            }
            $sdate1 = $_GET['sdate1'] ? strtotime($_GET['sdate1'] . ' 00:00:00') : 0;
            $edate = $_GET['edate'] ? strtotime($_GET['edate'] . ' 00:00:00') : 0;
            if ($sdate1 > 0 || $edate > 0) {
                $condition['actived'] = array('time', array($sdate1, $edate));
            }

            $sdate = $_GET['sdate2'] ? strtotime($_GET['sdate2'] . ' 00:00:00') : 0;
            $edate = $_GET['edate2'] ? strtotime($_GET['edate2'] . ' 00:00:00') : 0;
            if ($sdate > 0 || $edate > 0) {
                $condition['tsused'] = array('time', array($sdate, $edate));
            }

        } else {
            if (strlen($q = trim($_REQUEST['query']))) {
                switch ($_REQUEST['qtype']) {
                    case 'sn':
                    case 'batchflag':
                    case 'admin_name':
                        $condition[$_REQUEST['qtype']] = array('like', '%' . $q . '%');
                        break;
                    case 'member_name':
                        $condition[$_REQUEST['qtype']] = $q;
                        break;
                }
            }
        }

        switch ($_REQUEST['sortname']) {
            case 'denomination':
            case 'tscreated':
            case 'tsused':
                $sort = $_REQUEST['sortname'];
                break;
            default:
                $sort = 'id';
                break;
        }
        if ($_REQUEST['sortorder'] != 'asc') {
            $sort .= ' desc';
        }

        return array(
            $condition,
            $sort,
        );
    }

    public function index_xmlOp(){
        list($condition, $sort) = $this->getConditionAndSort();
        $model = Model('rechargecard');
        $list = $model->getReceiverList();

        $data = array();
        $data['now_page'] = $model->shownowpage();
        $data['total_num'] = $model->gettotalnum();

        foreach ($list as $val) {
            $i = array();
            $operation = '';
            $i['operation'] = $operation;
            $i['sn'] = $val['sn'];
            $i['receiver'] = $val['receiver'];
            $data['list'][$val['id']] = $i;
        }

        echo Tpl::flexigridXML($data);
        exit;
    }

    public function receiver_listOp(){
        $receiver_list = Model('rechargecard')->getAllReceiverList();
        if(chksubmit()){
            if(!empty($_POST['receiver_sn'])){
                $receiver_sn_array    = $_POST['receiver_sn'];

                $res = Model('rechargecard')->setReceiverListStatus($receiver_sn_array);

                if($res){
                    showDialog(L('nc_common_op_succ'), '', 'succ', '$("#flexigrid").flexReload();CUR_DIALOG.close();');
                } else {
                    showDialog('失敗', '', 'succ', '$("#flexigrid").flexReload();CUR_DIALOG.close();');
                }
            }
        }

        Tpl::output('receiver_list', $receiver_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('receiver.list','null_layout');
    }



    public function add_receiverOp(){
        if(chksubmit()){

            $obj_validate = new Validate();
            $validate_arr[] = array("input"=>$_POST["sn"],"require"=>"true","message"=>'请提供领卡人标识');
            $validate_arr[] = array("input"=>$_POST["receiver"],"require"=>"true","message"=>'请提供领卡人名称');

            $obj_validate->validateparam = $validate_arr;
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage(Language::get('error').$error,'','','error');
            }

            $sn = intval($_POST['sn']);
            $receiver = trim($_POST['receiver']);

            $data = array();
            $data['sn'] = $sn;
            $data['receiver'] = $receiver;

            $modelRechargecard = Model('rechargecard');
            if(!$modelRechargecard->isReceiverExist($sn)){
                $result = Model('rechargecard')->addReceiver($data);
            } else {
                $result = Model('rechargecard')->updateReceiver($sn,$data);
            }
            showDialog(L('nc_common_op_succ'), '', 'succ', '$("#flexigrid").flexReload();CUR_DIALOG.close();');
        }
        Tpl::setDirquna('shop');
        Tpl::showpage('receiver.add','null_layout');
    }


}