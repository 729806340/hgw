<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
abstract class CpsUnion{

    public $cookieExpire = 2592000;
    public $cookieName = 'union_cookie';

    public $commissionRates = array(
        1061	=>	9.00,
        1062	=>	9.00,
        1058	=>	4.50,
        10	=>	4.50,
        1110	=>	4.50,
        4	=>	3.50,
        7	=>	3.50,
        8	=>	3.50,
        9	=>	3.50,
        11	=>	3.50,
        1057	=>	3.50,
        1111	=>	3.50,
        1113	=>	3.50,
        1137	=>	2.00,
        1138	=>	2.00,
        1139	=>	2.00,
        1140	=>	2.00,
        1141	=>	2.00,
        1142	=>	2.00,
        1143	=>	2.00,
        5	=>	1.50,
        6	=>	1.50,
        1059	=>	1.50,
        1060	=>	1.50,
        1112	=>	1.50,
        1179	=>	2.00,
    );


    /**
     * @param int $category
     * @return float|mixed
     */
    public function getCommissionRate($category=5){
        $rates = $this->commissionRates;
        return isset($rates[$category])?$rates[$category]:1.50;
    }

    public function record()
    {
        $cookie = $this->formatRequest();
        setNcCookie($this->cookieName, json_encode($cookie),time()+$this->cookieExpire);
    }

    /**
     * @param bool $direct
     * @return string
     */
    public function redirect($direct=true)
    {
        $redirect = !empty($_GET['target'])?$_GET['target']:SHOP_SITE_URL;
        //多麦用的是to参数表示跳转地址
        // 多麦跳转参数请在子类CpsDuomai重写本方法
        if ($direct) redirect($redirect);
        return $redirect;
    }

    /**
     * @param $goods_id integer
     * @param $lv  integer   给cps设置佣金的时候，是根据提报的二级分类id来的
     * @return int
     */
    protected function getCategory($goods_id,$lv=2)
    {
        /** @var goodsModel $goodsModel */
        $goodsModel = Model('goods');
        $goods = $goodsModel->getGoodsInfo(array('goods_id'=>$goods_id));
        if(empty($goods)) return 0;
        return $goods['gc_id_'.$lv];
    }



    /**
     * @return array
     */
    abstract function formatRequest();
    abstract function getConfig();

    /**
     * 推送订单
     * @param $id integer
     * @return mixed
     */
    abstract function push($id);
    abstract function getOrders();
    abstract function access();


}
