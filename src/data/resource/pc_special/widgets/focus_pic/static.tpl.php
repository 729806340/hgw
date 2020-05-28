
<style>
    #navigation-bar a{
        display: block;
        color: white;
        margin-bottom: 5px;
    }
    #navigation-bar{
        display: block;
        position: fixed;
        top: 210px;
        left: 0px;
        background: black;
        padding: 10px;
        z-index: 50;
        width: 55px;
    }
</style>

<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="index_block adv_list">
  <div nctype="item_content" class="content">
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {?>
	  <div nctype="item_image" class="item"><a target="_blank" href="<?php echo $item_value['url']; ?>"><img nctype="image" width="100%" style="max-width:1920px;" shopwwi-url="<?php echo getMbSpecialImageUrl($item_value['image']);?>" rel='lazy' src="<?php echo SHOP_SITE_URL;?>/img/loading.gif" alt=""></a>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>

