<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<!-- 图片组部分start -->
<div class="right-side-focus">
    <ul> 
    <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
    <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
      <?php if (is_array($val) && !empty($val)) { ?>
      <li>
        <a href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>" target="_blank"><img alt="<?php echo $val['pic_name'];?>" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" width="349" height="488" /></a>
      </li>
      <?php }}} ?>
    </ul>
  </div>
<!-- 图片组部分end -->
