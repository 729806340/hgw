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

class b2b_goodsModel extends Model
{
    public function __construct()
    {
        parent::__construct('b2b_goods');
    }
    const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE10 = 10;     // 违规
    const VERIFY1 = 1;      // 审核通过
    const VERIFY0 = 0;      // 审核失败
    const VERIFY10 = 10;    // 等待审核
    /**
     * 商品SKU列表
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

        if( !isset( $condition['is_del'] ) ){
            //$condition['is_del'] = 0 ;
        }else if($condition['is_del'] == -1){
            unset($condition['is_del']);
        }

        $goodsList = $this->table('b2b_goods')->field($field)->where($condition)->group($group)->order($order)->limit($limit)->page($page, $count)->select();
        if(empty($goodsList)) return $goodsList;
        $commonIds = array_unique(array_column($goodsList,'goods_commonid'));
        $goodsCommonList = $this->table('b2b_goods_common')->where(array('goods_commonid'=>array('in',$commonIds)))->limit($limit)->select();
        $goodsCommonList = array_under_reset($goodsCommonList,'goods_commonid');
        foreach ($goodsList as $k=>$v){
            $goodsList[$k]['common_info'] =  $goodsCommonList[$v['goods_commonid']];
        }
        return $goodsList;
    }

    //获取单条商品的信息
    public function getGoodsDetailbygoods_commonid($goods_commonid){
        if($goods_commonid<1){
            return null;
        }
        $goods_info=array();
        $result=$this->getGoodsCommonInfoByID($goods_commonid,"*");
        if(empty($result)){
            return null;
        }
        $upload_list = Model('upload')->getUploadList(array('item_id' => $result['goods_commonid'],'upload_type' => 7));
        $image_array = array();
        foreach($upload_list as $k =>$v){
            $image_array[$k]['image'] = UPLOAD_SITE_URL.'/b2b/goods/'.$v['file_name'];
            $image_array[$k]['main'] = $v['is_main'];
        }
        $result['goods_image'] = $image_array;
//        $result['goods_image'] = UPLOAD_SITE_URL.'/b2b/goods/'.$upload_list[0]['file_name'];
        $result1=$this->getmoreGoodsInfo(array('goods_commonid'=>$result['goods_commonid']));
        $result['goods_common']=$result1;
        return  $result;
    }


    /*
     *获取多条数据消息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getmoreGoodsInfo($condition, $field = '*')
    {
        return $this->table('b2b_goods')->field($field)->where($condition)->select();
    }

    /**
     * 获取单条商品SKU信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsInfo($condition, $field = '*')
    {
        $goodsInfo =  $this->table('b2b_goods')->field($field)->where($condition)->find();
        $common_info = $this->table('b2b_goods_common')->where(array('goods_commonid'=>$goodsInfo['goods_commonid']))->find();
        $commis_rate = $this->table('b2b_category')->where(array('bc_id'=>$common_info['gc_id']))->field('commis_rate')->find();
        $common_info['commis_rate'] = $commis_rate['commis_rate'];
        $goodsInfo['common_info'] = $common_info;
        return $goodsInfo;
    }

    /**
     * 获取单条商品信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsCommonInfo($condition, $field = '*')
    {
        return $this->table('b2b_goods_common')->field($field)->where($condition)->find();
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $goods_commonid
     * @param string $fields 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsCommonInfoByID($goods_commonid, $fields = '*')
    {
        $common_info = $this->_rGoodsCommonCache($goods_commonid, $fields);
        if (empty($common_info)) {
            $common_info = $this->getGoodsCommonInfo(array('goods_commonid' => $goods_commonid));
            $this->_wGoodsCommonCache($goods_commonid, $common_info);
        }
        return $common_info;
    }

    /**
     * 取得商品详细信息（优先查询缓存）（在售）
     * 如果未找到，则缓存所有字段
     * @param int $goods_id
     * @param string $field 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsOnlineInfoByID($goods_id, $field = '*')
    {
        if ($field != '*') {
            $field .= ',goods_state,goods_verify';
        }
        $goods_info = $this->getGoodsInfoByID($goods_id, trim($field, ','));
        if ($goods_info['goods_state'] != self::STATE1 || $goods_info['goods_verify'] != self::VERIFY1) {
            $goods_info = array();
        }
        return $goods_info;
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $goods_id
     * @param string $fields 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsInfoByID($goods_id, $fields = '*')
    {
        $goods_info = $this->_rGoodsCache($goods_id, $fields);
        if (empty($goods_info)) {
            $goods_info = $this->getGoodsInfo(array('goods_commonid' => $goods_id));
            $this->_wGoodsCache($goods_id, $goods_info);
        }
        return $goods_info;
    }

    public function checkOnline($goods)
    {
        if ($goods['goods_state'] == 1 && $goods['goods_verify'] == 1) {
            return true;
        }
        return false;
    }

    public function editGoodsById($update, $goodsid_array, $updateXS = false)
    {
        if (empty($goodsid_array)) {
            return true;
        }
        $condition = array();
        $condition['goods_id'] = array('in', $goodsid_array);
        $update['goods_edittime'] = TIMESTAMP;
//        var_dump($condition);
//        v($update);
        $result = $this->table('b2b_goods')->where($condition)->update($update);
        if ($result) {
            foreach ((array)$goodsid_array as $value) {
                $this->_dGoodsCache($value);
            }
            if (C('fullindexer.open') && $updateXS) {
                QueueClient::push('updateXS', $goodsid_array);
            }
        }
        return $result;
    }


    /**
     * 读取商品缓存
     * @param int $goods_id
     * @param string $fields
     * @return array
     */
    private function _rGoodsCache($goods_id, $fields)
    {
        return rcache($goods_id, 'b2b_goods', $fields);
    }

    /**
     * 写入商品缓存
     * @param int $goods_id
     * @param array $goods_info
     * @return boolean
     */
    private function _wGoodsCache($goods_id, $goods_info)
    {
        return wcache($goods_id, $goods_info, 'b2b_goods');
    }

    /**
     * 删除商品缓存
     * @param int $goods_id
     * @return boolean
     */
    private function _dGoodsCache($goods_id)
    {
        return dcache($goods_id, 'b2b_goods');
    }

    /**
     * 读取商品公共缓存
     * @param int $goods_commonid
     * @param string $fields
     * @return array
     */
    private function _rGoodsCommonCache($goods_commonid, $fields)
    {
        return rcache($goods_commonid, 'b2b_goods_common', $fields);
    }

    /**
     * 写入商品公共缓存
     * @param int $goods_commonid
     * @param array $common_info
     * @return boolean
     */
    private function _wGoodsCommonCache($goods_commonid, $common_info)
    {
        return wcache($goods_commonid, $common_info, 'b2b_goods_common');
    }

    /**
     * 删除商品公共缓存
     * @param int $goods_commonid
     * @return boolean
     */
    private function _dGoodsCommonCache($goods_commonid)
    {
        return dcache($goods_commonid, 'b2b_goods_common');
    }

}
