<?php
/**
 * 商品管理
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */


defined('ByShopWWI') or exit('Access Invalid!');

class b2b_goods_commonModel extends Model
{
    public function __construct()
    {
        parent::__construct('b2b_goods_common');
    }
    const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE10 = 10;     // 违规
    const VERIFY1 = 1;      // 审核通过
    const VERIFY0 = 0;      // 审核失败
    const VERIFY10 = 10;    // 等待审核
    /**商品表分页
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonList($condition, $field = '*', $page = 10, $order = 'goods_commonid desc', $limit = '')
    {
//        $condition['goods_state']=self::STATE1;
        return $this->table('b2b_goods_common')->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
    }

    /**--------
     * 商品图片记录
     * @param
     * @return array
     */
    public function getGoodsPicList($condition = array()) {
        $condition['upload_type'] = '7';//帮助内容图片
        $result = $this->table('upload')->where($condition)->select();
        return $result;
    }

    /**
     * 更新商品数据
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommon($update, $condition)
    {
        return $this->table('b2b_goods_common')->where($condition)->update($update);
    }


    /**
     * 新增商品公共数据
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoodsCommon($insert)
    {
        return $this->table('b2b_goods_common')->insert($insert);
    }

    //添加sku_array
    public function addGoodsSkuArray($insert)
    {
        return $this->table('b2b_goods')->insertAll($insert);
    }

    //添加sku_array
    public function delGoodsSkuArrayById($goods_commonid)
    {
        $condition = array();
        $condition['goods_commonid'] = $goods_commonid;
        return $this->table('b2b_goods')->where($condition)->delete();
    }

    //设置商品id绑定
    public function addGoodsImageArray($image_ids,$item_id){
        $update = array();
        $update['item_id'] = $item_id;
        $condition = array();
        $condition['upload_id'] = array('in',$image_ids);
        return $this->table('upload')->where($condition)->update($update);
    }

    public function delGoodsImageArray($image_ids){
        $condition = array();
        $condition['upload_id'] = array('in',$image_ids);
        return $this->table('upload')->where($condition)->delete();
    }


    //添加sku
    public function addGoodsSku($insert)
    {
        return $this->table('b2b_goods')->insert($insert);
    }

    //获取sku
    public function getGoodsSkuList($condition)
    {
        return $this->table('b2b_goods')->where($condition)->select();
    }

    public function getAllCategory(){
        $category_list = Model('b2b_category')->where('bc_pid = 0')->field('bc_id,bc_name')->select();
        $category_list_out = array();
        foreach($category_list as $k => $v){
            $goods_list = $this->getGoodsListByGcid($v['bc_id']);
            if(!empty($goods_list)){
                $category_list_out[] = $v;
                $category_list_out[$k]['goods_list'] = $goods_list;
            }
        }
        return $category_list_out;
    }


    //根据分类获取商品列表
    public function getGoodsListByGcid($gc_id = 0,$goods_name = '',$condition=array(),$page='',$order='')
    {
        if($gc_id != 0){
            $deep = Model('b2b_category')->getDeep($gc_id);
            if($deep = 1){
                $condition['gc_id_1'] = $gc_id;
            } else if($deep = 2){
                $condition['gc_id_2'] = $gc_id;
            } else if($deep = 3){
                $condition['gc_id_3'] = $gc_id;
            }
        }

        if($goods_name != ''){
            $condition['goods_name'] = array("like",'%' . $goods_name . '%');
        }
        $condition['goods_state'] = 1;
        $goods_list = $this->table('b2b_goods_common')->field('goods_commonid,goods_name,min_price,max_price')->where($condition)->order($order)->page($page)->select();
        foreach($goods_list as $k_g => $v_g){
            $upload_list = Model('upload')->getUploadList(array('item_id' => $v_g['goods_commonid'],'upload_type' => 7));
            $v_g['img'] = UPLOAD_SITE_URL.'/b2b/goods/'.$upload_list[0]['file_name'];
            $goods_list_out[] = $v_g;
        }
        return $goods_list_out;
    }

    /**
     * 商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @return array 二维数组
     */
    public function getGoodsList($condition, $field = '*', $group = '', $order = '', $limit = 0, $page = 0, $count = 0)
    {
        return $this->table('b2b_goods')->field($field)->where($condition)->group($group)->order($order)->limit($limit)->page($page, $count)->select();
    }

