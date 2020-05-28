<?php

class pddapi {
	
    public static $cookie_file = "";
    public static $pdd_header = array("Host:mms.yangkeduo.com");
	
	function __construct()
	{
		self::$cookie_file = BASE_DATA_PATH . "/log/grsc_cookie.txt";
	}

    //验证登陆状态，如未登录，则重新登录，如在登陆过程中要输入验证码，则打码输入后在提交验证码页面再次登陆
    public static function has_logged() {
        $res = self::list_orders(1,1,array());
        //cookie记录的登陆状态，已经失效，重新登录
        if (is_array($res) && isset($res['error_code']) ) {
           $login_res = self::login_pdd();

           if (isset($login_res['error_code'])) {
           		log::selflog("模拟登录失败", 'pinduoduo') ;
               return false;
           }

        }

        return true;
    }

    //人人店后台登陆，记录cookie信息
    public static function login_pdd($captcha='') {
        $login_url = 'http://mms.yangkeduo.com/auth';
      
        $post_fields['username'] = '果然商城';
        $post_fields['password'] = 'hangowa321';
        
        $s = self::curlPost($login_url, $post_fields);
        return json_decode($s, true);
    }

    /**
     * 根据查询条件获取 订单列表
     */
	public static function list_orders($pageNumber=1, $status = 1, $where, $pageSize = 30) {
		
        $filter = array(
            'confirmStartTime' => strtotime($where['begin']),
            'isLucky' => '0',
            'confirmEndTime' => strtotime($where['end']),
            'orderType'=>$status,
            //'afterSaleType'=>'1',
            'pageNumber'=> $pageNumber,
        	'pageSize' => $pageSize
        );
        if( $status == 4 ) unset($filter['orderType']) ;

        $url = "http://mms.yangkeduo.com/malls/782/orders3?" . http_build_query($filter);
        //$url = "http://mms.yangkeduo.com/malls/782/orders3?confirmStartTime=1473585740&confirmEndTime=1476177740&isLucky=0&orderType=1&afterSaleType=1&pageNumber=1&pageSize=30";
        $s = self::curlGet($url);
        $res = json_decode($s, true);
        if( !$res['result'] ) {
        	self::login_pdd() ;
        }
        
        return $res ;
    }

    /**
     * 获取历史订单
     * @params $begin,$end 日期2016-05-05
     */
    function getAll($status, $where)
    {
    	$getTotal = 0; //已拉取数量
    	if( self::has_logged() ) {
        	$model = Model('b2c_fxhistory') ;
    		
    		$flag = 1 ;
    		$pageNumber = 1 ;
    		$pageSize = 100;
    		while ( $flag ) {
    			 
    			$response = self::list_orders($pageNumber, $status, $where, $pageSize) ; //1为未发货 。 2为已发货。3为已签收
    			if( !$response['result'] ) break ;
    			
    			$total = $response['data']['totalItemNum'] ;
    			$savedOrderIds = $order_nos = array() ;
    			foreach ($response['data']['pageItems'] as $order) {
    				$order_nos[] = $order['orderSn'] ;
    			}
    			
    			$condition = array();
    			$condition['orderno'] = array('in', $order_nos) ;
    			$res = $model -> where( $condition ) -> select () ;
    			$savedOrderIds = $res ? array_column($res, 'orderno') : array() ;
    			
    			foreach ($response['data']['pageItems'] as $order ) {
    				if( in_array($order['orderSn'], $savedOrderIds) ) continue ;
    				
    				$data = $this -> getInsertData($order) ;
    				$data['status'] = $status;
    				if( !$model -> insert ( $data ) ) {
    					log::selflog("保存失败：" . $order['orderSn'], 'pddhistory') ;
    				}
    			}
    			
    			$getTotal += count($order_nos) ;
    			if( $getTotal >= $total ) {
    				$flag = 0;
    			} else {
    				$pageNumber += 1 ;
    			}
    		}
        }
        return $getTotal ;
    }

    function getInsertData( $row ){
    	$data = array() ;
        if( $row['orderSn'] ) {
            $goods_amount = $row['goodsAmount'] / 100 ;
            $goods_price = $row['goodsPrice'] / 100 ;
            $data = array(
                'source' => 'grsc',
                'orderno' => $row['orderSn'],
                'product_id' => $row['skuId'],
                'price' => $goods_price,
                'num' => $row['goodsNumber'],
                'thumb_url' => $row['thumbUrl'],
                'discount_amount' => $row['discountAmount'] / 100,
                'total_amount' => $row['goodsAmount'] / 100,
                'product_name' => $row['goodsName'],
                'ship_area' => $row['provinceName'] . "/" . $row['cityName'] . "/" . $row['districtName'],
                'ship_name' => $row['receiveName'],
                'ship_addr' => $row['shipAddress'],
                'ship_mobile' => $row['mobile'],
                'order_time' => $row['confirmTime'],
            	'refund_id' => intval($row['afterSaleId']),
            	'refund_status' => intval($row['refundStat']),
            	'refund_amount' => $row['refundAmount'] ? $row['refundAmount']/100 : 0,
            );//var_dump($data);
        }
        return $data ;
    }

    function get_refund_good_num($order_sn){
        if( !self::has_logged() ) return array();
        $url = 'http://mms.yangkeduo.com/orders?sn='.$order_sn;
        $s = self::curlPost_detail($url);
        return json_decode($s, true);
    }

