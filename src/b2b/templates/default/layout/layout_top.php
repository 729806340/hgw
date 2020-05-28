<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div id="append_parent"></div>
<div class="b2b-top-bar-outside">
	<div class="b2b-top-bar w1200 b2bclearfix">
		<?php if($_SESSION['is_login']=='1'){?>
		<div id="user-info" class="user-entry fl">您好&nbsp;<?php echo $_SESSION['member_name']?>，欢迎来到 <a  href='<?php echo B2B_SITE_URL; ?>' style="color:#000">汉购网</a>&nbsp;<a class="register" href='<?php echo urlLogin('login','logout');?>'>[退出]</a></div>
		<?php } else{ ?>
		<div id="user-login" class="user-entry fl"><a class="hgw-home" href='<?php echo B2B_SITE_URL; ?>'>汉购网首页</a><a class="login" href='<?php echo urlMember('login');?>'>请登录</a><a class="register" href='<?php echo urlLogin('login','register');?>'>免费注册</a></div>
		<?php } ?>
		<div class="quick-menu fr">
			<?php if( $output['purchaser_flag'] == 1 && $_SESSION['is_login']=='1'){?>
			<dl><dt><a href='/index.php?act=member&op=home'>采购商中心</a></dt></dl>
			<?php }?>
			<dl><dt>客户服务<i></i></dt>
				<dd class="dd">
					<ul>
						<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 2));?>">帮助中心</a></li>
						<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 5));?>">售后服务</a></li>
						<li><a href="<?php echo urlMember('article', 'article', array('ac_id' => 6));?>">客服中心</a></li>
					</ul>
				</dd>
			</dl>
			<?php if( $output['purchaser_flag'] == 0 && $_SESSION['is_login']=='1'){?>
				<dl><dt><a href="javascript:void(0)"  nc_type="dialog" dialog_title="注册采购商信息" dialog_id="my_address_edit"  uri="<?php echo B2B_SITE_URL;?>/index.php?act=member_purchase&op=register&type=add" dialog_width="550" title="注册采购商信息">成为采购商</a></dt></dl>
			<?php }?>
		</div>
	</div>
</div>