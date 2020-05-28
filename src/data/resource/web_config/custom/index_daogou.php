<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php if (('1' == $output['web_show']) && !empty($output['code_adv']['code_info'][1]['pic_name'])) {  ?>
    <div class="shopping-guide mt30 clearfix">
        <!--<div class="mod-title"><h2>淘吃货</h2><a href="http://guide.hangowa.com/" target="_blank">更多>></a></div>-->
        <ul class="clearfix">
            <?php foreach ($output['code_adv']['code_info'] as $key => $val) {  
                $extra_fields = preg_split("/[\n]+/", $val['extra_fields']);   
                $pic_url = !empty($val['pic_simg']) ? $val['pic_simg'] : UPLOAD_SITE_URL.'/'.$val['pic_img'];
            ?>
             <li>
                <div class="list-good buy">
                    <div class="good-pic">
                        <a href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>" class="pic-img" target="_blank"> 
                        <img alt="" src="<?php echo $pic_url;?>" class="J_lazy lazy good-pic" /><span class="new-icon">新品</span></a>
                    </div>
                    <h3 class="good-title">[包邮]
                        <a href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>"  target="_blank" _hover-ignore="1"><?php echo $val['pic_name'];?></a>
                        <div class="icon-all" style="display:none;"></div>
                    </h3>
                    <div class="good-price">
                        <div><span class="price-current"><em>￥</em><?php echo $val['pic_sname'];?></span><span class="sold">
                        <em><?php echo trim($extra_fields[0]);?></em></span></div>
                    </div><a class="y-like my-like" href="javascript:;"><i class="like-ico"><span class="heart_left"></span><span class="heart_right"></span></i></a></div>
            </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>


 