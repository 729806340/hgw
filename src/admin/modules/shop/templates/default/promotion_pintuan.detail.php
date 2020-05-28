<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"> <a class="back" href="<?php echo urlAdminShop('promotion_pintuan', 'pintuan_list'); ?>" title="返回列表"> <i class="fa fa-arrow-circle-o-left"></i> </a>
      <div class="subject">
        <h3>拼团 - 查看活动“<?php echo $output['pintuan_info']['pintuan_name'];?>”</h3>
        <h5>查看商家拼团活动详情</h5>
      </div>
    </div>
  </div>

  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>">活动规则</h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo '开始时间：'.date('Y-m-d H:i',$output['pintuan_info']['start_time']);?> - <?php echo $lang['end_time'].'：'.date('Y-m-d H:i',$output['pintuan_info']['end_time']);?></li>
      <li>购买下限：<?php echo $output['pintuan_info']['limit_floor'];?> </li>
      <li> <?php echo '状态：'.$output['pintuan_info']['pintuan_state_text'];?> </li>
    </ul>
  </div>
  <!-- 列表 -->
  <form id="list_form" method="post">
    <input type="hidden" id="object_id" name="object_id"  />
    <table class="flex-table">
      <thead>
        <tr>
          <th width="24" align="center" class="sign"><i class="ico-check"></i></th>
          <th width="60" align="center" class="handle-s"><?php echo $lang['nc_handle'];?></th>
          <th width="350" align="left">商品名称</th>
          <th width="80" align="center">商品图片</th>
          <th width="100" align="center">店铺价格（元）</th>
          <th width="100" align="center">折扣价格（元）</th>
          <th width="100" align="center">拼团库存</th>
          <th width="100" align="center">拼团已售</th>
          <th width="100" align="center">成团时限</th>
          <th width="100" align="center">成团人数</th>
          <th width="100" align="center">凑团人数</th>
          <th width="100" align="center">购买下限</th>
          <th width="100" align="center">单次购买上限</th>
          <th width="100" align="center">累计购买上限</th>
          <?php if($output['pintuan_info']['editable']) { ?>
          <th width="80" align="center">推荐</th>
          <?php } ?>
          <th></th>
        </tr>
      </thead>
      <tbody id="treet1">
        <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
        <?php foreach($output['list'] as $k => $val){ ?>
        <tr>
          <td class="sign"><i class="ico-check"></i></td>
          <td class="handle-s"><a href="<?php echo $val['goods_url'];?>" class="btn green" target="_blank"><i class="fa fa-list-alt"></i>查看</a></td>
          <td><?php echo $val['goods_name']; ?></td>
          <td><a class="pic-thumb-tip" onmouseover="toolTip('<img src=<?php echo $val['image_url'];?>>')" onmouseout="toolTip()" href="javascript:void(0);"><i class="fa fa-picture-o"></i></a></td>
          <td><span><?php echo $val['goods_price'];?></span></td>
          <td><span><?php echo $val['pintuan_price'];?></span></td>
          <td><span><?php echo $val['pintuan_storage'];?></span></td>
          <td><span><?php echo $val['pintuan_sold'];?></span></td>
          <td><span><?php echo $val['limit_time']/3600;?></span></td>
          <td><span><?php echo $val['limit_user'];?></span></td>
          <td><span><?php echo $val['minimum_user'];?></span></td>
          <td><span><?php echo $val['limit_floor'];?></span></td>
          <td><span><?php echo $val['limit_ceilling'];?></span></td>
          <td><span><?php echo $val['limit_total'];?></span></td>
          <?php if($output['pintuan_info']['editable']) { ?>
          <td class="yes-onoff"><?php if($val['pintuan_recommend']){ ?>
            <a href="JavaScript:void(0);" class=" enabled" ajax_branch='recommend' nc_type="inline_edit" fieldname="pintuan_recommend" fieldid="<?php echo $val['pintuan_goods_id']?>" fieldvalue="1" title="<?php echo $lang['nc_editable'];?>"><img src="<?php echo ADMIN_TEMPLATES_URL;?>/images/transparent.gif"></a>
            <?php }else { ?>
            <a href="JavaScript:void(0);" class=" disabled" ajax_branch='recommend' nc_type="inline_edit" fieldname="pintuan_recommend" fieldid="<?php echo $val['pintuan_goods_id']?>" fieldvalue="0" title="<?php echo $lang['nc_editable'];?>"><img src="<?php echo ADMIN_TEMPLATES_URL;?>/images/transparent.gif"></a>
            <?php } ?></td>
          <?php } ?>
          <td></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr>
          <td class="no-data" colspan="100"><i class="fa fa-exclamation-triangle"></i><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){
	$('.flex-table').flexigrid({
		height:'auto',// 高度自动
		usepager: false,// 不翻页
		striped:false,// 不使用斑马线
		resizable: false,// 不调节大小
		title: '参加该活动商品列表',// 表格标题
		reload: false,// 不使用刷新
		columnControl: false,// 不使用列控制
	});
});
</script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>