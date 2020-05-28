<?php
/**
 * 抓取相关网站商品销量信息
 * Created by CharlesChen
 * Date: 2018/2/27
 * Time: 14:45
 * File name:fetchDataControl.php
 */
defined('ByShopWWI') or exit('Access Invalid!');
class fetchDataControl extends BaseCronControl
{

    private static $curl_error = '';

    private static $prod_conf_list = array(
        '拼多多' => array(
            'prod_rule' => '/<div class=\"g-name\" data-reactid=\"\d+\"><span data-reactid=\"\d+"><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><\/span>/',
            'prize_rule' => '/<span class=\"g-group-price\" data-reactid=\"\d+\"><i data-reactid=\"\d+\">￥<\/i><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><\/span>/',
            'sales_rule' => '/<span class=\"g-sales\" data-reactid=\"\d+\"><!-- react-text: \d+ -->[^<>]+<!-- \/react-text --><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><!-- react-text: \d+ -->[^<>]+<!-- \/react-text -->/',
        ),
        '云联美购' => array(
            'prod_rule' => '/<h3 class=\"title_h3\">([^<>]+)<\/h3>/',
            'prize_rule' => '/<strong> ￥<i class=\"good-price\">([^<>]+)<\/i> <\/strong>/',
            'sales_rule' => '/<span class=\"cumulative\">销量：([^<>]+)<\/span>/',
        ),
        '楚楚街' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '萌店' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '会过' => array(
            'prod_rule' => 'api',
            'prize_rule' => 'api',
            'sales_rule' => 'api',
        ),
        '返利' =>array(
            'prod_rule' => '/<h2 class=\"detail-title\">([^<>]+)<\/h2>/',
            'prize_rule' => '/<span class=\"price-now\"><span class=\"rmb-icon\">[^<>]+<\/span>(\d+\.\d+)<\/span>/',
            'sales_rule' => '/<div class=\"tuan-join\">[^<>]+<span>(\d+)[^<>]+<\/span>([^<>]+)<\/div>/',
        ),

    );

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', '0');
    }

    public function fetchOp(){
        /** @var jingjiaModel $jingjiaModel */
        $jingjiaModel = Model('jingjia');
        /** @var prod_confModel $prodConfModel */
        $prod_confModel = Model('prod_conf');
        $list = $prod_confModel->getAllList();
        foreach($list as $conf){
            $prod=$data=$prize=$sales=array();
            $url=$conf['prod_url'];
            if ($conf['prod_from'] == '楚楚街') {
                $post_url = 'https://api-product.chuchujie.com/api.php?method=product_detail';
                preg_match('/\?id=\d+/', $url, $matches);
                $product_id = substr($matches[0], 4);
                $post_param = array(
                    'data' => json_encode(array(
                        'channel' => 'QD_appstore',
                        'package_name' => 'com.culiukeji.huanletao',
                        'client_version' => '3.9.101',
                        'client_type' => 'h5',
                        'product_id' => $product_id,
                        'api_version' => 'v5',
                        'method' => 'product_detail',
                        'gender' => '1',
                        'userId' => '',
                        'token' => '',
                    )),
                );
                $res = $this->_postApiUrl($post_url, $post_param);
                $prize[1] = '';
                $sales[1] = '';
                $prod[1] = '';
                if ($res) {
                    $result = json_decode($res, true);
                    $prize[1] = $result['data']['product']['sales_price_real'];
                    $sales[1] = $result['data']['product']['buy_num'];
                    $prod[1] = $result['data']['product']['productTitle'];
                }

            } elseif($conf['prod_from'] == '萌店') {

                $mirco_time = time() . '000';
                $post_url = 'http://m.vd.cn/api/goods/detailInfo?_='. $mirco_time;
                preg_match('/&activityId=\d+/', html_entity_decode($url), $matches_activityId);
                preg_match('/&origid=\d+/', html_entity_decode($url), $matches_gid);
                preg_match('/&oriaid=\d+/', html_entity_decode($url), $matches_aid);

                $goodsId = substr($matches_gid[0], 8);
                $aid = substr($matches_aid[0], 8);
                $activityId = substr($matches_activityId[0], 12);

                $post_param = array(
                    'goodsId' => $goodsId,
                    'aid' => $aid,
                    'activityId' => $activityId,
                    'shopId' => '0',
                    'scene' => 'detailShare',
                );
                $post_data = http_build_query($post_param);
                $res = $this->_postApiUrl($post_url, $post_data);
                $prize[1] = '';
                $sales[1] = '';
                $prod[1] = '';
                if ($res) {
                    $result = json_decode($res, true);
                    if ($result['code'] == 0 && $result['data']['goods']['common']['title']) {
                        $prize[1] = $result['data']['goods']['common']['salePrice'];
                        $sales[1] = $result['data']['goods']['common']['pintuanNum'];
                        $prod[1] = $result['data']['goods']['common']['title'];
                    }
                }

            } elseif($conf['prod_from'] == '会过') {
                preg_match('/\?goods_id=\d+/', $url, $matches);
                $goods_id = substr($matches[0], 10);
                $post_url = 'https://api.huiguo.net/m/goods/detail?goods_id='. $goods_id .'&user_level=0&jump_from=0&platform=wap&app_version=10.7.0';
                $res = $this->_postApiUrl($post_url, array());
                $prize[1] = '';
                $sales[1] = '';
                $prod[1] = '';
                if ($res) {
                    $result = json_decode($res, true);
                    $prize[1] = $result['data']['info']['cprice'];
                    preg_match('/\d+/', $result['data']['info']['sale_nums'], $hui_nums);
                    $sales[1] = $hui_nums[0];
                    $prod[1] = $result['data']['info']['title_long'];
                }

            } elseif($conf['prod_from'] == '格格家') {

                preg_match('/detail\/\d+/', $url, $matches);
                $goods_id = substr($matches[0], 7);
                $post_url = 'https://m.51bushou.com/ygg-hqbs/goods/info';
                $post_param = array(
                    'saleGoodsId' => $goods_id,
                );
                $post_data = http_build_query($post_param);
                $res = $this->_postApiUrl($post_url, $post_data);


                $prize[1] = '';
                $sales[1] = '';
                $prod[1] = '';
                if ($res) {
                    $result = json_decode($res, true);
                    $prize[1] = $result['lowPrice'];
                    preg_match('/\d+/', $result['sellCountInfo'], $get_nums);
                    $sales[1] = $get_nums[0];
                    $prod[1] = $result['name'];
                }

            } else {
                //正则匹配
                if($conf['prod_from'] == '拼多多') {
                    $prod_conf_list = self::$prod_conf_list;
                    $conf['sales_rule'] = $prod_conf_list['拼多多']['sales_rule'];
                    $conf['prize_rule'] = $prod_conf_list['拼多多']['prize_rule'];
                    $conf['prod_rule'] = $prod_conf_list['拼多多']['prod_rule'];
                }
                $res= $this->_postMatchUrl($url, 20);
                preg_match(html_entity_decode($conf['prize_rule']),$res,$prize);
                preg_match(html_entity_decode($conf['sales_rule']),$res,$sales);
                preg_match(html_entity_decode($conf['prod_rule']),$res,$prod);


                //新匹配
                if (empty($prod) && $conf['prod_from'] == '拼多多') {
                    $new_prod_rule = '/<span class=\"goods-name-content\" data-reactid=\"\d+\"><span data-reactid=\"\d+\"><img[^>]*?src="[^"]*?"[^>]*?><span class=\"enable-select\" data-reactid=\"\d+\">([^<>]+)<\/span>/';
                    preg_match(html_entity_decode($new_prod_rule),$res,$prod);
                }

                if (empty($prod) && $conf['prod_from'] == '拼多多') { // 新的商品名称
                    $new_prod_rule = '/<span class=\"goods-name-content\" data-reactid=\"\d+\"><span data-reactid=\"\d+\"><span class=\"enable-select\" data-reactid=\"\d+\">([^<>]+)<\/span>/';
                    preg_match(html_entity_decode($new_prod_rule),$res,$prod);
                }

                if (empty($prize) && $conf['prod_from'] == '拼多多') { // 新的价格1
                    $new_prize_rule = '/<span class=\"g-group-price false\" data-reactid=\"\d+\"><i class=\"false\" data-reactid=\"\d+\">￥<\/i><span class=\"price-range false\" data-reactid=\"\d+\"><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text -->/';
                    preg_match(html_entity_decode($new_prize_rule),$res,$prize);
                }

                if (empty($prize) && $conf['prod_from'] == '拼多多') { // 新的价格2
                    $new_prize_rule = '/<span class=\"g-group-price false\" data-reactid=\"\d+\"><i class=\"false\" data-reactid=\"\d+\">￥<\/i><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text -->/';
                    preg_match(html_entity_decode($new_prize_rule),$res,$prize);
                }

                if (empty($sales) && $conf['prod_from'] == '拼多多') { // 新的商品销量信息
                    $new_sales_rule = '/<span class=\"g-sales false\" data-reactid=\"\d+\"><!-- react-text: \d+ -->[^<>]+<!-- \/react-text --><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><!-- react-text: \d+ -->[^<>]+<!-- \/react-text -->/';
                    preg_match(html_entity_decode($new_sales_rule),$res,$sales);
                }

            }

            $data['msg'] = '';
            $data['prod_id']=$conf['id'];
            $data['fetch_time']=time();

            //当销量中含有万
            if (stripos($sales[1], '万')) {
                $sales[1] = intval($sales[1]) * 10000;
                $data['msg'] = '活动,数据里面有万';
            }

            if (self::$curl_error) {
                //todo 可做重试方案
                Log::record(date('Y-m-d H:i:s',time()).'：未匹配到竞价配置id为'.$conf['id']."商品名[". $conf['prod_name']. ']错误信息是_'. self::$curl_error);
                $data['msg'] = '网络错误';
                $lastData = $jingjiaModel->getInfo(array('prod_id' => $conf['id']));
                if (!$lastData) {
                    $data['prize'] = $lastData['prize'];
                    $data['sales'] = $lastData['sales'];
                    $data['name'] = $lastData['name'];
                    $this->_addData($data);
                }
                continue;
            }

            //处理拼多多 下架/价格变更 等特殊情况
            if ($conf['prod_from'] == '拼多多' && (empty($prize[1]) || empty($prod[1]) || empty($sales[1]))) {
                //判断是否下架
                $right_down = strpos($res, '已下架');
                //活动1
                $s1_special = strpos($res, '后恢复');
                //活动2
                $s2_special = strpos($res, '设置提醒');

                if ($right_down) {
                    $data['msg'] = '已下架';
                }

                if ($s1_special || $s2_special) {
                    $data['msg'] = '活动';
                }

                if ($s1_special || $right_down || $s2_special) {
                    $lastData = $jingjiaModel->getInfo(array('prod_id' => $conf['id']));
                    if (!$lastData) {
                        $data['prize'] = $lastData['prize'];
                        $data['sales'] = $lastData['sales'];
                        $data['name'] = $lastData['name'];
                        $this->_addData($data);
                    }
                    continue;
                }

                //拼多多兼容 仅剩多少件 todo
                //钱是整数
                if (!empty($prod[1]) && !empty($sales[1])) {
                    $new_price_rule = '/<span class=\"g-group-price\" data-reactid=\"\d+\"><i data-reactid=\"\d+\">￥<\/i><!-- react-text: \d+ -->([^<>]+)<!-- \/react-text --><\/span>/';
                    preg_match($new_price_rule,$res,$prize);
                }

            }

            if(!empty($prize[1])){
                $data['prize']=strip_tags($prize[1]);
            }else{
                Log::record(date('Y-m-d H:i:s',time()).'：未匹配到竞价配置id为'.$conf['id']."商品价格信息");
                continue;
            }
            if(!empty($sales[1])){
                $data['sales']=strip_tags($sales[1]);
            }else{
                Log::record(date('Y-m-d H:i:s',time()).'：未匹配到竞价配置id为'.$conf['id']."商品销量信息");
                continue;
            }
            if(!empty($prod[1])){
                $data['name']=strip_tags($prod[1]);
            }else{
                Log::record(date('Y-m-d H:i:s',time()).'：未匹配到竞价配置id为'.$conf['id']."商品名称信息");
                continue;
            }
            $this->_addData($data);

        }
    }

    //插入数据
    private function _addData($data) {
        /** @var jingjiaModel $jingjiaModel */
        $jingjiaModel = Model('jingjia');
        $result = $jingjiaModel->insert($data);
        if (!$result) {
            Log::record('竞品分析:插入数据失败配置id为'.$data['prod_id']);
        }
        return $result;
    }

    //api 请求curl
    private function _postApiUrl($post_url, $post_data, $timeout=20) {

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res=curl_exec($ch);
        if (curl_error($ch) || curl_errno($ch)) {
            self::$curl_error = 'curl错误码是:'. curl_errno($ch) . '_错误信息是:'. curl_error($ch);
        }
        curl_close($ch);
        return $res;
    }

    //正则 请求curl
    private function _postMatchUrl($url, $timeout = 20) {
        ini_set('pcre.backtrack_limit', 999999999);
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, html_entity_decode($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $res=curl_exec($ch);
        if (curl_error($ch) || curl_errno($ch)) {
            self::$curl_error = 'curl错误码是:'. curl_errno($ch) . '_错误信息是:'. curl_error($ch);
        }
        curl_close($ch);
        return $res;
    }

    public function fetchShopOp() {
        $jingjiaShopModel = Model('jingjia_shop');
        $condition = array(
            'type' => 1,
            'status' => 2,
        );
        $list = $jingjiaShopModel->where($condition)->select();
        foreach($list as $conf){
            preg_match('/mall_id=\d+/', html_entity_decode($conf['shop_url']), $mall_id_str);
            $mall_id = substr($mall_id_str[0], 8);
            $post_url = 'http://apiv3.yangkeduo.com/mall/' .  $mall_id . '/info?pdduid=0';
            $res= $this->_postMatchUrl($post_url, 20);

            $data = array(
                'shop_name' => $conf['shop_name'],
                'channel_name' => $conf['channel_name'],
                'type' => 2,
                'fetch_time' => time(),
                'father_id' => $conf['id'],
            );

            if (self::$curl_error || !$res) {
                Log::record(date('Y-m-d H:i:s',time()).'：未匹配到店铺配置id为'.$conf['id']."店铺名[". $conf['shop_name']. ']错误信息是_'. self::$curl_error);
                $lastData = $jingjiaShopModel->where(array('type' => 2, 'father_id' => $conf['id']))->order('id DESC')->find();
                if (!$lastData) {
                    continue;
                }

                $data['total_sales'] = $lastData['total_sales'];

            } else {
                $result = json_decode($res, true);
                if (!isset($result['mall_sales'])) {
                    Log::record(date('Y-m-d H:i:s',time()).'：未匹配到店铺配置id为'.$conf['id']."店铺名[". $conf['shop_name']. ']配置异常');
                }
                $data['total_sales'] = $result['mall_sales'];
            }
            $this->_addShopData($data);
        }
    }

    //插入数据
    private function _addShopData($data) {
        $jingjiaShopModel = Model('jingjia_shop');
        $result = $jingjiaShopModel->insert($data);
        if (!$result) {
            Log::record('竞品分析:插入店铺分析数据失败配置id为'.$data['father_id']);
        }
        return $result;
    }
}