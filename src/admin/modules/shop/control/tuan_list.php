<?php
/**
 * 接龙管理
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class tuan_listControl extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }

    private $links = array(
        array('url' => 'act=tuan_list&op=index', 'text' => '团长审核列表'),
        array('url' => 'act=tuan_list&op=tuan', 'text' => '接龙列表'),
    );

    public function indexOp()
    {
        Tpl::setDirquna('shop');
        Tpl::output('top_link', $this->sublink($this->links, $_GET['op']));
        Tpl::showpage('tuanzhang.list');
    }

    public function tuanlist_xmlOp()
    {
        $page = $_POST['rp'];
        $model_complain = Model('shequ_tuanzhang');
        $tuan_list = Model('shequ_tuanzhang')->getList($condition, $page, $order, '*');
        $data = array();
        $data['now_page'] = $model_complain->shownowpage();
        $data['total_num'] = $model_complain->gettotalnum();
        foreach ($tuan_list as $tuan_info) {
            $list = array();
//            if ($tuan_info['state'] == 0) {
//                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=tuan_list&op=change_state&id={$tuan_info['id']}\"><i class=\"fa fa-list-alt\"></i>待审核</a>";
//            } else {
//                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=tuan_list&op=change_state&id={$tuan_info['id']}\"><i class=\"fa fa-gavel\"></i>已审核</a>";
//            }
            if ($tuan_info['state'] == 0) {
                $list['operation'] = "<a class=\"btn orange\" href=\"index.php?act=tuan_list&op=change_state&id={$tuan_info['id']}\"><i class=\"fa fa-list-alt\"></i>查看详情</a>";
            }
            $list['name'] = $tuan_info['name'];
            $list['phone'] = $tuan_info['phone'];
            $list['sn'] = $tuan_info['sn'];
            $pic_base_url = UPLOAD_SITE_URL.DS;
            if (!empty($tuan_info['sn_image1'])) {
                $list['sn_image1'] .= "<a href='" . $pic_base_url . $tuan_info['sn_image1'] . "' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=" . $pic_base_url . $tuan_info['sn_image1'] . ">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['sn_image1'] = " ";
            }
            if (!empty($tuan_info['sn_image2'])) {
                $list['sn_image1'] .= "<a href='" . $pic_base_url . $tuan_info['sn_image2'] . "' target='_blank' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src=" . $pic_base_url . $tuan_info['sn_image2'] . ">\")'><i class='fa fa-picture-o'></i></a> ";
            } else {
                $list['sn_image2'] = " ";
            }
            $list['type'] = $model_complain::getType($tuan_info['type']);
            $list['category'] = $model_complain::getCate($tuan_info['category']);
            $list['store_name'] = $tuan_info['store_name'];
            $list['zhandui'] = $tuan_info['zhandui'];
            $list['area'] = $tuan_info['area'];
            $list['street'] = $tuan_info['street'];
            $list['community'] = $tuan_info['community'];
            $list['address'] = $tuan_info['address'];
            $list['bank_name'] = $tuan_info['bank_name'];
            $list['bank_sn'] = $tuan_info['bank_sn'];
            $data['list'][$tuan_info['id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }

    public function change_stateOp()
    {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            $id = intval($_POST['id']);
        }
        if ($id <= 0) {
            showMessage("参数错误", 'index.php?act=tuan_list&op=index', '', 'error');
        }
        $model_tuan_zhang = Model('shequ_tuanzhang');
        $info = $model_tuan_zhang->getOne(array('id' => "{$id}"));
        if (empty($info)) {
            showMessage(Language::get('param_error'), 'index.php?act=tuan_list&op=index', 'html', 'error');
        }
        if (chksubmit()) {
            $model_tuan_zhang->edit(array('id' => $id), array('state' => '1', 'update_time' => time()));
            if ($model_tuan_zhang) {
                showMessage("审核成功", 'index.php?act=tuan_list&op=index', 'succ');
            } else {
                showMessage("审核成功", 'index.php?act=tuan_list&op=index', 'error');
            }
        } else {
            $info['type'] = $model_tuan_zhang::getType($info['type']);
            $info['state'] = $model_tuan_zhang::getState($info['state']);
            $info['category'] = $model_tuan_zhang::getCate($info['category']);
            TPL::output('info', $info);
            Tpl::setDirquna('shop');
            Tpl::showpage('tuan.state');
        }

    }


    public function tuanOp()
    {

        Tpl::setDirquna('shop');
        Tpl::output('top_link', $this->sublink($this->links, $_GET['op']));
        Tpl::showpage('tuan.list');
    }

    public function tuan_order_xmlOp()
    {
        $page = $_POST['rp'];
        $model_shequ_tuan = Model('shequ_tuan');
        $order = "id desc";
        $tuan_list = Model('shequ_tuan')->getList($condition, $page, $order, '*');
        $data = array();
        $data['now_page'] = $model_shequ_tuan->shownowpage();
        $data['total_num'] = $model_shequ_tuan->gettotalnum();
        foreach ($tuan_list as $tuan_info) {
            $list = array();
            $list['operation'] = '';
            if ($tuan_info['type'] == '2')
                $list['operation'] = "<a class=\"btn orange confirm-on-click\" href=\"index.php?act=tuan_list&op=yjfh&id={$tuan_info['id']}\"><i class=\"fa fa-gavel\"></i>一键发货</a>";
            $list['operation'] .= "<a class=\"btn orange\" href=\"index.php?act=order&shequ_tuan_id={$tuan_info['id']}\"><i class=\"fa fa-list-alt\"></i>查看订单</a>";
            $list['name'] = $tuan_info['name'];
            $list['start_time'] = $tuan_info['start_time'] ? date('Y-m-d H:i:s', $tuan_info['start_time']) : ' ';
            $list['end_time'] = $tuan_info['end_time'] ? date('Y-m-d H:i:s', $tuan_info['end_time']) : ' ';
            $list['type'] = $model_shequ_tuan::getType($tuan_info['type']);
            $list['address'] = $tuan_info['address'];
            $list['longitude'] = $tuan_info['longitude'];
            $list['latitude'] = $tuan_info['latitude'];
            $list['amount'] = ncPriceFormat($tuan_info['amount']);
            $list['num'] = $tuan_info['num'];
            $list['commis'] = ncPriceFormat($tuan_info['commis']);
            $list['refund'] = ncPriceFormat($tuan_info['refund']);
            $list['refund_commis'] = ncPriceFormat($tuan_info['refund_commis']);
            $data['list'][$tuan_info['id']] = $list;
        }
        //  p($data['list']);
        exit(Tpl::flexigridXML($data));
    }

    public function yjfhOp()
    {
        //获取当前shequ_tuan_id下的所有订单;
        /** @var  $model_order */
        $model_order = Model('order');
        $model_refund_return = Model('refund_return');
        $condition['shequ_tuan_id'] = trim($_GET['id']);
        $condition['order_state'] = ORDER_STATE_PAY;
        $condition['refund_state'] = 0;
        $condition['lock_state'] = 0;
        $condition['delete_state'] = 0;
        $name = $this->admin_info['name'] ? $this->admin_info['name'] : "系统管理员";
        $order_list = $model_order->getOrderList($condition, " ", "*", "order_id desc", "", array('order_common'));
        foreach ($order_list as $v) {
            $if_store_send = $model_order->getOrderOperateState('store_send', $v);
            if ($if_store_send) {  //可以发货  设置发货状态
                $res = Logic('order')->changeOrderSend($v, 'admin', $name, $v['extend_order_common']);
            }
        }
        showMessage('操作成功', "index.php?act=tuan_list&op=tuan", '', 'succ');
    }

    public function uploadOp()
    {
        set_time_limit(0);
        if(!empty($_FILES)){
            $data = array();
            $file = $_FILES['file'];
            /**
             * 错误处理
             */
          if($file['error']>0){
                $data['state'] = false;
                $data['msg'] = '文件上传 错误';
                echo json_encode($data);
                die();
            }
            if(empty($file['name'])){
                $data['state'] = false;
                $data['msg'] = '请选择上传文件';
                echo json_encode($data);
                die();
            }
            if(!is_uploaded_file($file['tmp_name'])){
                $data['state'] = false;
                $data['msg'] = '文件不合法';
            }
            $ext_name = explode('.',$file['name']);
            $curFileType = $ext_name[count($ext_name)-1];
           if(!in_array($curFileType,array('csv','xls','xlsx'))){
                $data['state'] = false;
                $data['msg'] = '请上传csv,xls,xlsx文件';
                echo json_encode($data);
                die();
            }
            if($file['size']> intval(ini_get('upload_max_filesize')*1024*1024)){
                $data['state'] =  false;
                $data['msg'] = '文件过大';
                echo json_encode($data);
                die();
            }
            /**
             * 开始上传
             */
            $dir = BASE_UPLOAD_PATH.DS."tuanzhang".DS."tuanzhang_excel".DS;
            if(!is_dir($dir)){
                @mkdir(iconv("UTF-8","GBK",$dir),0777,true);
            }
            $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
            if(move_uploaded_file($file['tmp_name'],$fileName)){
                $model_order = Model('order');
                $admin_name = $this->admin_info['name'];
                $result = $model_order->shequbulkShipment($fileName,$admin_name);
                if(empty($result['state'])||$result['state']==false){
                    $data['state'] = false;
                    $data['msg'] = $result['msg'];
                    echo json_encode($data);
                    die();
                }
   /*             Array
                (
                    [totals] => 2
    [succNum] => 2
    [failNum] => 0
    [state] => 1
    [failOrderids] => Array
                (
                )

                [data] => Array
                (
                    [0] => Array
                    (
                    [0] => 181130104740495001
                    [1] => 邮政包裹
                    [2] => 123654789
                    [3] =>
                    [feedback] => 发货成功；
                    [feeback] => 【发货备注为空，未更改】
                )

            [1] => Array
                (
                    [0] => 181130104929328001
                    [1] => 邮政包裹
                    [2] => 789654123
                    [3] =>
                    [feedback] => 发货成功；
                [feeback] => 【发货备注为空，未更改】
                )

        )

)*/
                $data['state'] = true;
                $current_time = uniqid();
                $key_name = md5('feedback'.$current_time);
                $data['key_name'] = $key_name;
                $data['msg']='您已经成功完成'.$result['succNum'].'条订单发货，点击“确定”，下载反馈信息表!';
                wkcache($key_name,serialize($result['data']),360);
                echo json_encode($data);
                die();
            }else{
                $data['state'] = false;
                $data['msg'] = '文件权限不够';
                echo json_encode($data);
                die();
            }
        }

    }

    /**
     *批量出货反馈信息
     * @param $datas
     */
    public function export_excel_resultOp()
    {
        set_time_limit(0);
        ini_set('memory_limit','2G');
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('批量出货反馈表');
        $objExcel->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('D')
            ->setWidth(70);
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $objActSheet->setCellValue("A1", 'order_id');
        $objActSheet->setCellValue("B1", 'logi_name');
        $objActSheet->setCellValue("C1", 'logi_no');
        $objActSheet->setCellValue("D1", 'remark');
        $objActSheet->setCellValue("E1", '导入反馈结果');
        $k=2;
        $key_name=$_GET['key_name'];
        $data=unserialize(rkcache($key_name));
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v[0]);
            $objActSheet->setCellValue("B" . $k, $v[1]);
            $objActSheet->setCellValue("C" . $k, $v[2]);
            $objActSheet->setCellValue("d" . $k, $v[3]);
            $objActSheet->setCellValue("E" . $k, $v['feedback']);
            $k++;
        }
        $outfile =  '批量发货反馈表-' . date('Y-m-d') . '.xls';
        // export to exploer
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
        exit();
    }


}