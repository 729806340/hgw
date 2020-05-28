<?php
/**
 * 注销
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */



defined('ByShopWWI') or exit('Access Invalid!');

class logoutControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 注销
     */
    public function indexOp(){
        if(empty($_POST['username']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('参数错误');
        }

        $model_mb_user_token = Model('mb_user_token');

        if($this->member_info['member_name'] == $_POST['username']) {
            $condition = array();
            $condition['member_id'] = $this->member_info['member_id'];
            $condition['client_type'] = $_POST['client'];
            $model_mb_user_token->delMbUserToken($condition);

            setNcCookie('member_id','',-3600);
            setNcCookie('member_name','',-3600);
            setNcCookie('member_turename','',-3600);

            if (C('OLD_STATUS')) {
                //获取站点主机后缀(.com, .tmc or .local etc)
                if ('192.' == substr($_SERVER['HTTP_HOST'], 0, 4)) {
                    $site_cookie_domain = $_SERVER['HTTP_HOST'];    //内网ip访问
                } else {
                    $_aHttpHost = explode('.', $_SERVER['HTTP_HOST']);
                    $site_cookie_domain = '.' . $_aHttpHost[count($_aHttpHost) - 2] . '.' . $_aHttpHost[count($_aHttpHost) - 1];
                    $_pos = strpos($site_cookie_domain, ':');
                    $_pos && $site_cookie_domain = substr($site_cookie_domain, 0, $_pos);   //过滤端口号
                }
                setcookie('s', '', time() - 3600, '/', $site_cookie_domain);
                setcookie('UNAME', null, time() - 3600, '/', $site_cookie_domain);
                setcookie('S[MEMBER]', null, time() - 3600, '/', $site_cookie_domain);
                setcookie('S[SIGN][REMEMBER]', null, time() - 3600, '/', $site_cookie_domain);
            }
            output_data('1');
        } else {
            output_error('参数错误');
        }
    }

}
