<!--div class="nch-breadcrumb-layout">
  <?php if(!empty($output['nav_link_list']) && is_array($output['nav_link_list'])){?>
  <div class="nch-breadcrumb wrapper"><i class="icon-home"></i>
    <?php foreach($output['nav_link_list'] as $nav_link){?>
    <?php if(!empty($nav_link['link'])){?>
    <span><a href="<?php echo $nav_link['link'];?>"><?php echo $nav_link['title'];?></a></span><span class="arrow">></span>
    <?php }else{?>
    <span><?php echo $nav_link['title'];?></span>
    <?php }?>
    <?php }?>
  </div>
  <?php }?>
</div-->


<?php
if(!empty($output['nav_link_list']) && is_array($output['nav_link_list'])){?>
<div class="location-breadcrumb w1200">
  <a class="lb-home" href="<?php echo B2B_SITE_URL; ?>">首页</a><i>&#8250;</i>
  <?php foreach($output['nav_link_list'] as $nav_link){
  if(!empty($nav_link['link'])){
    ?>
    <a href="<?php echo $nav_link['link'];?>"><?php echo $nav_link['title']; ?></a><i>&#8250;</i>
    <?php } else{ ?>
    <a href="#"><?php echo $nav_link['title']; ?></a>
  <?php } }?>
  <!--a href="#">休闲食品</a-->
</div>
<?php }?>

