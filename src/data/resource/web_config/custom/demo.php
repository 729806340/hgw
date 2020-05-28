<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<!-- 商品组部分 -->
<div class="middle-layout"> 
  <ul class="tabs-nav">
    <?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) { 
      $i = 0; ?> 
    <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) { 
      $i++;?>        
      <li class="<?php echo $i==1 ? 'tabs-selected':'';?>">
        <i class="arrow"></i>
        <h3><?php echo $val['recommend']['name'];?></h3>
      </li> 
      <?php } ?>  
    <?php } ?> 
  </ul>

  <?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) { 
    $i = 0; ?>
    <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) { 
      $i++;?>
      <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?> 
      <div class="tabs-panel middle-goods-list <?php echo $i==1 ? '':'tabs-hide';?>"> 
        <ul> 
		  <?php foreach($val['goods_list'] as $k => $v){ ?> 
          <li> 
            <dl>
              <dt class="goods-name"><a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>">  <?php echo $v['goods_name']; ?></a></dt> <dd class="goods-thumb"><a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>"><img shopwwi-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"  alt="<?php echo $v['goods_name']; ?>" /></a></dd> <dd class="goods-price"><em><?php echo ncPriceFormatForList($v['goods_price']); ?></em><span class="original"><?php echo ncPriceFormatForList($v['market_price']); ?></span></dd> </dl> 
			</li> 
			<?php } ?>
		</ul> 
	</div>
	<?php }}} ?>                   
</div> 


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

<!-- 焦点图部分start -->
<div>
<ul id="fullScreenSlides" class="full-screen-slides"><?php if (is_array($output['code_screen_list']['code_info']) && !empty($output['code_screen_list']['code_info'])) { ?><?php foreach ($output['code_screen_list']['code_info'] as $key => $val) { ?> <?php if (is_array($val) && $val['ap_id'] > 0) { ?><li ap_id="<?php echo $val['ap_id'];?>" color="<?php echo $val['color'];?>" style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top"><a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a></li><?php }else { ?> <li style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top"><a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a></li><?php } } } ?></ul>

<ul><?php if (is_array($output['code_focus_list']['code_info']) && !empty($output['code_focus_list']['code_info'])) { ?> <?php foreach ($output['code_focus_list']['code_info'] as $key => $val) { ?> <li><?php if (is_array($val['pic_list']) && $val['pic_list'][1]['ap_id'] > 0) { ?> <?php foreach($val['pic_list'] as $k => $v) { ?> <a ap_id="<?php echo $v['ap_id'];?>" href="<?php echo $v['pic_url'];?>" target="_blank" title="<?php echo $v['pic_name'];?>"> <img src="<?php echo UPLOAD_SITE_URL.'/'.$v['pic_img'];?>" alt="<?php echo $v['pic_name'];?>"></a><?php }}else { ?> <?php foreach($val['pic_list'] as $k => $v) { ?><a href="<?php echo $v['pic_url'];?>" target="_blank" title="<?php echo $v['pic_name'];?>"> <img src="<?php echo UPLOAD_SITE_URL.'/'.$v['pic_img'];?>" alt="<?php echo $v['pic_name'];?>"></a> <?php } } ?></li> <?php } } ?></ul>
</div>
<script type="text/javascript">update_screen_focus();</script>
<!-- 焦点图部分end -->
