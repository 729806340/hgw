<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/congregate_show.css" rel="stylesheet" type="text/css">


<div>
    <div class="banner"><img src="<?php echo SHOP_CONGREGATE_URL;?>/banner-pic.jpg" /></div>
<!--    <div class="l-outside">-->
<!--        <div class="location w1200"><img src="--><?php //echo SHOP_CONGREGATE_URL;?><!--/icon01.png" /><a href="">汉购首页</a><img src="--><?php //echo SHOP_CONGREGATE_URL;?><!--/icon02.png" /><a href="">项目总览</a><img src="--><?php //echo SHOP_CONGREGATE_URL;?><!--/icon03.png" /><a href="">发起项目</a><img src="--><?php //echo SHOP_CONGREGATE_URL;?><!--/icon04.png" /><a href="">我的众筹</a></div>-->
<!--    </div>-->
    <div class="zc-content w1200">
        <div class="left-details-pic"><img src="<?php echo SHOP_CONGREGATE_URL;?>/details-pic.jpg" /></div>
        <div class="right-side">
            <div class="rs-con">
                <div class="zc-state"><span>预售中</span></div>
                <h1 style="line-height: 1.3">恩施鹤峰 生态骑龙芽茶200g </h1>
                <h2 class="subhead">看得见的有机生态产茶环境<br/>茶农手工采摘<br />独特烘青工艺制茶</h2>

                <div class="target">
                    <p class="t-sum color-333">预售价：<span>299</span>元</p>
                    <p class="normal-price">正常售价<span>420</span>元</p>

                </div>
                <div class="support-btn" nctype="support-btn">立即购买</div>
                <div class="current-outside"><p class="current-progress">当前进度<?php echo $output['progress']?>%</p></div>
                <div class="progress-bar">
                    <div style="width:<?php echo $output['progress']?>%" class="inside-bar"></div>
                </div>

                <div class="zc-num">
                    <div class="xianliang">限量100份</div>
                    <div class="yishou">已售<span><?php echo $output['goods1']['sell_num']?></span>份</div>
                </div>

                <div class="operation">

                    <div class="collect-btn" nctype="collect-btn"><em nctype="like_text">点赞</em>（<em nctype="goods_collect"><?php echo $output['goods_collect']?></em>）</div>
                    <div class="share-btn bshare-custom icon-medium" nctype="share-btn" title="更多平台"><a title="更多平台" class="bshare-more bshare-more-icon more-style-addthis"><em nctype="share_text">分享</em>（<span class="BSHARE_COUNT bshare-share-count">0</span>）</a></div>

                </div>

                <script type="text/javascript" charset="utf-8" src="http://static.bshare.cn/b/button.js#style=-1&amp;uuid=&amp;pophcol=2&amp;lang=zh"></script>
                <a class="bshareDiv" onclick="javascript:return false;"></a>
                <script type="text/javascript" charset="utf-8" src="http://static.bshare.cn/b/bshareC0.js"></script>

                <p class="zc-text color-333">收藏关注项目寻找更多机会</p>
                <p class="zc-text color-333">分享让发起者早日找到梦想合伙人</p>

                <div class="item-hint">
                    <div class="m-title">发起原因</div>
                    <div class="sub-title">为什么发起预售？</div>
                    <div class="hint zc-text color-333">
                        <p>1.放心茶难买：化肥农产等问题让喝茶人心里没底，买到放心茶成为苛求；</p>
                        <p>解决方案：寻找茶叶基地，采用可视化，手机扫码就可观看茶园监控，生态看得见。</p>
                        <p>2.生态茶难卖：山区交通闭塞，好茶不出山；</p>
                        <p>行动措施：用互联网的方式，把有管控的生态茶引向市场；</p>
                        <p>3.增收扶贫：产地是国家级贫困县，茶叶是当地主要经济作物。希望愿景：卖茶增收，助力精准扶贫。</p>
                    </div>
                </div>
                
                <div class="item-hint">
                    <div class="m-title">风险保障</div>
                    <div class="hint zc-text color-333">
                        <p>1.本次预售互联网+农业实体产品项目--茶叶，不存在技术风险，对于产品质量有严格检测保障。</p>
                        <p>2.项目回报实物产品发货可能因为山区原因，较平时城市网购产品要延迟1天左右，发起人承诺该延迟不超过48小时。</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<form id="buynow_form" method="post" action="<?php echo SHOP_SITE_URL;?>/index.php">
    <input id="act" name="act" type="hidden" value="buy" />
    <input id="op" name="op" type="hidden" value="buy_step1" />
    <input id="cart_id" name="cart_id[]" type="hidden"/>
</form>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/sns.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

    var like_text;
    setTimeout(function () {
        like_text = $.cookie('like_text');
        if(like_text != null &&  like_text == 1){
            $('[nctype="like_text"]').each(function(){
                $(this).html('已点赞');
            });
        }
    }, 100);

    //收藏商品js
    function collect_goods(fav_id,jstype,jsobj){
        if($.cookie('like_text') == 1){
            return;
        }

        $('[nctype="like_text"]').each(function(){
            $(this).html('已点赞');
        });
        $('[nctype="'+jsobj+'"]').each(function(){
            $(this).html(parseInt($(this).text())+1);
            setCookie('like_text',1,30);
        });
        $.get('index.php?act=special&op=ajaxlike', function(result){
        });
    }

    $('div[nctype="collect-btn"]').click(function(){
        collect_goods('<?php echo $output['goods']['goods_id']; ?>','count','goods_collect');
    });

    $('div[nctype="collect-store-btn"]').click(function(){
        collect_store('<?php echo $output['store_info']['store_id'];?>','count','store_collect');
    });

    // 立即购买
    $('div[nctype="support-btn"]').click(function(){
        buynow(<?php echo $output['goods1']['goods_id']?>,1);
    });


    // 立即购买js
    function buynow(goods_id,quantity,chain_id,area_id,area_name,area_id_2){
        console.log(goods_id);
        if(!Hangowa.isLogged()){
            login_dialog();
        }else{
            if (!quantity) {
                return;
            }
            var hango = Hangowa.getInstance();
            var userInfo = hango.userInfo;
            var storeId = userInfo.store_id||0;
            $("#cart_id").val(goods_id+'|'+quantity);
            if (typeof chain_id == 'number') {
                $('#buynow_form').append('<input type="hidden" name="ifchain" value="1"><input type="hidden" name="chain_id" value="'+chain_id+'"><input type="hidden" name="area_id" value="'+area_id+'"><input type="hidden" name="area_name" value="'+area_name+'"><input type="hidden" name="area_id_2" value="'+area_id_2+'">');
            }
            $("#buynow_form").submit();
        }

    }



</script>
