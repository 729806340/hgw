<?php
/**
 * Author: Shen.L
 * Date: 2016/7/19
 * Time: 16:25
 */

require_once('cps/CpsUnion.php');

/**
 * Class CpsService
 * 主要功能
 * 1、接受CPS跳转
 * 2、处理生成订单时写入cps数据
 */
class CpsService
{
    public $cookieName = 'union_cookie';

    protected $_unions = array( // 一起发，没得比，多麦，买手党，返利，
        array('id' => 'yiqifa', 'name' => '一起发', 'key' => 'unionid', 'value' => 'yiqifa'),
        array('id' => 'meidebi', 'name' => '没得比', 'key' => 'mdb', 'value' => 'any'),
        array('id' => 'duomai', 'name' => '多麦', 'key' => 'unionid', 'value' => 'duomai'),
        array('id' => 'maishoudang', 'name' => '买手党', 'key' => 'msd', 'value' => 'any'),
        array('id' => 'fanli', 'name' => '返利', 'key' => 'channel_id', 'value' => '51fanli'),
        array('id' => 'linkStars', 'name' => '星罗', 'key' => 'source', 'value' => 'linkstars'),
        array('id' => 'zhongmin', 'name' => '中民', 'key' => 'unionid', 'value' => 'zhongmin'),
    );

    /**
     * 接受cps
     */
    public function accept()
    {
        /** @var array $union */
        $union = $this->getUnionByRequest();
        /** @var CpsUnion $cpsUnion */
        if ($union == null) throw new Exception('找不到对应联盟信息！联盟ID：'.$union);
        $cpsUnion = $this->getUnion($union['id']);
        $cpsUnion->record();
        $redirect = $cpsUnion->redirect(false);
        return $redirect;
    }

    public function query()
    {
        /** @var array $union */
        $union = $this->getUnionByRequest();
        /** @var CpsUnion $cpsUnion */
        if ($union == null) throw new Exception('找不到对应联盟信息！');
        $cpsUnion = $this->getUnion($union['id']);
        if($cpsUnion->access()!= 'pass') return 'access denied.';
        return $cpsUnion->getOrders();
    }

    /**
     * 获取联盟信息
     * @return array|null 联盟信息数组
     */
    protected function getUnionByRequest()
    {
        foreach ($this->_unions as $value) {
            if (isset($_GET[$value['key']]) && ($_GET[$value['key']] == $value['value'] || $value['value'] == 'any')) return $value;
        }
        return null;
    }

    /**
     * 获取具体联盟Service
     * @param $id string 联盟ID
     * @return CpsUnion
     * @throws Exception
     */
    public function getUnion($id)
    {
        if ($id !== null && is_string($id)) {
            $className = 'Cps' . ucfirst($id);
            $fileName = __DIR__ . '/cps/' . $className . '.php';
            if (file_exists($fileName)) {
                require_once($fileName);
                if (class_exists($className)) return new $className();
            }
        }
        throw new Exception('找不到对应联盟信息！联盟ID：'.$id);
    }

    /**
     * 创建订单
     * @param $order_data array 订单逻辑DATA
     * @return bool
     */
    public function createOrder($order_data)
    {
        /** 先根据order_list查找对应商品，然后根据商品 */
        $cookie = json_decode(stripslashes(cookie($this->cookieName)), true);
        if (empty($cookie) || !isset($cookie['unionid'])) return false;
        $union = $this->getUnion($cookie['unionid']);
        $orders = $order_data['order_list'];
        $goods = $order_data['goods_list'];
        foreach ($orders as $id => $order) {
            $goods = $order['goods'];
            foreach ($goods as $k => $v) {
                //if ($order['store_id'] != $v['store_id']) continue;
                $order['goods_id'] .= $v['goods_id'] . '|';
                $order['goods_name'] .= $v['goods_name'] . '|';
                $order['goods_num'] .= $v['goods_num'] . '|';
                $order['goods_price'] .= floatval($v['goods_pay_price']/$v['goods_num'] ) . '|';
            }
            $cps = array(
                'euid' => !empty($cookie['euid']) ? $cookie['euid'] : '',
                'mid' => !empty($cookie['mid']) ? $cookie['mid'] : '',
                'source' => !empty($cookie['source']) ? $cookie['source'] : '',
                'channel' => !empty($cookie['channel']) ? $cookie['channel'] : '',
                'cid' => !empty($cookie['cid']) ? $cookie['cid'] : '',
                'wi' => !empty($cookie['wi']) ? $cookie['wi'] : '',
                'order_id' => $id,
                'order_sn' => $order['order_sn'],
                'createtime' => TIMESTAMP,
                'paytime' => $order['order_state']==ORDER_STATE_PAY?TIMESTAMP:0,
                'order_money' => $order['order_amount'],
                'orderstatus' => $order['order_state'],
                'goodsid' => $order['goods_id'],
                'goodname' => $order['goods_name'],
                'goodsint' => $order['goods_num'],
                'goodsprice' => $order['goods_price'],
                'goodstypes' => '99999',
                'goodstypesname' => '99999',
                'types' => $cookie['unionid'],
            );
            /** @var cpsModel $cpsModel */
            $cpsModel = Model('cps');
            $cpsId = $cpsModel->insert($cps);
            $cps['id'] = $cpsId;

            $cps['push_status'] = $union->push($cps)?1:0;
            $cpsModel->where(array('id'=>$cpsId))->update($cps);
        }
        return true;
    }

