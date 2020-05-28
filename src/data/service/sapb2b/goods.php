<?php

class goods extends commons
{
    //sap101 推送已审核的商品
    public function add()
    {
        $limit = $this->getLimit($this->getCode(__CLASS__, __FUNCTION__));
        $where['send_sap'] = '0';//未推送
        return $this->conversion($this->getList($where, $limit));
    }

    //sap101 推送成功后续操作
    public function add_after($success, $error, $exist='')
    {
        $this->updateSendState($success, 1);//成功的标志改为1
        return true;
    }

    //sap101 回调函数
    public function add_callback($success, $error, $exist='')
    {
        $this->updateSendState($success, 2);//成功的标志改为2
        $this->updateSendState($error, 0);//失败的标志改为0 重新推
        return true;
    }

    //sap102 编辑商品
    public function edit()
    {
        $limit = $this->getLimit($this->getCode(__CLASS__, __FUNCTION__));
        $where['send_sap'] = '2';//已推送
        $where['edit_sap'] = '0';//未更新
        return $this->conversion($this->getList($where, $limit));
    }

    //sap102 推送成功后续操作
    public function edit_after($success, $error, $exist='')
    {
        $this->updateEditState($success, 1);//成功的标志改为1
        return true;
    }

    //sap102 回调函数
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
        $where['goods_id'] = array('in', $ids);
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
        Model('b2b_goods')->where($where)->update(array('send_sap' => $state));
        return true;
    }

    //修改更新标志
    private function updateEditState($ids, $state)
    {
        if (empty($ids)) return true;
        $where['goods_id'] = array('in', $ids);
        $where['send_sap'] = '2';
        switch ($state) {
            case 0:
                $where['edit_sap'] = '1';
                break;
            case 1:
                $where['edit_sap'] = '0';
                break;
            case 2:
                $where['edit_sap'] = '2';
                break;
            default:
                return true;
        }
        Model('b2b_goods')->where($where)->update(array('edit_sap' => $state));
        return true;
    }

    private function getList($where, $limit = 0)
    {
        $rel = array();
        $list = $this->select('b2b_goods', $where, $limit);
        if (empty($list)) return $rel;
        $sub_ids = $supplier_ids = array();
        foreach ($list as $item) {
            $sub_ids[] = $item['goods_commonid'];
//            $supplier_ids[] = $item['supplier_id'];
        }

        if (empty($sub_ids)) return $rel;

        //添加商品common
        $sub_list = $this->select('b2b_goods_common', array('goods_commonid' => array('in', $sub_ids)));
        $sub_info_list = array_under_reset($sub_list, 'goods_commonid');

        $supplier_ids = array_column($sub_list,'supplier_id');
        //添加供应商信息
        $supplier_list = $this->select('b2b_supplier', array('supplier_id' => array('in', $supplier_ids)));
        $supplier_info_list = array_under_reset($supplier_list, 'supplier_id');

        foreach ($list as $v) {
            $goods_common = $sub_info_list[$v['goods_commonid']];
            $v['goods_common'] = $goods_common;
            $v['supplier'] = $supplier_info_list[$goods_common['supplier_id']];
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
            $it['tid'] = $time . '_' . $v['goods_id'];//操作唯一标识符
            $it['itemCode'] = $v['goods_id'];//商品（物料）编码	主键（唯一）
            $it['itemName'] = $v['goods_common']['goods_name'];//商品名称
            $it['itmsGrpCod'] = $v['is_own_shop'] == 1 ? '100' : ($v['supplier']['manage_type'] == 'co_construct' ? '102' : '101');//商品类型	参见B1类型代号 平台自营商品 102  平台商户商品  101 共建104
            $it['sellItem'] = 1;//是否销售物料
            $it['prchseItem'] = 0;//是否采购物料
            $it['invntItem'] = 0;//是否仓库物料
//            $it['validfor'] = 1;//商品是否可用	商品状态
//            $it['frozenfor'] = 0;//商品是否不可用（冻结）	商品状态

            $it['vatGroupPu'] = ($v['supplier']['manage_type'] == 'platform' ) ? inputTax(0) :  inputTax($v['goods_common']['tax_input']);//进项税
            $it['vatGourpSa'] = ($v['supplier']['manage_type'] == 'platform' ) ? outputTax(0) :  outputTax($v['goods_common']['tax_output']);//销项税

            $it['userFields'] = array(
//                'U_OITM_TYPE' => $v['supplier']['manage_type'] == 'b2b' ? '2' : '1',//1，普通； 2，3C；3，集采
//                'U_OITM_MERTID' => $v['goods_common']['gc_id'],//	分类ID
//                'U_OITM_MERTNAME' => $v['goods_common']['gc_name'],//	分类名称
                'U_OCRD_CODE' => 's'.$v['goods_common']['supplier_id'],//	隶属店铺ID
                'U_OITM_CARDNAME' => $v['goods_common']['supplier_name'],//	隶属店铺名称
//                'U_OITM_MARKET' => $v['goods_marketprice'],//商品市场价格
//                'U_OITM_PRICE' => $v['goods_cost'],//成本价格
//                'U_OITM_SALES' => $v['goods_price'],//商品价格
//                'U_OITM_FREIGHT' => $v['transport_id'] ? 10 : 0,//运费 运费字段，在商品没有运费模板时，统一传0，有运费模板的统一传10
            );
            $data[] = $it;
        }
        return $data;
    }

}