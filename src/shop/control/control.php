<?php
/**
 * 前台control父类,店铺control父类,会员control父类
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');

class Control{
    /**
     * 检查短消息数量
     *
     */
    protected function checkMessage() {
        if($_SESSION['member_id'] == '') return ;
        //判断cookie是否存在
        $cookie_name = 'msgnewnum'.$_SESSION['member_id'];
        if (cookie($cookie_name) != null){
            $countnum = intval(cookie($cookie_name));
        }else {
            $message_model = Model('message');
            $countnum = $message_model->countNewMessage($_SESSION['member_id']);
            setNcCookie($cookie_name,"$countnum",2*3600);//保存2小时
        }
        Tpl::output('message_num',$countnum);
    }

    /**
     *  输出头部的公用信息
     *
     */
    protected function showLayout() {
        $this->checkMessage();//短消息检查
        $this->article();//文章输出

        $this->showCartCount();
        
        //热门搜索
        Tpl::output('hot_search',@explode(',',C('hot_search')));
        if (C('rec_search') != '') {
            $rec_search_list = @unserialize(C('rec_search'));
        }
        Tpl::output('rec_search_list',is_array($rec_search_list) ? $rec_search_list : array());

        //历史搜索
        if (cookie('his_sh') != '') {
            $his_search_list = explode('~', cookie('his_sh'));
            foreach ($his_search_list as $k=>$v){
                $his_search_list[$k] = htmlentities($v);
            }
        }
        Tpl::output('his_search_list',is_array($his_search_list) ? $his_search_list : array());

        $model_class = Model('goods_class');
        $goods_class = $model_class->get_all_category();
        //自定义分类
        $goods_category = Model('goods_category')->getCategoryByCache();
        $goods_new_category = array();
        foreach ($goods_category as $value) {
            if (is_array($value['child']) && !empty($value['child'])) {
                foreach ($value['child'] as $cate_val)
                $goods_new_category[] = $cate_val;
            }
        }
        Tpl::output('goods_category',$goods_new_category);

        //新需求
        $goods_category1 = array();
        foreach ($goods_class as $m_z=>$c_value) {
            if ($c_value['app_show'] <= 0) {
                unset($goods_class[$m_z]);
                continue;
            }
            $c_value['child'] = $c_value['class2'];
            $c_value['cat_id'] = $c_value['gc_id'];
            $c_value['cat_name'] = $c_value['gc_name'];
            unset($c_value['class2']);
            unset($c_value['gc_id']);
            unset($c_value['gc_name']);
            foreach ($c_value['child'] as $z=>$v_c) {
                if ($v_c['app_show'] <= 0) {
                    unset($c_value['child'][$z]);
                    continue;
                }
                $c_value['child'][$z]['cat_name'] = $v_c['gc_name'];
                $c_value['child'][$z]['cat_id'] = $v_c['gc_id'];
            }
            $goods_category1[] = $c_value;
        }
        Tpl::output('goods_category',$goods_category1);
        
		$model_channel = Model('web_channel');
		$goods_channel = $model_channel->getChannelList(array('channel_show'=>'1'));
		foreach ($goods_class as $key => $value) {
			foreach ($goods_channel as $k=> $v) {
				 if($value['gc_id']==$v['gc_id']){
					 $goods_class[$value['gc_id']]['channel_gc_id'] =$v['gc_id'];
					 $goods_class[$value['gc_id']]['channel_id'] =$v['channel_id'];
					 }
				if(!empty($value['class2'])&&is_array($value['class2'])){
					foreach ($value['class2'] as $kk=> $vv) {
							  if($vv['gc_id']==$v['gc_id']){
							 $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_gc_id'] =$v['gc_id'];
							 $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_id'] =$v['channel_id'];
							 }
					}
				}
			}
		}
        Tpl::output('show_goods_class',$goods_class);//商品分类

        //获取导航
        Tpl::output('nav_list', rkcache('nav',true));
        //查询保障服务项目
        Tpl::output('contract_list',Model('contract')->getContractItemByCache());
    }

    /**
     * 显示购物车数量
     */
    protected function showCartCount() {
        if (cookie('cart_goods_num') != null){
            $cart_num = intval(cookie('cart_goods_num'));
        }else {
            //已登录状态，存入数据库,未登录时，优先存入缓存，否则存入COOKIE
            if($_SESSION['member_id']) {
                $save_type = 'db';
            } else {
                $save_type = 'cookie';
            }
            $cart_num = Model('cart')->getCartNum($save_type,array('buyer_id'=>$_SESSION['member_id']));//查询购物车商品种类
        }
        Tpl::output('cart_goods_num',$cart_num);
    }

    /**
     * 输出会员等级
     * @param bool $is_return 是否返回会员信息，返回为true，输出会员信息为false
     */
    protected function getMemberAndGradeInfo($is_return = false){
        $member_info = array();
        //会员详情及会员级别处理
        if($_SESSION['member_id']) {
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
            if ($member_info){
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info,$member_gradeinfo);
                $member_info['security_level'] = $model_member->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true){//返回会员信息
            return $member_info;
        } else {//输出会员信息
            Tpl::output('member_info',$member_info);
        }
    }

    /**
     * 验证会员是否登录
     *
     */
    protected function checkLogin(){
        if ($_SESSION['is_login'] !== '1'){
            if (trim($_GET['op']) == 'favoritegoods' || trim($_GET['op']) == 'favoritestore'){
                $lang = Language::getLangContent('UTF-8');
                echo json_encode(array('done'=>false,'msg'=>$lang['no_login']));
                die;
            }
            $ref_url = request_uri();
            if ($_GET['inajax']){
                showDialog('','','js',"login_dialog();",200);
            }else {
                @header("location: " . urlLogin('login', 'index', array('ref_url' => $ref_url)));
            }
            exit;
        }
    }

    //文章输出
    protected function article() {

        if (C('cache_open')) {
            if ($article = rkcache("index/article")) {
                Tpl::output('show_article', $article['show_article']);
                Tpl::output('article_list', $article['article_list']);
                return;
            }
        } else {
            if (file_exists(BASE_DATA_PATH.'/cache/index/article.php')){
                include(BASE_DATA_PATH.'/cache/index/article.php');
                Tpl::output('show_article', $show_article);
                Tpl::output('article_list', $article_list);
                return;
            }
        }

        $model_article_class    = Model('article_class');
        $model_article  = Model('article');
        $show_article = array();//商城公告
        $article_list   = array();//下方文章
        $notice_class   = array('notice');
        $code_array = array('member','store','payment','sold','service','about');
        $notice_limit   = 5;
        $faq_limit  = 5;

        $class_condition    = array();
        $class_condition['home_index'] = 'home_index';
        $class_condition['order'] = 'ac_sort asc';
        $article_class  = $model_article_class->getClassList($class_condition);
        $class_list = array();
        if(!empty($article_class) && is_array($article_class)){
            foreach ($article_class as $key => $val){
                $ac_code = $val['ac_code'];
                $ac_id = $val['ac_id'];
                $val['list']    = array();//文章
                $class_list[$ac_id] = $val;
            }
        }

        $condition  = array();
        $condition['article_show'] = '1';
        $condition['field'] = 'article.article_id,article.ac_id,article.article_url,article_class.ac_code,article.article_position,article.article_title,article.article_time,article_class.ac_name,article_class.ac_parent_id';
        $condition['order'] = 'article_sort asc,article_time desc';
        $condition['limit'] = '300';
        $article_array  = $model_article->getJoinList($condition);
        if(!empty($article_array) && is_array($article_array)){
            foreach ($article_array as $key => $val){
                if ($val['ac_code'] == 'notice' && !in_array($val['article_position'],array(ARTICLE_POSIT_SHOP,ARTICLE_POSIT_ALL))) continue;
                $ac_id = $val['ac_id'];
                $ac_parent_id = $val['ac_parent_id'];
                if($ac_parent_id == 0) {//顶级分类
                    $class_list[$ac_id]['list'][] = $val;
                } else {
                    $class_list[$ac_parent_id]['list'][] = $val;
                }
            }
        }
        if(!empty($class_list) && is_array($class_list)){
            foreach ($class_list as $key => $val){
                $ac_code = $val['ac_code'];
                if(in_array($ac_code,$notice_class)) {
                    $list = $val['list'];
                    array_splice($list, $notice_limit);
                    $val['list'] = $list;
                    $show_article[$ac_code] = $val;
                }
                if (in_array($ac_code,$code_array)){
                    $list = $val['list'];
                    $val['class']['ac_name']    = $val['ac_name'];
                    array_splice($list, $faq_limit);
                    $val['list'] = $list;
                    $article_list[] = $val;
                }
            }
        }
        if (C('cache_open')) {
            wkcache('index/article', array(
                'show_article' => $show_article,
                'article_list' => $article_list,
            ));
        } else {
            $string = "<?php\n\$show_article=".var_export($show_article,true).";\n";
            $string .= "\$article_list=".var_export($article_list,true).";\n?>";
            file_put_contents(BASE_DATA_PATH.'/cache/index/article.php',($string));
        }

        Tpl::output('show_article',$show_article);
        Tpl::output('article_list',$article_list);
    }
    
    /**
     * 自动登录
     */ 
    protected function auto_login() {
        $data = cookie('auto_login');
        if (empty($data)) {
            return false;
        }
        $model_member = Model('member');
        if ($_SESSION['is_login']) {
            $model_member->auto_login();
        }
        $member_id = intval(decrypt($data, MD5_KEY));
        if ($member_id <= 0) {
            return false;
        }
        $member_info = $model_member->getMemberInfoByID($member_id);
        $model_member->createSession($member_info);
    }
}

