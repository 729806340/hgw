
<div class="ncap-about">
  <div class="left-pic"></div>
  <div class="version">
    <h2>汉购网S版 电商平台系统</h2>
    <h4><?php echo $output['v_date'];?>版</h4>
    <hr>
    <h5>安装日期：<?php echo $output['s_date'];?></h5>
  </div>
  <div class="content">
    <div class="scroll switchbox" >
      <ul class="tema">
        <li>
          <h4>源码提供</h4>
          <p>汉购网</p>
		  <p>网址：<a href="http://www.hangowa.com" target="_blank">www.hangowa.com</a></p>
        </li>
      </ul>
    </div>
    <!-- 代码结束 -->
    <div class="scrollbar switchbox" style="display: none;">
      <div class="law-notice">
        <p>版权所有 (c) 2007-2015，汉购网交流中心www.hangowa.com。
          感谢您选择汉购网b2b2c电商平台系统。希望我们的努力能为您提供一个高效快速和强大的企业级电子商务整体解决方案。</p>
        <p>汉购网S版系统是根据网创系统进化而来！汉购网对本版本不负任何法律责任！汉购网官方网站网址为 <a href="http://www.hangowa.com" target="_blank">http://www.hangowa.com</a>。</p>
        <p>为了你更好的使用本系统！请加入QQ群</p>
        <p>尊爱网络环境！禁止非法传播。</p>
      </div>
    </div>
    <div class="switchbox" style="display:none;" >
      <ul>
        <li>
          <h4><?php echo $lang['dashboard_aboutus_idea'];?></h4>
          <p><?php echo $lang['dashboard_aboutus_idea_content'];?></p>
        </li>
        <li>
          <h4>关注我们</h4>
          <p><?php echo $lang['dashboard_aboutus_website'];?> <a href="http://www.hangowa.com" target="_blank">http://www.hangowa.com</a></p>
          <p><?php echo $lang['dashboard_aboutus_website_tip'];?></p>
        </li>
        <li>
          <h4><?php echo $lang['dashboard_aboutus_notice'];?></h4>
          <p><?php echo $lang['dashboard_aboutus_notice4'];?>&nbsp;:&nbsp;&nbsp;jQuery,kindeditor<?php echo $lang['dashboard_aboutus_notice5'];?>.&nbsp;<?php echo $lang['dashboard_aboutus_notice6'];?> </p>
        </li>
      </ul>
    </div>
  </div>
  <div class="btns"><a href="javascript:void(0);" onClick="about_change(0)" class="ncap-btn ncap-btn-green">开发团队</a><a href="javascript:void(0);" onClick="about_change(1)" class="ncap-btn">法律声明</a><a href="javascript:void(0);" onClick="about_change(2)" class="ncap-btn">致用户</a></div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.scroll.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript">
$(function(){
	$("div.scroll").myScroll({
		speed:30,
		rowHeight:60
	});
	$("div.scrollbar").perfectScrollbar();
});

function about_change(i) {
    $(".switchbox").hide().eq(i).show();
    $(".btns > a").removeClass("ncap-btn-green").eq(i).addClass("ncap-btn-green");
    if (i == 0) {
        $("div.scroll").myScroll({
            speed:30,
            rowHeight:60
        });
    }
}
</script>