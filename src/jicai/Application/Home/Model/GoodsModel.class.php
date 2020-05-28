<?php
namespace Home\Model;

use Think\Model;

class GoodsModel extends Model
{
    
    public function getGoodsList($pagesize = 6, $page = 1, $stage = '', $goodsname)
    {
        $conditions = array();/*
        $goodids = $this->getfxGoodsIds();
        count($goodids) > 0 and $conditions['goods_id'] = array(
            'not in',
            $goodids
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
            $result = M('goods')->field('id,name,price,store_id,cost,stock,tax,commission')
                ->where($conditions)
                ->order('id desc')
                ->limit($offset, $pagesize)
                ->select();
            $pagetotal = ceil($totalrows / $pagesize);
        }
        return array(
            $totalrows,
            $result,
            $pagetotal
        );
    }
}