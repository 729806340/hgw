<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/home_goods.css" rel="stylesheet" type="text/css">

<style>
    body{ background: #fff;}
    .ncs-detail{ overflow: hidden; border: none;}
    .ncs-detail .ncs-goods-picture{}
    .ncs-goods-picture .gallery{ top: 0; left: 0;}
    .ncs-goods-summary {width: 650px;}
    .ncs-goods-summary .name{ padding: 15px 0;}
    .ncs-goods-summary .name h1 {font-weight: normal;font-size: 18px;}
    .ncs-meta{ background: #fff5ec; border-top: solid 1px #ff7300;}
    .ncs-meta .goods-model{ overflow: hidden;}
    .ncs-meta .goods-model .column{ float: left; margin: 0 30px 0 20px;}
    .ncs-meta .goods-model .column .price-num{ color: #ff7300; font-size: 16px; font-weight: bold;}
    .ncs-meta .goods-model .column .price-num span{ font-size: 28px; font-weight: normal; margin-left: 2px;}
    .ncs-meta .goods-model .column .p1{ margin-bottom: 30px; height: 40px; line-height: 40px; overflow: hidden;}
    .ncs-meta .goods-model .column .p2{ line-height: 30px; color: #000;}
    .specifications{ margin-top: 20px; position: relative;}
    .specifications ul li{ overflow: hidden; border-top: solid 1px #f0f0f0; padding: 20px 0; line-height: 26px; /*display: none; background: url(/shop/templates/default/images/drop-down.jpg) no-repeat bottom;*/ }
    .specifications ul li div{ float: left; text-align: center;}
    .specifications ul li .s-til{ width: 80px;}
    .specifications ul li .s-size{ width: 100px; font-weight: bold;}
    .specifications ul li .s-price{ width: 120px;}
    .specifications ul li .s-repertory{ width: 150px;}
    .specifications ul li .s-num{ overflow: hidden; display: table-cell; vertical-align: middle; }
    .specifications ul li .s-num span{ font-size: 16px; font-weight: bold; border: solid 1px #e5e5e5; height: 26px; width: 26px; text-align: center; line-height: 26px; cursor: pointer;}
    .specifications ul li .s-num .minus{}
    .specifications ul li .s-num input{ float: left; vertical-align: middle; height: 26px; width: 60px; padding: 0 4px; border-right: none; border-left: none; text-align: center}
    .specifications ul li .s-num .plus{}
    .specifications .drop-down{ position: absolute; bottom: -12px; left: 25px; width: 81px; height: 14px; background:url(/b2b/templates/default/images/drop-down.jpg) no-repeat; cursor: pointer;}
    .specifications .draw-back{ position: absolute; bottom: 0; left: 25px; width: 81px; height: 14px; background:url(/b2b/templates/default/images/draw-back.jpg) no-repeat; cursor: pointer;}
    .order-goods-btn{ margin:30px 0; text-align: center}
    .order-goods-btn button{ width: 160px; height: 40px; text-align: center; font-size: 16px; cursor: pointer;}
    .order-goods-btn .buy-btn{ background: #ff7300; color: #fff; border:solid 1px #ff7300;}
    .order-goods-btn .cart-btn{ border: solid 1px #f2dcbc; background: #fff5ec; margin-left: 10px; color: #ff7300}
    .other-goods{ background: #f5f5f5; padding: 13px;}
    .other-goods h4{ text-align: center; line-height: 30px;}
    .other-goods ul{ width: 110px;}
    .other-goods ul li{ width: 110px; overflow: hidden;}
    .other-goods ul li .a-pic{ width: 110px; height: 110px; display: table-cell; vertical-align: middle; background: #fff; text-align: center; overflow: hidden;}
    .other-goods ul li .a-pic img{ vertical-align: middle; width: 110px; max-width: 110px;}
    .other-goods ul li p{ text-align: center; line-height: 30px;}
    .expanded .ncs-goods-main{border-top:none;}
    /*.expanded .ncs-goods-main{ border-top: solid 1px #ff7300;}*/
    .ncs-goods-title-nav ul li.current a{ /*border: none;*/ color: #ff7300;}
   /*.ncs-goods-title-nav ul li a{ border: none;}*/
</style>

<script type="text/javascript" src="<?php echo B2B_RESOURCE_SITE_URL;?>/js/mz-packed.js" charset="utf-8"></script>
<div class="wrapper pr">
    <div class="ncs-detail">
        <div id="ncs-goods-picture" class="ncs-goods-picture fl">
            <div class="gallery_wrap">
                <div class="gallery"><img title="鼠标滚轮向上或向下滚动，能放大或缩小图片哦~" src="<?php echo $output['goodsinfo']["goods_image"][0]['image']; ?>" class="cloudzoom" data-cloudzoom="zoomImage: '<?php echo $output['goodsinfo']["goods_image"][0]['image']; ?>'"> </div>
            </div>
            <div class="controller_wrap">
                <div class="controller">
                    <ul>
                        <?php foreach ($output["goodsinfo"]["goods_image"] as $key => $value) { ?>
                        <li><img title="鼠标滚轮向上或向下滚动，能放大或缩小图片哦~" class='cloudzoom-gallery' src="<?php echo $value['image']; ?>" data-cloudzoom="useZoom: '.cloudzoom', image: '<?php echo $value['image']; ?>', zoomImage: '<?php echo $value['image']; ?>' " width="60" height="60"></li>
                        <?php  }?>
                    </ul>
                </div>
            </div>
        </div>
        <!-- //焦点图 -->
        <!-- S 商品基本信息 -->
        <div id="goods-summary" class="ncs-goods-summary fl">
            <div class="name">
                <h1>
                    <?php echo $output['goodsinfo']["goods_name"] ?>
                </h1>
            </div>
            <div class="ncs-meta">
                <!-- S 商品参考价格 -->
                <div class="goods-model">
                    <div class="column">
                        <p class="p1">价格</p>
                        <!--p class="p2">起批量</p-->
                    </div>
                    <div class="column">
                        <p class="price-num p1">&yen;<span><?php echo   $output['goodsinfo']['min_price']==$output['goodsinfo']['max_price'] ? $output['goodsinfo']['max_price']:$output['goodsinfo']['min_price']."~".$output['goodsinfo']['max_price'];?></span></p>
                        <!--p class="p2">1-3件</p-->
                    </div>
                    <!--div class="column">
                        <p class="price-num p1">&yen;<span>24.90</span></p>
                        <p class="p2">4-20件</p>
                    </div>
                    <div class="column">
                        <p class="price-num p1">&yen;<span>22.90</span></p>
                        <p class="p2">&ge;21件</p>
                    </div-->
                </div>
                <!-- E 商品发布价格 -->
            </div>
            <!-- S 包装规格 -->
            <div class="specifications">
                <ul>
                    <?php  foreach($output['goodsinfo']['goods_common'] as $goods_common) {?>
                    <li>
                        <div class="s-til">包装规格</div>
                        <div class="s-size"><?php echo  $goods_common['goods_calculate']; ?></div>
                        <div class="s-price"><?php echo  $goods_common['goods_price']; ?>元</div>
                        <div class="s-repertory"><?php echo  $goods_common['goods_storage']; ?><?php echo  $goods_common['goods_calculate']; ?>可售</div>
                        <div class="s-num" data-goods_id="<?php echo  $goods_common['goods_id']; ?>"  data-num="0">
                            <span class="minus fl">-</span>
                            <input class="num" value="0" type="text"  data-stock="<?php echo  $goods_common['goods_storage']; ?>">
                            <span class="plus fl">+</span>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
                <div class="drop-down"></div>
            </div>
            <div class="order-goods-btn">
                <button class="buy-btn">立即订购</button>
                <button class="cart-btn">加入进货单</button>
            </div>
            <!-- E 包装规格 -->
        </div>
        <?php if(count($output['clickdata'])>0){?>
        <div class="other-goods fr">
            <h4>买家还在看</h4>
            <ul>
                <?php foreach($output['clickdata'] as $item){?>
                <li><a class="a-pic" href="<?php echo Urlb2b('goods','index',array("goods_commonid"=>$item['goods_commonid']))?>"><img  src="<?php echo $item['goods_image']; ?>" /></a><p><a href="#">&yen;<?php echo $item['min_price']; ?></a></p></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <div class="clear"></div>
    </div>
    <!-- S 优惠套装 -->
    <div class="ncs-promotion" id="nc-bundling" style="display:none;"></div>
    <!-- E 优惠套装 -->
    <div id="content" class="ncs-goods-layout expanded">
        <div class="ncs-goods-main" id="main-nav-holder">
            <div class="tabbar pngFix" id="main-nav">
                <div class="ncs-goods-title-nav">
                    <ul id="categorymenu">
                        <li class="current">
                            <a id="tabGoodsIntro" href="#content">详细信息</a>
                        </li>
                        <li>
<!--                            <a id="tabGoodsRate" href="#content">商品评价<em>(2)</em></a>-->
                        </li>
                        <li>
<!--                            <a id="tabGoodsTraded" href="#content">销售记录<em>(1)</em></a>-->
                        </li>
                        <li>
<!--                            <a id="tabGuestbook" href="#content">购买咨询</a>-->
                        </li>
                    </ul>
                    <div class="switch-bar"><a href="javascript:void(0)" id="fold">&nbsp;</a></div>
                </div>
            </div>
            <br/><br/>
            <div class="ncs-intro">
                <div class="content bd" id="ncGoodsIntro">
                    <div class="ncs-goods-info-content">
                        <?php if (isset($output['plate_top'])) {?>
                        <div class="top-template">
                            <?php echo $output['plate_top']['plate_content']?>
                        </div>
                        <?php }?>
                        <div class="default">
                            <?php echo $output['goodsinfo']['goods_body']; ?>
                        </div>
                        <?php if (isset($output['plate_bottom'])) {?>
                        <div class="bottom-template">
                            <?php echo $output['plate_bottom']['plate_content']?>
                        </div>
                        <?php }?>
                    </div>
                </div>
            </div>
            <?php if ($output['goods']['is_virtual'] == 1) {?>
            <!-- S 店铺地址地图 -->
            <div class="ncs-shop-map">
                <div class="ncs-goods-title-bar hd">
                    <h4><a href="javascript:void(0);">商家位置</a></h4>
                </div>
                <div class="bd" id="ncStoreMap" style="border: solid #E6E6E6; border-width: 0 1px 1px; margin-bottom: 20px; display:none; overflow: hidden;"> </div>
            </div>
            <!-- E 店铺地址地图 -->
            <?php }?>



        </div>
        <div class="ncs-sidebar">
            <?php if ($output['viewed_goods']) { ?>
            <!-- 最近浏览 -->
            <div class="ncs-sidebar-container ncs-top-bar">
                <div class="title">
                    <h4>最近浏览</h4>
                </div>
                <div class="content">
                    <div id="hot_sales_list" class="ncs-top-panel">
                        <ol>
                            <?php foreach ((array) $output['viewed_goods'] as $g) { ?>
                            <li>
                                <dl>
                                    <dt><a href="<?php echo urlb2b('goods', 'index', array('goods_commonid' => $g['goods_commonid'])); ?>"><?php echo $g['goods_name']; ?></a></dt>
                                    <dd class="goods-pic"><a href="<?php echo urlb2b('goods', 'index', array('goods_commonid' => $g['goods_commonid'])); ?>"><span class="thumb size60"><i></i><img src="<?php echo $g['goods_image'];//echo thumb($g['goods_image'], 60); ?>"  onload="javascript:DrawImage(this,60,60);"></span></a>
                                        <p><span class="thumb size100"><i></i><img src="<?php echo $g['goods_image'];//thumb($g['goods_image'], 240); ?>" onload="javascript:DrawImage(this,100,100);" title="<?php echo $g['goods_name']; ?>"><big></big><small></small></span></p>
                                    </dd>
                                    <dd class="price pngFix">
                                        <?php echo ncPriceFormat($g['min_price']); ?>
                                    </dd>
                                </dl>
                            </li>
                            <?php } ?>
                        </ol>
                    </div>
                    <p><a href="<?php echo B2B_SITE_URL;?>" class="nch-sidebar-all-viewed">全部浏览历史</a></p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<form id="buynow_form" method="post" action="<?php echo SHOP_SITE_URL;?>/index.php">
    <input id="act" name="act" type="hidden" value="buy" />
    <input id="op" name="op" type="hidden" value="buy_step1" />
    <input id="cart_id" name="cart_id[]" type="hidden" />
</form>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.charCount.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js" type="text/javascript"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/sns.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.F_slider.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.raty/jquery.raty.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />


<script type="text/javascript">
    jQuery(function($) {
        // 放大镜效果 产品图片
        CloudZoom.quickStart();
        // 图片切换效果
        $(".controller li").first().addClass('current');
        $('.controller').find('li').mouseover(function() {
            $(this).first().addClass("current").siblings().removeClass("current");
        });
    });


    $("input.num").keyup(function(){
        var num=parseInt($(this).val());
        var stocknum=parseInt($(this).attr("data-stock"));
        if(num>stocknum){
            $(this).val(stocknum);
        }
    })



    $("button.cart-btn").click(function(){
        var data="";
        $("div.s-num").each(function(){
            if(parseInt($(this).parent().find("input.num").val())>0){
              data+=$(this).attr("data-goods_id")+"-"+$(this).parent().find("input.num").val()+",";
            }
        })
        if(data==""){
           alert("请选择你要购买的商品规格");
            return false;
        }
        data=data.substring(0,data.length-1);
        $.ajax({
            type: "POST",
            url: "index.php?act=cart&op=add",
            data: {"data":data},
            dataType: "json",
            success: function (result) {
                   if(result.status!="1"){
                       alert(result.msg);
                       return false;
                   }
            alert(result.msg);
            $('#top-cart-count').html(result.cartnum).show();
            },
            error: function (result) {
                alert("提交数据失败！");
            }
        });

    })
    $("button.buy-btn").click(function(){
        var data="";
        $("div.s-num").each(function(){
            if(parseInt($(this).parent().find("input.num").val())>0){
              data+=$(this).attr("data-goods_id")+"-"+$(this).parent().find("input.num").val()+",";
            }
        })
        if(data==""){
           alert("请选择你要购买的商品规格");
            return false;
        }
        data=data.substring(0,data.length-1);
        $.ajax({
            type: "POST",
            url: "index.php?act=cart&op=add",
            data: {"data":data},
            dataType: "json",
            success: function (result) {
                   if(result.status!="1"){
                       alert(result.msg);
                       return false;
                   }
                   window.location.href = B2B_SITE_URL+'/?act=cart&op=index'
            },
            error: function (result) {
                alert("提交数据失败！");
            }
        });

    })


    //按钮新增
    $("span.plus").click(function(){
        var  num=$(this).parent().find("input").val();
        var  stock=$(this).parent().find("input").attr("data-stock");
        var  num=parseInt(num)+1;
        if(num>stock){
            return false;
        }
        //$(this).parent().attr("data-num",num);
        $(this).parent().find("input").val(num);
    })

    //按钮减少
    $("span.minus").click(function(){
        var  num=$(this).parent().find("input").val();
        var  num=parseInt(num)-1;
        if(num<0){
            return false;
        }
        //$(this).parent().attr("data-num",num);
        $(this).parent().find("input").val(num);
    })



    //评价列表
    $('#comment_tab').on('click', 'li', function() {
        $('#comment_tab li').removeClass('current');
        $(this).addClass('current');
    });

    //浮动导航  waypoints.js
    $('#main-nav').waypoint(function(event, direction) {
        $(this).parent().parent().parent().toggleClass('sticky', direction === "down");
        event.stopPropagation();
    });

    // 商品详情默认情况下显示全部
    $('#tabGoodsIntro').click(function(){
        $('.bd').css('display','');
        $('.hd').css('display','');
    });

    // 点击评价隐藏其他以及其标题栏
    $('#tabGoodsRate').click(function(){
        $('.bd').css('display','none');
        $('#ncGoodsRate').css('display','');
        $('.hd').css('display','none');
    });

    // 点击成交隐藏其他以及其标题
    $('#tabGoodsTraded').click(function(){
        $('.bd').css('display','none');
        $('#ncGoodsTraded').css('display','');
        $('.hd').css('display','none');
    });

    // 点击咨询隐藏其他以及其标题
    $('#tabGuestbook').click(function(){
        $('.bd').css('display','none');
        $('#ncGuestbook').css('display','');
        $('.hd').css('display','none');
    });

    $(function(){
        $('#fold').click(function(){
            $('.ncs-goods-layout').toggleClass('expanded');
        });
        // 商品内容介绍Tab样式切换控制
        $('#categorymenu').find("li").click(function(){
            $('#categorymenu').find("li").removeClass('current');
            $(this).addClass('current');
        });
    })

    // 验证购买数量
    function checkQuantity() {
        var quantity = parseInt($("#quantity").val());
        if (quantity < 1) {
            alert("<?php echo $lang['goods_index_pleaseaddnum'];?>");
            $("#quantity").val('1');
            return false;
        }
        max = parseInt($('[nctype="goods_stock"]').text());
        <?php if ($output['goods']['is_virtual'] == 1 && $output['goods']['virtual_limit'] > 0) {?>
        max = <?php echo $output['goods']['virtual_limit'];?>;
        if (quantity > max) {
            alert('最多限购' + max + '件');
            return false;
        }
        <?php } ?>
        <?php if (!empty($output['goods']['upper_limit'])) {?>
        max = <?php echo $output['goods']['upper_limit'];?>;
        if (quantity > max) {
            alert('最多限购' + max + '件');
            return false;
        }
        <?php } ?>
        if (quantity > max) {
            alert("<?php echo $lang['goods_index_add_too_much'];?>");
            return false;
        }
        return quantity;
    }
    // 立即购买js
    function buynow(goods_id, quantity, chain_id, area_id, area_name, area_id_2) {
        if (!Hangowa.isLogged()) {
            login_dialog();
        } else {
            if (!quantity) {
                return;
            }
            var hango = Hangowa.getInstance();
            var userInfo = hango.userInfo;
            var storeId = userInfo.store_id || 0;
            if (storeId == '<?php echo $output['
                goods ']['
                store_id '];?>') {
                alert('不能购买自己店铺的商品');
                return;
            }
            $("#cart_id").val(goods_id + '|' + quantity);
            if (typeof chain_id == 'number') {
                $('#buynow_form').append('<input type="hidden" name="ifchain" value="1"><input type="hidden" name="chain_id" value="' + chain_id + '"><input type="hidden" name="area_id" value="' + area_id + '"><input type="hidden" name="area_name" value="' + area_name + '"><input type="hidden" name="area_id_2" value="' + area_id_2 + '">');
            }
            $("#buynow_form").submit();
        }
    }

</script>

<script>
    /*包装规格下拉显示更多*/
    $(function() {
        $('.specifications ul li:nth-child(3)').css({
            "border-bottom": "solid 1px #f0f0f0"
        });
        var s_item = $('.specifications ul li');
        s_item.hide();
        s_item.slice(0, 3).show();

        $('.drop-down').toggle(function() {
            s_item.show();
            $('.specifications ul li:nth-child(3)').css({
                "border-bottom": "none"
            });
            $('.specifications ul li').last().css({
                "border-bottom": "solid 1px #f0f0f0"
            });
            $(this).addClass('draw-back');
        }, function() {
            s_item.hide();
            s_item.slice(0, 3).show();
            $('.specifications ul li:nth-child(3)').css({
                "border-bottom": "solid 1px #f0f0f0"
            });
            $(this).removeClass('draw-back');
        })

    })
</script>