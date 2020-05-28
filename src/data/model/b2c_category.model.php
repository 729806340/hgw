<?php

defined('ByShopWWI') or exit('Access Invalid!');

class b2c_categoryModel extends Model {

	public function addCategory($data, $id = '', $uid, $pid) {
		if (!$id) {/*
			if ($this -> getCategory($uid, '', $pid)) {
				return false;
			}*/
			return $this->table('b2c_category')->insert($data);
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

	public function getCategoryList($conditions, $page = 10, $limit = '') {

        $category_list = $this->table('b2c_category')
            ->order('id desc')
            ->limit($limit)
            ->where($conditions)
            ->page($page)
            ->select();
        $gids = array();
        foreach ($category_list as $item) {
            $gids[] = $item['gid'];
        }
        $goods_common = $this->table('goods_common')->where(array('goods_commonid' => array('in', $gids)))
            ->field('goods_commonid, goods_image')->select();
        $goods_common_a = array();
        foreach ($goods_common as $item) {
            $goods_common_a[$item['goods_commonid']] = $item['goods_image'];
        }
        $goods = $this->table('goods')->where(array('goods_id' => array('in', $gids)))->select();
        $goods = array_under_reset($goods, 'goods_id');
        foreach ($category_list as &$item) {
            if (isset($goods_common_a[$item['gid']])) {
                $item['goods_image'] = $goods_common_a[$item['gid']];
            }
            if (isset($goods[$item['gid']])) {
                $item['goods_price'] = $goods_common_a[$item['gid']]['goods_price'];
                $item['goods_cost'] = $goods_common_a[$item['gid']]['goods_cost'];
            }
        }
        return $category_list;
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
		$count = $this->table('b2c_category')-> where($where) -> count();
		if ($count == 1) {
			return false;
		} else {
			return true;
		}
	}

	//获取pid和fxpid数组
	public function getPidFxpidArrayByUid($uid) {
		$where['uid'] = array('in', $uid);
		$result = $this->table('b2c_category')->where($where) -> select();
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
		$result = $this->table('goods')->field('goods_id, goods_serial')->where($conditions)->select();
		$return = array();
		foreach ($result as $row) {
			$return[$row['goods_serial']] = $row['goods_id'] ;
		}
		return $return ;
	}
}
