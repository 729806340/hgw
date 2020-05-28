<?php
namespace Home\Model;

use Think\Model;

// $model->query('select * from user where id=%d and status=%d',array($id,$status));
class B2cCategoryModel extends Model {

	public function addCategory($data, $id = '', $uid, $pid) {
		if (!$id) {/*
			if ($this -> getCategory($uid, '', $pid)) {
				return false;
			}*/
			return $this -> add($data);
		} else {
		}
	}

	public function getCategory($uid = '', $parentid = '', $pid = '') {
		$conditions = array();
		if ($uid != '') {
			$conditions['uid'] = $uid;
		}
		if (!($parentid === '')) {
			$conditions['parentid'] = $parentid;
		}
		if ($pid != '') {
			$conditions['pid'] = $pid;
		}
		return $this -> where($conditions) -> select();
	}

	public function getCategoryList($pagesize = 6, $page = 1, $uid, $catename) {
		$conditions = array();
		if ($uid != '') {
			$conditions['uid'] = $uid;
		}
		if ($catename != '') {
			$conditions['goods_name'] = array('like', '%' . $catename . '%');
		}
		$offset = 0;
		if ($page > 1)
			$offset = ($page - 1) * $pagesize;
		$totalrows = $this -> where($conditions) -> count();
		$result = $this -> table('shopwwi_b2c_category as a') -> join(array('left join shopwwi_goods_common as b ON a.gid = b.goods_commonid', )) -> order('a.id desc') -> limit($offset, $pagesize) -> where($conditions) -> field('a.*,b.goods_image,FROM_UNIXTIME(ctime,"%Y-%m-%d %H:%i:%S") as datetime ') -> select();
		//echo $this->getLastSql();
		$pagetotal = ceil($totalrows / $pagesize);
		return array($totalrows, $result, $pagetotal);
	}

	public function delCategory($uid = '', $id = '') {
		if ($uid)
			$condition['uid'] = $uid;
		if ($id)
			$condition['id'] = $id;
		return $this -> where($condition) -> delete();
	}

	//根据id、uid、fxpid查询是否存在数据
	public function checkfxpid($uid, $fx_id,$id,$fxprice) {
		$where['uid'] = intval($uid);
		$where['fxpid']=intval($fx_id);
		$where['id']=intval($id);
		$where['fxprice']=trim($fxprice);
		$count = M('B2cCategory') -> where($where) -> count();
		if ($count == 1) {
			return false;
		} else {
			return true;
		}
	}

	//获取pid和fxpid数组
	public function getPidFxpidArrayByUid($uid) {
		$where['uid'] = intval($uid);
		$result = M('B2cCategory') -> where($where) -> select();
		$array = array();
		foreach ($result as $v) {
			$array[$v['fxpid']] = $v['pid'];
		}
		return $array;
	}
	//获取bn和pid的数组
	public function getPidFxpidArrayByBns($bns) {
		if( empty($bns) ) return array();
		$conditions['goods_serial'] = array('in', $bns) ;
		$result = M('goods')->field('goods_id, goods_serial')->where($conditions)->select();
		$return = array();
		foreach ($result as $row) {
			$return[$row['goods_serial']] = $row['goods_id'] ;
		}
		return $return ;
	}
}
