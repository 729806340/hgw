<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Class cpsControl
 * CPS接口控制器
 * cps接口着陆页统一为/shop/index.php?act=cps其余参数不变
 * cps查询接口统一为/shop/index.php?act=cps&op=orders
 * 增加联盟识别参数与着陆页联盟识别参数一致，其余参数不变
 */
class ajaxControl extends BaseApiControl{
    public function indexOp()
    {
        $this->success('you are welcome!');
    }
    public function get_wx_small_app_qrOp(){

        /** @var wx_small_appLogic $wxSmallApp */
        $wxSmallApp = Logic('wx_small_app');
        try{
            $res = $wxSmallApp->getQr($_GET['page'],$_GET['scene']);
            header ('Content-Type: image/png');
            exit($res);
        }catch (\Exception $exception){
            exit ($exception->getMessage());
        }
    }

    public function user_infoOp()
    {
        $id = $_SESSION['member_id'];
        /** @var memberModel $memberModel */
        $memberModel = Model('member');
        $userInfo = $memberModel->getMemberInfo(array('member_id'=>$id),
            array('crm_member_id','member_name','member_truename','member_avatar','member_sex','member_birthday','member_email','member_email_bind','member_mobile','member_mobile_bind','member_qq','member_ww','member_login_num','member_time','member_ip','member_login_time','member_old_login_time','member_login_ip','member_old_login_ip','member_points','available_predeposit','freeze_predeposit','available_rc_balance','freeze_rc_balance','inform_allow','is_buy','is_allowtalk','member_state','member_snsvisitnum','member_areaid','member_cityid','member_provinceid','member_areainfo','member_privacy','member_exppoints','source','member_type',)
            );
        $userInfo['member_avatar'] = getMemberAvatar($userInfo['member_avatar']);
        $userInfo['grade'] = $memberModel->getOneMemberGrade($userInfo['member_exppoints']);
        $this->success($userInfo);
    }

