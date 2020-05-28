<style>
    .hgzy-goods-list{ padding-top: 10px;}
    .hgzy-goods-list li{ float: left; width: 172px; margin-left: 24px;}
    .hgzy-goods-list li .goods-img{ width: 172px; height: 172px; overflow: hidden; position: relative;}
    .hgzy-goods-list li .goods-img a{ display: table-cell; width: 172px; height: 172px; vertical-align: middle; text-align: center;}
    .hgzy-goods-list li .goods-img img{ max-width: 172px; max-height: 172px; vertical-align: middle;}
    .hgzy-goods-list li .goods-info .goods-name{ font-size: 14px; color: #333; line-height: 20px; padding: 5px 0;}
    .hgzy-goods-list li .goods-info .goods-name a{ display: block; height: 40px; overflow: hidden;}
    .hgzy-goods-list li .goods-info .goods-name a .self-support{ display: inline-block; width: 34px; height: 20px; border: solid 1px #FE6601; border-radius: 3px; font-size: 14px; text-align: center; line-height: 20px; color: #FE6601; margin-right: 5px;}
    .hgzy-goods-list li .goods-info .price{ line-height: 30px; overflow: hidden; width: 100%; font-weight: normal;}
    .hgzy-goods-list li .goods-info .price .current-price{ font-size: 18px; color: #19C549; float: left !important;}
    .hgzy-goods-list li .goods-info .price .original-price{ text-decoration: line-through; font-size: 14px; color: #999; margin-left: 10px;}
    /**新品推荐***/
    .newShopp_title{
        height: 52px;
        line-height: 52px;
        border-bottom: 1px solid #EEEEEE;
    }
    .newShopp_title img{
        float: left;
        width: 20px;
        height: 20px;
        margin: 16px 14px;
    }
    .newShopp_title strong{
        font-size: 18px;
        float: left;
        margin-right: 8px;
        color: #28ABFF;
        font-family: "微软雅黑";
    }
    .newShopp_title h4{
        font-size: 15px;
        float: left;
        color: #999999;
        font-family: "微软雅黑";
        margin-top: 2px;;
    }

</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php if (!empty($output['code_sale_list']['code_info']) && is_array($output['code_sale_list']['code_info'])) { ?>
    <div class="hangoziying mt20">
        <div class="newShopp_title">
            <img src="/shop/templates/default/images/xptj_icon.png"/>
            <strong>新品推荐</strong>
            <h4>火爆新品有你想要</h4>
        </div>
        <?php echo $output['self_web_html']['self_index_self_recommend'];?>
        <ul class="hgzy-goods-list clearfix">
            <?php foreach ($output['code_sale_list']['code_info'] as $key => $val) { ?>
                <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
                    <?php foreach($val['goods_list'] as $k => $v){ ?>
                        <li>
                            <div class="goods-img">
                                <a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" class="pic">
                                    <img shopwwi-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" mff="sqde" alt="<?php echo $v['goods_name']; ?>"/>
                                </a>
                            </div>
                            <div class="goods-info">
                                <div class="goods-name">
                                    <a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=>$v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>">
                                        <span class="self-support">New</span><?php echo $v['goods_name']; ?>
                                    </a>
                                </div>
                                <div class="price">
                                    <span class="current-price fl"><?php echo ncPriceFormatForList($v['goods_price']); ?></span>
                                    <span class="original-price fl"><?php echo ncPriceFormatForList($v['market_price']); ?></span>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                <?php } } ?>
        </ul>

    </div>
<?php } ?>