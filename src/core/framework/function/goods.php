<?php
/**
 * 商品图片统一调用函数
 *
 *
 *
 * @package    function* www.hangowa.com汉购网技术交流中心为你提供售后服务 以便你更好的了解
 */
 
/**
 * 取得商品缩略图的完整URL路径，接收商品信息数组，返回所需的商品缩略图的完整URL
 *
 * @param array $goods 商品信息数组
 * @param string $type 缩略图类型  值为60,240,360,1280
 * @return string
 */
function thumb($goods = array(), $type = ''){
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
    if (!in_array($type, $type_array)) {
        $type = '240';
    }
    if (empty($goods)){
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    if (array_key_exists('apic_cover', $goods)) {
        $goods['goods_image'] = $goods['apic_cover'];
    }
    if (empty($goods['goods_image'])) {
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    $search_array = explode(',', GOODS_IMAGES_EXT);
    $file = str_ireplace($search_array,'',$goods['goods_image']);
    $fname = basename($file);
    //取店铺ID
    if (preg_match('/^(\d+_)/',$fname)){
        $store_id = substr($fname,0,strpos($fname,'_'));
    }else{
        $store_id = $goods['store_id'];
    }
    $file = $type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file);
    if (!file_exists(BASE_UPLOAD_PATH.'/'.ATTACH_GOODS.'/'.$store_id.'/'.$file)){
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    $thumb_host = UPLOAD_SITE_URL.'/'.ATTACH_GOODS;
    return $thumb_host.'/'.$store_id.'/'.$file;

}
/**
 * 取得商品缩略图的完整URL路径，接收图片名称与店铺ID
 *
 * @param string $file 图片名称
 * @param string $type 缩略图尺寸类型，值为60,240,360,1280
 * @param mixed $store_id 店铺ID 如果传入，则返回图片完整URL,如果为假，返回系统默认图
 * @return string
 */
function cthumb($file, $type = '', $store_id = false) {
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
    if (!in_array($type, $type_array)) {
        $type = '240';
    }
    if(is_array($file)&&isset($file['goods_image'])){
        $file = $file['goods_image'];
    }
    if (empty($file)) {
        return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
    }
    $search_array = explode(',', GOODS_IMAGES_EXT);
    $file = str_ireplace($search_array,'',$file);
    $fname = basename($file);
    // 取店铺ID
    if ($store_id === false || !is_numeric($store_id)) {
        $store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
    }
    // 本地存储时，增加判断文件是否存在，用默认图代替
    if ( !file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file)) )) {
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    $thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
    return $thumb_host . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file));
}

/**
 * 商品二维码
 * @param array $goods_info
 * @return string
 */
function goodsQRCode($goods_info,$save=true,$force=false) {

    import('Curl');

    $filePath = BASE_UPLOAD_PATH. '/' . ATTACH_STORE . '/' . $goods_info['store_id'] . '/' . $goods_info['goods_id'] . '.png';
    $url = UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.$goods_info['store_id'].DS.$goods_info['goods_id'].'.png';
    if ($force||!file_exists($filePath )) {
        $curl = new Curl();
        $requestUrl = SHOP_SITE_URL.'?act=ajax&op=get_wx_small_app_qr&page=pages%2FgoodsDetails%2FgoodsDetails&scene='.$goods_info['goods_id'];
        if(!$save) return $requestUrl;
        $qrImage = $curl->get($requestUrl);
        file_put_contents($filePath,$qrImage);
    }
    return $url;

    if (!file_exists(BASE_UPLOAD_PATH. '/' . ATTACH_STORE . '/' . $goods_info['store_id'] . '/' . $goods_info['goods_id'] . '.png' )) {
        return UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.'default_qrcode.png';
    }
    return UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.$goods_info['store_id'].DS.$goods_info['goods_id'].'.png';
}

/**
 * 取得抢购缩略图的完整URL路径
 *
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为small,mid,max
 * @return string
 */
