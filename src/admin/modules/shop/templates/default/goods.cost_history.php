<?php
defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Author: ljq
 * Date: 2016/11/21
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
				<h3>商品管理 - 编辑共建商品成本价格信息修改日志</h3>
			
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
				 <?php
				 foreach($v['workflow'] as $key=>$val){
				     if(count($val)>0){
				     $new_value = json_decode($val['new_value'] , true);
				     $old_value = json_decode($val['old_value'] , true);
				 ?>
                  <dl class="row">
                    <dt class="tit">发起人：<?php echo $val['user'] ?>【<?php echo $val['role']=="1" ? "商家":"系统人员"?>】</dt>
                    <dd class="opt">
                                    申请时间：<?php echo date('Y-m-d H:i' , $val['created_at']) ?>&nbsp;&nbsp;审核时间： <?php echo date('Y-m-d H:i' , $val['updated_at']) ?> 
             		</dd>
                  </dl>
                
                <dl class="row">
                	<dt class="tit">修改前值：</dt>
                  <dd class="opt">
                      <?php echo $old_value['goods_cost'] ?> 
                    </dd>
                </dl>
                <dl class="row">
                	<dt class="tit">修改后值：</dt>
                  <dd class="opt">
                      <?php echo $new_value['goods_cost'] ?> 
                    </dd>
                </dl>
                <dl class="row">
                	<dt class="tit"></dt>
                  <dd class="opt">
                     <a href="index.php?act=workflow&op=detail&id=<?php echo $val['id']?>" target="_blank">查看详情</a>
                    </dd>
                </dl>
                <?php 
				   }else{
				?>
				<dl class="row">
                	<dt class="tit"></dt>
                  <dd class="opt">
                     暂无日志
                    </dd>
                </dl>
                <?php
				   }
                  }
                 }
                }
                ?>
            </div>
	</div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>