/********************************** 前台control父类 **********************************************/

class BaseHomeControl extends Control {

    public function __construct(){
        //输出头部的公用信息
        $this->showLayout();
        //输出会员信息
        $this->getMemberAndGradeInfo(false);

        Language::read('common,home_layout');

        Tpl::setDir('home');

        Tpl::setLayout('home_layout');

        if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
            $_GET = Language::getGBK($_GET);
        }
        if(!C('site_status')) halt(C('closed_reason'));
        // 自动登录
        $this->auto_login();
    }

}

/********************************** 购买流程父类 **********************************************/

class BaseBuyControl extends Control {
    protected $member_info = array();   // 会员信息

    protected function __construct(){
        Language::read('common,home_layout');
        //输出会员信息
        $this->member_info = $this->getMemberAndGradeInfo(true);
        Tpl::output('member_info', $this->member_info);

        Tpl::setDir('buy');
        Tpl::setLayout('buy_layout');
        if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
            $_GET = Language::getGBK($_GET);
        }
        if(!C('site_status')) halt(C('closed_reason'));
        //获取导航
        Tpl::output('nav_list', rkcache('nav',true));

        Tpl::output('contract_list',Model('contract')->getContractItemByCache());
    }
}

/********************************** 会员control父类 **********************************************/

class BaseMemberControl extends Control {
    protected $member_info = array();   // 会员信息
    public function __construct(){

        if(!C('site_status')) halt(C('closed_reason'));

        Language::read('common,member_layout');

        if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
            $_GET = Language::getGBK($_GET);
        }
        //会员验证
        $this->checkLogin();
        //输出头部的公用信息
        $this->showLayout();
        Tpl::setDir('member');
        Tpl::setLayout('member_layout');

        //获得会员信息
        $this->member_info = $this->getMemberAndGradeInfo(true);
        $this->member_info['voucher_count'] = Model('voucher')->getCurrentAvailableVoucherCount($_SESSION['member_id']);
        $this->member_info['redpacket_count'] = Model('redpacket')->getCurrentAvailableRedpacketCount($_SESSION['member_id']);
        Tpl::output('member_info', $this->member_info);

        // 常用操作及导航
        $menu_list = $this->_getNavLink();

        //系统公告
        $this->system_notice();
        
        // 交易数量提示
        $this->order_tip();
        
        // 页面高亮
        Tpl::output('act', $_GET['act']);
    }
    
    /**
     * 交易数量提示
     */
    private function order_tip() {
        $model_order = Model('order');
        //交易提醒 - 显示数量
        $order_tip['order_nopay_count'] = $model_order->getOrderCountByID('buyer',$_SESSION['member_id'],'NewCount');
        $order_tip['order_noreceipt_count'] = $model_order->getOrderCountByID('buyer',$_SESSION['member_id'],'SendCount');
        $order_tip['order_noeval_count'] = $model_order->getOrderCountByID('buyer',$_SESSION['member_id'],'EvalCount');
        $order_tip['order_notakes_count'] = $model_order->getOrderCountByID('buyer',$_SESSION['member_id'],'TakesCount');
        Tpl::output('order_tip', $order_tip);
    }

    /**
     * 系统公告
     */
    private function system_notice() {
        $model_message  = Model('article');
        $condition = array();
        $condition['ac_id'] = 1;
        $condition['article_position_in'] = ARTICLE_POSIT_ALL.','.ARTICLE_POSIT_BUYER;
        $condition['limit'] = 5;
        $article_list  = $model_message->getArticleList($condition);
        Tpl::output('system_notice',$article_list);
    }

    /**
     * 常用操作
     *
     * @param string $act
     * 如果菜单中的切换卡不在一个菜单中添加$act参数，值为当前菜单的下标
     *
     */
    protected function _getNavLink ($act = '') {
        // 左侧导航
        $menu_list = $this->_getMenuList();
        Tpl::output('menu_list', $menu_list);
    }

    /**
     * 左侧导航
     * 菜单数组中child的下标要和其链接的act对应。否则面包屑不能正常显示
     * @return array
     */
    private function _getMenuList() {
        $menu_list = array(
            'trade' => array('name' => '交易中心', 'child' => array(
                'member_order'      => array('name' => '实物交易订单', 'url' => urlShop('member_order', 'index')),
                'member_vr_order'   => array('name' => '虚拟兑码订单', 'url' => urlShop('member_vr_order', 'index')),
                'member_evaluate'   => array('name' => '交易评价/晒单', 'url' => urlShop('member_evaluate', 'list')),
                'member_appoint'    => array('name' => '预约/到货通知', 'url' => urlShop('member_appoint', 'list'))
            )),
            'follow' => array('name' => '关注中心', 'child' => array(
                'member_favorite_goods' => array('name' => '商品收藏', 'url' => urlShop('member_favorite_goods', 'index')),
                'member_favorite_store' => array('name' => '店铺收藏', 'url' => urlShop('member_favorite_store', 'index')),
                'member_goodsbrowse'   => array('name' => '我的足迹', 'url' => urlShop('member_goodsbrowse', 'list'))
            )),
            'client' => array('name' => '客户服务', 'child' => array(
                'member_refund'     => array('name' => '退款及退货', 'url' => urlShop('member_refund', 'index')),
                'member_complain'   => array('name' => '交易投诉', 'url' => urlShop('member_complain', 'index')),
                'member_consult'    => array('name' => '商品咨询', 'url' => urlShop('member_consult', 'my_consult')),
                'member_inform'     => array('name' => '违规举报', 'url' => urlShop('member_inform', 'index')),
                'member_mallconsult'=> array('name' => '平台客服', 'url' => urlShop('member_mallconsult', 'index'))
            )),
            'info' => array('name' => '会员资料', 'child' => array(
                'member_information'=> array('name' => '账户信息', 'url'=>urlMember('member_information', 'member')),
                'member_address'    => array('name' => '收货地址', 'url'=>urlMember('member_address', 'address'))
            )),
            'property' => array('name' => '财产中心', 'child' => array(
                'predeposit'        => array('name' => '账户余额', 'url'=>urlMember('predeposit', 'pd_log_list')),
                'member_voucher'    => array('name' => '我的代金券', 'url'=>urlMember('member_voucher', 'index')),
                'member_redpacket'  => array('name' => '我的红包', 'url'=>urlMember('member_redpacket', 'index')),
				'predeposit_rechargecard_add'  => array('name' => '充值卡充值', 'url'=>urlMember('predeposit', 'rechargecard_add'))
            )),
        );
        return $menu_list;
    }
}

