<?php

class store extends commons
{
    //sap201 推送已审核的店铺
    public function add()
    {
        $where['send_sap'] = '0';//未推送
        $where['store_state'] = array('eq', '1');//不等于 审核中状态
        return $this->conversion($this->getList($where, $this->getLimit($this->getCode(__CLASS__, __FUNCTION__))));
    }

    //sap201 推送成功后续操作
    public function add_after($success, $error, $exist='')
    {
        $this->updateSendState($success, 1);//成功的标志改为1
        return true;
    }

    //sap201 回调函数
    public function add_callback($success, $error, $exist='')
    {
        $this->updateSendState($success, 2);//成功的标志改为2
        $this->updateSendState($error, 0);//失败的标志改为0 重新推
        return true;
    }

    //sap202 更新店铺
    public function edit()
    {
        $where['send_sap'] = '2';//已推送
        $where['edit_sap'] = '0';//未更新
        $where['store_state'] = array('eq', 1);//不等于 审核中2,关店0状态
        return $this->conversion($this->getList($where, $this->getLimit($this->getCode(__CLASS__, __FUNCTION__))));
    }

    //sap201 推送成功后续操作
    public function edit_after($success, $error, $exist='')
    {
        $this->updateEditState($success, 1);//成功的标志改为1
        return true;
    }

    //sap201 回调函数
    public function edit_callback($success, $error, $exist='')
    {
        $this->updateEditState($success, 2);//成功的标志改为2
        $this->updateEditState($error, 0);//失败的标志改为0 重新推
        return true;
    }

    //修改推送标志
    private function updateSendState($ids, $state)
    {
        if (empty($ids)) return true;
        $where['store_id'] = array('in', $ids);
        switch ($state) {
            case 0:
                $where['send_sap'] = '1';
                break;
            case 1:
                $where['send_sap'] = '0';
                break;
            case 2:
                $where['send_sap'] = '1';
                break;
            default:
                return true;
        }
        Model('store')->where($where)->update(array('send_sap' => $state));
        return true;
    }

    //修改更新标志
    private function updateEditState($ids, $state)
    {
        if (empty($ids)) return true;
        $where['store_id'] = array('in', $ids);
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
        Model('store')->where($where)->update(array('edit_sap' => $state));
        return true;
    }

    private function getList($where, $limit = 0)
    {
        $rel = array();
        $list = $this->select('store', $where, $limit);
        if (empty($list)) return $rel;
        $sub_ids = array_column($list, 'member_id');
        if (empty($sub_ids)) return $rel;

        $sub_list = $this->select('store_joinin', array('member_id' => array('in', $sub_ids)));
        $sub_info = array_under_reset($sub_list, 'member_id');
        foreach ($list as $v) {
            $v['store_joinin'] = $sub_info[$v['member_id']];
            $rel[] = $v;
        }
        return $rel;
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
            $company_name = empty($v['store_joinin']['company_name']) ? $v['store_company_name'] : $v['store_joinin']['company_name'];
            
            if (empty($company_name)) {
                $this->failed[] = array(
                    'title' => '报警：来自 ' . $this->code . ' 报警信息',
                    'msg' => '以下店铺的公司名未设置，请先设置再推送：' .$v['store_id'] . '|' .  $v['store_name']
                );
                continue;
            }

            $it['tid'] = $time . '_' . $v['store_id'];//操作唯一标识符
            $it['cardCode'] = $v['store_id'];//店铺ID
            $it['cardName'] = $v['store_name'];//店铺名称
            $it['cardFName'] = $v['seller_name'];//店主账号
            $it['validfor'] = 1; //$v['store_state'] == '1' ? 1 : 0;//是否可用
            $it['frozenfor'] = 0; //$v['store_state'] == '1' ? 0 : 1;//是否不可用
            $it['userFields'] = array(
                'U_ORCD_USERTYPE' => $v['is_own_shop'] == 1 ? '1' : ($v['manage_type'] == 'co_construct' ? '3' : '2'),// 自营传值：1    平台传值：2   共建传值：3
                'U_OCRD_USERID' => $v['member_id'],//商家账号
                'U_OCRD_RANK' => $v['grade_id'],//商家等级
                'U_OCRD_SORT' => $v['store_zy'],//店铺分类为店铺主营类目
                'U_OCRD_AREA' => $v['area_info'],//S所在地区
                'U_OCRD_ADDRESS' => $v['store_address'],//S详细地址
                'U_OCRD_COMPANYNAME' => $company_name,//P商户公司名称
                'U_OCRD_YYZZH' => $v['store_joinin']['business_licence_number'],//P商户营业执照号
                'U_OCRD_YHZH' => $v['store_joinin']['settlement_bank_account_number'],//P结算银行账号
                'U_OCRD_YHKHM' => $v['store_joinin']['settlement_bank_account_name'],//P结算银行开户名
                'U_OCRD_YHNAME' => $v['store_joinin']['settlement_bank_name'],//P结算银行名称
            );
            $data[] = $it;
        }
        return $data;
    }
}
