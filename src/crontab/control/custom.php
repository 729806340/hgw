<?php
/**
 * 任务计划 - 自定义执行的任务
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class customControl extends BaseCronControl {
    /**
     * 执行频率常量 1小时
     * @var int
     */
    const EXE_TIMES = 3600;

    private $_areaCache = array(array(),array(),array(),);
    private $_cityPatternCache = array();
    public $nonDeliverOrderEmail = array(
        //'shenlei@hansap.com',
        //'shen@shenl.com',
        //'tanliuyang@hansap.com',
    );

    /**
     * 清理缓存
     */
    public function clearcacheOp() {
        $lang = Language::getLangContent();
        $todo = array('index');

        $cacheItems = array(
            'setting',          // 基本缓存
            'seo',              // SEO缓存
            'groupbuy_price',   // 团购价格区间
            'nav',              // 底部导航缓存
            'express',          // 快递公司
            'store_class',      // 店铺分类
            'store_grade',      // 店铺等级
            'store_msg_tpl',    // 店铺消息
            'member_msg_tpl',   // 用户消息
            'consult_type',     // 咨询类型
            'circle_level',     // 圈子成员等级
            'admin_menu',       // 后台菜单
            'area',             // 地区
            'contractitem'      //消费者保障服务
        );
        foreach ($cacheItems as $i) {
            if (in_array($i, $todo)) {
                dkcache($i);
            }
        }
        // 商品分类
        if (in_array('goodsclass', $todo)) {
            dkcache('gc_class');
            dkcache('all_categories');
            dkcache('goods_class_seo');
            dkcache('class_tag');
        }

        //自定义分类
        if (in_array('goods_category', $todo)) {
            dkcache('goods_category');
        }

        // 广告
        if (in_array('adv', $todo)) {
            Model('adv')->makeApAllCache();
        }

        // 首页及频道
        if (in_array('index', $todo)) {
            Model('web_config')->updateWeb(array('web_show'=>1),array('web_html'=>''));
            delCacheFile('index');
            dkcache('channel');

            if (C('cache_open')) {
                dkcache('index/article');
            }
            //更新首页缓存
            var_dump(file_get_contents('http://www.hangowa.com/?clean=123456'));
        }
        die;
    }

    /**
     * 荆门的拼多多订单，定时批量自动发货
     */
    public function jinmenPddOp() {
        //拼多多-粮油店 荆门的（store_id=15）自动发货
        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
on a.order_id=b.order_id
set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
where a.buyer_id=194379 and a.store_id=15 and a.order_state in ('20','21')";
        $res = Model('goods')->execute($sql);

        //拼多多-果然商城 荆门的（store_id=15）自动发货
        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
on a.order_id=b.order_id
set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
where a.buyer_id=233577 and a.store_id=15 and a.order_state in ('20','21')";
        $res = Model('goods')->execute($sql);

        // 韩贵人 指定商品自动发货
        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
on a.order_id=b.order_id INNER JOIN shopwwi_order_goods as c on a.order_id=c.order_id
set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
where a.buyer_id=226476 and c.goods_id IN (104066,103416,102724,101958,102430,102706) and a.order_state in ('20','21')";
        $res = Model('goods')->execute($sql);

        /*//拼多多-火凤凰食品鲜专营店 自动发货
        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
on a.order_id=b.order_id
set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
where a.buyer_id=241681 and a.order_state='20'";
        $res = Model('goods')->execute($sql);

        //拼多多-易行九州水果生鲜专营店 自动发货
        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
on a.order_id=b.order_id
set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
where a.buyer_id=240993 and a.order_state='20'";
        $res = Model('goods')->execute($sql);*/

//        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_common as b
//on a.order_id=b.order_id
//set a.order_state='30', a.delay_time=a.payment_time+600,b.shipping_time=a.payment_time+600,a.shipping_time=a.payment_time+600
//where a.buyer_id=240993 and a.store_id =15 and a.order_state='20'";
//        $res = Model('goods')->execute($sql);

        $sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_goods as b on a.order_id=b.order_id
                set a.order_state='30'
                where a.buyer_id=194379 and a.order_state in ('20','21') and b.goods_id='104722'";
        $res = Model('goods')->execute($sql);

        /*$sql = "update shopwwi_orders as a INNER JOIN shopwwi_order_goods as b on a.order_id=b.order_id
                set a.order_state='30'
                where a.buyer_id=233577 and a.order_state='20' and b.goods_id='102936'";
        $res = Model('goods')->execute($sql);*/
    }

     //更新商品销量，直接从订单行来取
    public function refreshGoodsSaleNumOp(){
        //遍历所有在售商品
        $model_goods = Model('goods');
        $condition = array();
        $condition['is_del'] = 0;
        $condition['goods_state'] = 1;
        $goods_list = $model_goods->table('goods')->field('goods_id')->where($condition)->limit(10000)->select();


        $on = 'orders.order_id=order_goods.order_id';
        $field = 'sum(order_goods.goods_num) as sum_num';
        foreach ($goods_list as $key => $goods) {
            # code...
            $condition = array(
                'order_goods.goods_id' => $goods['goods_id'],
                'orders.order_state' => array('neq', 0)
            );
            $sum_num = $model_goods->table('orders,order_goods')->join('inner')->on($on)->field($field)->where($condition)->limit(10)->find();
            //更新商品表的销量字段
            if (!empty($sum_num['sum_num'])) {
                $data = array('goods_salenum' => $sum_num['sum_num']);
                $model_goods->table('goods')->where(array('goods_id'=>$goods['goods_id']))->update($data);
            }

        }

    }

    /**
     * 更新武汉收货人信息
     */
    public function updateWuhanReceiversOp(){
        ini_set('memory_limit','4G');
        set_time_limit(0);
        //初始化文件

        $path = BASE_UPLOAD_PATH.'/etc/wuhan-receiver.csv';
        $file = fopen($path,"w+");
        fwrite($file, mb_convert_encoding("收件人,联系电话,收货地址\r\n",'GBK','UTF-8'));
        /** @var orderModel $model */
        $model = Model('order');
        $maxInfo = $model->getOrderCommonInfo(array(),'max(order_id) as id');
        $maxId = $maxInfo['id'];
        for ($i=3100;$i<=$maxId;$i=$i+100){
            $condition = array(
                'reciver_city_id' => 258,
                'order_id' => array('between',array($i,$i+99))
            );
            $items = $model->getOrderCommonList($condition,'reciver_name,reciver_info','',100);
            foreach ($items as $item){
                $receiverInfo = $this->_getWuhanReceiverInfo($item);
                if (empty($receiverInfo)) continue;
                $receiverInfo = mb_convert_encoding("$receiverInfo\r\n",'GBK','UTF-8');
                if (empty(trim($receiverInfo))) continue;
                fwrite($file, $receiverInfo);
            }
        }
        fclose($file);
        echo "数据处理完成\r\n";

    }

    private function _getWuhanReceiverInfo($item){
        static $cache = array();
        $name = $item['reciver_name'];
        $receiver_info = @unserialize($item['reciver_info']);
        $phone = isset($receiver_info['phone'])?$receiver_info['phone']:'';
        $phone = preg_replace('/[^\d]+/','',$phone);
        if(empty($phone)) return null;
        if (isset($cache[$phone])) return null;
        $cache[$phone] = 1;
        $phone .= "\t";
        $address = isset($receiver_info['address'])?$receiver_info['address']:'';
        return trim("$name,$phone,$address");
    }

    public function checkNonDeliverOrderOp()
    {
        // 循环处理订单
        $res = $this->_checkNonDeliverOrders();
        return;
        $title = "超区订单超时提醒：".date('Y-m-d H:i',$res['start']).'至'.date('Y-m-d H:i',$res['end']);
        var_dump($res);
        $content = $this->_renderNonDeliverOrderNotifyEmail($res);
        $mail	= new Email();
        foreach ($this->nonDeliverOrderEmail as $email){
            $res = $mail->send_sys_email($email,$title,$content);
        }
    }

    private function _renderNonDeliverOrderNotifyEmail($data){
        $orders = $data['orders'];
        if(empty($orders)) return '恭喜，暂无超区订单';
        $res = '<p>尊敬的用户：您好！</p><p>以下为订单明细：</p><table><thead><tr><td>订单编号</td><td>超区状态</td><td>客户信息</td><td>商品</td><td>数量</td><td>超区状态</td></tr></thead><tbody>';
        foreach ($orders as $order){
            $goodsCount = count($order['extend_order_goods']);
            $deliveryStatus = $order['non_delivery']=='1'?'超区':'部分超区';
            $commonInfo = $order['extend_order_common'];
            $buyer = $commonInfo['reciver_name'];
            $reciver_info = $commonInfo['reciver_info'];
            $phone = $reciver_info['phone']?$reciver_info['phone']:$reciver_info['mob_phone'];
            $address = $reciver_info['address'];
            $res .= '<tr>';
            $res .= "<td rowspan=\"{$goodsCount}\">{$order['order_sn']}</td>";
            $res .= "<td rowspan=\"{$goodsCount}\">{$deliveryStatus}</td>";
            $res .= "<td rowspan=\"{$goodsCount}\">{$buyer} {$phone} {$address}</td>";
            foreach ($order['extend_order_goods'] as $key => $goods)
            {
                if($key>0) $res .= '<tr>';
                $goodsDeliveryStatus = $goods['non_delivery']=='1'?'超区':'未超区';
                $res .= "<td>{$goods['goods_name']}</td>";
                $res .= "<td>{$goods['goods_num']}</td>";
                $res .= "<td>{$goodsDeliveryStatus}</td>";
                $res.= '</tr>';
            }
        }
        $res .= '</tbody></table>';
        return $res;

    }
    private function _checkNonDeliverOrders()
    {
        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        /** @var transportModel $transportModel */
        $transportModel = Model('transport');
        $now = time();
        $res = array('orders'=>array(),'start'=>0);
        do{
            // 检查最近24小时内，状态为待发货，超区状态为0的订单
            $orders = $orderModel->getOrderList(array(
                'order_state'=>array('in',array(ORDER_STATE_PAY,ORDER_STATE_SEND)),
                'add_time'=>array('gt',$now-7*24*3600),
                'non_delivery'=>'0',
            ),'','order_id,order_sn,add_time','order_id ASC',1000,array('order_goods','order_common'));
            $orderGoodsList = $orderModel->getOrderGoodsList(array('order_id'=>array('in',array_keys($orders))),'goods_id',99999);
            $goodsList = $goodsModel->getGoodsList(array(
                'goods_id'=>array('in',array_unique(array_column($orderGoodsList,'goods_id'))),
            ),'goods_id,transport_id','','',99999);
            $goodsList = array_under_reset($goodsList,'goods_id');
            $transports = $transportModel->getExtendList(
                array(
                    'transport_id'=>array('in',array_unique(array_column($goodsList,'transport_id')))
                )
            );
            $transports = array_under_reset($transports,'transport_id',2);
            foreach ($orders as $order){
                if($res['start']<=0) $res['start'] = $order['add_time'];
                //var_dump($res['start']);
                $order = $this->_checkNonDeliverOrder($order,$goodsList,$transports);
                if($order['non_delivery']>0){
                    $res['orders'][] = $order;
                }
            }

            sleep(1);
        }while(false && !empty($orders));
        if($res['start']<=0) $res['start'] = $now - 12*3600;

        $res['end'] = time();
        return $res;
    }

    private function _checkNonDeliverOrder($order,$goodsList,$transportList)
    {

        /** @var orderModel $orderModel */
        $orderModel = Model('order');
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        // 检查此订单是否超区
        $address = trim($order['extend_order_common']['reciver_info']['address']);
        if(preg_match('/^中国(.*)$/isU',$address,$match)){
            $address = $match[1];
        }
        $nonDeliver = 0;
        foreach ($order['extend_order_goods'] as $key=>$order_goods){
            if(!isset($goodsList[$order_goods['goods_id']])){
                continue;
            }
            $goods = $goodsList[$order_goods['goods_id']];
            if(
                $goods['transport_id']<=0
                || !isset($transportList[$goods['transport_id']])
            ) {
                continue;
            }
            $transports = $transportList[$goods['transport_id']];
            $areaIds =  array();
            foreach ($transports as $transport){
                $areaIds = array_merge($areaIds,explode(',',$transport['area_id']));
            }
            $areaIds = array_filter(array_unique($areaIds));
            $inDeliverArea = $this->_isInDeliverArea($address,$areaIds);
            if($inDeliverArea){
                continue;
            }
            $nonDeliver++;
            // 修改商品行数据
            $orderModel->editOrderGoods(array('non_delivery'=>1),array('rec_id'=>$order_goods['rec_id']));
            $order['extend_order_goods'][$key]['non_delivery']=1;
        }
        $nonDeliverState = 0;
        if(!$nonDeliver) {
            $nonDeliverState = -1;
        }else{
            $nonDeliverState = $nonDeliver<count($order['extend_order_goods'])?10:1;
        }
        $orderModel->editOrder(array('non_delivery'=>$nonDeliverState),array('order_id'=>$order['order_id']));
        $order['non_delivery'] = $nonDeliverState;
        return $order;
    }
    private function _isInDeliverArea($address,$areaIds)
    {
        /** @var areaModel $areaModel */
        $areaModel = Model('area');
        // 首先使用空格区分省市区
        $arr = explode(' ',$address);
        if(count($arr)>3){
            $pid = 0;
            for($i=0;$i<2;$i++){
                $areaName = mb_substr($arr[$i],0,2, 'utf-8');
                if(isset($this->_areaCache[$i][$pid.$areaName])){
                    $areaId = $this->_areaCache[$i][$pid.$areaName];
                }else{
                    $area = $areaModel->getAreaInfo(array('area_name'=>array('like','%'.$areaName.'%'),'area_parent_id'=>$pid));
                    if(empty($area)) break;
                    $this->_areaCache[$i][$pid.$areaName] = $areaId = $area['area_id'];
                }
                if(in_array($areaId,$areaIds)) return true;
                $pid = $areaId;
            }
        }
        // 抓取前2个字符进行省级匹配
        $areaName = mb_substr($address,0,2, 'utf-8');
        if(!isset($this->_areaCache[0]['0'.$areaName])){
            $area = $areaModel->getAreaInfo(array('area_name'=>array('like','%'.$areaName.'%'),'area_parent_id'=>0));
            if(empty($area)) {
                $provenceId = 0;
            }else{
                $this->_areaCache[0]['0'.$areaName] = $provenceId = $area['area_id'];
            }
        }else{
            $provenceId = $this->_areaCache[0]['0'.$areaName];
        }
        if($provenceId<=0){
            // 若未匹配到省级，则直接匹配市级
            if(!isset($this->_areaCache[0]['1'.$areaName])){
                $area = $areaModel->getAreaInfo(array('area_name'=>array('like','%'.$areaName.'%')));
                if(empty($area)) {
                    return false;
                }else{
                    $this->_areaCache[0]['1'.$areaName] = $cityId = $area['area_id'];
                }
            }else{
                $cityId = $this->_areaCache[0]['1'.$areaName];
            }
            return in_array($cityId,$areaIds);
        }else{
            if(in_array($provenceId,$areaIds)) return true;
            // 根据使用正则匹配本省市级名称
            $address = mb_substr($address,2,1000, 'utf-8');
            if(isset($this->_cityCache[$provenceId])){
                $pattern = $this->_cityPatternCache[$provenceId];
            } else {
                $cities = $areaModel->getAreaList(array('area_parent_id'=>$provenceId));
                $names = array();
                foreach ($cities as $city){
                    $names[] = mb_substr($city['area_name'],0,2, 'utf-8');
                }
                $this->_cityPatternCache[$provenceId] = $pattern = implode('|',$names);
            }

            preg_match('/('.$pattern.')/isu',$address,$match);
            if(!isset($match[1])) return false;
            $areaName = $match[1];

            if(!isset($this->_areaCache[1]['1'.$areaName])){
                $area = $areaModel->getAreaInfo(array('area_name'=>array('like','%'.$areaName.'%')));
                if(empty($area)) {
                    return false;
                }else{
                    $this->_areaCache[1]['1'.$areaName] = $cityId = $area['area_id'];
                }
            }else{
                $cityId = $this->_areaCache[1]['1'.$areaName];
            }
            return in_array($cityId,$areaIds);
        }

    }
}
