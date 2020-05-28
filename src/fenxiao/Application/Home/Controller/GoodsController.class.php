<?php

namespace Home\Controller;

use Home\Model\B2cCategoryModel;
use Home\Model\B2cOrderFenxiaoErrorModel;
use Home\Model\GoodsModel;
use Think\Controller;
use Think\Model;

class GoodsController extends AuthController {

	public function goodsList() {
		$this -> display('goods/goodslist');
	}

	public function distributorgoods() {
		$this -> display('goods/distributorgoods');
	}

    /**
     * 增加活动
     * @param $id
     */
	public function add_promotion($id){
        $this->assign('id', $id);
        $this -> display('goods/add_promotion');
    }
	public function view_promotion($id){
        /** @var B2cCategoryModel $b2cGoodsModel */
        $b2cGoodsModel = D('B2cCategory');
        $fxGoods = $b2cGoodsModel->where(array('id'=>$id))->find();
        if(empty($fxGoods)) $this->error('对应的分销商品不存在');
        $this->assign('id', $id);
        $this -> display('goods/view_promotion');
    }

    public function ajax_promotion() {
        if (IS_AJAX) {
            $id = I('post.id');
            /** @var B2cCategoryModel $b2cGoodsModel */
            $b2cGoodsModel = D('B2cCategory');
            $fxGoods = $b2cGoodsModel->where(array('id'=>$id))->find();
            if(empty($fxGoods)) $this -> ajaxReturn(array('status' => '-1', 'msg' => '商品不存在！'));
            /** @var Model $promotionModel */
            $promotionModel = D('B2cPromotion');
            $pagesize = 20;
            $page = I('post.page', '', 'htmlspecialchars');
            $map = array(
                'fx_pid' => $fxGoods['fxpid'],
                'uid' => $fxGoods['uid'],
            );
            $offset = 0;
            if ($page > 1) $offset = ($page - 1) * $pagesize;
            $total_num = $promotionModel->where($map)->count();
            $items = $promotionModel->where($map)
                ->order('id desc')
                ->limit($offset, $pagesize)
                ->select();
            if (!count($items) > 0)
                $this -> ajaxReturn(array('status' => '0', 'msg' => '暂无数据！'));
            $data['total_num'] = $total_num;
            $data['list'] = $items;
            $data['page_total_num'] = ceil($total_num / $pagesize);
            $data['page_size'] = $pagesize;
            //$data['status'] = $status;
            $this -> ajaxReturn(array('status' => '1', 'msg' => $data));
        } else {
            $this -> ajaxReturn(array('status' => '-1', 'msg' => '无效的操作！'));
        }
    }


    public function save_promotion(){
	    $id = I('post.id');
        /** @var B2cCategoryModel $b2cGoodsModel */
        $b2cGoodsModel = D('B2cCategory');
	    $fxGoods = $b2cGoodsModel->where(array('id'=>$id))->find();
	    if(empty($fxGoods)) $this -> ajaxReturn(array('status' => '0', 'err' => '没有找到对应的分销商品'));
	    $data = array(
	        'uid'=>$fxGoods['uid'],
	        'fx_pid'=>$fxGoods['fxpid'],
	        'price'=>I('post.price'),
	        'start_at'=>strtotime(I('post.start_at')),
	        'end_at'=>strtotime(I('post.end_at')),
	        'created_at'=>time(),
        );
	    if($data['end_at']<=$data['start_at']){
            $this -> ajaxReturn(array('status' => '0', 'err' => '起始时间必须小于结束时间'));
        }
	    if(D('B2cPromotion')->add($data)){
            $this -> ajaxReturn(array('status' => '1', 'msg' => '添加成功'));
        }else{
            $this -> ajaxReturn(array('status' => '0', 'err' => '添加失败'));
        }
    }

	public function ajax() {
		if (IS_AJAX) {
			$action = I('post.action', '', 'htmlspecialchars');
			switch ($action) {
				case 'getlist' :
					$pagesize = 20;
					$page = I('post.page', '', 'htmlspecialchars');
					$goodsname = trim(I('post.distributorname', '', 'htmlspecialchars'));
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
					$catename = trim(I('post.distributorname', '', 'htmlspecialchars'));
					/** @var B2cCategoryModel $product */
					$product = D('B2cCategory');
					$result = $product -> getCategoryList($pagesize, $page, session('uid'), $catename);
					if (!count($result) > 0)
						$this -> ajaxReturn(array('status' => '0', 'msg' => '暂无数据！'));
                    /** @var GoodsModel $goodsModel */
                    $goodsModel = D('Goods');
                    $list = $result[1];
                    /*$goodsList = $goodsModel->where(array(
                        'goods_id'=>array('in',array_column($list,'pid'))
                    ))->select();
                    $goodsList = array_under_reset($goodsList,'goods_id');*/
                    foreach ($list as $key =>$value){
                        /*if($value['fxprice']<=0&&isset($goodsList[$value['pid']])){
                            $value['fxprice'] = $goodsList[$value['pid']]['goods_price'];
                        }*/
                        $value['promotion_start'] = date('Y-m-d H:i:s',$value['promotion_start']);
                        $value['promotion_end'] = date('Y-m-d H:i:s',$value['promotion_end']);
                        $list[$key] = $value;
                    }
					$data['total_num'] = $result[0];
					$data['list'] = $list;
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
                case 'import':
                    $this->_import();
                    break;
			}
		} else {
			$this -> ajaxReturn(array('status' => '-1', 'msg' => '无效的操作！'));
		}
	}

