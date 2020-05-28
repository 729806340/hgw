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
				<h3>商品管理 - 精斗云供应商维护</h3>
				<h5>修改精斗云供应商</h5>
			</div>
		</div>
	</div>
	<div class="homepage-focus" nctype="editStoreContent">
			<div class="ncap-form-default">
                <form action="" id="jdy-form" method="post">
                    <input type="hidden" name="form_submit" value="ok" />

                    <?php
                    $goods = $output['goods'];
                    $suppliers = $output['supplierList'];

if (is_array($suppliers)) {
                        ?>
                        <p style="margin: 10px; font-size: 16px;"><?php echo $goods['goods_name']; ?></p>
				<p style="margin: 10px; font-size: 14px;">
                    当前供应商：<span style="color: red;"><?php echo $goods['jdy_supplier_id']&&isset($suppliers[$goods['jdy_supplier_id']])?$suppliers[$goods['jdy_supplier_id']]:'未设置'?></span>
				</p>

                    <dl class="row">
					<dt class="tit">
						<label for="jdy_supplier_id"><em>*</em>请选择供应商：</label>
					</dt>
					<dd class="opt">
                        <select title="jdy_supplier_id" name="jdy_supplier_id" id="jdy_supplier_id">
                            <?php
                            foreach ($suppliers as $key => $supplier){
                                echo "<option value='$key'>{$supplier}</option>";
                            }
                            ?>
                        </select>
                        <?php if ($_GET['force']){
                            echo "<p>供应商数据已刷新</p>";
                        }else{ ?>

                        <p>若下拉框数据不是最新数据，请 <a href="index.php?act=goods&op=jdy_supplier_edit&goods_commonid=<?php echo $goods['goods_commonid']?>&force=1">点击刷新数据</a></p>
                        <?php } ?>
					</dd>
				<div class="bot">
					<a href="JavaScript:void(0);" nc_type="submit_btn" class="ncap-btn-big ncap-btn-green"><?php echo $lang['nc_submit'];?></a>
				</div>
                <?php                    
                }
                ?>
                </form>

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
		    $("#jdy-form").submit();return;
		var jdy_supplier_id = $("#jdy_supplier_id").val();
		$.ajax({
			    dataType:'json',
				url:"index.php?act=goods&op=jdy_supplier_edit",
				type:"post",
				data:{
				  goods_commonid:<?php echo $goods['goods_commonid']?>,
                    jdy_supplier_id:jdy_supplier_id,
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
});
</script>

