<?php

namespace Home\Model;

use Think\Model;

class B2cOrderFenxiaoErrorModel extends Model {

    //保存错误日志
	public function addLog($orderno, $error, $log_type = "order" )
    {
		$data['orderno'] = $orderno;
		$data['error'] = $error;
		$data['order_time'] = 0;
		$data['log_time'] = time();
		$data['sourceid'] = session('uid');
		$data['source'] = session('username');
		$data['log_type'] = $log_type;
		$this->data($data)->add();
    }
	
	public function getLogList($pagesize = 6, $page = 1, $oid="", $logtype = "",$keywords = "")
	{
		$conditions = array() ;
		$conditions['sourceid'] = session('uid') ;
		if( $oid ) {
			$conditions['orderno'] = $oid ;
		}
		if( $logtype ) {
			$conditions['log_type'] = $logtype ;
		}

		if( !empty($keywords) ) {
			$conditions['error'] = array('like' , '%'.$keywords.'%');
		}

		$offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = $this->where($conditions)->count();
		$result = $this->order('id desc')->limit($offset, $pagesize)->where($conditions)->field('*,FROM_UNIXTIME(log_time,"%Y-%m-%d %H:%i:%S") as logtime')->select();
        $pagetotal = ceil($totalrows / $pagesize);
        return array($totalrows, $result, $pagetotal);
	}

}