	public function savelist() {
		$pids = $_POST['pids'];//汉购网id
		$mids = $_POST['mids'];//表id
		$category = D('B2cCategory');
		$fxprice=$_POST['fxprice'];//分销id
		$fxcost=$_POST['fxcost'];//分销id
		$multiple_goods=$_POST['multiple_goods'];//倍数
        $checkCategory = D('B2cCategory');
        $count=0;
		foreach ($_POST['ids'] as $k => $id) {
			//保证修改的分销id的唯一性
			$result = $checkCategory -> checkfxpid(session('uid'), $id,$mids[$k],$fxprice[$k]);
			if (!$result) {
                continue;
			}
            $category -> fxpid = trim($id);
            $category->fxprice=trim($fxprice[$k]);
            $category->fxcost=trim($fxcost[$k]);
            $category->multiple_goods=trim($multiple_goods[$k]);
            $count++;
            $res=$category -> where('id=' . intval($mids[$k]) . ' AND uid=' . session('uid')) -> save();
		}
        $this -> ajaxReturn(array('status' => '1', 'msg' => '更新'.$count.'条数据成功！'));
	}

	private function _import()
    {
        set_time_limit(0);
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array(
            'xls',
            'xlsx'
        ); // 设置附件上传类型
        $upload->rootPath = './Public/uploads/';
        $upload->savePath = ''; // 设置附件上传目录
        // 上传文件
        $info = $upload->upload();
        if (! $info) {
            // 上传错误提示错误信息
            $this->ajaxReturn(array(
                'status' => '0',
                'msg' => '不支持的附件类型'
            ));
        } else {
            // 上传成功
            $filepath = $upload->rootPath . $info['file0']['savepath'] . $info['file0']['savename'];
            $succ = $this->importExcel($filepath);
            $this->ajaxReturn(array(
                'status' => '1',
                'msg' => "商品导入成功 {$succ['num']}条！点击“确定”导出反馈结果Excel表",
                'key_name'=>$succ['key_name']
            ));
        }
        $this -> ajaxReturn(array('status' => '1', 'msg' => 'Error'));
        return;
    }