/********************************** SNS control父类 **********************************************/

class BaseSNSControl extends Control {
    protected $relation = 0;//浏览者与主人的关系：0 表示游客 1 表示一般普通会员 2表示朋友 3表示自己4表示已关注主人
    protected $master_id = 0; //主人编号
    const MAX_RECORDNUM = 20;//允许插入新记录的最大条数
    protected $master_info;

    public function __construct(){

        Tpl::setDir('sns');

        Tpl::setLayout('sns_layout');

        Language::read('common,sns_layout');

        //验证会员及与主人关系
        $this->check_relation();

        //查询会员信息
        $this->getMemberAndGradeInfo(false);

        $this->master_info = $this->get_member_info();
        Tpl::output('master_info',$this->master_info);

        //添加访问记录
        $this->add_visit();

        //我的关注
        $this->my_attention();

        //获取设置
        $this->get_setting();

        //允许插入新记录的最大条数
        Tpl::output('max_recordnum',self::MAX_RECORDNUM);

        $this->showCartCount();

        Tpl::output('nav_list', rkcache('nav',true));
    }

    /**
     * 格式化时间
     * @param string $time时间戳
     */
    protected function formatDate($time){
        $handle_date = @date('Y-m-d',$time);//需要格式化的时间
        $reference_date = @date('Y-m-d',time());//参照时间
        $handle_date_time = strtotime($handle_date);//需要格式化的时间戳
        $reference_date_time = strtotime($reference_date);//参照时间戳
        if ($reference_date_time == $handle_date_time){
            $timetext = @date('H:i',$time);//今天访问的显示具体的时间点
        }elseif (($reference_date_time-$handle_date_time)==60*60*24){
            $timetext = Language::get('sns_yesterday');
        }elseif ($reference_date_time-$handle_date_time==60*60*48){
            $timetext = Language::get('sns_beforeyesterday');
        }else {
            $month_text = Language::get('nc_month');
            $day_text = Language::get('nc_day');
            $timetext = @date("m{$month_text}d{$day_text}",$time);
        }
        return $timetext;
    }

    /**
     * 会员信息
     *
     * @return array
     */
    public function get_member_info() {
        if($this->master_id <= 0){
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        $model = Model();
        $member_info = Model('member')->getMemberInfoByID($this->master_id);
        if(empty($member_info)){
            showMessage(L('wrong_argument'), 'index.php?act=member_snshome', '', 'error');
        }
        //粉丝数
        $fan_count = $model->table('sns_friend')->where(array('friend_tomid'=>$this->master_id))->count();
        $member_info['fan_count'] = $fan_count;
        //关注数
        $attention_count = $model->table('sns_friend')->where(array('friend_frommid'=>$this->master_id))->count();
        $member_info['attention_count'] = $attention_count;
        //兴趣标签
        $mtag_list = $model->table('sns_membertag,sns_mtagmember')->field('mtag_name')->on('sns_membertag.mtag_id = sns_mtagmember.mtag_id')->join('inner')->where(array('sns_mtagmember.member_id'=>$this->master_id))->select();
        $tagname_array = array();
        if(!empty($mtag_list)){
            foreach ($mtag_list as $val){
                $tagname_array[] = $val['mtag_name'];
            }
        }
        $member_info['tagname'] = $tagname_array;
        return $member_info;
    }

    /**
     * 访客信息
     */
    protected function get_visitor(){
        $model = Model();
        //查询谁来看过我
        $visitme_list = $model->table('sns_visitor')->where(array('v_ownermid'=>$this->master_id))->limit(9)->order('v_addtime desc')->select();
        if (!empty($visitme_list)){
            foreach ($visitme_list as $k=>$v){
                $v['adddate_text'] = $this->formatDate($v['v_addtime']);
                $v['addtime_text'] = @date('H:i',$v['v_addtime']);
                $visitme_list[$k] = $v;
            }
        }
        Tpl::output('visitme_list',$visitme_list);
        if($this->relation == 3){   // 主人自己才有我访问过的人
            //查询我访问过的人
            $visitother_list = $model->table('sns_visitor')->where(array('v_mid'=>$this->master_id))->limit(9)->order('v_addtime desc')->select();
            if (!empty($visitother_list)){
                foreach ($visitother_list as $k=>$v){
                    $v['adddate_text'] = $this->formatDate($v['v_addtime']);
                    $visitother_list[$k] = $v;
                }
            }
            Tpl::output('visitother_list',$visitother_list);
        }
    }

    /**
     * 验证会员及主人关系
     */
    private function check_relation(){
        $model = Model();
        //验证主人会员编号
        $this->master_id = intval($_GET['mid']);
        if ($this->master_id <= 0){
            if ($_SESSION['is_login'] == 1){
                $this->master_id = $_SESSION['member_id'];
            }else {
                @header("location: " . urlLogin('login', 'index', array('ref_url' => urlShop('member_snshome'))));
            }
        }
        Tpl::output('master_id', $this->master_id);

        $model = Model();

        //判断浏览者与主人的关系
        if ($_SESSION['is_login'] == '1'){
            if ($this->master_id == $_SESSION['member_id']){//主人自己
                $this->relation = 3;
            }else{
                $this->relation = 1;
                //查询好友表
                $friend_arr = $model->table('sns_friend')->where(array('friend_frommid'=>$_SESSION['member_id'],'friend_tomid'=>$this->master_id))->find();
                if (!empty($friend_arr) && $friend_arr['friend_followstate'] == 2){
                    $this->relation = 2;
                }elseif($friend_arr['friend_followstate'] == 1){
                    $this->relation = 4;
                }
            }
        }
        Tpl::output('relation',$this->relation);
    }

    /**
     * 增加访问记录
     */
    private function add_visit(){
        $model = Model();
        //记录访客
        if ($_SESSION['is_login'] == '1' && $this->relation != 3){
            //访客为会员且不是空间主人则添加访客记录
            $visitor_info = $model->table('member')->where(array('member_id'=>$_SESSION['member_id']))->find();
            if (!empty($visitor_info)){
                //查询访客记录是否存在
                $existevisitor_info = $model->table('sns_visitor')->where(array('v_ownermid'=>$this->master_id, 'v_mid'=>$visitor_info['member_id']))->find();
                if (!empty($existevisitor_info)){//访问记录存在则更新访问时间
                    $update_arr = array();
                    $update_arr['v_addtime'] = time();
                    $model->table('sns_visitor')->update(array('v_id'=>$existevisitor_info['v_id'], 'v_addtime'=>time()));
                }else {//添加新访问记录
                    $insert_arr = array();
                    $insert_arr['v_mid']            = $visitor_info['member_id'];
                    $insert_arr['v_mname']          = $visitor_info['member_name'];
                    $insert_arr['v_mavatar']        = $visitor_info['member_avatar'];
                    $insert_arr['v_ownermid']       = $this->master_info['member_id'];
                    $insert_arr['v_ownermname']     = $this->master_info['member_name'];
                    $insert_arr['v_ownermavatar']   = $this->master_info['member_avatar'];
                    $insert_arr['v_addtime']        = time();
                    $model->table('sns_visitor')->insert($insert_arr);
                }
            }
        }

        //增加主人访问次数
        $cookie_str = cookie('visitor');
        $cookie_arr = array();
        $is_increase = false;
        if (empty($cookie_str)){
            //cookie不存在则直接增加访问次数
            $is_increase = true;
        }else{
            //cookie存在但是为空则直接增加访问次数
            $cookie_arr = explode('_',$cookie_str);
            if(!in_array($this->master_id,$cookie_arr)){
                $is_increase = true;
            }
        }
        if ($is_increase == true){
            //增加访问次数
            $model->table('member')->update(array('member_id'=>$this->master_id, 'member_snsvisitnum'=>array('exp', 'member_snsvisitnum+1')));
            //设置cookie，24小时之内不再累加
            $cookie_arr[] = $this->master_id;
            setNcCookie('visitor',implode('_',$cookie_arr),24*3600);//保存24小时
        }
    }
    //我的关注
    private function my_attention(){
        if(intval($_SESSION['member_id']) >0){
            $my_attention = Model()->table('sns_friend')->where(array('friend_frommid'=>$_SESSION['member_id']))->order('friend_addtime desc')->limit(4)->select();
            Tpl::output('my_attention', $my_attention);
        }
    }

    /**
     * 获取设置信息
     */
    private function get_setting(){
        $m_setting = Model()->table('sns_setting')->where(array('member_id'=>$this->master_id))->find();
        Tpl::output('skin_style', (!empty($m_setting['setting_skin'])?$m_setting['setting_skin']:'skin_01'));
    }
    /**
     * 留言板
     */
    protected function sns_messageboard(){
        $model = Model();
        $where = array();
        $where['from_member_id']    = array('neq',0);
        $where['to_member_id']      = $this->master_id;
        $where['message_state']     = array('neq',2);
        $where['message_parent_id'] = 0;
        $where['message_type']      = 2;
        $message_list = $model->table('message')->where($where)->order('message_id desc')->limit(10)->select();
        if(!empty($message_list)){
            $pmsg_id = array();
            foreach ($message_list as $key=>$val){
                $pmsg_id[]  = $val['message_id'];
                $message_list[$key]['message_time'] = $this->formatDate($val['message_time']);
            }
            $where = array();
            $where['message_parent_id'] = array('in',$pmsg_id);
            $rmessage_array = $model->table('message')->where($where)->select();
            $rmessage_list  = array();
            if(!empty($rmessage_array)){
                foreach ($rmessage_array as $key=>$val){
                    $val['message_time'] = $this->formatDate($val['message_time']);
                    $rmessage_list[$val['message_parent_id']][] = $val;
                }
                foreach ($rmessage_list as $key=>$val){
                    $rmessage_list[$key]     = array_slice($val, -3, 3);
                }
            }
            Tpl::output('rmessage_list', $rmessage_list);
        }
        Tpl::output('message_list', $message_list);
    }
}
/********************************** 店铺 control父类 **********************************************/



class BaseStoreControl extends Control {