    public function payOrder($order_id)
    {
        /** @var cpsModel $cpsModel */
        $cpsModel = Model('cps');
        $map = array('order_id'=>$order_id);
        $cps = $cpsModel->where($map)->find();
        if(empty($cps)) return false;
        $cps['orderstatus'] = ORDER_STATE_PAY;
        $cps['paytime'] = TIMESTAMP;
        //$cpsModel->where(array('id'=>$cps['id']))->update($cps);
        $union = $this->getUnion($cps['types']);

        $cps['push_status'] = $union->push($cps)?1:0;
        return $cpsModel->where(array('id'=>$cps['id']))->update($cps);
        //return $union->push($cps['id']);
    }

    /**
     * 订单退款
     * @param $refund_id
     * @return null
     */
    public function refundOrder($refund_id){
        //查找退款记录
        /** @var refund_returnModel $refundModel */
        $refundModel = Model('refund_return');
        $refund = $refundModel->getRefundReturnInfo(array('refund_id'=>$refund_id));
        $map = array('order_id'=>$refund['order_id']);
        /** @var cpsModel $cpsModel */
        $cpsModel = Model('cps');
        $cps = $cpsModel->where($map)->find();
        if(empty($cps)) return false;
        if($refund['goods_id']==0){ //整单退款
            $cps['orderstatus'] = ORDER_STATE_CANCEL;
        }else{
            $goodsIds = explode('|', rtrim($cps['goodsid'], '|'));
            $goodsNames = explode('|', rtrim($cps['goodname'], '|'));
            $goodsPrices = explode('|', rtrim($cps['goodsprice'], '|'));
            $goodsNum = explode('|', rtrim($cps['goodsint'], '|'));
            foreach ($goodsIds as $k=>$goodsId){
                $count = count($goodsIds);
                if($goodsId == $refund['goods_id']){
                    // 商品退货退款后合计为商品数量为1，商品单价为实付金额减去退款金额
                    $goodsAmount = $goodsPrices[$k]*$goodsNum[$k];
                    $goodsAmount -= $refund['refund_amount'];
                    if($goodsAmount<=0){
                        if($count>1) {
                            $cps['order_money'] -= $refund['refund_amount'];
                            unset($goodsIds[$k],$goodsNames[$k],$goodsNum[$k],$goodsPrices[$k]);
                        } else {
                            $cps['orderstatus'] = ORDER_STATE_CANCEL;
                        }
                        break;
                    }
                    $cps['order_money'] -= $refund['refund_amount'];
                    $goodsPrices[$k] = $goodsAmount;
                    $goodsNum[$k] = 1;
                }
            }
            $cps['goodsid'] = implode('|',$goodsIds);
            $cps['goodname'] = implode('|',$goodsNames);
            $cps['goodsprice'] = implode('|',$goodsPrices);
            $cps['goodsint'] = implode('|',$goodsNum);
        }
        //$cpsModel->where(array('id'=>$cps['id']))->update($cps);

        $union = $this->getUnion($cps['types']);

        $cps['push_status'] = $union->push($cps)?1:0;
        return $cpsModel->where(array('id'=>$cps['id']))->update($cps);
        //return $union->push($cps['id']);
    }

    public function test()
    {
        $union = $this->getUnion($_GET['union']);
        return $union->push(1);
    }

}