    /**
     * 在售商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsList($condition, $field, $group, $order, $limit, $page, $count);
    }

    private function _updateGoodsTaxFlow($goods_id,$tax_input,$tax_output,$admin_info){



        $goods_info = $this->table('b2b_goods')->where(array('goods_id' => $goods_id))->find();
        $goods_common_info = $this->table('b2b_goods_common')->where(array('goods_commonid' => $goods_info['goods_commonid']))->field('goods_name')->find();
        if($tax_input == $goods_info['tax_input'] && $tax_output == $goods_info['tax_output']){
            return 2;
        }

        if($admin_info['gname'] !='运营部'){
            return -3;
        }

        $type = 63;
        $whereWorkflow = array();
        $whereWorkflow['type'] = $type;
        $whereWorkflow['model']='b2b_goods';
        $whereWorkflow['model_id'] = $goods_id;
        $whereWorkflow['status'] = array('neq' ,'1');
        $workflowInfo = Model('workflow')->getWorkflowInfo($whereWorkflow , 'id');

        if($workflowInfo['id']>0){
//                            showMessage('此商品已提交了修改该商品税率审核,不能重复提交');
            return -4;
        }

        $new_value = array('tax_input'=>$tax_input,'tax_output'=>$tax_output);
        $old_value = array('tax_input'=>$goods_info['tax_input'],'tax_output'=>$goods_info['tax_output']);
        $data = array();          //审核表数组
        $data['title'] = '编号：'.$goods_id."商品（{$goods_common_info['goods_name']}）税率变更审核流程(b2b)";
        $data['type'] = $type;   //类目税率变更
        $data['model'] = 'b2b_goods';
        $data['model_id'] = $goods_id;
        $data['stage'] = $admin_info['gname'];
        $data['new_value'] = JSON($new_value);
        $data['old_value'] = JSON($old_value);
        $data['reference'] = "";
        $data['role'] = '0';
        $data['user'] = $admin_info['name'];

        if(!$workflowId = Model('workflow')->addWorkflow($data)){
            return -1;
//            throw new Exception( '插入审核数据库失败' );
        }

        $workflow = Model('workflow')->getWorkflowInfo(array('id'=>$workflowId));
        /** @var WorkflowService $service */
        $service = Service('Workflow');
        // 初始化数据
        $service->init($workflow,$admin_info['name'],$admin_info['gname']);
        if(!$service->approve('提交审核')){
            return -2;
//            throw new Exception( '审核提交失败' );
        }
        return 1;
    }

    public function setGoodsInfo($commonid = 0,$type,$post_data,$admin_info){

        $member_id = $admin_info['id'];
        $goods_body_data = html_entity_decode($post_data['goods_body']);
        $good_info = array();
        $good_info['goods_name']        = $post_data['goods_name'];
        $good_info['goods_body']        = $goods_body_data;

        if($type == 'goods_add'){
            $good_info['gc_id']        = intval($post_data['class_id']);
        } else if($type == 'goods_edit') {
            $good_info['gc_id']        = intval($post_data['choose_gcid']);
        }

        $model_category = Model('b2b_category');
        $gccache_arr = $model_category->getGoodsclassCache($good_info['gc_id'],3);
        $gcid_array = $gccache_arr['choose_gcid'];
        $good_info['gc_id_1'] = $gcid_array[0];
        $good_info['gc_id_2'] = $gcid_array[1];
        $good_info['gc_id_3'] = $gcid_array[2];
        $gc_name = $model_category->getBcNameByBcid($gcid_array);
        if(!empty($gc_name)){
            $good_info['gc_name'] = $gc_name[0]['bc_name'].'>'.$gc_name[1]['bc_name'].'>'.$gc_name[2]['bc_name'];
        }
        $good_info['b2c_goodsid']        = intval($post_data['b2c_goodsid']);
        $good_info['goods_state']        = intval($post_data['show_type']);
        $good_info['memberid']        = intval($member_id);
        $good_info['addtime'] = time();

        try {
            $this -> beginTransaction();
            if($commonid){
                $condition = array();
                $condition['goods_commonid'] = $commonid;
                $goods_commonid = $this->editGoodsCommon($good_info,$condition);
                if( !$goods_commonid ) {
                    throw new Exception( '商品更新失败' );
                }
                $goods_commonid = $commonid;
            } else {
                $goods_commonid = $this->addGoodsCommon($good_info);
                if( !$goods_commonid ) {
                    throw new Exception( '商品添加失败' );
                }
            }

            if(empty($post_data['at_value'])){
                //删除属性值
                $del_rel = $this->delGoodsSkuArrayById($goods_commonid);
                if( !$del_rel ) {
                    throw new Exception( '删除sku失败' );
                }
            }

            $this->table('b2b_goods')->where(array('goods_commonid' => $goods_commonid))->update(array('edit_sap' => 0));

            //属性处理
            if(!empty($post_data['at_value'])){
                $sku_array_update = array();
                $sku_array_add = array();

                $attribute_array        = $post_data['at_value'];
                foreach ($attribute_array as $k => $v){
                    if(!empty($v)){
                        $attr_array = array();
                        $attr_array['goods_commonid']    = $goods_commonid;
                        $attr_array['goods_calculate']    = $v['calculate'];
                        $attr_array['goods_price']    = ncPriceFormat($v['price']);
                        $attr_array['goods_storage']    = $v['storage'];
                        $attr_array['tax_input']    = $v['tax_input'];
                        $attr_array['tax_output']    = $v['tax_output'];
                        $attr_array['goods_cost']    = ncPriceFormat($v['cost']);
                        $attr_array['edit_sap']    = 0;

                        $tax_input = sprintf("%.3f",$v['tax_input']);
                        $tax_output = sprintf("%.3f",$v['tax_output']);
                        if($tax_input>=100||$tax_input<0||$tax_output>=100||$tax_output<0){
                            throw new Exception( '税率都必须大于0且小于100' );
                        }

                        if($k == 0){
                            $sku_array_add[$k]['sku'] = $attr_array;  //新增sku
                            $sku_array_add[$k]['tax'] = array('tax_input' => $tax_input,'tax_output' => $tax_output);  //新增sku
                        } else {
                            $sku_array_update[$k]['sku'] = $attr_array;  //更新sku
                            $sku_array_update[$k]['tax'] = array('tax_input' => $tax_input,'tax_output' => $tax_output);  //更新sku
                        }
                    }
                }

                $new_good_ids = array_keys($sku_array_update);
                $old_goods_ids = $this->table('b2b_goods')->where(array('goods_commonid' =>$goods_commonid))->field('goods_id')->select();
                $old_goods_ids = array_column($old_goods_ids,'goods_id');

                //需要删除的
                $del_ids = array_diff($old_goods_ids,$new_good_ids);

                if(!empty($del_ids)){
                    $condition = array();
                    $condition['goods_id'] = array('in',array_values($del_ids));
                    $del_rel =  $this->table('b2b_goods')->where($condition)->delete();
                    if(!$del_rel){
                        throw new Exception( '删除sku失败' );
                    }
                }

                //更新
                if(!empty($sku_array_update)){
                    foreach($sku_array_update as $k => $v){
                        $update_rel = $this->table('b2b_goods')->where(array('goods_id' => $k))->update($v['sku']);
                        if(!$update_rel){
                            throw new Exception( '更新sku失败' );
                        }
                    }
                }

                //税率更新审核
                // 临时关闭税率审批
                if(false&&!empty($sku_array_update)){
                    foreach($sku_array_update as $goods_id => $v){
                        $result = $this->_updateGoodsTaxFlow($goods_id,$v['tax']['tax_input'],$v['tax']['tax_output'],$admin_info);
                        if($result == 2 || $result == 1){
                            continue;
                        } else if($result == -1){
                            throw new Exception( '插入审核数据库失败' );
                        } else if($result == -2){
                            throw new Exception( '审核提交失败' );
                        } else if($result == -3){
                            throw new Exception( '对不起！只有运营人员才能发起成本变更流程' );
                        } else if($result == -4){
                            throw new Exception( '此商品已提交了修改该商品税率审核,不能重复提交' );
                        }
                    }
                }

                //新增
                if(!empty($sku_array_add)){
                    foreach($sku_array_add as $goods_id =>$v){
                        $add_rel =  $this->table('b2b_goods')->insert($v['sku']);
                        $result = $this->_updateGoodsTaxFlow($add_rel,$v['tax']['tax_input'],$v['tax_output'],$admin_info);
                        if($result == 2 || $result = 1){
                            continue;
                        } else if($result == -1){
                            throw new Exception( '插入审核数据库失败' );
                        } else if($result == -2){
                            throw new Exception( '审核提交失败' );
                        } else if($result == -3){
                            throw new Exception( '对不起！只有运营人员才能发起成本变更流程' );
                        } else if($result == -4){
                            throw new Exception( '此商品已提交了修改该商品税率审核,不能重复提交' );
                        }

                        if(!$add_rel){
                            throw new Exception( '新增sku失败' );
                        }
                    }
                }

                //计算最低价、最高价、总库存
                $condition = array();
                $condition['goods_commonid'] = $goods_commonid;
                $static_info = $this->table('b2b_goods')->field('min(goods_price) as min_price,max(goods_price) as max_price,sum(goods_storage) as max_storage')->where($condition)->master(true)->find();
                if( !$static_info ) {
                    throw new Exception( '统计sku失败' );
                }
                $good_info = array();
                $good_info['min_price']        = ncPriceFormat($static_info['min_price']);
                $good_info['max_price']        = ncPriceFormat($static_info['max_price']);
                $good_info['total_storage']        = $static_info['max_storage'];

                $condition = array();
                $condition['goods_commonid'] = $goods_commonid;

                $goods_sku = $this->editGoodsCommon($good_info,$condition);
                if( !$goods_sku ) {
                    throw new Exception( '更新统计sku失败' );
                }
            }

            //文件处理
            if(!empty($post_data['file_id'])){
                //关联商品id
                $add_rel = $this->addGoodsImageArray($post_data['file_id'],$goods_commonid);
                if( !$add_rel ) {
                    throw new Exception( '关联商品失败' );
                }
            }

            //绑定供应商
            if(!empty($post_data['supplier_id'])){
                $bind_rel = $this->bindGood($goods_commonid,$post_data['supplier_id']);
                if( !$bind_rel ) {
                    throw new Exception( '绑定供应商失败' );
                }
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $result = array() ;
            $result['state'] = false ;
            $result['msg'] = $e->getMessage() ;
            return $result ;
        }
        $result = array() ;
        $result['state'] = true ;
        $result['msg'] = '操作成功' ;
        return $result ;
    }
    /**
     * 商品下架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOffline($condition)
    {
        $update = array('goods_state' => self::STATE0);
        return $this->table('b2b_goods_common')->where($condition)->update($update);
    }

    /**
     * 商品上架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOnline($condition)
    {
        $update = array('goods_state' => self::STATE1);
        return $this->table('b2b_goods_common')->where($condition)->update($update);
    }


    public function bindGood($goods_commonid,$supplier_id){
        $update = array();
        $update['supplier_id'] = $supplier_id;
        $supplier_info = $this->table('b2b_supplier')->where(array('supplier_id'=>$supplier_id))->field('company_name')->find();
        $update['supplier_name'] = $supplier_info['company_name'];

        $condition = array();
        $condition['goods_commonid'] = $goods_commonid;
        return $this->table('b2b_goods_common')->where($condition)->update($update);
    }

    //解绑商品
    public function unbindGood($goods_commonid){
        $update = array();
        $update['supplier_id'] = 0;

        $condition = array();
        $condition['goods_commonid'] = $goods_commonid;
        return $this->table('b2b_goods_common')->where($condition)->update($update);
    }

    /**
     * 获取单条商品SKU信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsInfo($condition, $field = '*')
    {
        return $this->table('b2b_goods_common')->field($field)->where($condition)->find();
    }

    public function importGoods($condition){
        $b2c_goods_info = Model('goods')->where($condition)->find();
        $goods_data = array();
        $goods_data['goods_name'] = $b2c_goods_info['goods_name'];
        $goods_data['b2c_goodsid'] = $condition['goods_commonid'];
        $this->addGoodsCommon($goods_data);
        return true;
    }

    public function delGoodsAll($condition){
        $this->table('b2b_goods_common')->where($condition)->delete();
        $this->table('b2b_goods')->where($condition)->delete();
    }

    /**
     * 已下架商品列表
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonLockUpList($condition, $field = '*', $page = 10, $order = "goods_commonid desc", $limit = '')
    {
        $condition['goods_state'] = self::STATE0;
        return $this->getGoodsCommonList($condition, $field, $page, $order, $limit);
    }

    public function setMainImage($post_data){
        try {
            $this -> beginTransaction();

            //主图处理
            $file_id = 0;
                foreach($post_data['file_id'] as $k=>$v){
                    if($post_data['file_main'][$k] == 1){
                        $file_id = $v;
                    }
                }

            $upload_condition = array();
            $upload_condition['upload_id'] = $file_id;

            $item_info = $this->table('upload')->where($upload_condition)->find();
            if( !$item_info ) {
                throw new Exception( '未找到商品id' );
            }

            $item_condition = array();
            $item_condition['item_id'] = $item_info['item_id'];
            $this->table('upload')->where($item_condition)->update(array('is_main' => 0));
            $this->table('upload')->where($upload_condition)->update(array('is_main' => 1));

            $goods_condition = array();
            $goods_condition['goods_commonid'] = $item_info['item_id'];

            $this->table('b2b_goods_common')->where($goods_condition)->update(array('goods_image' => $item_info['file_name']));

            $this->commit();
            return true ;

        } catch (Exception $e) {
            $this->rollback();
            return false ;
        }

    }

}
