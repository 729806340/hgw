<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/19
 * Time: 17:37
 */
require_once('CpsUnion.php');

class CpsMeidebi extends CpsUnion
{
    private $_config = array(
        'id'=>'meidebi'
    );

    public function formatRequest()
    {
        $data = array(
            'unionid' => $this->_config['id'],
            'euid' => '1',
            'source' => '',
            'channel' => '',
            'cid' => '',
            'wi' => '',
        );
        return $data;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function push($id)
    {
        return true;
    }

    public function access()
    {
        return 'denied';
    }
    public function getOrders()
    {
        return false;
    }


}