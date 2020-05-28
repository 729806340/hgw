<style>
    .hgzy-goods-list{ padding-top: 10px;}
    .hgzy-goods-list li{ float: left; width: 228px; background:#fff;margin-top:32px;margin-left:12px;border:1px solid rgba(204,204,204,1);}
    .hgzy-goods-list li:nth-child(-n+5){margin-top: 0;}
    .hgzy-goods-list li:nth-child(5n+1){margin-left: 0;}
    .hgzy-goods-list li .goods-img{ width: 191px; height: 193px; overflow: hidden; position: relative;margin:auto;}
    .hgzy-goods-list li .goods-img img:hover {
        -webkit-transform: scale(1.10);
        -moz-transform: scale(1.10);
        transform: scale(1.10);
    }
    .hgzy-goods-list li .goods-img a{ display: table-cell; width: 191px; height: 193px; vertical-align: middle; text-align: center;}
    .hgzy-goods-list li .goods-img img{ max-width: 191px; max-height: 193px; vertical-align: middle;-moz-transition: all 0.6s;
     -ms-transition: all 0.6s;
     -o-transition: all 0.6s;
     -webkit-transition: all 0.6s;
     transition: all 0.6s;}
    .hgzy-goods-list li .goods-info .goods-name{ font-size: 15px; color: #333; line-height: 20px; padding: 5px 12px;box-sizing:border-box;}
    .hgzy-goods-list li .goods-info .goods-name a{ display: block; height: 40px; overflow: hidden;}
    .hgzy-goods-list li .goods-info .goods-name a .self-support{ display: inline-block; width: 34px; height: 20px; border: solid 1px #FE6601; border-radius: 3px; font-size: 14px; text-align: center; line-height: 20px; color: #FE6601; margin-right: 5px;}
    .hgzy-goods-list li .goods-info .price{ line-height: 30px; overflow: hidden; width: 100%; font-weight: normal;padding:0 12px;box-sizing:border-box;}
    .hgzy-goods-list li .goods-info .price .current-price{ font-size: 18px; color: #FF0000; float: left !important;float:left;}
    .hgzy-goods-list li .goods-info .price .original-price{ text-decoration: line-through; font-size: 15px; color: #999; float:right;}
    /**新品推荐***/
    .newShopp_title{
        width:100%;
        height: 116px;
        line-height: 116px;
        background:url('../../../../shop/resource/img/ntjx_img.png') no-repeat;
        background-size:100% 100%;
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
    
    .more_shop{
        
    }
    .more_shop a{
        display:block;
        width:148px;
        height:39px;
        line-height:41px;
        background:rgba(255,255,255,1);
        border:1px solid rgba(49,49,49,1);
        border-radius:10px;
        font-weight:600;
        font-size:20px;
        color:#313131;
        margin:auto;
        text-align:center;
        margin-top:20px;
    }

</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php if (!empty($output['code_sale_list']['code_info']) && is_array($output['code_sale_list']['code_info'])) { ?>
<div class="hangoziying mt20" style="margin-top: -5px!important;">
    <div class="newShopp_title">
        <!-- <img src="/shop/templates/default/images/xptj_icon.png"/>
        <strong>新品推荐</strong>
        <h4>火爆新品有你想要</h4> -->
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
                                    <?php echo $v['goods_name']; ?>
                                </a>
                            </div>
                            <div class="price clearfix">
                                <span class="current-price fl"><?php echo ncPriceFormatForList($v['goods_price']); ?></span>
                                <span class="original-price fl"><?php echo ncPriceFormatForList($v['market_price']); ?></span>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            <?php } } ?>
</ul>
<div class="more_shop">
    <a href="javascript:;">更多优惠产品</a>
</div>

</div>
<?php } ?>