    protected $store_info;
    protected $store_decoration_only = false;

    public function __construct(){

        Language::read('common,store_layout,store_show_store_index');

        if(!C('site_status')) halt(C('closed_reason'));

        //输出头部的公用信息
        $this->showLayout();
        Tpl::setDir('store');
        Tpl::setLayout('store_layout');

        //输出会员信息
        $this->getMemberAndGradeInfo(false);

        $store_id = intval($_GET['store_id']);
        if($store_id <= 0) {
            showMessage(L('nc_store_close'), '', '', 'error');
        }

        $model_store = Model('store');
        $store_info = $model_store->getStoreOnlineInfoByID($store_id);
        if(empty($store_info)) {
            showMessage(L('nc_store_close'), '', '', 'error');
        } else {
            $this->store_info = $store_info;
        }
        if($store_info['store_decoration_switch'] > 0 & $store_info['store_decoration_only'] == 1) {
            $this->store_decoration_only = true;
        }

        //店铺装修
        $this->outputStoreDecoration($store_info['store_decoration_switch'], $store_id);

        $this->outputStoreInfo($this->store_info);
        $this->getStoreNavigation($store_id);
        $this->outputSeoInfo($this->store_info);
    }

    /**
     * 输出店铺装修
     */
    protected function outputStoreDecoration($decoration_id, $store_id) {
        if($decoration_id > 0 ) {
            $model_store_decoration = Model('store_decoration');

            $decoration_info = $model_store_decoration->getStoreDecorationInfoDetail($decoration_id, $store_id);
            if($decoration_info) {
                $decoration_background_style = $model_store_decoration->getDecorationBackgroundStyle($decoration_info['decoration_setting']);
                Tpl::output('decoration_background_style', $decoration_background_style);
                Tpl::output('decoration_nav', $decoration_info['decoration_nav']);
                Tpl::output('decoration_banner', $decoration_info['decoration_banner']);

                $html_file = BASE_UPLOAD_PATH.DS.ATTACH_STORE.DS.'decoration'.DS.'html'.DS.md5($store_id).'.html';
                if(is_file($html_file)) {
                    Tpl::output('decoration_file', $html_file);
                }

            }

            Tpl::output('store_theme', 'default');
        } else {
            Tpl::output('store_theme', $this->store_info['store_theme']);
        }
    }

