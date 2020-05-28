<div class="sale_everyday" style="margin-left: 0px">
    <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
    <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>

    <?php if (is_array($val) && !empty($val)) { ?>
    <a href="<?php echo $val['pic_url'];?>">
        <img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="">
    </a>
                <?php break;?>
            <?php }?>
        <?php }?>
    <?php }?>
</div>