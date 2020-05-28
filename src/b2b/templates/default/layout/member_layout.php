<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<?php include template('layout/common_layout');?>
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/member.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/member.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ToolTip.js"></script>
<script>
//sidebar-menu
$(document).ready(function() {
    $.each($(".side-menu > a"), function() {
        $(this).click(function() {
            var ulNode = $(this).next("ul");
            if (ulNode.css('display') == 'block') {
            	$.cookie(COOKIE_PRE+'Mmenu_'+$(this).attr('key'),1);
            } else {
            	$.cookie(COOKIE_PRE+'Mmenu_'+$(this).attr('key'),null);
            }
			ulNode.slideToggle();
				if ($(this).hasClass('shrink')) {
					$(this).removeClass('shrink');
				} else {
					$(this).addClass('shrink');
				}
        });
    });
	$.each($(".side-menu-quick > a"), function() {
        $(this).click(function() {
            var ulNode = $(this).next("ul");
			ulNode.slideToggle();
				if ($(this).hasClass('shrink')) {
					$(this).removeClass('shrink');
				} else {
					$(this).addClass('shrink');
				}
        });
    });
});

</script>

<div class="ncm-container">
  <div class="ncm-header">
    <div class="ncm-header-top">
      <div class="ncm-member-info">
        <div class="avatar"><a href="<?php echo urlMember('member_information', 'avatar');?>" title="修改头像"><img src="<?php echo getMemberAvatar($output['member_info']['member_avatar']);?>">
          <div class="frame"></div>
          </a>
          <?php if (intval($output['message_num']) > 0){ ?>
          <a href="<?php echo MEMBER_SITE_URL?>/index.php?act=member_message&op=message" class="new-message" title="新消息"><?php echo intval($output['message_num']); ?></a>
          <?php }?>
        </div>
        <dl>
          <dt class="menber-icon"><a href="<?php echo urlMember('member_information', 'member');?>" title="修改资料"><?php echo $output['member_info']['member_name'];?></a></dt>
          <dd>采购商信息：
            <div><?php echo $output['purchaser_info']['company_name'];?></div>
          </dd>
        </dl>
      </div>
    </div>
    <div class="ncm-header-nav">
      <ul class="nav-menu">
        <li><a href="<?php echo urlShop('member', 'home');?>" class="current">我的采购中心</a></li>
      </ul>
      <div class="notice">
        <ul class="line">
          <?php if (is_array($output['system_notice']) && !empty($output['system_notice'])) { ?>
          <?php foreach ($output['system_notice'] as $v) { ?>
          <li><a <?php if($v['article_url']!=''){?>target="_blank"<?php }?> href="<?php if($v['article_url']!='')echo $v['article_url'];else echo urlMember('article', 'show', array('article_id'=>$v['article_id']));?>"><?php echo $v['article_title']?>
            <time>(<?php echo date('Y-m-d',$v['article_time']);?>)</time>
            </a> </li>
          <?php } ?>
          <?php } ?>
        </ul>
      </div>
      <script>
$(function() {
    var _wrap = $('ul.line');
    var _interval = 2000;
    var _moving;
    _wrap.hover(function() {
        clearInterval(_moving);
    },
    function() {
        _moving = setInterval(function() {
            var _field = _wrap.find('li:first');
            var _h = _field.height();
            _field.animate({
                marginTop: -_h + 'px'
            },
            600,
            function() {
                _field.css('marginTop', 0).appendTo(_wrap);
            })
        },
        _interval)
    }).trigger('mouseleave');
});
</script> 
    </div>
  </div>
  <div class="left-layout">
    <ul id="sidebarMenu" class="ncm-sidebar">
      <?php if (!empty($output['menu_list'])) {?>
      <?php foreach ($output['menu_list'] as $key => $value) {?>
      <li class="side-menu"><a href="javascript:void(0)" key="<?php echo $key;?>" <?php if (cookie('Mmenu_'.$key) == 1) echo 'class="shrink"';?>>
        <h3><?php echo $value['name'];?></h3>
        </a>
        <?php if (!empty($value['child'])) {?>
        <ul <?php if (cookie('Mmenu_'.$key) == 1) echo 'style="display:none"';?>>
          <?php foreach ($value['child'] as $key => $val) {?>
          <li <?php if ($key == $output['act']) {?>class="selected"<?php }?>><a href="<?php echo $val['url'];?>"><?php echo $val['name'];?></a></li>
          <?php }?>
        </ul>
        <?php }?>
      </li>
      <?php }?>
      <?php }?>
    </ul>
  </div>
  <div class="right-layout">
    <?php require_once($tpl_file);?>
  </div>
  <div class="clear"></div>
</div>
<?php require_once template('footer');?>
</body></html>