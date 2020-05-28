<style>
    .nongguxian{
        margin-top: 20px;
        width: 100%;
        background: #ffffff;
    }
    .nongguxian_title{
        height: 52px;
        line-height: 52px;
    }
    .nongguxian_title img{
        float: left;
        margin: 17px 14px;
    }
    .nongguxian_title h4{
        font-size: 15px;
        float: left;
        color: #999999;
        font-family: "微软雅黑";
        margin-top: 2px;
    }
    .nongguxian_cont{
        width: 100%;
        height: 355px;
        overflow: hidden;
    }
    .nongguxian_cont_left{
        float: left;
        width: 721px;
        height: 355px;
        padding: 0 56px;
        box-sizing: border-box;
        border: 1px solid #EEEEEE;
    }
    .dnSlide-main{
        margin-top: 40px;
    }
    .nongguxian_cont_right{
        float: left;
        width: 478px;
        height: 355px;
        border: 1px solid #EEEEEE;
        padding: 10px;
        box-sizing: border-box;
        overflow: hidden;
    }
    .nongguxian_cont_right img{
        display: block;
        width: 100%;
        height: 100%;
    }
</style>
<?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
    <?php
        $code_adv = $output['code_adv']['code_info'];
        $last_data = array_pop($code_adv);
        $code_adv_num = count($code_adv);
        if ($code_adv_num > 0  && $code_adv_num%2==0) {
            array_pop($code_adv);
        }
    ?>
<div class="nongguxian">
    <div class="nongguxian_title">
        <img src="/shop/templates/default/images/ngxtb_icon.png"/>
        <h4>健康新鲜又美味</h4>
    </div>
    <div class="nongguxian_cont">
        <div class="nongguxian_cont_left">
            <!--<div class='dnSlide-main'>
                <?php /*foreach ($code_adv as $val) {*/?>
                    <a href="<?php /*echo !empty($val['pic_url']) ? $val['pic_url'] : '#';*/?>"><img src="<?php /*echo UPLOAD_SITE_URL.'/'.$val['pic_img'];*/?>" width="100%" /></a>
                <?php /*}*/?>
            </div>-->

            <div class="poster-main B_Demo">
                <div class="poster-btn poster-prev-btn"></div>
                <ul class="poster-list">
                    <?php foreach ($code_adv as $val) {?>
                        <li class="poster-item"><a target="_blank" href="<?php echo !empty($val['pic_url']) ? $val['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" width="100%" ></a></li>
                    <?php }?>
                </ul>
                <div class="poster-btn poster-next-btn"></div>
            </div>
        </div>
        <div class="nongguxian_cont_right">
            <a target="_blank" href="<?php echo !empty($last_data['pic_url']) ? $last_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$last_data['pic_img'];?>" width="100%" /></a>
        </div>
    </div>
</div>
<?php }?>