function gthumb($image_name = '', $type = ''){
	if (!in_array($type, array('small','mid','max'))) $type = 'small';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	}
    list($base_name, $ext) = explode('.', $image_name);
    list($store_id) = explode('_', $base_name);
    $file_path = ATTACH_GROUPBUY.DS.$store_id.DS.$base_name.'_'.$type.'.'.$ext;
    if(!file_exists(BASE_UPLOAD_PATH.DS.$file_path)) {
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	}
	return UPLOAD_SITE_URL.DS.$file_path;
}
/**
 * 取得买家缩略图的完整URL路径
 *
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为240,1024
 * @return string
 */
function snsThumb($image_name = '', $type = ''){
	if (!in_array($type, array('240','1024'))) $type = '240';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
    }

    list($member_id) = explode('_', $image_name);
	list($year,$member_id) = explode('/', $member_id);
    $file_path = ATTACH_MALBUM.DS.$member_id.DS.str_ireplace('.', '_'.$type.'.', $image_name);
    if(!file_exists(BASE_UPLOAD_PATH.DS.$file_path)) {
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	}
	return UPLOAD_SITE_URL.DS.$file_path;
}



/**
 * 取得积分商品缩略图的完整URL路径
 *
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为small
 * @return string
 */
function pointprodThumb($image_name = '', $type = ''){
	if (!in_array($type, array('small','mid'))) $type = '';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
    }

    if($type) {
        $file_path = ATTACH_POINTPROD.DS.str_ireplace('.', '_'.$type.'.', $image_name);
    } else {
        $file_path = ATTACH_POINTPROD.DS.$image_name;
    }
    if(!file_exists(BASE_UPLOAD_PATH.DS.$file_path)) {
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	}
	return UPLOAD_SITE_URL.DS.$file_path;
}
/**
 * 取得品牌图片
 *
 * @param string $image_name
 * @return string
 */
function brandImage($image_name = '') {
    if ($image_name != '') {
        return UPLOAD_SITE_URL.'/'.ATTACH_BRAND.'/'.$image_name;
    }
    return UPLOAD_SITE_URL.'/'.ATTACH_COMMON.'/default_brand_image.gif';
}

/**
* 取得订单状态文字输出形式
*
* @param array $order_info 订单数组
* @return string $order_state 描述输出
*/
function orderState($order_info) {
    switch ($order_info['order_state']) {
        case ORDER_STATE_CANCEL:
            $order_state = '已取消';
        break;
        case ORDER_STATE_NEW:
            $order_state = '待支付';
        break;
        case ORDER_STATE_TUAN_PAY:
            $order_state = '拼团中';
        break;
        case ORDER_STATE_PAY:
            $order_state = '已支付';
        break;
        case ORDER_STATE_PREPARE:
            $order_state = '备货中';
        break;
        case ORDER_STATE_PART_SEND:
            $order_state = '部分发货';
        break;
        case ORDER_STATE_SEND:
            $order_state = '已发货';
        break;
        case ORDER_STATE_SUCCESS:
            $order_state = '已收货';
        break;
    }
    return $order_state;
}
/**
 * 取得订单支付类型文字输出形式
 *
 * @param array $payment_code
 * @return string
 */
function orderPaymentName($payment_code) {
    return str_replace(
            array('offline','online','alipay','tenpay','chinabank','predeposit','wxpay','fenxiao','jicai','yeepay','bestpay','wx_jsapi'),
            array('货到付款','在线付款','支付宝','财付通','网银在线','站内余额支付','微信支付','分销平台支付','线下集采','易宝支付','翼支付','微信支付'),
            $payment_code);
}

/**
 * 取得订单商品销售类型文字输出形式
 *
 * @param array $goods_type
 * @return string 描述输出
 */
function orderGoodsType($goods_type)
{
	return str_replace(array("1", "2", "3", "4", "5", "8", "9"), array("", "特卖", "限时折扣", "优惠套装", "赠品", "", "换购"), $goods_type);
}
/**
 * 取得结算文字输出形式
 *
 * @param array $bill_state
 * @return string 描述输出
 */
