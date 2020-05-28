<!-- 图片组部分start -->
<style>
    .nongmaotehui{ width: 1152px; overflow: hidden; margin: auto; padding-top: 10px;}
    .nongmaotehui .n-title{ border-bottom: solid 1px #eee; line-height: 50px;}
    .nongmaotehui .n-title h3{ font-size: 20px; color: #fc9258; display: inline-block;}
    .nongmaotehui .n-title h4{ font-size: 14px; color: #999; display: inline-block; margin-left: 15px;}
    .nongmaotehui ul{ width: 1192px; overflow: hidden; margin-top: 20px; }
    .nongmaotehui ul li{ float: left; width: 258px; margin: 0 40px 30px 0;}
    .nongmaotehui ul li .goods-img{ width: 258px; height: 258px; overflow: hidden; position: relative;}
    .nongmaotehui ul li .goods-img a{ display: table-cell; width: 258px; height: 258px; vertical-align: middle; text-align: center;}
    .nongmaotehui ul li .goods-img img{ max-width: 258px; max-height: 258px; vertical-align: middle;}
    .nongmaotehui ul li .goods-img .qrcode{  background: url('/shop/img/mart-bg.png') repeat; display: none; position: absolute; left: 0; top: 0; width:258px; height: 258px;}
    .nongmaotehui ul li .goods-img .qrcode .ewm{ width: 133px; height: 133px; margin: 50px auto 0 auto; background: #fff;padding: 10px;}
    .nongmaotehui ul li .goods-img .qrcode .ewm img{ width: 133px; height:133px;}
    .nongmaotehui ul li .goods-img .qrcode p{ text-align: center; line-height: 35px; color: #fff; font-size: 14px;}
    .nongmaotehui ul li .goods-info .goods-name{ font-size: 16px; color: #CA2D33; line-height: 40px;}
    .nongmaotehui ul li .goods-info .intro{ line-height: 30px; overflow: hidden; width: 100%;}
    .nongmaotehui ul li .goods-info .intro .fl{ font-size: 12px; color: #A1A1A1; float: left !important;}
    .nongmaotehui ul li .goods-info .intro .fr{font-size: 20px; color: #CA2D33; float: right !important;}


</style>

<div class="nongmaotehui">
    <div class="n-title"><h3>农猫特惠</h3><h4>精选优质好货</h4></div>
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
                            <p class="goods-name"><?php echo $val['pic_name'];?></p>
                            <div class="intro clearfix"><span class="fl" style="float: left !important;"><?php echo $val['pic_sname'];?></span><span class="fr" style="float: right !important;">&yen;<?php echo $val['pic_simg'];?></span></div>
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
                width: 133,
                height: 133,
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