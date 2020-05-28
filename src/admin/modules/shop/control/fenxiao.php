<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6 0006
 * Time: 上午 9:50
 */

defined('ByShopWWI') or exit('Access Invalid!');
class fenxiaoControl extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 结算单列表
     *
     */
    public function indexOp()
    {
        Tpl::setDirquna('shop');
        Tpl::showpage('fenxiao.index');
    }

}