<?php
/**
 * 商品类别模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */

defined('ByShopWWI') or exit('Access Invalid!');

class goods_categoryModel extends Model
{
    public function __construct() {
        parent::__construct('goods_category');
    }
    /**
     * 获取分类
     * @param bool $tree    true返回多维数组 false返回简单排序数据
     * @param bool  $is_all 是否返回所有（包括disable=true）
     * @param int  $pid     父id
     * @return array
     */
    public function getCategory($tree = false , $is_all = false ,$pid = 0)
    {
        if(!$is_all){
            $condition['disable'] = 'false';
        }
        $result = $this->table('goods_category')->where($condition)->order('cat_sort asc')->limit(10000)->select();
        $data = $tree ? $this->getCategoryTree($result) :$this->sortCategory($result,$pid);
        return $data;
    }

    /**
     * 分类排序
     * @param array     $data   需要循环的数组
     * @param int       $id     获取id为$id下的子分类，0为所有分类
     * @param array     $arr    将获取到的数据暂时存储的数组中，方便数据返回
     * @return array            二维数组
     */
    public function sortCategory($data, $id = 0, &$arr = array())
    {
        foreach ($data as $v) {
            if ($id == $v['parent_id']) {
                $arr[] = $v;
                $this->sortCategory($data, $v['cat_id'], $arr);
            }
        }
        return $arr;
    }

    /**
     *  树形排序
     * @param array $data   需要排序的分类数据
     * @return array        多维数组
     */
    public function getCategoryTree($data = array())
    {
        $tree = array();
        $tmpMap = array();
        foreach ($data as $k => $v) {
            $tmpMap[$v['cat_id']] = $v;
        }
        
        foreach ($data as $value) {
            if (isset($tmpMap[$value['parent_id']])) {
                $tmpMap[$value['parent_id']]['child'][] = &$tmpMap[$value['cat_id']];
            } else {
                $tree[] = &$tmpMap[$value['cat_id']];
            }
        }
        
        unset($tmpMap);
        return $tree;
    }

    /**
     * 获取二三级分类
     * @param int $pid  获取分类（根据父id）
     * @return mixed
     */
    public function getCategoryTwoThree($pid = 0)
    {
        $condition['disable'] = 'false';
        $result = Model('goods_category')->where($condition)->field('cat_id,cat_name,parent_id')->order('cat_sort asc')->select();
        $data = $this->getCategoryTree($result);
        if($pid == 0){
            return $data;
        }
        foreach ($data as $k => $v) {
            if($v['cat_id'] != $pid){
                unset($data[$k]);
            }
        }
        sort($data);
        return $data[0]['child'];
    }

    /**
     * 根据分类ID删除该分类及子分类
     * @param $cat_id   分类ID
     */
    public function categoryDel($cat_id)
    {
        
        $result = $this->getCategory(false,true,$cat_id);
        $cat_ids = array();
        if($result){
            foreach ($result as $k => $v) {
                $cat_ids[] = $v['cat_id'];
            }
        }
        $cat_ids[] = $cat_id;
        sort($cat_ids);
        $res = Model('goods_category')->where(array('cat_id'=>array('in',$cat_ids)))->delete();
        if($res){
            exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
        }else{
            exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
        }
    }

    /**
     * 缓存(前台头部的商品分类)
     * @param int $update_all
     * @return array
     * @throws Exception
     */
    public function getCategoryByCache($update_all= 0)
    {
        if ($update_all == 1 || !($data = rkcache('goods_category'))) {
            $data = $this->_getCategoryAll();
            wkcache('goods_category', $data);
        }
        return $data;
    }

    /**
     * 获取自定义分类详细信息
     * @return array
     */
    private function _getCategoryAll(){
        $model_cat = Model('goods_category');
        $model_nav = Model('goods_category_nav');
        $result = $model_cat->where(array('disable'=>'false'))->order('cat_sort asc')->select();
        foreach ($result as $k => $v) {
            if(!empty($v['recommend_catids'])){
                $result[$k]['recommend_cats'] = Model('goods_category')->where(array('cat_id'=>array('in',explode(',',$v['recommend_catids']))))->field('cat_id,cat_name,cat_link')->select();
            }

            if($v['parent_id'] == 0){
                $result[$k]['ad'] = $model_nav->where(array('cat_id'=>$v['cat_id']))->select();
                if(!empty($result[$k]['ad'])){
                    foreach ($result[$k]['ad'] as $adk => $adv) {
                        $result[$k]['ad'][$adk]['nav_url'] = UPLOAD_SITE_URL. '/' . ATTACH_GOODS_CATEGORY . '/' . $adv['nav_url'];
                    }
                }
            }
            $result[$k]['logo'] = UPLOAD_SITE_URL. '/' . ATTACH_GOODS_CATEGORY . '/' . $v['logo'];
            $result[$k]['wap_logo'] = UPLOAD_SITE_URL. '/' . ATTACH_GOODS_CATEGORY . '/' . $v['wap_logo'];
        }
        $sortCategory = $this->sortCategory($result);//重新排序 去掉disable=true的分类以及子分类
        $data = $this->getCategoryTree($sortCategory);
        return $data;
    }
    
    /**
     * 
     */
    public function getGoodsCategoryForCacheModel()
    {
        $data =  $this->getCategoryByCache();
        return $data;
    }

}
