<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="explanation" id="explanation">
  <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
	<h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
  </div>
  <ul>
	<li>点击添加新的广告条按钮可以添加新的广告条</li>
	<li>鼠标移动到已有的广告条上点击出现的删除按钮可以删除对应的广告条</li>
	<li>操作完成后点击保存编辑按钮进行保存</li>
  </ul>
</div>

<div class="index_block adv_list">
  <h3><?php echo $output['module_info']['desc'];?></h3>
  <div nctype="item_content" class="content">
    <h5>内容：</h5>
		挂件模板： 
		<select name="item_template">
			<option value ="default" <?php if('default'==$item_info['item_template']) {echo "selected";} ?> >默认焦点图样式</option>
            <option value ="style02" <?php if('style02'==$item_info['item_template']) {echo "selected";} ?> >一行两张铺满全屏图</option>
		</select>

    <?php if(!empty($item_data['item']) && is_array($item_data['item'])) {?>
    <?php foreach($item_data['item'] as $item_key => $item_value) {?>
    <div nctype="item_image" class="item"> <img nctype="image" src="<?php echo getMbSpecialImageUrl($item_value['image']);?>" alt="">
      <input nctype="image_name" name="item_data[item][<?php echo $item_key;?>][image]" type="hidden" value="<?php echo $item_value['image'];?>">
      <input nctype="image_type" name="item_data[item][<?php echo $item_key;?>][type]" type="hidden" value="<?php echo $item_value['type'];?>">
      <input nctype="image_data" name="item_data[item][<?php echo $item_key;?>][data]" type="hidden" value="<?php echo $item_value['data'];?>">
	  <input nctype="image_titlebelow" name="item_data[item][<?php echo $item_key;?>][titlebelow]" type="hidden" value="<?php echo $item_value['titlebelow'];?>">

	  <a nctype="btn_del_item_image" href="javascript:;" style="display:hidden;margin-right:60px;"><i class="fa fa-trash-o"></i>删除</a>
      <a nctype="btn_edit_item_image" data-desc="640*260" href="javascript:;" style="display:hidden;"><i class="fa fa-pencil-square-o"></i>编辑</a>

    </div>
    <?php } ?>
    <?php } ?>
  </div>
  <a nctype="btn_add_item_image" class="ncap-btn" data-desc="640*240" href="javascript:;"><i class="fa fa-plus"></i>添加新的广告图</a>
</div>



 