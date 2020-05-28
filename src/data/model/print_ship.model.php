<?php
/**
 * Created by PhpStorm.
 * User: lijingquan
 * Date: 2017/10/10
 * Time: 10:55
 */
defined('ByShopWWI') or exit('Access Invalid!');

class print_shipModel extends Model {

    public function __construct() {
        parent::__construct('print_ship');
    }

    public function getPrintShipInfo($condition){
        return $this->where($condition)->find();
    }

    public function getPrintShipExtendsInfo($condition){
        return $this->table('printship_extends')->where($condition)->find();
    }


    public function getPrintShipList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'id desc', $group = null, $key = null) {
        return $this->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    }


    public function getPrintShipLogList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'id desc', $group = null, $key = null) {
        return $this->table('printship_log')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    }

    /*
    * 更新
    * @param array $update
    * @param array $condition
    * @return bool
    */
    public function editPrintShip($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function delPrintShip($condition){
        return $this->where($condition)->delete();
    }

    public function addPrintShip($input){
        if(is_array($input) && !empty($input)){
            return Db::insert('print_ship',$input);
        }else{
            return false;
        }
    }

    public function setPrintShipLog($order_info , $template_info){
        $postDate = $this->_setRequestDateToJson($order_info , $template_info);
        try{
            $model = Model('printship_log');
            $model->beginTransaction();
            $printship_log = array(
                'store_id' => $order_info['store_id'],
                'order_sn' => $order_info['order_sn'],
                'express_code'=> $template_info['express_code'],
                'template_id' => $template_info['id'],
                'order_info' => $postDate,
                'add_time' => TIMESTAMP
            );
            $log_id = Model('printship_log')->insert($printship_log);
            if(!$log_id){
                throw new Exception('电子面单发货对列表插入失败');
            }
            //修改订单
            $condition = array('order_sn'=>$order_info['order_sn']);
            $update  = array('is_printship'=>1);
            $res = Model('orders')->where($condition)->update($update);
            if(!$res){
                throw new Exception('订单信息更新失败');
            }
            $model->commit();
            return array('error'=>1000 , 'msg'=>'订单创建成功');
        }catch (Exception $e){
            $model->rollback();
            return array('error'=>1001 , 'msg'=>$e->getMessage());
        }
    }


    public function setPrintShipMoreLog($filename ,$template_id){
        if(!is_file($filename)){
            return callback(false , '文件不存在');
        }

        $data = $this->_excelToArray($filename);
        if(!count($data) > 1){
            return callback(false ,'订单数据有误');
        }
        if($data[0][0] != 'order_id'){
            return callback(false ,'文件格式错误');
        }

        unset($data[0]);
        $sn = array();
        foreach($data as $k=>$v){
            $sn[] = preg_replace('/\D/','',$v[0]);
        }
        if(count($sn)==0){
            return callback(false ,'没有导入数据');
        }
        /**
         * 检查电子面单模板
         */
        $condition = array('id'=>$template_id , 'store_id'=>$_SESSION['store_id']);
        $template_info = $this->getPrintShipInfo($condition);
        if($template_info['template_name']==''){
            return callback(false ,'电子面单模板不存在');
        }


        $order_list = array();
        $condition = array(
            'order_sn' => array('in' , $sn),
            'store_id' => $_SESSION['store_id']
        );
        $resOrder  = Model('order')->getOrderList($condition,'','','',false,array('order_common','order_goods'));
        foreach($resOrder as $k=>$v){
            $order_list[$v['order_sn']] = $v;
        }
        $succNum = 0;   //成功条数
        $failNum = 0;   //失败条数
        $succData = array();
        $succSns = array();
        $failOrderids = $errorMsg = array();

        foreach($order_list as $k=>$v){
            $result = Model('order')->getOrderOperateState('print_ship', $v);
            if($result == false){
                $failNum++;
                $failOrderids[] = $k;
                $errorMsg[] = $k . "状态错误" ;
                continue;
            }
            $succData[] = array(
                'store_id' => $_SESSION['store_id'],
                'order_sn' => $k,
                'express_code'=> $template_info['express_code'],
                'template_id' => $template_info['id'],
                'order_info' => $this->_setRequestDateToJson($v,$template_info),
                'add_time' => TIMESTAMP
            );
            $succSns[] = $k;
        }

        if(count($succSns)<1){
            return callback(false ,'没有符合导入电子面单的订单');
        }

        try{
            $model = Model('printship_log');
            $model->beginTransaction();
            $result = $model->insertAll($succData);
            if(!$result){
                throw new Exception('电子面单发货对列表插入失败');
            }

            //修改订单状态
            $condition = array('order_sn'=> array('in' , $succSns));
            $update  = array('is_printship'=>1);
            $res = Model('orders')->where($condition)->update($update);
            if(!$res){
                throw new Exception('订单状态更新失败');
            }
            $model->commit();
        }
        catch (Exception $e)
        {
            $model->rollback();
            return callback('false' , $e->getMessage());
        }
        $ret = array('totals'=>count($data) , 'succNum'=>count($succSns) , 'failNum'=>$failNum , 'failOrderids'=>$failOrderids, 'errorMsg' => $errorMsg);
        return $ret;

    }


    /**
     * csv、Excel转数组
     * @author wx
     * @param string $filePath      文件路径
     * @param int $sheet            第几个sheet 从0开始
     * @return array|bool
     */
    private function _excelToArray($filePath = '', $sheet = 0)
    {
        if (empty($filePath) or !file_exists($filePath)) {
            return false;
        }
        $fileType = explode('.',$filePath);
        $fileType = $fileType[count($fileType)-1];

        //csv类型直接str_getcsv转换
        if($fileType == 'csv'){
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

    public function setPrintShipRequest($log_id , $order_sn , $ret= array()){
        //print_r($ret);die();
        $update = array();
        $update['ship_status'] = $ret['Success']==1?1:2;
        $update['ship_error']  = $ret['Reason'];
        $update['ship_time']   = TIMESTAMP;
        try{
            $model = Model('printship_log');
            $model->beginTransaction();
            $res = $model->where(array('id'=>$log_id))->update($update);
            if(!$res){
                throw new Exception('更新队列表失败');
            }

            $update = array();
            $update['is_printship'] = $ret['Success']==1?2:3;
            $res = Model('orders')->where(array('order_sn'=>$order_sn))->update($update);
            if(!$res){
                throw new Exception('订单状态更新失败');
            }
            //申请成功
            if($ret['Success']==1){
                $data = array(
                    'id'=>$log_id,
                    'print_info' => $ret['PrintTemplate'],
                );
                $res = Model('printship_extends')->insert($data);
                if(!$res){
                    throw new Exception('更新扩展表失败');
                }
                //发货
                $express_list = Model('express')->getPushExpress();
                $express_list = array_under_reset($express_list,'kdncode');
                $order_info = Model('order')->getOrderInfo(array('order_sn'=>$order_sn));
                $post = array(
                    'shipping_express_id'=> $express_list[$ret['Order']['ShipperCode']]['hgwid'],
                    'shipping_code'      => $ret['Order']['LogisticCode']
                );
                $logic = logic('order');
                $res = $logic->changeOrderSend($order_info, 'system', $user = '', $post);
                if(!$res['state']){
                    throw new Exception($res['msg']);
                }
                $model->commit();
            }
        }catch (Exception $e){
            $model->rollback();
        }
    }


    private function _setRequestDateToJson($order_info=array() , $template_info=array()){
        $postDate = array();
        $postDate['CallBack'] = '';
        $postDate['Commodity'] = array();
        foreach($order_info['extend_order_goods'] as $item=>$value){
            $postDate['Commodity'][$item]['GoodsName'] = $value['goods_name'];
            $postDate['Commodity'][$item]['Goodsquantity'] = $value['goods_num'];
        }
        $postDate['ExpType'] = 1;
        $postDate['IsNotice'] = $template_info['is_notify']==1? 0:1;
        $postDate['IsReturnPrintTemplate'] = 1;
        $postDate['OrderCode'] = $order_info['order_sn'];
        $postDate['PayType'] = 1;
        $area = explode(" ", $order_info['extend_order_common']['reciver_info']['area']);
        $postDate['Receiver'] = array(
            'Address'=>$order_info['extend_order_common']['reciver_info']['street'],
            'CityName'=>$area[1],
            'ExpAreaName'=>$area[2],
            'Mobile'=>$order_info['extend_order_common']['reciver_info']['mob_phone'],
            'Name'=>$order_info['extend_order_common']['reciver_name'],
            'ProvinceName'=>$area[0],
            'PostCode'=>000000,
        );
        $region = explode(" ", $template_info['region']);
        $postDate['Sender'] = array(
            'Address'=>$template_info['address'],
            'CityName'=>$region[1],
            'ExpAreaName'=>$region[2],
            'Mobile'=>$template_info['mobile'],
            'Name'=>$template_info['sender'],
            'ProvinceName'=>$region[0],
            'PostCode'=>$template_info['shipcode'],
        );
        $postDate['ShipperCode'] = $template_info['express_code'];
        $postDate = JSON($postDate);
        return $postDate;
    }
}