<?php
/**
 * 店铺管理界面
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');

class punishControl extends SystemControl
{
    const EXPORT_SIZE = 1000;

    public function __construct()
    {
        parent::__construct();
        Language::read('store,store_grade');
    }

    public function indexOp()
    {
        $this->punishOp();
    }

    /**
     * 店铺
     */
    public function punishOp()
    {
        //店铺等级

        /** @var member_fenxiaoModel $fenxiaoModel */
        $fenxiaoModel = Model('member_fenxiao');
        $member_fenxiao = $fenxiaoModel->getMemberFenxiao();
        Tpl::output('member_fenxiao', $member_fenxiao);

        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('punish.index');
    }


    /**
     * 输出XML数据
     */
    public function get_xmlOp()
    {
        /** @var storeModel $model_store */
        $model_store = Model('store');
        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        // 设置页码参数名称
        $condition = array();
        $storeName = $_POST['query'];
        if ($_GET['store_name'] != '') {
            $storeName = $_GET['store_name'];
        }

        if ($storeName != '') {
            $stores = $model_store->getStoreList(array('store_name' => array('like', '%' . $storeName . '%')));
            if (empty($stores)) {
                $condition['store_id'] = 0;
            } else {
                $condition['store_id'] = array('in', array_column($stores, 'store_id'));
            }
        }
        if ($_GET['fx_order_id'] != '') {
            $condition['fx_order_id'] = array('like', '%' . $_GET['fx_order_id'] . '%');
        }
        if ($_GET['channel_id'] != '') {
            $condition['channel_id'] = $_GET['channel_id'];
        }
        $order = '';
        $param = array('cost_id');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }

        $page = $_POST['rp'];

        //店铺列表
        $store_cost_list = $model_store_cost->getStoreCostList($condition, $page, $order);

        $storeList = $model_store->getStoreList(array('store_id' => array('in', array_column($store_cost_list, 'cost_store_id'))));
        $storeList = array_under_reset($storeList, 'store_id');
        $data = array();
        $data['now_page'] = $model_store->shownowpage();
        $data['total_num'] = $model_store->gettotalnum();
        foreach ($store_cost_list as $value) {
            $store = $storeList[$value['cost_store_id']];
            $param = array();
            $operation = "";
            $param['operation'] = $operation;
            $param['store_id'] = $value['cost_store_id'];
            $param['store_name'] = $store['store_name'];
            $param['cost_price'] = $value['cost_price'];
            $param['cost_remark'] = $value['cost_remark'];
            $param['fx_order_id'] = $value['fx_order_id'];
            $param['channel_name'] = $value['channel_name'];
            $param['cost_time'] = date('Y-m-d H:i:s',$value['cost_time']);
            $param['send_sap'] = $value['send_sap'];
            $param['purchase_sap'] = $value['purchase_sap'];
            $param['errInf'] = $value['errInf'];
            $param['check_result'] = $value['check_result'];
            $param['check_status'] = $value['check_status'];
            $data['list'][$value['cost_id']] = $param;
        }
        echo Tpl::flexigridXML($data);
        exit();
    }
    public function import1Op()
    {
        $lang = Language::getLangContent();

        /** @var storeModel $model_store */
        $model_store = Model('store');


        /** @var member_fenxiaoModel $fenxiaoModel */
        $fenxiaoModel = Model('member_fenxiao');
        $member_fenxiao = $fenxiaoModel->getMemberFenxiao();
        Tpl::output('member_fenxiao' , $member_fenxiao);

        Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('punish.import','null_layout');
    }


    public function importOp(){
        set_time_limit(0);
        if(false&&empty($_POST)){
            $data['state'] = false;
            $data['msg'] = '上传数据为空';
            die(json_encode($data));
        }
        $data = array();
        $file	= $_FILES['file'];
        /**
         * 上传错误
         */
        if ($file['error'] > 0) {
            //showMessage('文件上传出错', '', 'html', 'error');
            $data['state'] = false;
            $data['msg'] = '文件上传错误';
            echo json_encode($data);
            die();
        }
        /**
         * 上传文件存在判断
         */
        if(empty($file['name'])){
            //showMessage('请选择上传文件','','html','error');
            $data['state'] = false;
            $data['msg'] = '请选择上传文件';
            echo json_encode($data);
            die();
        }
        /**
         * 文件来源判定
         */
        if(!is_uploaded_file($file['tmp_name'])){
            //showMessage('文件不合法','','html','error');
            $data['state'] = false;
            $data['msg'] = '文件不合法';
            echo json_encode($data);
            die();
        }
        /**
         * 文件类型判定
         */
        $file_name_array	= explode('.',$file['name']);
        $curFileType = $file_name_array[count($file_name_array) - 1];
        if (!in_array(strtolower($curFileType), array('csv','xls','xlsx'))) {
            //showMessage('文件类型不合法'.$file_name_array[count($file_name_array)-1],'','html','error');
            $data['state'] = false;
            $data['msg'] = '请上传csv/xls/xlsx文件';
            echo json_encode($data);
            die();
        }
        /**
         * 文件大小判定
         */
        if($file['size'] > intval(ini_get('upload_max_filesize'))*1024*1024){
            //showMessage('文件过大','','html','error');
            $data['state'] = false;
            $data['msg'] = '文件大小不可以超过'.ini_get('upload_max_filesize')."M";
            echo json_encode($data);
            die();
        }
        /**
         * 开始上传
         */
        $dir = BASE_UPLOAD_PATH.DS.'admin'.DS.'temp'.DS;
        if(!is_dir($dir)){
            @mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
        }
        $fileName = $dir.date('Y').date('m').date('d').time().'.'.$curFileType;
        if (move_uploaded_file($file['tmp_name'], $fileName)) {
            $result = $this->_importPunish($fileName);
            if(!empty($result['state'])){
                $data['state'] = false;
                $data['msg'] = $result['msg'];
                echo json_encode($data);
                die();
            }
            $data['state'] = true;
            $data['result'] = $result;
            die(json_encode($data));
        }
        if(false){
        }
        $data['state'] = true;
        $data['result'] = array('total'=>10,'success'=>5,'fail'=>array(123,123,123),'errorMsg'=>array(),);
        echo json_encode($data);
        die();
    }


    private function _importPunish($filePath)
    {
        if(!is_file($filePath)){
            return callback(false , '文件不存在');
        }
        $data = $this->_excelToArray($filePath);
        if(!count($data) > 1){
            return callback(false ,'数据有误');
        }
        /* 检查文件格式是否正确 */
        $title = $data[0];
        if($title[0]!='罚款金额'||$title[1]!='店铺ID'||$title[2]!='罚款原因'){
            return array('state'=>'false','msg'=>'文件格式错误');
        }
        unset($data[0]);
        $res = array('total'=>0,'success'=>0,'fail'=>array(),'errorMsg'=>array());
        $tasks = array();
        /** @var orderModel $orderModel */
        $orderModel = Model('order');

        /** @var member_fenxiaoModel $fenxiaoMember */
        $fenxiaoMember = Model('member_fenxiao');
        $channels = $fenxiaoMember->getMemberFenxiao();
        $channels = array_under_reset($channels,'member_en_code');
        // 整理数据
        foreach ($data as $k=>$v){
            foreach ($v as $key=>$value) $v[$key] = trim(trim($value,'*'));
            if(!is_numeric($v[0])) break;
            $channel = isset($channels[$v[4]])?$channels[$v[4]]:array('member_id'=>0,'member_en_code'=>'','member_cn_code'=>'');
            $res['total']+=1;
            $item = array('row'=>'第'.($k+1).'行');
            $item['cost_price'] = $v[0];
            $item['cost_store_id'] = $v[1];
            $item['cost_remark'] = $v[2];
            $item['fx_order_id'] = !empty($v[3])?$v[3]:'0';
            $item['channel_id'] = $channel['member_id'];
            $item['channel_name'] = $channel['member_cn_code'];
            $tasks[] = $item;
        }
        /** @var memberModel $memberModel */
        $memberModel = Model('member');
        foreach ($tasks as $k=>$task){
            if(true === ($result = $this->_addPunish($task)))
                $res['success']+=1;
            else{
                $res['fail'][] = $task['row'];
                $res['errorMsg'][] = $task['row'].' : '.$result;
            }
        }
        return $res;
    }

    private function _addPunish($task)
    {
        /** @var store_costModel $costModel */
        $costModel = Model('store_cost');
        if(empty($task['cost_store_id'])
            ||empty($task['channel_id'])
            ||empty($task['cost_price'])
            ||empty($task['cost_remark'])
        ){
            return '数据不完整';
        }

        /** @var sellerModel $sellerModel */
        $sellerModel = Model('seller');
        $seller = $sellerModel->getSellerInfo(array('store_id'=>$task['cost_store_id']));
        if(empty($seller)) return '店铺不存在';
        $data = array(
            'cost_store_id'=>$task['cost_store_id'],
            'cost_seller_id'=>$seller['seller_id'],
            'channel_id'=>$task['channel_id'],
            'channel_name'=>$task['channel_name'],
            'cost_price'=>$task['cost_price'],
            'cost_remark'=>$task['cost_remark'],
            'fx_order_id'=>$task['fx_order_id'],
            'type'=>empty($task['fx_order_id'])?1:10,
            'cost_time'=>time(),
        );
        $res = $costModel->insert($data);
        if($res) return true;
        return '数据保存失败';
    }

    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.',$filePath);
        $fileType = $fileType[count($fileType)-1];

        //csv类型直接str_getcsv转换
        if(strtolower($fileType) == 'csv'){
            $lines = array_map('str_getcsv', file($filePath));;
            $result = array();
            for ($i = 0; $i < count($lines); $i++) {        //循环读取每行内容注意行从第1行开始($i=0)
                $obj = $lines[$i];
                foreach ($obj as $k => $v) {
                    $result[$i][] = mb_convert_encoding($v, 'UTF-8', 'gbk');
                }
            }
            return $result;
        }

        //excel类型 PHPExcel类库转换
        vendor('PHPExcel/Reader/Excel2007');
        vendor('PHPExcel/Reader/Excel5');
        $PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                return false;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet($sheet);            //读取excel文件中的指定工作表
        $allColumn = $currentSheet->getHighestColumn();         //*取得最大的列号
        $allRow = $currentSheet->getHighestRow();               //取得一共有多少行
        $data = array();
        for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从第1行开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if ($cell instanceof PHPExcel_RichText) {       //转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex-1][] = $cell;
            }
        }
        return $data;
    }

    //导出数据
    public function export_csvOp() {

        $condition = array();
        $limit = false;
        if ($_GET['id'] != '') {
            $id_array = explode(',', $_GET['id']);
            $condition['cost_id'] = array('in', $id_array);
        }
        /** @var storeModel $model_store */
        $model_store = Model('store');
        /** @var orderModel $model_order */
        $model_order = Model('order');
        /** @var store_costModel $model_store_cost */
        $model_store_cost = Model('store_cost');
        // 设置页码参数名称
        $storeName = $_POST['query'];
        if ($_GET['store_name'] != '') {
            $storeName = $_GET['store_name'];
        }

        if ($storeName != '') {
            $stores = $model_store->getStoreList(array('store_name' => array('like', '%' . $storeName . '%')));
            if (empty($stores)) {
                $condition['store_id'] = 0;
            } else {
                $condition['store_id'] = array('in', array_column($stores, 'store_id'));
            }
        }
        if ($_GET['fx_order_id'] != '') {
            $condition['fx_order_id'] = array('like', '%' . $_GET['fx_order_id'] . '%');
        }
        if ($_GET['channel_id'] != '') {
            $condition['channel_id'] = $_GET['channel_id'];
        }
        $order = 'cost_id desc';
        $param = array('cost_id');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }

        //店铺列表
        $store_cost_list = $model_store_cost->getStoreCostListAll($condition, $order);
        $storeList = $model_store->getStoreList(array('store_id' => array('in', array_column($store_cost_list, 'cost_store_id'))));
        $storeList = array_under_reset($storeList, 'store_id');

        $orderList = $model_order->getOrderList(array('fx_order_id' => array('in', array_column($store_cost_list, 'fx_order_id'))),'', '*', 'order_id desc', '', array('order_goods'));
        $orderList = array_under_reset($orderList, 'fx_order_id');
        $out_data = array();
        foreach ($store_cost_list as $value) {
            $store = $storeList[$value['cost_store_id']];
            $goods_name = '';
            foreach ($orderList[$value['fx_order_id']]['extend_order_goods'] as $goods) {
                $goods_name = $goods['goods_name'] . '|';
            }
            $goods_name = trim($goods_name , '|');
            $param = array();
            $param['store_id'] = $value['cost_store_id'];
            $param['store_name'] = $store['store_name'];
            $param['cost_price'] = $value['cost_price'];
            $param['cost_remark'] = $value['cost_remark'];
            $param['fx_order_id'] = $value['fx_order_id']."\t";
            $param['order_id'] = isset($orderList[$value['fx_order_id']]) ? $orderList[$value['fx_order_id']]['order_sn']. "\t" : '';
            $param['shipping_code'] =isset($orderList[$value['fx_order_id']]) ? $orderList[$value['fx_order_id']]['shipping_code']. "\t" : '';
            $param['goods_name'] = $goods_name;
            $param['channel_name'] = $value['channel_name'];
            $param['cost_time'] = date('Y-m-d H:i:s',$value['cost_time']);
            $param['send_sap'] = $value['send_sap'];
            $param['purchase_sap'] = $value['purchase_sap'];
            $param['errInf'] = $value['errInf'];
            $param['check_result'] = $value['check_result'];
            $param['check_status'] = $value['check_status'];
            $out_data[] = $param;
        }
        $this->createCsv($out_data);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($out_data)
    {
        $header = array(
            'store_id' => '店铺ID',
            'store_name' => '店铺名称',
            'cost_price' => '罚款金额',
            'cost_remark' => '罚款原因',
            'fx_order_id' => '关联订单',
            'order_id' => '汉购网订单Sn',
            'shipping_code' => '汉购网物流单号',
            'goods_name' => '汉购网产品名称',
            'channel_name' => '关联渠道',
            'cost_time' => '罚款时间',
            'send_sap' => 'send_sap',
            'purchase_sap' => 'purchase_sap',
            'errInf' => 'errInf',
            'check_result' => 'check_result',
            'check_status' => 'check_status'
        );
        array_unshift($out_data, $header);
        $csv = new Csv();
        $export_data = $csv->charset($out_data,CHARSET,'GBK');
        $csv->filename = $csv->charset('store_cost', CHARSET) . date('Y-m-d');
        $csv->export($export_data);
    }


}
