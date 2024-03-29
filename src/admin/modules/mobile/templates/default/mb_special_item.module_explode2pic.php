<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php if($item_edit_flag) { ?>

<div class="explanation" id="explanation">
  <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
    <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
  </div>
  <ul>
    <li>点击添加新的块内容按钮可以添加新的内容</li>
    <li>鼠标移动到已有的内容上点击出现的删除按钮可以对其进行删除</li>
    <li>操作完成后点击保存编辑按钮进行保存</li>
  </ul>
</div>
<?php } ?>
<div class="index_block explode3">
  <?php if($item_edit_flag) { ?>
  <h3>一行2个 2图2文</h3>
  <?php } ?>
  <div class="title">
    <?php if($item_edit_flag) { ?>
    <h5>标题1：</h5>
    <input id="home1_title" type="text" class="txt w200" name="item_data[title]" value="<?php echo $item_data['title'];?>">
    <?php } else { ?>
    <span><?php echo $item_data['title'];?></span>
    <?php } ?>
  </div>
<div class="title">
    <?php if($item_edit_flag) { ?>
        <h5>副标题1：</h5>
        <input id="home1_stitle" type="text" class="txt w200" name="item_data[stitle]" value="<?php echo $item_data['stitle'];?>">
    <?php } else { ?>
        <span><?php echo $item_data['stitle'];?></span>
    <?php } ?>
</div>
    <div class="title">
        <?php if($item_edit_flag) { ?>
            <h5>标题2：</h5>
            <input id="home1_title1" type="text" class="txt w200" name="item_data[title1]" value="<?php echo $item_data['title1'];?>">
        <?php } else { ?>
            <span><?php echo $item_data['title1'];?></span>
        <?php } ?>
    </div>
    <div class="title">
        <?php if($item_edit_flag) { ?>
            <h5>副标题2：</h5>
            <input id="home1_stitle1" type="text" class="txt w200" name="item_data[stitle1]" value="<?php echo $item_data['stitle1'];?>">
        <?php } else { ?>
            <span><?php echo $item_data['stitle1'];?></span>
        <?php } ?>
    </div>
  <div nctype="item_content" class="content">
    <?php if($item_edit_flag) { ?>
    <h5>内容：</h5>
    <?php } ?>
    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {?>
    <div nctype="item_image" class="item"> <img nctype="image" src="<?php echo getMbSpecialImageUrl($item_value['image']);?>" alt="" width="156px;">
      <?php if($item_edit_flag) { ?>
      <input nctype="image_name" name="item_data[item][<?php echo $item_key;?>][image]" type="hidden" value="<?php echo $item_value['image'];?>">
      <input nctype="image_type" name="item_data[item][<?php echo $item_key;?>][type]" type="hidden" value="<?php echo $item_value['type'];?>">
      <input nctype="image_data" name="item_data[item][<?php echo $item_key;?>][data]" type="hidden" value="<?php echo $item_value['data'];?>">
      <input nctype="image_titlebelow" name="item_data[item][<?php echo $item_key;?>][titlebelow]" type="hidden" value="<?php echo $item_value['titlebelow'];?>">
      <a nctype="btn_del_item_image" href="javascript:;" style="display:hidden;margin-right:60px;"><i class="fa fa-trash-o"></i>删除</a>
      <a nctype="btn_edit_item_image" data-desc="640*260" href="javascript:;" style="display:hidden;"><i class="fa fa-pencil-square-o"></i>编辑</a>
      <?php } ?>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
  <?php if($item_edit_flag) { ?>
  <a nctype="btn_add_item_image" class="ncap-btn" data-desc="320*85" href="javascript:;"><i class="fa fa-plus"></i>添加新的块内容</a>
  <?php } ?>
</div>
