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
				<h3>商品管理 - 编辑社区团购分销佣金</h3>
				<h5>修改社区团购商品佣金</h5>
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
                            <label>佣金比例：</label>
                        </dt>
                        <dd class="opt">
                            <input type="text" value="<?php echo isset($output['shequ_return_goods_list'][$v['goods_id']]) ?  $output['shequ_return_goods_list'][$v['goods_id']]['return_money_rate'] : 0;?>" id="goods_one_<?php echo $v['goods_id'];?>"   name="return_money_rate[<?php echo $v['goods_id'];?>]" class="input-txt" />0-1
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
		var return_money_rate = $("#goods_one_"+goods_id).val();
		$.ajax({
			    dataType:'json',
				url:"index.php?act=goods&op=edit_shequ_return_money",
				type:"post",
				data:{
				    isajax:1,
                    form_submit:"ok",
				    goods_id:goods_id,
                    goods_commonid:goods_commonid,
                    return_money_rate:return_money_rate,
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