    public function importExcel($saveFile , $pid)
    {
        set_time_limit(0);
        vendor('PHPExcel');
        vendor('PHPExcel.IOFactory');
        $PHPExcel = new \PHPExcel();
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (! $PHPReader->canRead($saveFile)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (! $PHPReader->canRead($saveFile)) {
                echo 'no Excel';
                return 0;
            }
        }
        $objPHPExcel = \PHPExcel_IOFactory::load($saveFile);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        unset($sheetData[1]);
        $error = 0;
        $log = "";
        /** @var B2cOrderFenxiaoErrorModel $errorlog */
        $errorlog = D('B2cOrderFenxiaoError');
        $bns = array();
        $order_nos = array();
        $items = array();
        foreach ($sheetData as $k => $v) {
            $error = 0;
            $feedback="";//导入订单反馈信息数组
            if( !$v['A'] ) {
                $log .= "第{$k}行没有填写商品ID";
                $error++;
                $feedback.="商品ID为空；";
            }
            if( !$v['C'] ) {
                $log .= "第{$k}行没有填写分销商品ID";
                $error++;
                $feedback.='分销商品ID为空';
            }
            $items[$k] = array(
                'pid'=>$v['A'],
                'fxpid'=>$v['C'],
                'fxprice'=>$v['D'],
                'promotion_price'=>$v['E'],
                'promotion_start'=>$v['F'],
                'promotion_end'=>$v['G'],
            );
            $sheetData[$k]['H']=$feedback;
        }
        if( $error > 0 ) {
            $errorlog->addLog(date('YmdHis'), $log, 'order');
//            return 0;
        }
        if (empty($items)) return 0;
        /** @var B2cCategoryModel $b2cGoodsModel */
        $b2cGoodsModel = D('B2cCategory');
        $goodsModel = D('Goods');
        $pids = array_column($items,'pid');
        $goodsList = $goodsModel->where(array('goods_id'=>array('in',$pids)))->select();
        $b2bGoodsList = $b2cGoodsModel->where(array('pid'=>array('in',$pids)))->select();
        $goodsList = array_under_reset($goodsList,'goods_id');
        $b2bGoodsList = array_under_reset($b2bGoodsList,'pid',2);
        $resultArr = array();

        $num = 0;
        foreach ($items as $k => $v) {
            $message = '';
            $result = true;
            if(!isset($goodsList[$v['pid']])) continue;
            if(empty($v['fxpid'])) continue;
            $goods = $goodsList[$v['pid']];

            $b2bGoods = false;
            $b2bGoodsPidList = array();
            if(isset($b2bGoodsList[$v['pid']])){ // 不存在对应商品
                $b2bGoodsPidList = $b2bGoodsList[$v['pid']];
            }
            foreach ($b2bGoodsPidList as $value){
                if($value['fxpid'] == $v['fxpid']) $b2bGoods = $value;
            }
//            if(!$b2bGoods) $this->_addCategory($goods,$v);
            if(!$b2bGoods){ // 不存在对应商品
                $res = $this->_addCategory($goods,$v);
            }else{
                $data['fxprice'] = $v['fxprice']>0?$v['fxprice']:$goods['goods_price'];
                $data['promotion_price'] = $v['promotion_price']>0?$v['promotion_price']:0;
                $data['promotion_start'] = $v['promotion_price']>0?intval(strtotime($v['promotion_start'])):0;
                $data['promotion_end'] = $v['promotion_price']>0?intval(strtotime($v['promotion_end'])):0;
                $res =$b2cGoodsModel->where(array('id'=>$b2bGoods['id']))->save($data);
            }
            if($res){
                $sheetData[$k]['H'].="商品导入成功；";
                $num++;
            }else{
                $sheetData[$k]['H'].="商品已存在，商品信息写入数据库失败；";
            }
        }
        //将数据写入缓存
        $key_name=md5('feedback'.uniqid());
        $redis=new \Redis();
        $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
        $redis->set($key_name,serialize($sheetData),3600);
        $return=array('num'=>$num,'key_name'=>$key_name);
        return $return;
    }
    /**
     *导入商品反馈信息
     * @param $datas
     */
    public function exportExcelResult()
    {
        set_time_limit(0);
        header("Content-type:text/html;charset=utf-8");
        vendor('PHPExcel');
        $objExcel = new \PHPExcel();
        // set document Property
        $objExcel->getActiveSheet()->setTitle('导入商品反馈表');
        $objExcel->getActiveSheet()
            ->getColumnDimension('A')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('B')
            ->setWidth(15);
        $objExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setWidth(30);
        $objExcel->getActiveSheet()
            ->getColumnDimension('D')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('E')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('F')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('G')
            ->setWidth(10);
        $objExcel->getActiveSheet()
            ->getColumnDimension('H')
            ->setWidth(70);
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $objActSheet->setCellValue("A1", '汉购网商品ID');
        $objActSheet->setCellValue("B1", '商品名称（选填）');
        $objActSheet->setCellValue("C1", '分销渠道商品ID（不修改请留空）');
        $objActSheet->setCellValue("D1", '分销渠道供货价');
        $objActSheet->setCellValue("E1", '分销渠道促销价');
        $objActSheet->setCellValue("F1", '促销开始时间');
        $objActSheet->setCellValue("G1", '促销结束时间');
        $objActSheet->setCellValue("H1", '导入反馈结果');
        $k=2;
        $redis=new \Redis();
        $redis->connect(C('REDIS_HOST'),C('REDIS_PORT'));
        $data=unserialize($redis->get(I('get.key_name')));
        foreach($data as $ka =>$v){
            $objActSheet->setCellValue("A" . $k, $v['A']);
            $objActSheet->setCellValue("B" . $k, $v['B']);
            $objActSheet->setCellValue("C" . $k, $v['C']);
            $objActSheet->setCellValue("D" . $k, $v['D']);
            $objActSheet->setCellValue("E" . $k, $v['E']);
            $objActSheet->setCellValue("F" . $k, $v['F']);
            $objActSheet->setCellValue("G" . $k, $v['G']);
            $objActSheet->setCellValue("H" . $k, $v['H']);
            $k++;
        }
        $outfile =  '导入商品反馈表-' . date('Y-m-d') . '.xls';
        // export to exploer
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
        exit();
    }
    private function _addCategory($goods,$v)
    {

        /** @var B2cCategoryModel $b2cGoodsModel */
        $b2cGoodsModel = D('B2cCategory');
        $data['catename'] = $goods['goods_name'];
        $data['pid'] = $goods['goods_id'];
        $data['gid'] = $goods['goods_commonid'];
        $data['uid'] = session('uid');
        $data['fxpid'] = session('member_type') == 'jicai' ? intval($data['pid']) : $v['fxpid'];
        $data['ctime'] = time();
        $data['fxprice'] = $v['fxprice']>0?$v['fxprice']:$goods['goods_price'];
        $data['promotion_price'] = $v['promotion_price']>0?$v['promotion_price']:0;
        $data['promotion_start'] = $v['promotion_price']>0?intval(strtotime($v['promotion_start'])):0;
        $data['promotion_end'] = $v['promotion_price']>0?intval(strtotime($v['promotion_end'])):0;
        return $b2cGoodsModel->addCategory($data, '', session('uid'), $data['pid']);

    }

}