    //模拟登录获取拼多多后台退款列表接口数据，只获取上线后同步到汉购网的订单退款单
    function list_refund_order($onlineDate=null)
    {	
    	//return $this -> demoRefundData();
        $onlineDate = time() - 10*24*3600;
        if( !self::has_logged() ) return array();
        
        $hasnext = true ;
        $page = 1 ;
        $size = 100 ;
        $result = array() ;
        while ( $hasnext )
        {
            $filter = array(
                //'beginTime' => date('Y-m-d H:i:s', time()-3*3600 ),
            	'beginTime' => $onlineDate,
            	'endTime' => time(),
                'mallId' => '8',
                'offset' => ($page-1)*$size,
                'size' => $size,
                //'statusList[]' => '1'
            );

            $url = "http://mms.yangkeduo.com/malls/782/after/sales/list?" . http_build_query($filter);
            $res = self::curlGet($url);
            $s = json_decode($res, true);

            if( $s['success'] )
            {
                $total = $s['result']['total'] ;
                foreach( $s['result']['afterSalesList'] as $refund ){
                    //过滤上线日期以前成团的订单
                    if( $refund['createdTime'] < $onlineDate )
                        continue ;

                    $result[] = $refund ;
                }
                if( $filter['offset']+$filter['size'] > $total ) {
                    $hasnext = false ;
                }
                $page ++ ;
            } else {
                $hasnext = false ;
            }
        }
        return $result ;
    }
    
    function demoRefundData()
    {
    	$data[] = array(
    			'id'	=> '1008145',
    			'orderSn' => '160801-05982016917',
    			'refundAmount' => "2190",
    			'goodsId' => '3274410',
    			'goodsName' => '商品1'
    	) ;
    	$data[] = array(
    			'id'	=> '1008145',
    			'orderSn' => '160802-39126136224',
    			'refundAmount' => "12750",
    			'goodsId' => '13829',
    			'goodsName' => '商品2'
    	) ;
    	$data[] = array(
    			'id'	=> '1008145',
    			'orderSn' => '160802-36448653800',
    			'refundAmount' => "2390",
    			'goodsId' => '327441',
    			'goodsName' => '商品3'
    	) ;
    	return $data ;
    }

    /**
     * @param $id 退款sn号
     * @return 退款明细
     */
    public function get_refund_detail( $id )
    {
        $filter = array(
            'id' => $id
        );

        $url = "http://mms.yangkeduo.com/malls/782/after/sales?" . http_build_query($filter);
        $res = self::curlGet($url);
        $s = json_decode($res, true);

        return $s ;
    }

    /**
     * 获取退款SN号码
     */
    public function get_refund_sn( $orderno )
    {
        $url = "http://mms.yangkeduo.com/refund/" . $orderno;
        $res = self::curlGet($url);
        $s = json_decode($res, true);

        return $s[0]['refund_sn'] ? $s[0]['refund_sn'] : 0 ;
    }

    /**
     * 根据拼多多订单号获取退款详情
     */
    public function get_refunddetail_byono( $refund_id )
    {
    	if( !self::has_logged() || !$refund_id ) return array();

        $detail = $this -> get_refund_detail( $refund_id ) ;
        return $detail ;
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
        
        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_COOKIE, "laravel_session=eyJpdiI6IkJnOWdqOXVHaGNJbzlDMUs5Z2tXU0E9PSIsInZhbHVlIjoidGMzWUt2UGlxOUJ5NmdqTzBwS2xCenZPNWs2S0hyb2hIb3JEZVFGampqZDgwcndtaEFvVnQ1WmV6Q212WHh5aytuaFlHOUhRRndjcDVrSlJWb2FRV0E9PSIsIm1hYyI6ImUwNTJjNGYwYzJkMmUwZTNmMDJlOWExMDhhNDM0NDNkNDA1ZGYyMWI3ZjU1Nzg1Mjg3ZjI0MmJkNWIyOTYyMDIifQ%3D%3D");
        
        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

    public static function curlPost_detail($url, $data, $timeout=6) {
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');

        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;
        curl_setopt($ch, CURLOPT_COOKIEFILE , $cookie_file);

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

        $header = self::$pdd_header;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $cookie_file = self::$cookie_file;  
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);  

        // 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // 释放curl句柄
        curl_close($ch);
        return $output;
    }

	public function list_chargeback($where = array())
	{
		$hasnext = true ;
        $page = 1 ;
        $size = 100 ;
        $result = array() ;
		$getTotal = 0;
		
		while ( $hasnext ) {
			$filter = array(
					"orderSn" => "",
					"startTime" => $where['begin'],
					"endTime" => $where['end'],
					"pageNumber" => $page,
					"pageSize" => $size,
			) ;
			
			$url = "http://mms.yangkeduo.com/malls/782/unshipChargebacks?" . http_build_query($filter);
			$res = self::curlGet($url);
			$s = json_decode($res, true);
			
			if( $s['totalCount'] > 0 )
			{
				$total = $s['totalCount'] ;
				foreach ($s['records'] as $cb) {
					$data=array();
					$data['orderno'] = $cb['orderSn'];
					$data['cost_price'] = $cb['chargebackAmount'];
					$data['reason'] = $cb['chargebackReason'];
					$result[] = $data ;
					$getTotal+=1;
				}
				
				if( $getTotal >= $total ) {
					$hasnext = false;
				} else {
					$page += 1 ;
				}

			} else {
				$hasnext = false ;
			}
		}
		
		return $result;
	}
}

?>