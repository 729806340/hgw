<?php
/**
 * ������Ʒsku
 * User: liaochenyun
 * Date: 2017-06-19
 * Time: 18:06
 */
defined('ByShopWWI') or exit('Access Invalid!');
class fenxiao_skuModel extends Model{
    public function addSkuList($param,$source){
        $sku_id_array = $this->table('b2c_fenxiao_sku')->field('sku_id')->where(array('source' => $source))->select();
        $sku_id_array = array_column($sku_id_array,'sku_id');

        $data_out = array();
        foreach($param as $v){
            //只新增不存在的
            if(!in_array($v['sku_id'],$sku_id_array)){
                $data_out[] = $v;
            } else {
                $condition = array();
                $condition['source'] = $source;
                $condition['sku_id'] = $v['sku_id'];
                $this->table('b2c_fenxiao_sku')->where($condition)->update(array('goods_name' => $v['goods_name']));
            }
        }

        //如果没有可新增的,需要去更新产品id
        if(empty($data_out)){
            //刷新pid
            $res_pid = $this->updatePid($source);
            if(!$res_pid) return false;
            return true;
        }
        $res = $this->table('b2c_fenxiao_sku')->insertAll($data_out);
        if(!$res) return false;
        return  true;
    }

    //获取pid和fxpid数组
    public function getPidFxpidArrayByUid($uid) {
        $where['uid'] = intval($uid);
        $result = $this->table('b2c_category') -> where($where) -> select();
        $array = array();
        foreach ($result as $v) {
            $array[$v['fxpid']] = $v['pid'];
        }
        return $array;
    }

    //只更新有pid的
    public function updatePid($source){
        $sku_list = $this->table('b2c_fenxiao_sku')->where(array('source' => $source))->select();
        $member_id = Model('member_fenxiao')->getMemberIdByCode($source);
        $pid_array = $this->getPidFxpidArrayByUid($member_id);

        $data_out = array();
        foreach($sku_list as $k => $v){
            $v['spu_id'] = $pid_array[$v['sku_id']]?$pid_array[$v['sku_id']]:0;
            if($v['spu_id'] != 0){
                $data_out[] = $v;
                $res = $this->table('b2c_fenxiao_sku')->where(array('id' => $v['id']))->update(array('spu_id' => $v['spu_id']));
            }
        }
        return true;
    }

    public function getSkuList($condition,$page,$order = 'spu_id desc'){
        $sku_list = $this->table('b2c_fenxiao_sku')->where($condition)->order($order)->page($page)->select();
        return $sku_list;
    }
}