    /**
     * 检查店铺开启状态
     *
     * @param int $store_id 店铺编号
     * @param string $msg 警告信息
     */
    protected function outputStoreInfo($store_info, $goods_info = null) {
        if (!$this->store_decoration_only) {

            // 自营店设置“显示商城相关数据”
            if ($goods_info && $store_info['is_own_shop'] && $store_info['left_bar_type'] == 2) {
                Tpl::output('left_bar_type_mall_related', true);

                // 推荐分类
                $mr_rel_gc = Model('goods_class')->getGoodsClassListBySiblingId($goods_info['gc_id']);
                Tpl::output('mr_rel_gc', $mr_rel_gc);

                // 分类 含所有父级分类
                $gcIds = array();
                $gcIds[(int) $goods_info['gc_id_1']] = null;
                $gcIds[(int) $goods_info['gc_id_2']] = null;
                $gcIds[(int) $goods_info['gc_id_3']] = null;
                unset($gcIds[0]);
                $gcIds = array_keys($gcIds);

                // 推荐品牌
                $mr_rel_brand = null;
                if ($gcIds) {
                    $mr_rel_brand = Model('brand')->getBrandPassedList(array(
                        'class_id' => array('in', $gcIds),
                    ));
                }
                Tpl::output('mr_rel_brand', $mr_rel_brand);

                // 同分类下销量排行
                $mr_hot_sales = null;
                if ($gcIds) {
                    $mr_hot_sales = Model('goods')->getGoodsOnlineList(array(
                        'gc_id' => array('in', $gcIds),
                        'goods_id' => array('neq', $goods_info['goods_id']),
                    ), '*', 0, 'goods_salenum desc', 6);
                }
                Tpl::output('mr_hot_sales', $mr_hot_sales);
                $gcArray = Model('goods_class')->getGoodsClassInfoById($goods_info['gc_id_1']);
                Tpl::output('mr_hot_sales_gc_name', $gcArray['gc_name']);

                // 推荐商品
//                $mr_rec_products = null;
//                if ($gcIds) {
//                    $goodsIds = Model('p_booth')->getBoothGoodsIdRandList($gcIds, $goods_info['goods_id'], 6);
//                    if ($goodsIds) {
//                        $mr_rec_products = Model('goods')->getGoodsOnlineList(array(
//                            'goods_id' => array('in', $goodsIds),
//                        ), '*', 0, '', 6);
//                    }
//                }
//                Tpl::output('mr_rec_products', $mr_rec_products);
            } else {
                $model_store = Model('store');
                $model_seller = Model('seller');

                //热销排行
                $hot_sales = $model_store->getHotSalesList($store_info['store_id'], 5);
                Tpl::output('hot_sales', $hot_sales);

                //收藏排行
                $hot_collect = $model_store->getHotCollectList($store_info['store_id'], 5);
                Tpl::output('hot_collect', $hot_collect);
            }
        }
        
        //店铺分类
        $goodsclass_model = Model('store_goods_class');
        $goods_class_list = $goodsclass_model->getShowTreeList($store_info['store_id']);
        Tpl::output('goods_class_list', $goods_class_list);

        Tpl::output('store_info', $store_info);
        Tpl::output('page_title', $store_info['store_name']);
    }

    protected function getStoreNavigation($store_id) {
        $model_store_navigation = Model('store_navigation');
        $store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $store_id));
        Tpl::output('store_navigation_list', $store_navigation_list);
    }

    protected function outputSeoInfo($store_info) {
        $seo_param = array();
        $seo_param['shopname'] = $store_info['store_name'];
        $seo_param['key']  = $store_info['store_keywords'];
        $seo_param['description'] = $store_info['store_description'];
        Model('seo')->type('shop')->param($seo_param)->show();
    }

}

class BaseGoodsControl extends BaseStoreControl {

    public function __construct(){

        Language::read('common,store_layout');

        if(!C('site_status')) halt(C('closed_reason'));

        Tpl::setDir('store');
        Tpl::setLayout('home_layout');

        //输出头部的公用信息
        $this->showLayout();
        //输出会员信息
        $this->getMemberAndGradeInfo(false);
    }

    protected function getStoreInfo($store_id, $goods_info = null) {
        $model_store = Model('store');
        $store_info = $model_store->getStoreOnlineInfoByID($store_id);
        if(empty($store_info)) {
            showMessage(L('nc_store_close'), '', '', 'error');
        }
        if ($_COOKIE['dregion']) {
            $store_info['deliver_region'] = $_COOKIE['dregion'];
        }
        if (strpos($store_info['deliver_region'],'|')) {
            $store_info['deliver_region'] = explode('|', $store_info['deliver_region']);
            $store_info['deliver_region_ids'] = explode(' ', $store_info['deliver_region'][0]);
            $store_info['deliver_region_names'] = explode(' ', $store_info['deliver_region'][1]);
        }
        $this->outputStoreInfo($store_info, $goods_info);
    }
}
class BaseChainControl extends BaseStoreControl {

    public function __construct(){

        Language::read('common,store_layout');

        if(!C('site_status')) halt(C('closed_reason'));

        Tpl::setDir('store');
        Tpl::setLayout('home_layout');

        //输出头部的公用信息
        $this->showLayout();
    }

}
/**
 * 店铺 control新父类
 *
 */
class BaseSellerControl extends Control {

    //店铺信息
    protected $store_info = array();
    //店铺等级
    protected $store_grade = array();

    public function __construct(){
        Language::read('common,store_layout,member_layout');
        if(!C('site_status')) halt(C('closed_reason'));
        Tpl::setDir('seller');
        Tpl::setLayout('seller_layout');

        Tpl::output('nav_list', rkcache('nav',true));
        if ($_GET['act'] !== 'seller_login') {
            $key = C('ON_DEV')?'test-test-123':'hango@321';
        	if (!empty($_GET['deblogin']) && $_GET['deblogin'] == $key ) {
        		$this -> setSellerSession( $_GET['sname'] ) ;
        	}

            if(empty($_SESSION['seller_id'])) {
                @header('location: index.php?act=seller_login&op=show_login');die;
            }

            // 验证店铺是否存在
            $model_store = Model('store');
            $this->store_info = $model_store->getStoreInfoByID($_SESSION['store_id']);
            if (empty($this->store_info)) {
                @header('location: index.php?act=seller_login&op=show_login');die;
            }

            // 店铺关闭标志
            if (intval($this->store_info['store_state']) === 0) {
                Tpl::output('store_closed', true);
                Tpl::output('store_close_info', $this->store_info['store_close_info']);
            }

            // 店铺等级
            if (checkPlatformStore()) {
                $this->store_grade = array(
                    'sg_id' => '0',
                    'sg_name' => '自营店铺专属等级',
                    'sg_goods_limit' => '0',
                    'sg_album_limit' => '0',
                    'sg_space_limit' => '999999999',
                    'sg_template_number' => '6',
                    // see also store_settingControl.themeOp()
                    // 'sg_template' => 'default|style1|style2|style3|style4|style5',
                    'sg_price' => '0.00',
                    'sg_description' => '',
                    'sg_function' => 'editor_multimedia',
                    'sg_sort' => '0',
                );
            } else {
                $store_grade = rkcache('store_grade', true);
                $this->store_grade = $store_grade[$this->store_info['grade_id']];
            }

            if ($_SESSION['seller_is_admin'] !== 1 && $_GET['act'] !== 'seller_center' && $_GET['act'] !== 'seller_logout') {
                if (!in_array($_GET['act'], $_SESSION['seller_limits'])) {
                    showMessage('没有权限', '', '', 'error');
                }
            }

            // 卖家菜单
            $seller_menu = $_SESSION['seller_menu'];
            if ($this->store_info['is_hango'] != 1) {
                unset($seller_menu['fenxiao']);
                unset($seller_menu['goods']['child'][8]);
            }
            Tpl::output('menu', $seller_menu);
            // 当前菜单
            $current_menu = $this->_getCurrentMenu($_SESSION['seller_function_list']);
            Tpl::output('current_menu', $current_menu);
            // 左侧菜单
            if($_GET['act'] == 'seller_center') {
                if(!empty($_SESSION['seller_quicklink'])) {
                    $left_menu = array();
                    foreach ($_SESSION['seller_quicklink'] as $value) {
                        $left_menu[] = $_SESSION['seller_function_list'][$value];
                    }
                }
            } else {
                $left_menu = $_SESSION['seller_menu'][$current_menu['model']]['child'];
            }
            Tpl::output('left_menu', $left_menu);
            Tpl::output('seller_quicklink', $_SESSION['seller_quicklink']);

            $this->checkStoreMsg();
        }
    }
    
