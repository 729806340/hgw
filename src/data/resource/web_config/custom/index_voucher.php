<div class="Coupon">
    <ul class="clearfix">
        <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
            <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>

        <?php if (is_array($val) && !empty($val)) { ?>
        <li>
            <a href="<?php echo $val['pic_url'];?>" title="优惠券">
                <img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="优惠券">
            </a>
        </li>
        <?php }?>
        <?php }?>
        <?php }?>
    </ul>
</div>