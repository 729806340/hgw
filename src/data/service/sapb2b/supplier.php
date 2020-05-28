<?php

class supplier extends commons
{
    //sap201 推送已审核的店铺
    public function add()
    {
        $where['send_sap'] = '0';//未推送
        return $this->conversion($this->getList($where, $this->getLimit($this->getCode(__CLASS__, __FUNCTION__))));
    }

    //sap201 推送成功后续操作
    public function add_after($success, $error, $exist='')
    {

        if( !empty($exist) ) {
            $ids = array() ;
            foreach ($exist as  $tid) {
                list($id, $type) = explode("_", $tid) ;
                $ids[$type][] = $id ;
            }
            foreach($ids as $k =>$v){
                $this->updateSendState($v, 2,$k);//成功的标志改为1
            }
        }

        if( !empty($success) ) {
            $ids = array() ;
            foreach ($success as  $tid) {
                list($id, $type) = explode("_", $tid) ;
                $ids[$type][] = $id ;
            }

            foreach($ids as $k =>$v){
                $this->updateSendState($v, 1,$k);//成功的标志改为1
            }
        }
        return true;
    }

    //sap201 回调函数
    public function add_callback($success, $error, $exist='')
    {
        if( !empty($success) ) {
            $success_ids = array() ;
            $error_ids = array() ;
            foreach ($success as $tid) {
                list($id, $type) = explode("_", $tid) ;
                $success_ids[$type][] = $id ;
            }

            foreach ($error as $tid) {
                list($id, $type) = explode("_", $tid) ;
                $error_ids[$type][] = $id ;
            }

            foreach($success_ids as $k =>$v){
                $this->updateSendState($v, 2,$k);//已进b1
            }

            foreach($error_ids as $k =>$v){
                $this->updateSendState($v, 0,$k);//成功的标志改为1
            }
        }
        return true;
    }

    //sap202 更新店铺
    public function edit()
    {
        $where['send_sap'] = '2';//已推送
        $where['edit_sap'] = '0';//未更新
        return $this->conversion($this->getList($where, $this->getLimit($this->getCode(__CLASS__, __FUNCTION__))));
    }

    public function edit_after($success, $error, $exist='')
    {

        if( !empty($exist) ) {
            $ids = array() ;
            foreach ($exist as  $tid) {
                list($id, $type) = explode("_", $tid) ;
                $ids[$type][] = $id ;
            }
            foreach($ids as $k =>$v){
                $this->updateEditState($v, 2,$k);//存在
            }
        }

        if( !empty($success) ) {
            $ids = array() ;
            foreach ($success as  $tid) {
                list($id, $type) = explode("_", $tid) ;
                $ids[$type][] = $id ;
            }

            foreach($ids as $k =>$v){
                $this->updateEditState($v, 1,$k);//已进sap
            }
        }
        return true;
    }

    public function edit_callback($success, $error, $exist='')
    {
        if( !empty($success) ) {
            $success_ids = array() ;
            $error_ids = array() ;
            foreach ($success as $tid) {
                list($id, $type) = explode("_", $tid) ;
                $success_ids[$type][] = $id ;
            }

            foreach ($error as $tid) {
                list($id, $type) = explode("_", $tid) ;
                $error_ids[$type][] = $id ;
            }

            foreach($success_ids as $k =>$v){
                $this->updateEditState($v, 2,$k);//已进b1
            }

            foreach($error_ids as $k =>$v){
                $this->updateEditState($v, 10,$k);//错误
            }
        }
        return true;
    }

    //修改推送标志
    private function updateSendState($ids, $state,$type)
    {
        if (empty($ids)) return true;
        if($type == 'C'){
            $field = 'purchaser_id';
        } else {
            $field = 'supplier_id';
        }
        $where[$field] = array('in', $ids);
        switch ($state) {
            case 0:
                $where['send_sap'] = '1';
                break;
            case 10:
                $where['send_sap'] = '1';
                break;
            case 1:
                $where['send_sap'] = '0';
                break;
            case 2:
//                $where['send_sap'] = '0';
                break;
            default:
                return true;
        }

        if($type == 'S'){
            $table = 'b2b_supplier';
        } else if($type == 'C'){
            $table = 'b2b_purchaser';
        }

        Model($table)->where($where)->update(array('send_sap' => $state));
//        echo Model($table)->getLastSql();
        return true;
    }

