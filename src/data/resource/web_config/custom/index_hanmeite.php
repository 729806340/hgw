<style>
    /**汉美特****/
    .hanmeite{
        margin-top: 20px;
        width: 100%;
        background: #ffffff;
    }
    .hanmeite_title{
        height: 52px;
        line-height: 52px;
    }
    .hanmeite_title img{
        float: left;
        margin: 17px 14px;
    }
    .hanmeite_title h4{
        font-size: 15px;
        float: left;
        color: #999999;
        font-family: "微软雅黑";
        margin-top: 2px;
    }
    .hanmeite_cont{
        width: 100%;
        height: 302px;
        overflow: hidden;
    }
    .hanmeite_cont_left{
        float: left;
        width: 478px;
        height: 302px;
        border: 1px solid #EEEEEE;
        padding: 10px;
        box-sizing: border-box;
        overflow: hidden;
    }
    .hanmeite_cont_left img{
        display: block;
        width: 100%;
        height: 100%;
    }
    .hanmeite_cont_right{
        float: left;
        width: 721px;
        height: 302px;
        box-sizing: border-box;
    }
    #hanmeite_scroll_list{
        width: 100%;
        height: 302px;
        position: relative;
    }
    .hanmeite_prev{
        position: absolute;
        left: 0;
        top: 45%;
    }
    .hanmeite_next{
        position: absolute;
        right: 0;
        top: 45%;
    }

    .hanmeite_cont_right ul{
        width: 200%;
        height: 302px;
    }
    .hanmeite_cont_right ul li{
        float: left;
        width: 240.3px;
        height: 302px;
        padding: 10px 0;
        box-sizing: border-box;
        border: 1px solid #EEEEEE;
    }
    .hanmeite_cont_right ul li img{
        display: block;
        width: 100%;
        height: 100%;
    }
</style>
<?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
<?php
    $code_adv = $output['code_adv']['code_info'];
    $first_data = array_shift($code_adv);
?>
<div class="hanmeite">
    <div class="hanmeite_title">
        <img src="/shop/templates/default/images/hmttb_icon.png"/>
        <h4>有情有味汉美特</h4>
    </div>
    <div class="hanmeite_cont">
        <div class="hanmeite_cont_left">
            <a target="_blank" href="<?php echo !empty($first_data['pic_url']) ? $first_data['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$first_data['pic_img'];?>" /></a>
        </div>
        <div class="hanmeite_cont_right">
            <div id="hanmeite_scroll_list">
                <a href="javascript:;" class="scroll_btn hanmeite_prev">
                    <img src="/shop/templates/default/images/lefticon.png"/>
                </a>
                <a href="javascript:;" class="scroll_btn hanmeite_next">
                    <img src="/shop/templates/default/images/righticon.png"/>
                </a>
                <ul id="scroll_list">
                    <?php foreach ($code_adv as $value) {?>
                        <li>
                            <a target="_blank" href="<?php echo !empty($value['pic_url']) ? $value['pic_url'] : 'javascript:void(0);';?>"><img src="<?php echo UPLOAD_SITE_URL.'/'.$value['pic_img'];?>" /></a>
                        </li>
                    <?php }?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php }?>