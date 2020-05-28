<?php

namespace Home\Controller;

use Home\Model\B2cCategoryModel;
use Home\Model\GoodsModel;
use Think\Controller;

class GoodsController extends AuthController {

	public function goodsList() {
		$this -> display('goods/goodslist');
	}

	public function add(){
	    if(IS_POST){
	        $data = I('post.');
	        if(empty($data['name'])||empty($data['price'])||empty($data['cost'])||empty($data['tax'])){
                $this -> ajaxReturn(array('status' => '-1', 'msg' => '名称、价格、成本或者税率不得为空！'));
            }
	        $model = M('Goods');
            $model->create();
            if(!$model->add()){
                $this -> ajaxReturn(array('status' => '-1', 'msg' => '数据保存失败！'));
            }
            $this -> ajaxReturn(array('status' => '1', 'msg' => '添加成功'));

        }
        $this -> display('goods/add');
    }
	public function edit(){
	    $id = (int)I('get.id');
        $model = M('Goods');
        $goods = $model->where(array('id'=>$id))->find();
        if(empty($goods)) $this->error('找不到商品');
        if(IS_POST){
	        $data = I('post.');
	        if(empty($data['name'])||empty($data['price'])||empty($data['cost'])||empty($data['tax'])){
                $this -> ajaxReturn(array('status' => '-1', 'msg' => '名称、价格、成本或者税率不得为空！'));
            }
            $model->create();
            if(!$model->where(array('id'=>$id))->save($data)){
                $this -> ajaxReturn(array('status' => '-1', 'msg' => '数据保存失败！'));
            }

            $this -> ajaxReturn(array('status' => '1', 'msg' => '修改成功'));

        }
        $this->assign('goods',$goods);
        $this -> display('goods/edit');
    }

	public function distributorgoods() {
		$this -> display('goods/distributorgoods');
	}

	public function ajax() {
		if (IS_AJAX) {
			$action = I('post.action', '', 'htmlspecialchars');
			switch ($action) {
				case 'getlist' :
					$pagesize = 20;
					$page = I('post.page', '', 'htmlspecialchars');
					$goodsname = trim(I('post.goods_name', '', 'htmlspecialchars'));
					/** @var GoodsModel $product */
					$product = D('Goods');
					$result = $product -> getGoodsList($pagesize, $page, 1, $goodsname);
					if (!count($result) > 0)
						$this -> ajaxReturn(array('status' => '0', 'msg' => '暂无数据！'));
					$data['total_num'] = $result[0];
					$data['list'] = $result[1];
					$data['page_total_num'] = $result[2];
					$data['page_size'] = $pagesize;
					$data['status'] = $status;
					$this -> ajaxReturn(array('status' => '1', 'msg' => $data));
					break;
				case 'getdistributorgoodslist' :
					$pagesize = 20;
					$page = I('post.page', '', 'htmlspecialchars');
					$catename = trim(I('post.goods_name', '', 'htmlspecialchars'));
					/** @var B2cCategoryModel $product */
					$product = D('B2cCategory');
					$result = $product -> getCategoryList($pagesize, $page, session('uid'), $catename);
					if (!count($result) > 0)
						$this -> ajaxReturn(array('status' => '0', 'msg' => '暂无数据！'));
					$data['total_num'] = $result[0];
					$data['list'] = $result[1];
					$data['page_total_num'] = $result[2];
					$data['page_size'] = $pagesize;
					$data['status'] = $status;
					$this -> ajaxReturn(array('status' => '1', 'msg' => $data));
					break;
				case 'add' :
					$data['catename'] = I('post.name', '', 'htmlspecialchars');
					$data['pid'] = I('post.pid', '', 'htmlspecialchars');
					$data['gid'] = I('post.gid', '', 'htmlspecialchars');
					$data['uid'] = session('uid');
					$data['fxpid'] = session('member_type') == 'jicai' ? intval($data['pid']) : 0;
					$data['ctime'] = time();
					/** @var GoodsModel $goodsModel */
					$goodsModel = D('Goods');
					$goods = $goodsModel->where(array('goods_id'=>$data['pid']))->find();
					$storeModel = D('Store');
					$store = $storeModel->where(array('store_id'=>$goods['store_id']))->find();
					if($store['manage_type'] != 'platform'&&$goods['tax_input']==200){
                        $this -> ajaxReturn(array('status' => '0', 'msg' => '添加失败，共建商品税率未设置！'));
                        exit ;
                    }
					$category = D('B2cCategory');
					if ($category -> addCategory($data, '', session('uid'), $data['pid'])) {
						$this -> ajaxReturn(array('status' => '1', 'msg' => '添加成功！'));
						exit ;
					}
					$this -> ajaxReturn(array('status' => '0', 'msg' => '服务器繁忙！'));
					break;
				case 'del' :
					$id = I('post.id', '', 'htmlspecialchars');
					$category = D('B2cCategory');
					if ($category -> delCategory(session('uid'), $id)) {
						$this -> ajaxReturn(array('status' => '1', 'msg' => '删除成功！'));
						exit ;
					}
					$this -> ajaxReturn(array('status' => '0', 'msg' => '服务器繁忙！'));
					break;
				case 'checkgoodsrel' :
					$category = D('B2cCategory');
					$result = $category -> where('fxpid=0 AND uid=' . session('uid')) -> select();
					if (empty($result))
						$this -> ajaxReturn(array('status' => '1', 'msg' => ''));
					//有未映射的商品
					else
						$this -> ajaxReturn(array('status' => '0', 'msg' => ''));
					//无未映射的商品
					break;
			}
		} else {
			$this -> ajaxReturn(array('status' => '-1', 'msg' => '无效的操作！'));
		}
	}

	public function savelist() {
		$pids = $_POST['pids'];
		$mids = $_POST['mids'];
		$category = D('B2cCategory');
		$result = false;
		foreach ($_POST['ids'] as $k => $id) {
			$checkCategory = D('B2cCategory');
			$result = $checkCategory -> checkfxpid(session('uid'), $id, $mids[$k]);
			if (!$result) {
				break;
			}
		}
		//$result=true;
		if ($result) {
			foreach ($_POST['ids'] as $k => $id) {
				$mid = $mids[$k];
				$category -> fxpid = trim($id);
				$category -> where('id=' . intval($mid) . ' AND uid=' . session('uid')) -> save();
			}
			$this -> ajaxReturn(array('status' => '1', 'msg' => '保存成功！'));
		} else {
			$this -> ajaxReturn(array('status' => '0', 'err' => '保存失败！分销平台商品ID重复请核实后在保存！'));
		}
	}

}
