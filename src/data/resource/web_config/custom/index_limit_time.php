<style>
    .xianshihuodong{
        width: 100%;
        background: #ffffff;
        margin-top: 20px;
    }
    .xianshihuodong_title{
        height: 52px;
        line-height: 52px;
    }
    .xianshihuodong_title img{
        float: left;
        width: 20px;
        height: 20px;
        margin: 16px 14px;
    }
    .xianshihuodong_title strong{
        font-size: 18px;
        float: left;
        margin-right: 8px;
        color: #FF2A53;
        font-family: "微软雅黑";
    }
    .xianshihuodong_title h4{
        font-size: 15px;
        float: left;
        color: #999999;
        font-family: "微软雅黑";
        margin-top: 2px;;
    }
    .xianshihuodong_cont{
        width: 100%;
        height: 346px;
        overflow: hidden;
    }
    .xianshihuodong_cont_left{
        float: left;
        width: 456px;
        height: 346px;
        padding: 10px 14px;
        box-sizing: border-box;
        border: 1px solid #eeeeee;
    }
    .xianshihuodong_cont_left:hover{
        border-color: #FE6601;
    }
    .xianshihuodong_cont_left img{
        width: 100%;
        height: 100%;
        background: sandybrown;
    }
    .xianshihuodong_cont_right{
        float: right;
        width: 744px;
        height: 346px;
    }
    .xianshihuodong_cont_right1{
        width: 100%;
        height: 173px;
        overflow: hidden;
    }
    .xianshihuodong_cont_right1 ul li{
        float: left;
        width: 50%;
        height: 173px;
        padding: 10px 14px;
        box-sizing: border-box;
        border: 1px solid #EEEEEE;
    }
    .xianshihuodong_cont_right1 ul li img{
        display: block;
        width: 100%;
        height: 100%;
    }
    .xianshihuodong_cont_right2{
        width: 100%;
        height: 173px;
        overflow: hidden;
    }
    .xianshihuodong_cont_right2 ul li{
        float: left;
        width: 248px;
        height: 173px;
        padding: 10px 14px;
        box-sizing: border-box;
        border: 1px solid #EEEEEE;
    }
    .xianshihuodong_cont_right2 ul li img{
        display: block;
        width: 100%;
        height: 100%;
    }
    .xianshihuodong_cont_right li:hover{
        border-color: #FE6601;
    }
</style>
<?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
<div class="xianshihuodong">
    <div class="xianshihuodong_title">
        <img src="/shop/templates/default/images/xshd_icon.png"/>
        <strong>限时活动</strong>
        <h4>总有你意想不到的低价</h4>
    </div>
    <div class="xianshihuodong_cont">
        <?php
            $code_adv = $output['code_adv']['code_info'];
            $first_data = (is_array($code_adv[1]) && !empty($code_adv[1])) ? $code_adv[1] : array();
            $two_data = (is_array($code_adv[2]) && !empty($code_adv[2])) ? $code_adv[2] : array();
            $three_data = (is_array($code_adv[3]) && !empty($code_adv[3])) ? $code_adv[3] : array();
            $four_data = (is_array($code_adv[4]) && !empty($code_adv[4])) ? $code_adv[4] : array();
            $five_data = (is_array($code_adv[5]) && !empty($code_adv[5])) ? $code_adv[5] : array();
            $six_data = (is_array($code_adv[6]) && !empty($code_adv[6])) ? $code_adv[6] : array();
        ?>
        <div class="xianshihuodong_cont_left">
            <a target="_blank" href="<?php echo !empty($first_data['pic_url']) ? $first_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$first_data['pic_img'];?>" /></a>
        </div>
        <div class="xianshihuodong_cont_right">
            <div class="xianshihuodong_cont_right1">
                <ul>
                    <li>
                        <a target="_blank" href="<?php echo !empty($two_data['pic_url']) ? $two_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$two_data['pic_img'];?>" /></a>
                    </li>
                    <li>
                        <a target="_blank" href="<?php echo !empty($three_data['pic_url']) ? $three_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$three_data['pic_img'];?>" /></a>
                    </li>
                </ul>
            </div>
            <div class="xianshihuodong_cont_right2">
                <ul>
                    <li>
                        <a target="_blank" href="<?php echo !empty($four_data['pic_url']) ? $four_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$four_data['pic_img'];?>" /></a>
                    </li>
                    <li>
                        <a target="_blank" href="<?php echo !empty($five_data['pic_url']) ? $five_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$five_data['pic_img'];?>" /></a>
                    </li>
                    <li>
                        <a target="_blank" href="<?php echo !empty($six_data['pic_url']) ? $six_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$six_data['pic_img'];?>" /></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php }?>