<?php

defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/9/3
 * Time: 14:02
 */
?>

<style type="text/css">
.d_inline {
	display: inline;
}
.input-file-show{
	vertical-align: top;display: inline-block;width: 80px;height: 30px;margin: 5px 5px 0 0;
}
.input-file-show a{display: block;position: relative;z-index: 1}
.input-file-show span{width: 80px; height: 30px;position: absolute;left: 0;top: 0;z-index: 2;cursor: pointer;}
.input-file{width: 80px;height: 30px;padding: 0;margin: 0;border: none 0;opacity: 0;filter: alpha(opacity=0);cursor: pointer;}
.upload-image{float:left; width:150px; height:80px; margin-right:5px;}
.upload-image img{ max-height:80px; max-width:150px;}
</style>

<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<a class="back" href="javascript:history.back(-1)"
				title="返回<?php echo $lang['manage'];?>列表"><i
				class="fa fa-arrow-circle-o-left"></i></a>
			<div class="subject" style ="height: auto;">
				<h3>商品管理 - 编辑共建商品成本价格信息</h3>
				<h5>修改共建商品成本价格</h5>
				<p style="color: red;">备注：当毛利小于5%需上传总裁签字凭证；负毛利需上传董事长签字凭证。</p>
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
                            <?php if($v['goods_cost_status']>0){ ?>
                        <p style="margin: 10px; font-size: 14px;">
					新成本价： <span style="color: red;"><?php echo $v['goods_cost_new']; ?>元</span>
					状态： <span style="color: red;"><?php echo $v['goods_cost_status']=='1'?'待第一级审核':'待第二级审核'; ?></span>
					若需要重新修改，<a href="index.php?act=goods&op=cost_status_reset&goods_id=<?php echo $v['goods_id']?>">请先撤回当前审核流程！</a>
				</p>
                    <?php }?>

                    <dl class="row">
					<dt class="tit">
						<label for="goods_<?php echo $v['goods_id'];?>"><em>*</em>请输入新成本价：</label>
					</dt>
					<dd class="opt">
                    <input type="hidden" id="goods_price<?php echo $v['goods_id']?>" value="<?php echo $v['goods_price']?>">
						<input type="number" value="<?php echo $v['goods_cost'];?>" id="goods_<?php echo $v['goods_id'];?>" data-goods_id="<?php echo $v['goods_id']?>" name="goods_cost_new[<?php echo $v['goods_id'];?>]"class="input-txt" /> <span class="err"></span>
						<p class="notic">当前直接更新成本价，后续增加审核功能</p>
					</dd>
            	   <dl class="row">
                    <dt class="tit">
                      <label for="login_pic3">总裁凭证上传：</label>
                    </dt>
                    <dd class="opt">
                      <div id="show-zc-image<?php echo $v['goods_id']?>" class="upload-image"></div>
                      <div class="input-file-show">
                      <a href="javascript:void(0);"><span>
                      <input type="file" hidefocus="true" data-type="zc" data-goods_id="<?php echo $v['goods_id']?>" size="1" class="input-file" name="sign" id="submitSign"  nc_type="upload_sign">
                      </span>
                      <p><i class="icon-upload-alt"></i>凭证上传</p>
                      </a>
                      </div>
                    </dd>
                  </dl>
                   <dl class="row">
                    <dt class="tit">
                      <label for="login_pic3">董事长凭证上传：</label>
                    </dt>
                    <dd class="opt">
                      <div id="show-dsz-image<?php echo $v['goods_id']?>" class="upload-image"></div>
                      <div class="input-file-show">
                      <a href="javascript:void(0);"><span>
                      <input type="file" hidefocus="true" data-type="dsz" data-goods_id="<?php echo $v['goods_id']?>" size="1" class="input-file" name="sign" id="submitSign"  nc_type="upload_sign">
                      </span>
                      <p><i class="icon-upload-alt"></i>凭证上传</p>
                      </a>
                      </div>
                    </dd>
                  </dl>
				<div class="bot">
					<a href="JavaScript:void(0);" nc_type="submit_btn" class="ncap-btn-big ncap-btn-green"
						data-goods_id="<?php echo $v['goods_id']?>"><?php echo $lang['nc_submit'];?></a>
				</div>
                <?php                    
                  }
                }
                ?>
            </div>
	</div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
	//提交申请
	$('a[nc_type="submit_btn"]').each(function(){
		$(this).click(function(){
		var goods_id = $(this).data('goods_id');
		var goods_price = $("#goods_price"+goods_id).val();
		var goods_cost = $("#goods_"+goods_id).val();
		var sign_zc = $("#show-zc-image"+goods_id).attr('img');
		var sign_dsz = $("#show-dsz-image"+goods_id).attr('img');
		if(parseFloat(goods_price) - parseFloat(goods_cost) < parseFloat(goods_cost)*0.05 && sign_zc==''){
			alert('毛利小于百分之5，请上传总裁签证');
			return false;
		}
		if(parseFloat(goods_price) < parseFloat(goods_cost) && sign_dsz==''){
			alert('负毛利，请上传董事长签证');
			return false;
		}
		$.ajax({
			    dataType:'json',
				url:"index.php?act=goods&op=cost_edit",
				type:"post",
				data:{
				  isajax:1,
				  goods_id:goods_id,
				  goods_cost:goods_cost,
				  sign_zc:sign_zc,
				  sign_dsz:sign_dsz,
				},
            	success:function(data){
               	   if(data.state ==true){
                   	   alert('审核提交成功！');
                   	   window.location.href='index.php?act=workflow&op=ismy';
                   }else{
                       alert(data.msg);
                   }
            	}
          });
		});
	});
	//上传凭证
	$('input[nc_type="upload_sign"]').each(function(){
		 $(this).fileupload({
			dataType: 'json',
			url: 'index.php?act=goods&op=cost_edit',
			formData: '',
			done: function (e,data) {
				if(data.result.state== "true"){
					var upload_dir = "<?php echo DIR_UPLOAD;?>";
					var goods_id = $(this).data('goods_id');
					var type  = $(this).data('type');
					var file_path = "/"+upload_dir+"/"+data.result.file_path;
					var img = "<img src=\""+file_path+"\">";
					$("#show-"+type+"-image"+goods_id).html(img);
					$("#show-"+type+"-image"+goods_id).attr('img' , file_path);
				}
			},
   		});
	});
});
</script>

