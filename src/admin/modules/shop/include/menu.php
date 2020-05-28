<?php
/**
 * 菜单
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

$_menu['shop'] = array(
    'name' => '商城-汉购网',
    'child' => array(
        array(
            'name' => '设置',
            'child' => array(
                'setting' => '商城设置',
                'upload' => '图片设置',
                'search' => '搜索设置',
                'seo' => $lang['nc_seo_set'],
                'message' => $lang['nc_message_set'],
                'payment' => $lang['nc_pay_method'],
                'express' => $lang['nc_admin_express_set'],
                'waybill' => '运单模板',
                'web_config' => '首页管理',
                'web_channel' => '频道管理',
                'pc_special' => 'PC专题',
                'fenxiao' => '分销渠道',
            )
        ),
        array(
            'name' => $lang['nc_goods'],
            'child' => array(
                'goods' => $lang['nc_goods_manage'],
                'sku' => 'SKU管理',
                'goods_class' => $lang['nc_class_manage'],
                'brand' => $lang['nc_brand_manage'],
                'type' => $lang['nc_type_manage'],
                'spec' => $lang['nc_spec_manage'],
                //'category_tag' => $lang['nc_category_tag_manage'],
                'goods_album' => $lang['nc_album_manage'],
                'goods_recommend' => '商品推荐',
                'goods_category' => '自定义分类管理',
                'fenxiao_map' => '分销商品SKU',
                'jdy_mapping' => '精斗云映射商品',
            ),
            'subop' => array(
                'goods' => array(
                    array(
                        'action' => 'index|lockup_list|waitverify_list|get_xmlOp|get_goods_sku_listOp|export_csvOp|transportOp',
                        'name' => '查看商品'
                    ),
                    array(
                        'action' => 'goods_lockup|waitverify|goods_verify',
                        'name' => '审核商品'
                    ),
                    array(
                        'action' => 'goods_setOp',
                        'name' => '商品设置'
                    ),
					array(
                        'action' => 'cost_edit',
                        'name' => '成本价维护'
                    ),
					array(
                        'action' => 'cost_historyOp',
                        'name' => '成本价日志'
                    ),
                    array(
                        'action' => 'tax_edit',
                        'name' => '税率维护'
                    ),
                    array(
                        'action' => 'viewOp',
                        'name' => '商品详情'
                    ),
                	array(
                		'action' => 'flush_goods_cacheOp',
                		'name' => '更新缓存'
                	),
                    array(
                        'action' => 'edit_retail_money|edit_shequ_return_money',
                        'name' => '设置社区团购佣金'
                    ),
                ),
            )
        ),
        array(
            'name' => $lang['nc_store'],
            'child' => array(
                'store' => $lang['nc_store_manage'],
                'store_grade' => $lang['nc_store_grade'],
                'store_class' => $lang['nc_store_class'],
                'domain' => $lang['nc_domain_manage'],
                'sns_strace' => $lang['nc_s_snstrace'],
                'help_store' => '店铺帮助',
                'store_joinin' => '商家入驻',
                'ownshop' => '自营店铺',
                'punish' => '店铺罚款',
            ),
            'subop' => array(
                'store' => array(
                    array(
                        'action' => 'index|bill_cycle|store|get_xml|get_bill_cycle_xml|ckeck_store_name|export_csv|check_seller_name|check_member_name|store_joinin|get_joinin_xml|get_bind_class_applay_xml',
                        'name' => '查看店铺'
                    ),
                    array(
                        'action' => 'store_joinin_detail|store_joinin_verify',
                        'name' => '审核店铺'
                    ),
                    array(
                        'action' => 'bill_cycyle|bill_cycyle_edit',
                        'name' => '结算周期'
                    ),
                    array(
                        'action' => 'shopwwi_addOp',
                        'name' => '新增店铺'
                    ),
                    array(
                        'action' => 'store_editOp',
                        'name' => '店铺编辑'
                    ),
                    array(
                        'action' => 'edit_save_joininOp',
                        'name' => '店铺注册资质编辑'
                    ),
                    array(
                        'action' => 'manage_typeOp',
                        'name' => '店铺类型编辑'
                    ),
                    array(
                        'action' => 'empty_costOp|get_empty_cost_xmlOp',
                        'name' => '店铺零成本商品检测'
                    ),
                    array(
                        'action' => 'empty_commis_classOp',
                        'name' => '店铺零佣金分类检测'
                    ),
                    array(
                        'action' => 'store_bind_classOp|store_bind_class_addOp|store_bind_class_delOp|store_bind_class_updateOp',
                        'name' => '店铺经营类目管理'
                    ),
                    array(
                        'action' => 'store_bind_class_applay_listOp|store_bind_class_applay_checkOp|store_bind_class_applay_delOp',
                        'name' => '经营类目申请'
                    ),
                    array(
                        'action' => 'reopen_listOp|get_reopen_xmlOp|reopen_checkOp|reopen_delOp|remind_renewalOp',
                        'name' => '续签申请管理'
                    )
                )
                
            )
        ),
        array(
            'name' => $lang['nc_member'],
            'child' => array(
                'member' => $lang['nc_member_manage'],
                'pyramid_member' => '分销会员',
                'member_fenxiao' => '渠道管理',
                'member_exp' => '等级经验值',
                'points' => $lang['nc_member_pointsmanage'],
                'sns_sharesetting' => $lang['nc_binding_manage'],
                'sns_malbum' => $lang['nc_member_album_manage'],
                'snstrace' => $lang['nc_snstrace'],
                'sns_member' => $lang['nc_member_tag'],
                'predeposit' => $lang['nc_member_predepositmanage'],
                'chat_log' => '聊天记录'
            ),
            'subop' => array(
                'member' => array(
                    array(
                        'action' => 'index|ajaxOp|get_xmlOp|export_csvOp|member_view',
                        'name' => '查看会员'
                    ),
                    array(
                        'action' => 'member_editOp',
                        'name' => '会员修改'
                    ),
                    array(
                        'action' => 'paypwd_editOp',
                        'name' => '会员支付密码修改'
                    )
                )
            )
        ),
        array(
            'name' => $lang['nc_trade'],
            'child' => array(
                'order' => $lang['nc_order_manage'],
                'pyramid_order' => '推广分销订单',
                //'vr_order' => '虚拟订单',
                'refund' => '退款管理',
                'return' => '退货管理',
                //'vr_refund' => '虚拟订单退款',
                'consulting' => $lang['nc_consult_manage'],
                'inform' => $lang['nc_inform_config'],
                'evaluate' => $lang['nc_goods_evaluate'],
                'complain' => $lang['nc_complain_config'],
                'pendtreat'=>'退款退货待处理',
                'pyramid_crash'=>'分销提现管理',
            ),
            'subop' => array(
                
                'order' => array(
                    array(
                        'action' => 'index|get_xmlOp|get_all_xml|show_orderOp|export_step1Op',
                        'name' => '查看订单'
                    ),
                    array(
                        'action' => 'change_stateOp',
                        'name' => '订单状态修改'
                    ),
                    array(
                        'action' => 'edit_address',
                        'name' => '修改收货地址'
                    ),
                    array(
                        'action' => 'edit_deliver',
                        'name' => '修改快递单号'
                    ),
                    array(
                        'action' => 'show_goods_column',
                        'name' => '财务数据查看'
                    ),
                    array(
                        'action' => 'jicai_total|change_stateOp|add_trade_snOp',
                        'name' => '集采订单处理',
                    ),
                    array(
                        'action' => 'rc_orderOp',
                        'name' => '老订单作废重建订单'
                    )
                ),
                'refund' => array(
                    array(
                        'action' => 'index|get_all_xml|export_step1|get_manage_xml|view',
                        'name' => '所有记录'
                    ),
                    array(
                        'action' => 'kefu|kefu_edit|import|upload_img|edit_refund_amount|cancel_refund|kefu_reject|view_detail|get_view_detail_xml',
                        'name' => '客服处理'
                    ),
                    array(
                        'action' => 'seller_edit',
                        'name' => '待商家处理'
                    ),
                    array(
                        'action' => 'fxsellerdo',
                        'name' => '商家已处理分销订单'
                    ),
                    array(
                        'action' => 'store_reject|get_reject_xml|changeDisplay|changeDisplays|refuse_agree|refuse_agrees|refuse_restore|refuse_restores',
                        'name' => '商家已拒绝'
                    ),
                    array(
                        'action' => 'caiwu|edit',
                        'name' => '财务处理'
                    ),
                    array(
                        'action' => 'reason',
                        'name' => '退款退货原因查看'
                    ),
                    array(
                        'action' => 'add_reason|edit_reason|del_reason',
                        'name' => '退款退货原因设置'
                    ),
                    array(
                        'action' => 'go_refund|pic_upload|delimg',
                        'name' => '新增退款页面'
                    ),
                    array(
                        'action' => 'add_refund|edit_refund|del_refund',
                        'name' => '编辑退款操作'
                    ),
                    array(
                        'action'=>'changeRemark',
                        'name'=>'编辑用户退款说明'
                    ),
                    array(
                      'action'=>'upload_img',
                        'name'=>'上传凭证',
                    )
                ),
                'return' => array(
                    array(
                        'action' => 'index|get_all_xml|export_step1|get_manage_xml|view',
                        'name' => '所有记录'
                    ),
                    array(
                        'action' => 'kefu_manage|kefu|kefu_edit|upload_img',
                        'name' => '客服处理'
                    ),
                    array(
                        'action' => 'seller_edit',
                        'name' => '待商家处理'
                    ),
                    array(
                        'action' => 'fxsellerdo',
                        'name' => '商家已处理分销订单'
                    ),
                    array(
                        'action' => 'store_reject|get_reject_xml|changeDisplay|refuse_agree|refuse_restore',
                        'name' => '商家已拒绝'
                    ),
                    array(
                        'action' => 'caiwu_manage|caiwu|edit',
                        'name' => '财务处理'
                    ),
                    array(
                        'action' => 'reason',
                        'name' => '退款退货原因查看'
                    ),
                    array(
                        'action' => 'add_reason|edit_reason|del_reason',
                        'name' => '退款退货原因设置'
                    ),
                    array(
                        'action' => 'go_return|pic_upload|delimg',
                        'name' => '新增退货页面',
                    ),
                    array(
                        'action' => 'add_return',
                        'name' => '新增退货操作',
                    ),
                    array(
                        'action' => 'ship',
                        'name' => '设置退货物流',
                    )
                ),
            )
        ),
        array(
            'name' => $lang['nc_operation'],
            'child' => array(
                'operating' => '运营设置',
                'bill' => $lang['nc_bill_manage'],
                'shequ_bill' => '社区团长结算',
                'vr_bill' => '虚拟订单结算',
                'mall_consult' => '平台客服',
                'kefu_manager' => '客服经理',
                'rechargecard' => '平台充值卡',
                'delivery' => '物流自提服务站',
                'contract' => '消费者保障服务',
                'workflow' => '审批管理',
                'channelfill'=>'渠道补抓',
                'channeltest'=>'渠道接口调试',
                'channel_bill'=>'渠道结算',
                'sale_analysis'=>'竞品分析'
            )
        ),
        array(
            'name' => '促销',
            'child' => array(
                'operation' => '促销设定',
                'groupbuy' => $lang['nc_groupbuy_manage'],
                'vr_groupbuy' => '虚拟团购设置',
                'promotion_cou' => '加价购',
                'promotion_xianshi' => $lang['nc_promotion_xianshi'],
                'promotion_pintuan' => '拼团',
                'promotion_mansong' => $lang['nc_promotion_mansong'],
                'promotion_bundling' => $lang['nc_promotion_bundling'],
                'promotion_booth' => '推荐展位',
                'promotion_book' => '预售商品',
                'promotion_fcode' => 'Ｆ码商品',
                'promotion_combo' => '推荐组合',
                'promotion_sole' => '手机专享',
                'pointprod' => $lang['nc_pointprod'],
                'voucher' => $lang['nc_voucher_price_manage'],
                'redpacket' => '平台红包',
                'activity' => $lang['nc_activity_manage'],
                'tuan_list'=>'社区接龙',
                'tuan_config'=>'社区团购设置',
            )
        ),
        array(
            'name' => $lang['nc_stat'],
            'child' => array(
                'stat_general' => $lang['nc_statgeneral'],
                'stat_industry' => $lang['nc_statindustry'],
                'stat_member' => $lang['nc_statmember'],
                'stat_store' => $lang['nc_statstore'],
                'stat_trade' => $lang['nc_stattrade'],
                'stat_goods' => $lang['nc_statgoods'],
                'stat_marketing' => $lang['nc_statmarketing'],
                'stat_aftersale' => $lang['nc_stataftersale'],
                'stat_channel' => '渠道分析',
            )
        )
    )
);