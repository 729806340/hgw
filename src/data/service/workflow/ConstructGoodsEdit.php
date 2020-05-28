<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:51
 */
require_once ('WorkflowHandler.php');

class ConstructGoodsEdit extends WorkflowHandler
{

    public function getId()
    {
        return 1;
    }

    public function getConfig()
    {
        return array(
            'name' => '非平台商家商品信息修改审批',
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
                array(
                    'name' => 'tax_input',
                    'type' => 'text',
                    'label' => '进项税率',
                    'on' => array('商家','运营部'),
                ),
                array(
                    'name' => 'tax_output',
                    'type' => 'text',
                    'label' => '销项税率',
                    'on' => array('商家','运营部'),
                ),
                array(
                    'name' => 'sign_ceo',
                    'type' => 'file',
                    'label' => '总裁签字',
                    'on' => '运营部',
                    'attachment' => true,
                    'notice'=>'凭证上传',
                    'when' => '/goods/apply/sign_vp'
                ),
                array(
                    'name' => 'sign_president',
                    'type' => 'file',
                    'label' => '董事长签字',
                    'on' => '运营部',
                    'attachment' => true,
                    'notice'=>'凭证上传',
                    'when' => '/goods/apply/sign_cp'
                )
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
                        return '运营总监';
                    },
                    'reject' => '商家',
                    'action' => 'con_goods',
                ),
                /*'运营总监' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        return '公司商务';
                    },
                    'reject' => '商家'
                ),*/
                
                '运营总监' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        /** @var goodsModel $goodsModel */
                        $goodsModel = Model('goods');
                        $goods_info = $goodsModel->getGoodsInfo(array('goods_id' => $model['model_id']));
                        $goods_price = ncPriceFormat($model['new_value']['goods_price']);
                        //毛利率大于5%时，跳过财务审核，直接完结流程
                        if ((($goods_price - $goods_info['goods_cost']) > $goods_info['goods_cost'] * 0.05)) {
                            ConstructGoodsEdit::doSubmit($model);
                            return 'closed';
                        } else {
                            return '财务部';
                        }
                    },

                    'reject' => ''
                ),
                
                '财务部' => array(
                    'timeout' => 3600,
                    'approve' => function ($model) {
                        ConstructGoodsEdit::doSubmit($model);
                        return 'closed';
                    },
                    'reject' => ''
                )
            )
        );
    }


    //最后一步审批结束时，提交数据更新处理
    static public function doSubmit($model) {
        // TODO
        $data = array();
        $condition = array();
        
        foreach($model['new_value'] as $k=>$v){
            $data[$k] = $v;
        }
        $condition['goods_id'] = intval($model['model_id']);
        $data['goods_verify'] = 1;
        $data['goods_state'] = 1;
        $data['goods_promotion_price'] = $data['goods_price'];
        $result = Model('goods')->where($condition)->update($data);
        if($result){
            $goods_common =Model('goods')->getGoodsInfo(array('goods_id'=>$model['model_id']) ,'goods_commonid');
            unset($data['goods_promotion_price']);
            Model('goods_common')->where(array('goods_commonid'=>$goods_common['goods_commonid']))->update($data);
            
            //删除缓存
            delGoodsCache($model['model_id'], $goods_common['goods_commonid']);
        }
    }
}