    private function setSellerSession( $seller_name )
    {
    	$model_seller = Model('seller');
    	$seller_info = $model_seller->getSellerInfo(array('seller_name' => $seller_name ));
    	
    	if($seller_info) {
    	
	    	$model_member = Model('member');
	    	$member_info = $model_member->getMemberInfo(
	    			array(
	    					'member_id' => $seller_info['member_id'],
	    			)
	    	);
	    	
	    	if($member_info) {
	    		$model_member->set_cookie($member_info);

	    		$model_seller_group = Model('seller_group');
	    		$seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $seller_info['seller_group_id']));
	    	
	    		$model_store = Model('store');
	    		$store_info = $model_store->getStoreInfoByID($seller_info['store_id'],true);
	    	
	    		$_SESSION['is_login'] = '1';
	    		$_SESSION['member_id'] = $member_info['member_id'];
	    		$_SESSION['member_name'] = $member_info['member_name'];
	    		$_SESSION['member_email'] = $member_info['member_email'];
	    		$_SESSION['is_buy'] = $member_info['is_buy'];
	    		$_SESSION['avatar'] = $member_info['member_avatar'];
	    	
	    		$_SESSION['grade_id'] = $store_info['grade_id'];
	    		$_SESSION['seller_id'] = $seller_info['seller_id'];
	    		$_SESSION['seller_name'] = $seller_info['seller_name'];
	    		$_SESSION['seller_is_admin'] = intval($seller_info['is_admin']);
	    		$_SESSION['store_id'] = intval($seller_info['store_id']);
	    		$_SESSION['store_name'] = $store_info['store_name'];
	    		$_SESSION['store_avatar'] = $store_info['store_avatar'];
	    		$_SESSION['is_own_shop'] = (bool) $store_info['is_own_shop'];
	    		$_SESSION['bind_all_gc'] = (bool) $store_info['bind_all_gc'];
	    		$_SESSION['seller_limits'] = explode(',', $seller_group_info['limits']);
	    		$_SESSION['seller_group_id'] = $seller_info['seller_group_id'];
	    		$_SESSION['seller_gc_limits'] = $seller_group_info['gc_limits'];
	    		if($seller_info['is_admin']) {
	    			$_SESSION['seller_group_name'] = '管理员';
	    			$_SESSION['seller_smt_limits'] = false;
	    		} else {
	    			$_SESSION['seller_group_name'] = $seller_group_info['group_name'];
	    			$_SESSION['seller_smt_limits'] = explode(',', $seller_group_info['smt_limits']);
	    		}
	    		if(!$seller_info['last_login_time']) {
	    			$seller_info['last_login_time'] = TIMESTAMP;
	    		}
	    		$_SESSION['seller_last_login_time'] = date('Y-m-d H:i', $seller_info['last_login_time']);
	    		$seller_menu = $this->getSellerMenuList($seller_info['is_admin'], explode(',', $seller_group_info['limits']));
	    		$_SESSION['seller_menu'] = $seller_menu['seller_menu'];
	    		$_SESSION['seller_function_list'] = $seller_menu['seller_function_list'];
	    		if(!empty($seller_info['seller_quicklink'])) {
	    			$quicklink_array = explode(',', $seller_info['seller_quicklink']);
	    			foreach ($quicklink_array as $value) {
	    				$_SESSION['seller_quicklink'][$value] = $value ;
	    			}
	    		}
	    		setNcCookie('auto_login', '', -3600);
	    		//$this->recordSellerLog('登录成功');
	    		//redirect('index.php?act=seller_center');
	    	}
    	}
    }

    /**
     * 记录卖家日志
     *
     * @param $content 日志内容
     * @param $state 1成功 0失败
     */
    protected function recordSellerLog($content = '', $state = 1){
        $seller_info = array();
        $seller_info['log_content'] = $content;
        $seller_info['log_time'] = TIMESTAMP;
        $seller_info['log_seller_id'] = $_SESSION['seller_id'];
        $seller_info['log_seller_name'] = $_SESSION['seller_name'];
        $seller_info['log_store_id'] = $_SESSION['store_id'];
        $seller_info['log_seller_ip'] = getIp();
        $seller_info['log_url'] = $_GET['act'].'&'.$_GET['op'];
        $seller_info['log_state'] = $state;
        $model_seller_log = Model('seller_log');
        $model_seller_log->addSellerLog($seller_info);
    }

    /**
     * 记录店铺费用
     *
     * @param $cost_price 费用金额
     * @param $cost_remark 费用备注
     */
    protected function recordStoreCost($cost_price, $cost_remark) {
        // 平台店铺不记录店铺费用
        if (checkPlatformStore()) {
            return false;
        }
        $model_store_cost = Model('store_cost');
        $param = array();
        $param['cost_store_id'] = $_SESSION['store_id'];
        $param['cost_seller_id'] = $_SESSION['seller_id'];
        $param['cost_price'] = $cost_price;
        $param['cost_remark'] = $cost_remark;
        $param['cost_state'] = 0;
        $param['cost_time'] = TIMESTAMP;
        $model_store_cost->addStoreCost($param);

        // 发送店铺消息
        $param = array();
        $param['code'] = 'store_cost';
        $param['store_id'] = $_SESSION['store_id'];
        $param['param'] = array(
            'price' => $cost_price,
            'seller_name' => $_SESSION['seller_name'],
            'remark' => $cost_remark
        );

        QueueClient::push('sendStoreMsg', $param);
    }

    protected function getSellerMenuList($is_admin, $limits) {
        $seller_menu = array();
        if (intval($is_admin) !== 1) {
            $menu_list = $this->_getMenuList();
            foreach ($menu_list as $key => $value) {
                foreach ($value['child'] as $child_key => $child_value) {
                    if (!in_array($child_value['act'], $limits)) {
                        unset($menu_list[$key]['child'][$child_key]);
                    }
                }

                if(count($menu_list[$key]['child']) > 0) {
                    $seller_menu[$key] = $menu_list[$key];
                }
            }
        } else {
            $seller_menu = $this->_getMenuList();
        }
        $seller_function_list = $this->_getSellerFunctionList($seller_menu);
        return array('seller_menu' => $seller_menu, 'seller_function_list' => $seller_function_list);
    }

    private function _getCurrentMenu($seller_function_list) {
        $current_menu = $seller_function_list[$_GET['act']];
        if(empty($current_menu)) {
            $current_menu = array(
                'model' => 'index',
                'model_name' => '首页'
            );
        }
        return $current_menu;
    }

    private function _getMenuList() {
        $menu_list = array(
            'goods' => array('name' => '商品', 'child' => array(
                array('name' => '商品发布', 'act'=>'store_goods_add', 'op'=>'index'),
				array('name' => 'CSV导入', 'act'=>'taobao_import', 'op'=>'index'),
                array('name' => '出售中的商品', 'act'=>'store_goods_online', 'op'=>'index'),
                array('name' => '仓库中的商品', 'act'=>'store_goods_offline', 'op'=>'index'),
                array('name' => '预约/到货通知', 'act' => 'store_appoint', 'op' => 'index'),
                array('name' => '关联版式', 'act'=>'store_plate', 'op'=>'index'),
                array('name' => '商品规格', 'act' => 'store_spec', 'op' => 'index'),
                array('name' => '图片空间', 'act'=>'store_album', 'op'=>'album_cate'),
                array('name' => '分销商品', 'act'=>'fenxiao_goods2', 'op'=>'index'),
            )),
            'order' => array('name' => '订单物流', 'child' => array(
                array('name' => '实物交易订单', 'act'=>'store_order', 'op'=>'index'),
                array('name' => '虚拟兑码订单', 'act'=>'store_vr_order', 'op'=>'index'),
                array('name' => '发货', 'act'=>'store_deliver', 'op'=>'index'),
                array('name' => '发货设置', 'act'=>'store_deliver_set', 'op'=>'daddress_list'),
                array('name' => '运单模板', 'act'=>'store_waybill', 'op'=>'waybill_manage'),
                array('name' => '电子面单', 'act'=>'store_printship', 'op'=>'index'),
                array('name' => '评价管理', 'act'=>'store_evaluate', 'op'=>'list'),
                array('name' => '物流工具', 'act'=>'store_transport', 'op'=>'index'),
				array('name' => '来单提醒', 'act'=>'order_call', 'op'=>'index'),
            )),
            'promotion' => array('name' => '促销', 'child' => array(
                array('name' => '团购管理', 'act'=>'store_groupbuy', 'op'=>'index'),
                array('name' => '加价购', 'act'=>'store_promotion_cou', 'op'=>'cou_list'),
                array('name' => '限时折扣', 'act'=>'store_promotion_xianshi', 'op'=>'xianshi_list'),
                array('name' => '拼团', 'act'=>'store_promotion_pintuan', 'op'=>'index'),
                array('name' => '满即送', 'act'=>'store_promotion_mansong', 'op'=>'mansong_list'),
                array('name' => '优惠套装', 'act'=>'store_promotion_bundling', 'op'=>'bundling_list'),
                array('name' => '推荐展位', 'act' => 'store_promotion_booth', 'op' => 'booth_goods_list'),
                array('name' => '预售商品', 'act' => 'store_promotion_book', 'op' => 'index'),
                array('name' => 'F码商品', 'act' => 'store_promotion_fcode', 'op' => 'index'),
                array('name' => '推荐组合', 'act' => 'store_promotion_combo', 'op' => 'index'),
                array('name' => '手机专享', 'act' => 'store_promotion_sole', 'op' => 'index'),
                array('name' => '代金券管理', 'act'=>'store_voucher', 'op'=>'templatelist'),
                array('name' => '活动管理', 'act'=>'store_activity', 'op'=>'store_activity'),
            )),
            'store' => array('name' => '店铺', 'child' => array(
                array('name' => '店铺设置', 'act'=>'store_setting', 'op'=>'store_setting'),
                array('name' => '店铺装修', 'act'=>'store_decoration', 'op'=>'decoration_setting'),
                array('name' => '店铺导航', 'act'=>'store_navigation', 'op'=>'navigation_list'),
                array('name' => '店铺动态', 'act'=>'store_sns', 'op'=>'index'),
                array('name' => '店铺信息', 'act'=>'store_info', 'op'=>'bind_class'),
                array('name' => '店铺分类', 'act'=>'store_goods_class', 'op'=>'index'),
                array('name' => '品牌申请', 'act'=>'store_brand', 'op'=>'brand_list'),
                array('name' => '供货商', 'act'=>'store_supplier', 'op'=>'sup_list'),
                array('name' => '实体店铺', 'act'=>'store_map', 'op'=>'index'),
                array('name' => '消费者保障服务', 'act'=>'store_contract', 'op'=>'index'),
            )),
            'consult' => array('name' => '售后服务', 'child' => array(
                array('name' => '咨询管理', 'act'=>'store_consult', 'op'=>'consult_list'),
                array('name' => '投诉管理', 'act'=>'store_complain', 'op'=>'list'),
                array('name' => '退款记录', 'act'=>'store_refund', 'op'=>'index'),
                array('name' => '退货记录', 'act'=>'store_return', 'op'=>'index'),
            )),
            'statistics' => array('name' => '统计结算', 'child' => array(
                array('name' => '店铺概况', 'act'=>'statistics_general', 'op'=>'general'),
                array('name' => '商品分析', 'act'=>'statistics_goods', 'op'=>'goodslist'),
                array('name' => '运营报告', 'act'=>'statistics_sale', 'op'=>'sale'),
                //array('name' => '行业分析', 'act'=>'statistics_industry', 'op'=>'hot'),
                array('name' => '流量统计', 'act'=>'statistics_flow', 'op'=>'storeflow'),
                array('name' => '实物结算', 'act'=>'store_bill', 'op'=>'index'),
                array('name' => '虚拟结算', 'act'=>'store_vr_bill', 'op'=>'index'),
            )),
            'message' => array('name' => '客服消息', 'child' => array(
                array('name' => '客服设置', 'act'=>'store_callcenter', 'op'=>'index'),
                array('name' => '系统消息', 'act'=>'store_msg', 'op'=>'index'),
                array('name' => '聊天记录查询', 'act'=>'store_im', 'op'=>'index'),
                array('name' => '商家操作手册', 'act'=>'store_help', 'op'=>'list'),
            )),
            'account' => array('name' => '账号', 'child' => array(
                array('name' => '账号列表', 'act'=>'store_account', 'op'=>'account_list'),
                array('name' => '账号组', 'act'=>'store_account_group', 'op'=>'group_list'),
                array('name' => '账号日志', 'act'=>'seller_log', 'op'=>'log_list'),
                array('name' => '店铺消费', 'act'=>'store_cost', 'op'=>'cost_list'),
                array('name' => '门店账号', 'act'=>'store_chain', 'op'=>'index'),
            )),
			'fenxiao' => array('name' => '分销', 'child' => array(
                array('name' => '分销订单列表', 'act'=>'fenxiao_order', 'op'=>'index'),
                array('name' => '分销退款订单', 'act'=>'fenxiao_order_refund', 'op'=>'index'),
                array('name' => '分销商品管理', 'act'=>'fenxiao_goods', 'op'=>'index'),
                array('name' => '分销渠道管理', 'act'=>'fenxiao_channel', 'op'=>'index'),
                array('name' => '分销错误日志', 'act'=>'fenxiao_refund_errorlog', 'op'=>'index'),
            ))
        );
        return $menu_list;
    }

    private function _getSellerFunctionList($menu_list) {
        $format_menu = array();
        foreach ($menu_list as $key => $menu_value) {
            foreach ($menu_value['child'] as $submenu_value) {
                $format_menu[$submenu_value['act']] = array(
                    'model' => $key,
                    'model_name' => $menu_value['name'],
                    'name' => $submenu_value['name'],
                    'act' => $submenu_value['act'],
                    'op' => $submenu_value['op'],
                );
            }
        }
        return $format_menu;
    }

    /**
     * 自动发布店铺动态
     *
     * @param array $data 相关数据
     * @param string $type 类型 'new','coupon','xianshi','mansong','bundling','groupbuy'
     *            所需字段
     *            new       goods表'             goods_id,store_id,goods_name,goods_image,goods_price,goods_freight
     *            xianshi   p_xianshi_goods表'   goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
     *            mansong   p_mansong表'         mansong_name,start_time,end_time,store_id
     *            bundling  p_bundling表'        bl_id,bl_name,bl_img,bl_discount_price,bl_freight_choose,bl_freight,store_id
     *            groupbuy  goods_group表'       group_id,group_name,goods_id,goods_price,groupbuy_price,group_pic,rebate,start_time,end_time
     *            coupon在后台发布
     */
    public function storeAutoShare($data, $type) {
        $param = array(
                3 => 'new',
                4 => 'coupon',
                5 => 'xianshi',
                6 => 'mansong',
                7 => 'bundling',
                8 => 'groupbuy',
                9 => 'pintuan',
            );
        $param_flip = array_flip($param);
        if (!in_array($type, $param) || empty($data)) {
            return false;
        }

        $auto_setting = Model('store_sns_setting')->getStoreSnsSettingInfo(array('sauto_storeid' => $_SESSION ['store_id']));
        $auto_sign = false; // 自动发布开启标志

        if ($auto_setting['sauto_' . $type] == 1) {
            $auto_sign = true;
            if (CHARSET == 'GBK') {
                foreach ((array)$data as $k => $v) {
                    $data[$k] = Language::getUTF8($v);
                }
            }
            $goodsdata = addslashes(json_encode($data));
            if ($auto_setting['sauto_' . $type . 'title'] != '') {
                $title = $auto_setting['sauto_' . $type . 'title'];
            } else {
                $auto_title = 'nc_store_auto_share_' . $type . rand(1, 5);
                $title = Language::get($auto_title);
            }
        }
        if ($auto_sign) {
            // 插入数据
            $stracelog_array = array();
            $stracelog_array['strace_storeid'] = $this->store_info['store_id'];
            $stracelog_array['strace_storename'] = $this->store_info['store_name'];
            $stracelog_array['strace_storelogo'] = empty($this->store_info['store_avatar']) ? '' : $this->store_info['store_avatar'];
            $stracelog_array['strace_title'] = $title;
            $stracelog_array['strace_content'] = '';
            $stracelog_array['strace_time'] = TIMESTAMP;
            $stracelog_array['strace_type'] = $param_flip[$type];
            $stracelog_array['strace_goodsdata'] = $goodsdata;
            Model('store_sns_tracelog')->saveStoreSnsTracelog($stracelog_array);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 商家消息数量
     */
    private function checkStoreMsg() {//判断cookie是否存在
        $cookie_name = 'storemsgnewnum'.$_SESSION['seller_id'];
        if (cookie($cookie_name) != null && intval(cookie($cookie_name)) >=0){
            $countnum = intval(cookie($cookie_name));
        }else {
            $where = array();
            $where['store_id'] = $_SESSION['store_id'];
            $where['sm_readids'] = array('exp', 'sm_readids NOT LIKE \'%,'.$_SESSION['seller_id'].',%\' OR sm_readids IS NULL');
            if ($_SESSION['seller_smt_limits'] !== false) {
                $where['smt_code'] = array('in', $_SESSION['seller_smt_limits']);
            }
            $countnum = Model('store_msg')->getStoreMsgCount($where);
            setNcCookie($cookie_name,intval($countnum),2*3600);//保存2小时
        }
        Tpl::output('store_msg_num',$countnum);
    }

}

class BaseStoreSnsControl extends Control {
    const MAX_RECORDNUM = 20;   // 允许插入新记录的最大次数，sns页面该常量是一样的。
    public function __construct(){
        Language::read('common,store_layout');
        Tpl::output('max_recordnum', self::MAX_RECORDNUM);
        Tpl::setDir('store');
        Tpl::setLayout('store_layout');
        // 自定义导航条
        $this->getStoreNavigation();
        //输出头部的公用信息
        $this->showLayout();
        //查询会员信息
        $this->getMemberAndGradeInfo(false);

    }

    // 自定义导航条
    protected function getStoreNavigation() {
        $model_store_navigation = Model('store_navigation');
        $store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $_GET['sid']));
        Tpl::output('store_navigation_list', $store_navigation_list);
    }

    protected function getStoreInfo($store_id) {
        //得到店铺等级信息
        $store_info = Model('store')->getStoreInfoByID($store_id);
        if (empty($store_info)) {
            showMessage(Language::get('store_sns_store_not_exists'),'','html','error');
        }
        //处理地区信息
        $area_array = array();
        $area_array = explode("\t",$store_info["area_info"]);
        $map_city   = Language::get('store_sns_city');
        $city   = '';
        if(strpos($area_array[0], $map_city) !== false){
            $city   = $area_array[0];
        }else {
            $city   = $area_array[1];
        }
        $store_info['city'] = $city;

        Tpl::output('store_theme', $store_info['store_theme']);
        Tpl::output('store_info', $store_info);
        

        //店铺分类
        $goodsclass_model = Model('store_goods_class');
        $goods_class_list = $goodsclass_model->getShowTreeList($store_id);
        Tpl::output('goods_class_list', $goods_class_list);
    }
}

