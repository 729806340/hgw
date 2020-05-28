


<div class="title clearfix">
    <h3 class="fl">排行榜</h3>
    <div class="explain fl">人气商品等你来挑选</div>
</div>
<div class="rank-con">
    <ul>
        <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
        <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
        <?php if (is_array($val) && !empty($val)) { ?>
        <li class="clearfix">
            <div class="l-small-pic fl"><a href="<?php echo $val['pic_url'];?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>"></a></div>
            <div class="r-rank-info fl clearfix">
                <span class="r-num fl"><?php echo $val['pic_sname'];?></span><a class="fl" href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>"><?php echo $val['pic_name'];?></a>
            </div>
        </li>
        <?php }?>
        <?php }?>
        <?php }?>
<!--
        <li class="clearfix">
            <div class="l-small-pic fl"><a href="#"><img src="shop/templates/default/images/egg_img.png"></a></div>
            <div class="r-rank-info fl clearfix">
                <span class="r-num fl">2</span><a class="fl" href="#">农谷鲜红心鸡蛋</a>
            </div>
        </li>
        <li class="clearfix">
            <div class="l-small-pic fl"><a href="#"><img src="shop/templates/default/images/egg_img.png"></a></div>
            <div class="r-rank-info fl clearfix">
                <span class="r-num fl">3</span><a class="fl" href="#">农谷鲜红心鸡蛋</a>
            </div>
        </li>
-->
    </ul>
</div>