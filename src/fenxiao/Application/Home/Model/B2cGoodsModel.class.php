<?php
namespace Home\Model;

use Think\Model;

// $model->query('select * from user where id=%d and status=%d',array($id,$status));
class B2cGoodsModel extends Model
{

    // 获取product
    public function getGoodsList($pagesize = 6, $page = 1, $stage = '', $goodsname)
    {
        $conditions = array();
        if ($stage != '') {
            $conditions['goods_verify'] = 1;
        }
        if ($goodsname != '') {
            $conditions['goods_name'] = array(
                'like',
                '%' . $goodsname . '%'
            );
        }
        $offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $totalrows = $this->table('sdb_b2c_goods as a')
            ->where($conditions)
            ->count();
        
        
        $result = $this->table('sdb_b2c_goods as a')
            ->join('sdb_image_image as b ON a.image_default_id = b.image_id', 'left')
            ->join('sdb_b2c_products as c ON a.goods_id = c.goods_id', 'left')
            ->order('goods_id desc')
            ->limit($offset, $pagesize)
            ->where($conditions)
            ->field('a.*,b.s_url,c.product_id,c.name as p_name,c.price as p_price,c.bn as p_bn,c.store-c.freez as p_store,c.spec_info')
            ->select();
        // echo $this->getLastSql();
        $pagetotal = ceil($totalrows / $pagesize);
        return array(
            $totalrows,
            $result,
            $pagetotal
        );
    }
}
