<?php

class b2c_order_renrendian_rrdapi {
	
    public static $cookie_file = "";
    public static $rrd_header = array("Host: mch.wxrrd.com");
	
	function __construct()
	{
		self::$cookie_file = DATA_DIR . "/logs/rrd_cookie.txt";
	}

    //验证登陆状态，如未登录，则重新登录，如在登陆过程中要输入验证码，则打码输入后在提交验证码页面再次登陆
    public static function has_logged($captcha) {
        $res = self::list_orders(array(), 1, 1);
        //cookie记录的登陆状态，已经失效，重新登录
        if (!is_array($res) || !isset($res['data'])) {
           $login_res = self::login_rrd($captcha);

           if (!isset($login_res['success']) || true != $login_res['success']) {
               return false;
           }

        }

        return true;
    }

    //人人店后台登陆，记录cookie信息
    public static function login_rrd($captcha='') {
        $login_url = 'http://mch.wxrrd.com/auth/login.json';
      
        $post_fields['username'] = '18627173588:jishu';  
        $post_fields['password'] = 'hangowa123';
        $post_fields['remember'] = true;  
        if ($captcha) {
            $post_fields['captcha'] = $captcha;  
        }
        
        $s = self::curlPost($login_url, $post_fields);
        return json_decode($s, true);
    }

    //根据订单号获取推客订单详细信息
    public static function get_tuike_order($order_id) {
        $url = "http://mch.wxrrd.com/guider/order.json?column=created_at&direction=desc&limit=10&mobile=&nickname=&offset=0&order_sn={$order_id}&settled_at=&status=all&title=";
        $ret = json_decode(self::curlGet($url), true);
        return $ret['data'][0];
    }

    /**
     * 根据查询条件获取 订单列表
     */
    public static function list_orders($where=array(), $page=1, $limit=100) { 
        $filter = array(
            'endDate' => empty($where['endDate']) ? '' : $where['endDate'],//'2016-04-12',
            'express_type'=>'all',
            'feedback'=>0,  //同意退款，用accept。用于获取退款订单列表时
            'goods_name'=>'',
            'limit'=> $limit,
            'offset'=>($page-1)*$limit,
            'payment_sn'=>'',
            'sort'=>'desc',
            'startDate' => empty($where['startDate']) ? '' : $where['startDate'],//'2016-04-12',
            'type'=>'tosend'
        );

        $url = "http://mch.wxrrd.com/order.json?" . http_build_query($filter);
        $s = self::curlGet($url);
        return json_decode($s, true);
    }


    /**
     * 获取登录验证码数据，用于image/png类型的header后输出
     */
    public static function get_captcha_data() {
       $captcha_url = 'http://mch.wxrrd.com/captcha';
       $httpheader = array("Host: mch.wxrrd.com");
       $s = self::curlGet($captcha_url, $httpheader);
       return $s;
    }

    public static function curlGet($url, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//禁止直接显示获取的内容 重要
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // curl_setopt($ch, CURLOPT_REFERER, 'http://www.ickd.cn/auto.html');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        
        $header = self::$rrd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($ch, CURLOPT_COOKIE, "wxrrd_mch_session=eyJpdiI6IjBGbGxJVkxGQWlEalN0bDJhd29MWXc9PSIsInZhbHVlIjoiYkE3VUMzUHREekpad3pORStpVyszejZSaEtiRUxza2xTa2NiOHkwa1pZUGpRNzBrSmI2ZndkcEhLSUZZY2NcL0hGeEJYOVZSeElza1daR2w3TEZJVzZBPT0iLCJtYWMiOiI0MTdlNzZiOTZlNWU5ZWE3NGVjZGRlMjA5MjI3ZTM1M2VkZGQwYWIxMmNkN2ZjMzUyYmE1MTM2ZWZiOTg3ZDA3In0%3D");
        
        $cookie_file = self::$cookie_file;  
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);  

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public static function curlPost($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');

        $header = self::$rrd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;  
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);  

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }


}

?>