function billState($bill_state)
{
    $state = array(
        '1'=>'已出账',
        '2'=>'商家已确认',
        '3'=>'平台已审核',
        '4'=>'结算完成',
        '5'=>'部分结算',
        '10'=>'汉购网商务审核',
        '11'=>'公司商务审核',
        '12'=>'总经理审核',
        '13'=>'已冻结',
    );
    return $state[$bill_state]?:'';
	return str_replace(array("1", "2", "3", "4"), array("已出账", "商家已确认", "平台已审核", "结算完成"), $bill_state);
}
function shequBillState($bill_state)
{
    $state = array(
        '1'=>'已出账',
        '2'=>'商家已确认',
        '3'=>'平台已审核',
        '4'=>'结算完成',
        '5'=>'部分结算',
        '10'=>'汉购网商务审核',
        '11'=>'汉购网商务已审核',
        '12'=>'财务支付中',
        '13'=>'已冻结',
    );
    return $state[$bill_state]?:'';
	return str_replace(array("1", "2", "3", "4"), array("已出账", "商家已确认", "平台已审核", "结算完成"), $bill_state);
}

/**
 * 取得订单退款进度
 * @param array $order_info
 * @return string 描述输出
 */
function orderRefundStep( $order_info )
{
	$step = "" ;
	
	if( $order_info['lock_state'] > 0 ) {
		$step = '退款退货中' ;
	} else if ( $order_info['refund_state'] > 0 ) {
		$step = '已' . $order_info['refund_state'] . $order_info['refund_amount'] . '元' ;
		$step = str_replace(array('1','2'), array('部分退款','全额退款'), $step) ;
	}
	
	return $step ;
}

/**
 * 根据订单详情，增加订单那商品退款进度
 * @param array $order_info
 * @return array $order_info
 */
function getOrderGoodsRefundStep( $order_info )
{
	if( !isset($order_info['extend_order_goods']) ) return $order_info;
	
	foreach ( $order_info['extend_order_goods'] as &$goods ) {
		$refund = isset($goods['extend_refund']) ? $goods['extend_refund'] : $order_info['refund_all'] ;
		$step = "" ;
		if( $refund['seller_state'] == '1' ) {
			
			$step = "商家审核中" ;
			
		} else if( $refund['seller_state'] == '2' && $refund['refund_state'] == '2' ) {
			
			if ( $refund['kefu_state'] == '1'  ) {
				$step = "客服审核中" ;
			} else if( $refund['kefu_state'] == '2' ) {
				$step = "财务审核中" ;
			}
			
		} else if($refund['seller_state'] == '2' && $refund['refund_state'] == '3') {
			$step = "审核完成" ;
		} else if($refund['seller_state'] == '3' && $refund['refund_state'] == '3'){
			$step = "商家拒绝" ;
		}
		$goods['step'] = $step ;
	}
	
	return $order_info ;
}

/**
 * 订单来源显示
 */
function orderFrom( $from ,$buyer_name='')
{
	$from = str_replace( array(1,2,3,4,5,6,7,8,9), array('PC端','移动端','分销','集采','b2b','微信小程序','抖音小程序','微信小程序','微信小程序'), $from );
    if (!empty($buyer_name) && $from == '分销') {
        $from = getFxNameByUname($buyer_name);
    }
    return $from;
}


/**
 * 根据分销会员用户名获取分销渠道名
 */