    //修改修改标志
    private function updateEditState($ids, $state,$type)
    {
        if (empty($ids)) return true;
        if($type == 'C'){
            $field = 'purchaser_id';
        } else {
            $field = 'supplier_id';
        }
        $where[$field] = array('in', $ids);
        $where['send_sap'] = '2';
        switch ($state) {
            case 0:
                $where['edit_sap'] = '1';
                break;
            case 10:
                $where['edit_sap'] = '1';
                break;
            case 1:
                $where['edit_sap'] = '0';
                break;
            case 2:
                $where['edit_sap'] = '1';
                break;
            default:
                return true;
        }

        if($type == 'S'){
            $table = 'b2b_supplier';
        } else if($type == 'C'){
            $table = 'b2b_purchaser';
        }

        Model($table)->where($where)->update(array('edit_sap' => $state));
        return true;
    }

    private function updateEditState1($ids, $state)
    {
        if (empty($ids)) return true;
        $where['supplier_id'] = array('in', $ids);
        $where['send_sap'] = '2';
        switch ($state) {
            case 0:
                $where['edit_sap'] = '1';
                break;
            case 1:
                $where['edit_sap'] = '0';
                break;
            case 2:
                $where['edit_sap'] = '1';
                break;
            default:
                return true;
        }
        Model('b2b_supplier')->where($where)->update(array('edit_sap' => $state));
        return true;
    }




    private function getList($where, $limit = 0)
    {
        $rel = array();
        $list = array();
        $supplier_list = $this->select('b2b_supplier', $where, $limit);
        foreach($supplier_list as $v){
            $v['partner_type'] = 'S';
            $list[] = $v;
        }

        $purchaser_list = $this->select('b2b_purchaser',$where,$limit);
        foreach($purchaser_list as $v){
            $v['partner_type'] = 'C';
            $v['supplier_id'] = $v['purchaser_id'];
            $list[] = $v;
        }
        if (empty($list)) return $rel;
        return $list;
    }

    private function select($table, $where, $limit = 0)
    {
        if (empty($table) || empty($where)) return array();
        return Model($table)->where($where)->limit($limit)->select();
    }

    private function conversion($list)
    {
        $data = array();
        $time = time();
        foreach ((array)$list as $v) {
            //公司名必须
            $company_name = $v['company_name'];

            if (empty($company_name)) {
                $this->failed[] = array(
                    'title' => '报警：来自 ' . $this->code . ' 报警信息',
                    'msg' => '以下供应商的公司名未设置，请先设置再推送：' .$v['supplier_id']
                );
                continue;
            }

            if($v['partner_type'] == 'S'){
                if($v['is_own_shop'] == 1){
                    $groupcode = 102;
                } else {
                    if($v['manage_type'] == 'co_construct'){
                        $groupcode = 103;
                    } else if($v['manage_type'] == 'platform'){
                        $groupcode = 101;
                    }
                }
            } else {
                $groupcode = 100;
            }

            $it['tid'] = $time . '_' . $v['supplier_id'] . '_' . $v['partner_type'];//操作唯一标识符
            $it['cardCode'] = $v['supplier_id'];//供应商ID
            $it['cardName'] = $v['company_name'];//店铺名称
            $it['cardFName'] = $v['company_name'];//店主账号
            $it['cardType'] = ($v['partner_type'] == 'S') ? 1:0;//店主账号
//            $it['cardType'] = $v['partner_type'];//店主账号
            $it['GROUPCODE'] = $groupcode;//店主账号
//            $it['validfor'] = 1; //$v['store_state'] == '1' ? 1 : 0;//是否可用
//            $it['frozenfor'] = 0; //$v['store_state'] == '1' ? 0 : 1;//是否不可用
            $it['userFields'] = array(
                'U_OCRD_PARTNER_TYPE' => $v['is_own_shop'] == 1 ? '3' : ($v['manage_type'] == 'co_construct' ? '2' : '1'),// 自营传值：1    平台传值：2   共建传值：3
//                'U_OCRD_USERID' => $v['member_id'],//商家账号
//                'U_OCRD_RANK' => $v['grade_id'],//商家等级
//                'U_OCRD_SORT' => $v['store_zy'],//店铺分类为店铺主营类目
//                'U_OCRD_AREA' => $v['area_info'],//S所在地区
//                'U_OCRD_ADDRESS' => $v['address'],//S详细地址
//                'U_OCRD_COMPANYNAME' => $company_name,//P商户公司名称
//                'U_OCRD_YYZZH' => $v['business_licence_number'],//P商户营业执照号
//                'U_OCRD_YHZH' => $v['settlement_bank_account_number'],//P结算银行账号
//                'U_OCRD_YHKHM' => $v['settlement_bank_account_name'],//P结算银行开户名
//                'U_OCRD_YHNAME' => $v['settlement_bank_name'],//P结算银行名称
            );
            $data[] = $it;
        }
        return $data;
    }
}
