<?php

defined('ByShopWWI') or exit('Access Invalid!');
?>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<a class="back" href="javascript:history.back(-1)"
				title="返回<?php echo $lang['manage'];?>列表"><i
				class="fa fa-arrow-circle-o-left"></i></a>
			<div class="subject" style ="height: auto;">
				<h3>商品管理 - 编辑分销佣金</h3>
				<h5>修改分销商品佣金</h5>
			</div>
		</div>
	</div>
	<div class="homepage-focus" nctype="editStoreContent">
			<div class="ncap-form-default">
                <?php
                    if (is_array($output['goodsList'])) {
                        foreach ($output['goodsList'] as $k => $v) {
                    ?>
                    <p style="margin: 10px; font-size: 16px;"><?php echo $v['goods_name']; ?></p>
                    <p style="margin: 10px; font-size: 14px;">
                        当前售价：<span id="goods_price<?php echo $v['goods_price']?>>"><?php echo $v['goods_price']?></span>元 ;当前成本价： <span style="color: red;"><?php echo $v['goods_cost']; ?>元</span>
                    </p>
                    <dl class="row">
                        <dt class="tit">
                            <label>一级分销佣金：</label>
                        </dt>
                        <dd class="opt">
                            <input type="text" value="<?php echo isset($output['retail_goods_list'][$v['goods_id']]) ?  $output['retail_goods_list'][$v['goods_id']]['retail_one_return'] : 0;?>" id="goods_one_<?php echo $v['goods_id'];?>"   name="retail_one_return[<?php echo $v['goods_id'];?>]" class="input-txt" />
                        </dd>
                    </dl>
                    <dl class="row">
                        <dt class="tit">
                            <label>二级分销佣金：</label>
                        </dt>
                        <dd class="opt">
                            <input type="text" value="<?php echo isset($output['retail_goods_list'][$v['goods_id']]) ?  $output['retail_goods_list'][$v['goods_id']]['retail_two_return'] : 0;?>" id="goods_two_<?php echo $v['goods_id'];?>"  name="retail_two_return[<?php echo $v['goods_id'];?>]" class="input-txt" />
                        </dd>
                    </dl>
                    <dl class="row">
                        <dt class="tit">
                            <label>三级分销佣金：</label>
                        </dt>
                        <dd class="opt">
                            <input type="text" value="<?php echo isset($output['retail_goods_list'][$v['goods_id']]) ?  $output['retail_goods_list'][$v['goods_id']]['retail_three_return'] : 0;?>" id="goods_three_<?php echo $v['goods_id'];?>"   name="retail_three_return[<?php echo $v['goods_id'];?>]" class="input-txt" />
                        </dd>
                    </dl>
                    <dl class="row">
                        <dt class="tit">
                            <label>今日推荐到期时间：</label>
                        </dt>
                        <dd class="opt">
                            <input type="text" value="<?php echo $output['retail_goods_list'][$v['goods_id']]['retail_show_time'] > 0 ?  date('Y-m-d H:i:s', $output['retail_goods_list'][$v['goods_id']]['retail_show_time']) : '';?>" id="retail_show_time_<?php echo $v['goods_id'];?>" name="retail_show_time[<?php echo $v['goods_id'];?>]" class="input-txt" />
                            <span>格式如下 2019-08-23 11:44:30</span>
                        </dd>
                    </dl>
                    <div class="bot">
                        <a href="JavaScript:void(0);" nc_type="submit_btn" class="ncap-btn-big ncap-btn-green"
                            data-goods_id="<?php echo $v['goods_id']?>" data-goods_common_id="<?php echo $v['goods_commonid']?>" ><?php echo $lang['nc_submit'];?></a>
                    </div>
                <?php
                    } }
                ?>
            </div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	//提交申请
	$('a[nc_type="submit_btn"]').each(function(){
		$(this).click(function(){
		var goods_id = $(this).data('goods_id');
		var goods_commonid = $(this).data('goods_common_id');
		var retail_one_return = $("#goods_one_"+goods_id).val();
		var retail_two_return = $("#goods_two_"+goods_id).val();
		var retail_three_return = $("#goods_three_"+goods_id).val();
		var retail_show_time = $("#retail_show_time_"+goods_id).val();
		$.ajax({
			    dataType:'json',
				url:"index.php?act=goods&op=edit_retail_money",
				type:"post",
				data:{
				    isajax:1,
                    form_submit:"ok",
				    goods_id:goods_id,
                    goods_commonid:goods_commonid,
                    retail_one_return:retail_one_return,
                    retail_two_return:retail_two_return,
                    retail_three_return:retail_three_return,
                    retail_show_time:retail_show_time
				},
            	success:function(data){
               	   if(data.state ==true){
                   	   alert('修改成功！');
                   }else{
                       alert(data.msg);
                   }
            	}
          });
		});
	});

});
</script>