    public function goods_infoOp() {
        $goods_id = intval($_GET['id']);
        /** @var goodsModel $model_goods */
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        $goods_info = $goods_detail['goods_info'];
        /** @var storeModel $model_store */
        $model_store = Model('store');
        $goods_info['store_info'] = $model_store->getStoreInfo(array('store_id'=>$goods_info['store_id']));
        if (empty($goods_info)) {
            return $this->error(L('goods_index_no_goods'));
        }

        // 验证预定商品是否到期
        if ($goods_info['is_book'] == 1) {
            $goods_info['remain'] = intval($goods_info['book_down_time'])- TIMESTAMP;
        }
        $goods_info['presell_deliverdate'] = date('Y-m-d', $goods_info['presell_deliverdate']);
        $goods_info['promotion_info'] = array(
            'mansong_info'=>$goods_detail['mansong_info'],
            'gift_array'=>$goods_detail['gift_array'],
        );
        ob_start();
        Language::read('home_layout');
        ?>
        <?php if (isset($goods_info['promotion_type']) || $goods_info['have_gift'] == 'gift' || !empty($goods_info['jjg_explain']) || !empty($goods_detail['mansong_info'])) {?>
            <div class="ncs-sale">
                <?php if (isset($goods_info['promotion_type']) || !empty($goods_info['jjg_explain']) || !empty($goods_detail['mansong_info'])) {?>
                    <dl>
                        <dt>促&#12288;&#12288;销：</dt>
                        <dd class="promotion-info">
                            <?php if (isset($goods_info['title']) && $goods_info['title'] != '') {?>
                                <span class="sale-name"><?php echo $goods_info['title'];?></span>
                            <?php }?>
                            <!-- S 限时折扣 -->
                            <?php if ($goods_info['promotion_type'] == 'xianshi') {?>
                                <span class="sale-rule w400">直降<em><?php echo L('currency').ncPriceFormat($goods_info['down_price']);?></em>
                                    <?php if($goods_info['lower_limit']) {?>
                                        <?php echo sprintf('最低%s件起，',$goods_info['lower_limit']);?>
                                        <?php echo $goods_info['xianshi_info']['xianshi_limit']<$goods_info['lower_limit']?'': sprintf('最多购买%s件，',$goods_info['xianshi_info']['xianshi_limit']);?>
                                        <?php echo $goods_info['explain'];?>
                                    <?php } ?>
            </span>
                            <?php }?>
                            <!-- E 限时折扣  -->
                            <!-- S 特卖-->
                            <?php if ($goods_info['promotion_type'] == 'groupbuy') {?>
                                <?php if ($goods_info['upper_limit']) {?>
                                    <em><?php echo sprintf('最多限购%s件',$goods_info['upper_limit']);?></em>
                                <?php } ?>
                                <span><?php echo $goods_info['remark'];?></span><br>
                            <?php }?>
                            <!-- E 特卖 -->
                            <!--S 满就送 -->
                            <?php if($goods_detail['mansong_info']) { ?>
                                <div class="ncs-mansong"> <span class="sale-name">满即送</span> <span class="sale-rule">
              <?php $rule = $goods_detail['mansong_info']['rules'][0]; echo L('nc_man');?>
                                        <em><?php echo L('currency').ncPriceFormat($rule['price']);?></em>
                                        <?php if(!empty($rule['discount'])) { ?>
                                            ，<?php echo L('nc_reduce');?><em><?php echo L('currency').ncPriceFormat($rule['discount']);?></em>
                                        <?php } ?>
                                        <?php if(!empty($rule['goods_id'])) { ?>
                                            ，<?php echo L('nc_gift');?><a href="<?php echo $rule['goods_url'];?>" title="<?php echo $rule['mansong_goods_name'];?>" target="_blank">赠品</a>
                                        <?php } ?>
              </span> <span class="sale-rule-more" nctype="show-rule"><a href="javascript:void(0);">共<strong><?php echo count($goods_detail['mansong_info']['rules']);?></strong>项，展开查看<i></i></a></span>
                                    <div class="sale-rule-content" style="display: none;" nctype="rule-content">
                                        <div class="title"><span class="sale-name">满即送</span>共<strong><?php echo count($goods_detail['mansong_info']['rules']);?></strong>项，促销活动规则<a href="javascript:;" nctype="hide-rule">关闭</a></div>
                                        <div class="content">
                                            <div class="mjs-tit"><?php echo $goods_detail['mansong_info']['mansong_name'];?>
                                                <time>( <?php echo L('nc_promotion_time');?><?php echo L('nc_colon');?><?php echo date('Y-m-d',$goods_detail['mansong_info']['start_time']).'--'.date('Y-m-d',$goods_detail['mansong_info']['end_time']);?> )</time>
                                            </div>
                                            <ul class="mjs-info">
                                                <?php foreach($goods_detail['mansong_info']['rules'] as $rule) { ?>
                                                    <li> <span class="sale-rule"><?php echo L('nc_man');?><em><?php echo L('currency').ncPriceFormat($rule['price']);?></em>
                                                            <?php if(!empty($rule['discount'])) { ?>
                                                                ， <?php echo L('nc_reduce');?><em><?php echo L('currency').ncPriceFormat($rule['discount']);?></em>
                                                            <?php } ?>
                                                            <?php if(!empty($rule['goods_id'])) { ?>
                                                                ， <?php echo L('nc_gift');?> <a href="<?php echo $rule['goods_url'];?>" title="<?php echo $rule['mansong_goods_name'];?>" target="_blank" class="gift"> <img src="<?php echo cthumb($rule['goods_image'], 60);?>" alt="<?php echo $rule['mansong_goods_name'];?>"> </a>&nbsp;。
                                                            <?php } ?>
                      </span> </li>
                                                <?php } ?>
                                            </ul>
                                            <div class="mjs-remark"><?php echo $goods_detail['mansong_info']['remark'];?></div>
                                        </div>
                                        <div class="bottom"><a href="<?php echo urlShop('show_store', 'mansong_goods', array('store_id' => $goods_info['store_id']));?>" class="url" target="_blank">查看更多店铺“满即送”活动商品</a></div>
                                    </div>
                                </div>
                            <?php } ?>
                            <!--E 满就送 -->
                            <!-- S 加价购 -->
                            <?php if ($goods_info['jjg_explain']) { ?>
                                <div class="ncs-jjg"> <span class="sale-name">加价购</span> <span class="sale-rule"><?php echo $goods_info['jjg_explain']; ?></span> <span nctype="show-rule" class="sale-rule-more"><a href="javascript:void(0);">共<strong><?php echo count($goods_info['jjg_info']['levels']);?></strong>项，展开查看<i></i></a></span>
                                    <div class="sale-rule-content" style="display: none;" nctype="rule-content">
                                        <div class="title"><span class="sale-name">加价购</span>共<strong><?php echo count($goods_info['jjg_info']['levels']);?></strong>项，促销活动规则<a href="javascript:;" nctype="hide-rule">关闭</a></div>
                                        <div class="cou-rule-list">
                                            <div class="couRuleScrollbar">
                                                <?php $shownLevelSkus = array();
                                                foreach ((array) $goods_info['jjg_info']['levels'] as $levelId => $v) { ?>
                                                    <div class="cou-rule">
                                                        <h4>规则<?php echo $levelId; ?>：消费满<strong><?php echo L('currency').$v['mincost']; ?></strong>
                                                            <?php if ($v['maxcou'] > 0) { ?>
                                                                可换购最多<strong><?php echo $v['maxcou']; ?></strong>种优惠商品
                                                            <?php } else { ?>
                                                                可换购任意多种优惠商品
                                                            <?php } ?>
                                                        </h4>
                                                        <ul>
                                                            <?php foreach ((array) $goods_info['jjg_info']['levelSkus'][$levelId] as $sku => $vv) {
                                                                $g = $goods_info['jjg_info']['items'][$sku];
                                                                if (empty($g) || isset($shownLevelSkus[$sku])) {
                                                                    continue;
                                                                }
                                                                $shownLevelSkus[$sku] = true; ?>
                                                                <li title="<?php echo $g['name']; ?>"><a mxf="sqde" target="_blank" href="<?php echo urlShop('goods', 'index', array('goods_id' => $sku)); ?>"> <img alt="" src="<?php echo cthumb($g['goods_image'], 60); ?>"/>
                                                                        <h5><?php echo $g['name']; ?></h5>
                                                                        <h6 title="换购价">￥<?php echo $vv['price']; ?></h6>
                                                                    </a> </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="bottom"><a href="<?php echo urlShop('show_store', 'cou_goods', array('store_id' => $goods_info['store_id'], 'cou_id' => $goods_info['jjg_info']['info']['id']));?>" class="url" target="_blank">查看更多店铺“加价购”活动商品</a></div>
                                    </div>
                                </div>
                            <?php } ?>
                            <!-- E 加价购 -->
                        </dd>
                    </dl>
                <?php }?>
                <!-- S 赠品 -->
                <?php if ($goods_info['have_gift'] == 'gift') {?>
                    <hr/>
                    <dl>
                        <dt>赠&#12288;&#12288;品：</dt>
                        <dd class="goods-gift" id="ncsGoodsGift"> <span>数量有限，赠完为止。</span>
                            <?php if (!empty($goods_detail['gift_array'])) {?>
                                <ul>
                                    <?php foreach ($goods_detail['gift_array'] as $val){?>
                                        <li>
                                            <div class="goods-gift-thumb"><span><img src="<?php echo cthumb($val['gift_goodsimage'], '60', $goods_info['store_id']);?>"></span></div>
                                            <a href="<?php echo urlShop('goods', 'index', array('goods_id' => $val['gift_goodsid']));?>" class="goods-gift-name" target="_blank"><?php echo $val['gift_goodsname']?></a><em>x<?php echo $val['gift_amount'];?></em> </li>
                                    <?php }?>
                                </ul>
                            <?php }?>
                        </dd>
                    </dl>
                <?php }?>
                <!-- E 赠品 -->

            </div>
        <?php }?>
    <?php
        $goods_info['promotion_html'] = ob_get_contents();
        ob_clean();
        return $this->success($goods_info);
    }

    public function testOp()
    {
        v(MD5_KEY,0);
        v(md5('c791084ae2a7c2469cd9e7e2a26a1bbc'),0);
    }
}
