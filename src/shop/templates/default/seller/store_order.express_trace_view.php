<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<style>

    .trace{margin: 20px 2%;}
    .time{display: inline-block;width: 120px;}
</style>
<ul class="trace">
    <?php foreach ($output['data'] as $key => $value){ ?>
        <li><span class="time"><?php echo $value['time'];?></span> <span><?php echo $value['context'];?></span></li>
    <?php } ?>
</ul>