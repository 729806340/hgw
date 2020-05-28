<?php
namespace Home\Model;

use Think\Model;

class GoodsModel extends Model
{
    
    // 获取当前分销商已分销的商品
    private function getfxGoodsIds()
    {
        $uid = session('uid');
        $goodsIds = array();
        $result = M('B2cCategory')->field('pid')
            ->where(array(
            'uid' => $uid
        ))
            ->select();
        if (count($result) > 0) {
            foreach ($result as $k => $v) {
                $goodsIds[] = $v['pid'];
            }
        }
        return $goodsIds;
    }

    public function getGoodsList($pagesize = 6, $page = 1, $stage = '', $goodsname)
    {
        $conditions = array();/*
        $goodids = $this->getfxGoodsIds();
        count($goodids) > 0 and $conditions['goods_id'] = array(
            'not in',
            $goodids
        );*/
        $conditions['is_del'] = 0;
        $stage != '' and $conditions['goods_state'] = $stage;
        //税率未设置的不让显示上架
        /*$conditions['tax_input'] = array(
            'neq',
            '200'
        );*/
        $goodsname != '' and $conditions['goods_name'] = array(
            'like',
            '%' . $goodsname . '%'
        );
        $offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = M('goods')->where($conditions)->count();
        // echo M()->getLastSql();
        if ($totalrows > 0) {
            $result = M('goods')->field('goods_id,goods_commonid,goods_name,store_id,store_name,goods_price,goods_state,goods_storage,goods_image')
                ->where($conditions)
                ->order('goods_id desc')
                ->limit($offset, $pagesize)
                ->select();
            if (count($result) > 0) {
                foreach ($result as $k => $v) {
                    $result[$k]['goods_image'] = cthumb($v['goods_image'], 60);
                }
            }
            $pagetotal = ceil($totalrows / $pagesize);
        }
        return array(
            $totalrows,
            $result,
            $pagetotal
        );
    }
}