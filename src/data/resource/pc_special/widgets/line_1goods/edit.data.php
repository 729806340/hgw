<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.mb-item-edit-content {
background: #EFFAFE url(<?php echo ADMIN_TEMPLATES_URL;
?>/images/cms_edit_bg_line.png) repeat-y scroll 0 0;
}
</style>

<div class="explanation" id="explanation">
  <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
	<h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
  </div>
  <ul>
	<li>从右侧筛选按钮，点击添加按钮完成添加</li>
    <li>鼠标移动到已有商品上，会出现删除按钮可以对商品进行删除</li>
    <li>操作完成后点击保存编辑按钮进行保存</li>
  </ul>
</div>

<div class="index_block adv_list">
  <h3><?php echo $output['module_info']['desc'];?></h3>
  <div nctype="item_content" class="content">
    <h5>内容：</h5>
		挂件模板： 
		<select name="item_template">
			<option value ="default" <?php if('default'==$item_info['item_template']) {echo "selected";} ?> >默认样式</option>
		</select>

    <?php if(!empty($item_data['item']['goods']) && is_array($item_data['item']['goods'])) {?>
    <?php foreach($item_data['item']['goods'] as $item_value) {?>
    <div nctype="item_image" class="item">
      <div class="goods-pic"><img nctype="goods_image" src="<?php echo cthumb($item_value['goods_image']);?>" alt=""></div>
      <div class="goods-name" nctype="goods_name"><?php echo $item_value['goods_name'];?></div>
      <div class="goods-price" nctype="goods_price">￥<?php echo $item_value['goods_promotion_price'];?></div>
      <input nctype="goods_id" name="item_data[item][goods][]" type="hidden" value="<?php echo $item_value['goods_id'];?>">
      <a nctype="btn_del_item_image" href="javascript:;"><i class="fa fa-trash-o
"></i>删除</a>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>

<div class="search-goods">
  <h3>选择商品添加</h3>
  <h5>商品关键字：</h5>
  <input id="txt_goods_name" type="text" class="txt w200" name="">
  <a id="btn_pc_special_goods_search" class="ncap-btn" href="javascript:;" style="vertical-align: top; margin-left: 5px;">搜索</a>
  <div id="pc_special_goods_list"></div>
</div>
<script id="item_goods_template" type="text/html">
    <div nctype="item_image" class="item">
        <div class="goods-pic"><img nctype="image" src="<%=goods_image%>" alt=""></div>
        <div class="goods-name" nctype="goods_name"><%=goods_name%></div>
        <div class="goods-price" nctype="goods_price"><%=goods_price%></div>
        <input nctype="goods_id" name="item_data[item][goods][]" type="hidden" value="<%=goods_id%>">
        <a nctype="btn_del_item_image" href="javascript:;">删除</a>
    </div>
</script> 
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js" type="text/javascript"></script> 
<script type="text/javascript">
    $(document).ready(function(){
        $('#btn_pc_special_goods_search').on('click', function() {
            var url = '<?php echo urlAdminShop('pc_special', 'goods_list');?>';
            var keyword = $('#txt_goods_name').val();
            if(keyword) {
                $('#pc_special_goods_list').load(url + '&' + $.param({keyword: keyword}));
            }
        });

        $('#pc_special_goods_list').on('click', '[nctype="btn_add_goods"]', function() {
            var item = {};
            item.goods_id = $(this).attr('data-goods-id');
            item.goods_name = $(this).attr('data-goods-name');
            item.goods_price = $(this).attr('data-goods-price');
            item.goods_image = $(this).attr('data-goods-image');
            var html = template.render('item_goods_template', item);
            $('[nctype="item_content"]').append(html);
        });
    });
</script> 
