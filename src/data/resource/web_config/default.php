<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<?php
$floorId = rand(10000,99999);
?>

<div class="home-standard-layout style-<?php echo $output['style_name'];?> clearfix">
  <div class="left-sidebar">
    <div class="title"> 
		<a class="wwisp"  href="" ref="<?php echo $output['code_tit']['code_info']['floor'];?>"> 
			<p class="title-icon"><img shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$output['code_tit']['code_info']['pic'];?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"/></p></a>
			<?php echo $output['code_tit']['code_info']['floor'];?>&nbsp;&nbsp;&nbsp; 
      <a class="title-name" href="<?php echo $output['code_tit']['code_info']['url'];?>" target="_blank"><?php echo $output['code_tit']['code_info']['title'];?></a>
		
      
    </div>
  </div>
  <div class="middle-layout">

      
      
    <div class="right-side-focus">
      <ul class="clearfix">
        <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
        <?php $right_side_pic_arr = current($output['code_adv']['code_info']);?>
        <li><a href="<?php echo $right_side_pic_arr['pic_url'];?>" title="<?php echo $right_side_pic_arr['pic_name'];?>" target="_blank"><img alt="<?php echo $right_side_pic_arr['pic_name'];?>" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$right_side_pic_arr['pic_img'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" width="349" height="488" /></a></li>
        <?php } ?>
      </ul>
    </div>
      
      
    <?php if (!empty($output['code_recommend_list']['code_info']) && is_array($output['code_recommend_list']['code_info'])) { $i = 0; ?>
    <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) { $i++;?>
    <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
    <div class="tabs-panel middle-goods-list <?php echo $i==1 ? '':'tabs-hide';?>">
      <ul class="clearfix" id="floor_<?php echo $floorId;?>">
        <?php foreach($val['goods_list'] as $k => $v){ ?>
        <li>
          <dl>
			  <dd class="goods-thumb"><a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>"><img shopwwi-url="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"  alt="<?php echo $v['goods_name']; ?>" /></a></dd>
            <dt class="goods-name"><a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=> $v['goods_id'])); ?>" title="<?php echo $v['goods_name']; ?>"> <?php echo $v['goods_name']; ?></a></dt>
            
            <dd class="goods-price clearfix"><em><?php echo ncPriceFormatForList($v['goods_price']); ?></em><span class="original"><?php echo ncPriceFormatForList($v['market_price']); ?></span></dd>
          </dl>
        </li>
        <?php } ?>
      </ul>
    </div>
    <?php } elseif (!empty($val['pic_list']) && is_array($val['pic_list'])) { ?>
    <div class="tabs-panel middle-banner-style01 fade-img <?php echo $i==1 ? '':'tabs-hide';?>">
      <ul class="shopwwi-panel-r">
        <li class="item tall">
          <div class="title"><a href="<?php echo $val['pic_list']['11']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['11']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['11']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['11']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['11']['pic_img'];?>"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo $val['pic_list']['11']['pic_name'];?>"></a> </li>
        <li class="item tall">
          <div class="title"><a href="<?php echo $val['pic_list']['12']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['12']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['12']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['12']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['12']['pic_img'];?>"   rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo $val['pic_list']['12']['pic_name'];?>"></a> </li>
        <li class="item ">
          <div class="title"><a href="<?php echo $val['pic_list']['21']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['21']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['21']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['21']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['21']['pic_img'];?>" mff="sqde"  rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo $val['pic_list']['21']['pic_name'];?>"></a> </li>
        <li class="item bm">
          <div class="title"><a href="<?php echo $val['pic_list']['24']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['24']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['24']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['24']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170"  shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['24']['pic_img'];?>"   rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo $val['pic_list']['24']['pic_name'];?>"></a> </li>
        <li class="item bm">
          <div class="title"><a href="<?php echo $val['pic_list']['31']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['31']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['31']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['31']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['31']['pic_img'];?>"   rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt="<?php echo $val['pic_list']['31']['pic_name'];?>"></a> </li>
        <li class="item bm">
          <div class="title"><a href="<?php echo $val['pic_list']['32']['pic_url'];?>" target="_blank"><?php echo $val['pic_list']['32']['pic_name'];?></a></div>
          <p><?php echo $val['pic_list']['32']['pic_sname'];?></p>
          <a href="<?php echo $val['pic_list']['32']['pic_url'];?>" class="pic" target="_blank"><img width="170" height="170" mff="sqde" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_list']['32']['pic_img'];?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif"   alt="<?php echo $val['pic_list']['32']['pic_name'];?>"></a> </li>
      </ul>
    </div>
    <?php }}} ?>
  </div>
</div>
<?php if (!empty($output['code_brand_list']['code_info']) && is_array($output['code_brand_list']['code_info'])) { $k = 0 ;?>
<!--<ul class="shopwwi-brand clearfix">
  <?php foreach ($output['code_brand_list']['code_info'] as $key => $val) { $k++; ?>
  <?php if($k<11){?>
  <li class="item<?php echo $k ;?>"><a href="<?php echo urlShop('brand', 'list', array('brand'=> $val['brand_id'])); ?>" target="_blank" title="<?php echo $val['brand_name']; ?>"><img alt="" shopwwi-url="<?php echo UPLOAD_SITE_URL.'/'.$val['brand_pic'];?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" ></a></li>
  <?php }} ?>
</ul>-->
<?php } ?>


<script>
$(function(){
    <?php
        $right_side_pic_arr_new = array_slice($output['code_adv']['code_info'], 1, 2);
    ?>
    var add_pic="<div class='medium-pic1'><?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?><div><a href='<?php echo $right_side_pic_arr_new[0]['pic_url'];?>' title='<?php echo $right_side_pic_arr_new[0]['pic_name'];?>' target='_blank'><img alt='<?php echo $right_side_pic_arr_new[0]['pic_name'];?>' shopwwi-url='<?php echo UPLOAD_SITE_URL.'/'.$right_side_pic_arr_new[0]['pic_img'];?>'  rel='lazy' src='<?php echo SHOP_SITE_URL;?>/img/loading.gif' /></a></div><?php } ?></div>";
    var add_pic1="<div class='medium-pic2'><?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?><div><a href='<?php echo $right_side_pic_arr_new[1]['pic_url'];?>' title='<?php echo $right_side_pic_arr_new[1]['pic_name'];?>' target='_blank'><img alt='<?php echo $right_side_pic_arr_new[1]['pic_name'];?>' shopwwi-url='<?php echo UPLOAD_SITE_URL.'/'.$right_side_pic_arr_new[1]['pic_img'];?>'  rel='lazy' src='<?php echo SHOP_SITE_URL;?>/img/loading.gif' /></a></div><?php } ?></div>";
    $("#floor_<?php echo $floorId;?>").find("li").eq(0).before(add_pic);
    $("#floor_<?php echo $floorId;?>").find("li").eq(3).after(add_pic1);
   // $("#floor_<?php echo $floorId;?>").find("li").append(add_pic1);
    /*$(".home-standard-layout .middle-layout .middle-goods-list ul").each(function(index,element){
        $(this).find("li").eq(1).after(add_pic);    
    })*/
    
})
</script>