function getFxNameByUname( $uname )
{
    $unameArr = array(
            'youzan' => '有赞',
            'renrendian' => '人人店',
            'pinduoduo' => '拼多多',
            'oldhango' => '汉购旧平台',
            'fanli' => '返利',
            'zhe800' => '折800',
            'gegejia' => '格格家',
            'mengdian' => '萌店',
            'juanpi' => '卷皮',
            'taobaofx' => '淘宝',
            'xiaomaolv' => '小毛驴',
            'lvjingnongchang' => '绿净农场',
            'chuchujie' => '楚楚街',
            'chuchujiephs' => '楚楚街拼划算',
            'hanguiren' => '韩贵人',
			'xunshizheshuo' => '寻食者说',
    		'yuanyenongye' => '原野农业',
    		'wutongmao' => '梧桐猫',
    		'hzwd' => '合中味道',
    		'beibeiwang' => '贝贝网',
    		'grsc' => '果然商城',
    		'jingrui' => '京瑞',
        'meiguo' => '美果',
        'pindaojia' => '拼到家',
        'suningyigou' => '苏宁易购',
        'maidouguoyuan' => '麦豆果园',
    );
    $member_fenxiao = Model('member_fenxiao')->getMemberFenxiao();
    $member_fenxiao_out = array();
    foreach($member_fenxiao as $v){
        $member_fenxiao_out[$v['member_en_code']] = $v['member_cn_code'];
    }
    $unameArr = $member_fenxiao_out;
    return empty($unameArr[$uname]) ? '分销' : $unameArr[$uname];
}

/**
 * 进项税
 */
function inputTax( $tax )
{
    $map = array(
        'J1'=>'17.000',
        'J2'=>'13.000',
        'J3'=>'6.000',
        'J4'=>'11.000',
        'J5'=>'16.000',
        'J6'=>'10.000',
    );
    $tax = sprintf("%.3f", $tax);
    $code = array_search($tax,$map);
    if($code) return $code;
    return 'J0';
    $tax = sprintf("%.3f", $tax);
    if (in_array($tax, array('17.000','13.000','6.000','11.000','16.000','10.000'))) {
        $taxCode = str_replace( array('17.000','13.000','6.000','11.000','16.000','10.000'), array('J1','J2','J3','J4','J5','J6'), $tax );
    } else {
        $taxCode = 'J0';
    }
	return $taxCode;
}
/**
 * 销项税
 */
function outputTax( $tax )
{
    $map = array(
        'X1'=>'17.000',
        'X2'=>'13.000',
        'X3'=>'6.000',
        'X4'=>'11.000',
        'X5'=>'16.000',
        'X6'=>'10.000',
    );
    $tax = sprintf("%.3f", $tax);
    $code = array_search($tax,$map);
    if($code) return $code;
    return 'X0';
    if (in_array($tax, array('17.000','13.000','6.000','11.000','16.000','10.000'))) {

        $taxCode = str_replace( array('17.000','13.000','6.000','11.000','16.000','10.000'), array('X1','X2','X3','X4','X5','X6'), $tax );
    } else {
        $taxCode = 'X0';
    }
    return $taxCode;
}

/**
 * 删除商品缓存数据
 *
 * @param array $insert 数据
 * @param string $table 表名
 */
function delGoodsCache($goods_id=0, $goods_commonid=0)
{
    if (empty($goods_id) && empty($goods_commonid)) {
        return;
    }
    $goods_id = intval($goods_id);
    $goods_commonid = intval($goods_commonid);

    //删除goods缓存
    if (empty($goods_id)) {
        $goods_info =Model('goods')->getGoodsInfo(array('goods_commonid'=>$goods_commonid) ,'goods_id');
        $goods_id = $goods_info['goods_id'];
    }
    dcache($goods_id ,'goods');
    dcache($goods_id ,'product');

    //删除goods_common缓存
    if (empty($goods_commonid)) {
        $goods_common =Model('goods')->getGoodsInfo(array('goods_id'=>$goods_id) ,'goods_commonid');
        $goods_commonid = $goods_common['goods_commonid'];
    }

    dcache($goods_commonid, 'goods_common');
    dcache($goods_commonid, 'goods_spec');
    //@file_get_contents("http://www.hangowa.com/item-{$goods_id}.html?clean=123456");
}

defined("ByShopWWI") || exit("Access Invalid!");

?>