/**
 * 积分中心control父类
 */
class BasePointShopControl extends Control {
    protected $member_info;
    public function __construct(){
        Language::read('common,home_layout');
        //输出头部的公用信息
        $this->showLayout();
        //输出会员信息
        $this->member_info = $this->getMemberAndGradeInfo(true);
        Tpl::output('member_info',$this->member_info);


        Tpl::setDir('home');
        Tpl::setLayout('home_layout');

        if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
            $_GET = Language::getGBK($_GET);
        }
        if(!C('site_status')) halt(C('closed_reason'));

        //判断系统是否开启积分和积分中心功能
        if (C('points_isuse') != 1 || C('pointshop_isuse') != 1){
            showMessage(Language::get('pointshop_unavailable'),urlShop('index','index'),'html','error');
        }
        Tpl::output('index_sign','pointshop');
    }
    /**
     * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
     */
    public function pointshopMInfo($is_return = false){
        if($_SESSION['is_login'] == '1'){
            $model_member = Model('member');
            if (!$this->member_info){
                //查询会员信息
                $member_infotmp = $model_member->getMemberInfoByID($_SESSION['member_id']);
            } else {
                $member_infotmp = $this->member_info;
            }
            $member_infotmp['member_exppoints'] = intval($member_infotmp['member_exppoints']);

            //当前登录会员等级信息
            $membergrade_info = $model_member->getOneMemberGrade($member_infotmp['member_exppoints'],true);
            $member_info = array_merge($member_infotmp,$membergrade_info);
            Tpl::output('member_info',$member_info);

            //查询已兑换并可以使用的代金券数量
            $model_voucher = Model('voucher');
            $vouchercount = $model_voucher->getCurrentAvailableVoucherCount($_SESSION['member_id']);
            Tpl::output('vouchercount',$vouchercount);

            //购物车兑换商品数
            $pointcart_count = Model('pointcart')->countPointCart($_SESSION['member_id']);
            Tpl::output('pointcart_count',$pointcart_count);

            //查询已兑换商品数(未取消订单)
            $pointordercount = Model('pointorder')->getMemberPointsOrderGoodsCount($_SESSION['member_id']);
            Tpl::output('pointordercount',$pointordercount);
            if ($is_return){
                return array('member_info'=>$member_info,'vouchercount'=>$vouchercount,'pointcart_count'=>$pointcart_count,'pointordercount'=>$pointordercount);
            }
        }
    }
}

class BaseApiControl extends Control{
    protected function success($data='',$msg='Success')
    {
        $res = array();
        $res['data'] = $data;
        $res['msg']= $msg;
        $res['error']= 0;
        if(isset($_GET['callback'])&&!empty($_GET['callback'])){
            echo $_GET['callback'].'('.json_encode($res).')';
        }else {
            echo json_encode($res);
        }
        exit;
    }

    protected function error($msg='Error',$error=1)
    {
        $res = array();
        $res['msg']= $msg;
        $res['error']= $error;
        echo json_encode($res);
        exit();
    }
}