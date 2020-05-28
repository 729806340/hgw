<?php

defined('ByShopWWI') or exit('Access Invalid!');

class sapb2bControl extends BaseCronControl
{
    //根据交易码发起不同的交易
    public function indexOp()
    {
        $code = $_GET['code'];
        if (empty($code)) {
            $this->log('crontab sap code error!');
        } else {
            //$this->_order_commis_rate_update();
            Service('Sapb2b')->task($code);
        }
    }

}