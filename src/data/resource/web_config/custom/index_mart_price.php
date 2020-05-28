<!-- 图片组部分start -->
<style>
    .nongmaotehui{ width: 960px; overflow: hidden; margin: auto;}
    .nongmaotehui .n-title{ border-bottom: solid 1px #eee; line-height: 50px;}
    .nongmaotehui .n-title h3{ font-size: 20px; color: #fc9258; display: inline-block;}
    .nongmaotehui .n-title h4{ font-size: 14px; color: #999; display: inline-block; margin-left: 15px;}
    .nongmaotehui ul{ overflow: hidden; padding-top: 15px;}
    .nongmaotehui ul li{ float: left; width: 160px; margin-left: 28px;}
    .nongmaotehui ul li .goods-img{ width: 160px; height: 160px; overflow: hidden; position: relative;}
    .nongmaotehui ul li .goods-img a{ display: table-cell; width: 160px; height: 160px; vertical-align: middle; text-align: center;}
    .nongmaotehui ul li .goods-img img{ max-width: 160px; max-height: 160px; vertical-align: middle;}
    .nongmaotehui ul li .goods-img .qrcode{  background: url('/shop/img/mart-bg.png') repeat; display: none; position: absolute; left: 0; top: 0; width:160px; height: 160px;}
    .nongmaotehui ul li .goods-img .qrcode .ewm{ width: 80px; height: 80px; margin: 20px auto 0 auto; background: #fff;padding: 10px;}
    .nongmaotehui ul li .goods-img .qrcode .ewm img{ width: 80px !important; height:80px !important;}
    .nongmaotehui ul li .goods-img .qrcode p{ text-align: center; line-height: 35px; color: #fff; font-size: 14px;}
    .nongmaotehui ul li .goods-info .goods-name{ font-size: 14px; color: #333; line-height: 20px; padding-top: 10px; height: 40px; overflow: hidden;}
    .nongmaotehui ul li .goods-info .nm-price{ line-height: 30px; overflow: hidden; width: 100%; padding-top: 3px; border-top: solid 1px #dcdcdc; margin-top: 10px;}
    .nongmaotehui ul li .goods-info .nm-price .current-price{ font-size: 18px; color: #FE6601; float: left !important;}
    .nongmaotehui ul li .goods-info .nm-price .original-price{ text-decoration: line-through; font-size: 14px; color: #999; margin-left: 10px;}


</style>

<div class="nongmaotehui fr">
    <ul>
        <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
            <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
                <?php if (is_array($val) && !empty($val)) { ?>
                    <li>
                        <div class="goods-img">
                            <a href="javascript:void(0)" title="<?php echo $val['pic_name'];?>">
                                <!--<img alt="<?php /*echo $val['pic_name'];*/?>" shopwwi-url="<?php /*echo UPLOAD_SITE_URL.'/'.$val['pic_img'];*/?>"  rel='lazy' src="<?php /*echo SHOP_SITE_URL;*/?>/img/loading.gif" width="349" height="488" />-->
                                <img alt="<?php echo $val['pic_name'];?>"     src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>"   />
                            </a>
                            <div class="qrcode">
                                <div class="ewm mart_code" data-src="<?php echo $val['pic_url'];?>"></div>
                                <p>扫描微信二维码购买</p>
                            </div>
                        </div>
                        <div class="goods-info">
                            <p class="goods-name" title="<?php echo $val['pic_name'];?>"><?php echo $val['pic_name'];?></p>
                            <div class="nm-price clearfix">
                                <span class="current-price fl">&yen;<?php echo $val['pic_sname'];?></span>
                                <span class="original-price fl">&yen;<?php echo $val['pic_simg'];?></span></div>
                        </div>
                    </li>
                <?php }?>
            <?php }?>
        <?php }?>
    </ul>
</div>
<!-- 图片组部分end -->

<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.qrcode.min.js"></script>
<script>
    $(function(){

        $(".mart_code").each(function () {
            var goods_url = $(this).attr('data-src');
            $(this).qrcode({
                width: 80,
                height: 80,
                text: goods_url
            });
        });

        $(".nongmaotehui ul li").hover(function(){
            $(this).find(".qrcode").show();
        },function(){
            $(this).find(".qrcode").hide();
        })
    })
</script>