<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once ('WorkflowHandler.php');

class Platformgoodsedit extends WorkflowHandler
{

    public function getId()
    {
        return 2;
    }

    public function getConfig()
    {
        return array(
            'name' => '平台商家商品信息修改审批',
            'model' => 'goods',
            'primary_key' => 'goods_id',
            'attributes' => array(
                array(
                    'name' => 'goods_price',
                    'type' => 'text',
                    'label' => '商品价格',
                    'on' => '商家'
                ),
                array(
                    'name' => 'gc_id',
                    'type' => 'text',
                    'label' => '顶级分类编号',
                    'on' => '商家'
                ),
                array(
                    'name' => 'gc_id_1',
                    'type' => 'text',
                    'label' => '一级分类编号',
                    'on' => '商家'
                ),
                array(
                    'name' => 'gc_id_2',
                    'type' => 'text',
                    'label' => '二级分类编号',
                    'on' => '商家'
                ),
                array(
                    'name' => 'gc_id_3',
                    'type' => 'text',
                    'label' => '三级分类编号',
                    'on' => '商家'
                ),
            ),
            'reference' => '/admin/modules/shop/index.php?act=goods&op=view&goods_id={id}',
            'start' => '商家', // 启动用户组
            'flow' => array(
                '商家' => array(
                    'approve' => function ($model) {
                        return '运营部';
                    },
                    'reject' => ''
                ),
                '运营部' => array(
                    'approve' => function ($model) {
                     $data = array();
                     $condition = array();
                     foreach($model['new_value'] as $k=>$v){
                        $data[$k] = $v;
                     }
                     $data['goods_verify'] = 1;
                     $data['goods_state'] = 1;
                     $data['goods_promotion_price'] = $data['goods_price'];
                     $condition['goods_id'] = $model['model_id'];
                     $result = Model('goods')->where($condition)->update($data);
                        if($result){
                        $goods_common =Model('goods')->getGoodsInfo(array('goods_id'=>$model['model_id']) ,'goods_commonid');
                        unset($data['goods_promotion_price']);
                        Model('goods_common')->where(array('goods_commonid'=>$goods_common['goods_commonid']))->update($data);

                            //删除缓存
                        delGoodsCache($model['model_id'], $goods_common['goods_commonid']);
                     }
                    return 'closed';
                    },
                    'reject' => '商家'
                ),
            )
        );
    }
}