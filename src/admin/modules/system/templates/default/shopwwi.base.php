<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <div class="subject">
        <h3><?php echo $lang['shopwwi_set'];?></h3>
        <h5><?php echo $lang['shopwwi_set_subhead'];?></h5>
      </div>
      <?php echo $output['top_link'];?> </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>在这里可以设置汉购网开发的一些基本功能。</li>
    </ul>
  </div>
  <form method="post" enctype="multipart/form-data" name="form1">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="shopwwi_stitle"><?php echo $lang['shopwwi_stitle'];?></label>
        </dt>
        <dd class="opt">
          <input id="shopwwi_stitle" name="shopwwi_stitle" value="<?php echo $output['list_setting']['shopwwi_stitle'];?>" class="input-txt" type="text" />
          <p class="notic"><?php echo $lang['shopwwi_stitle_notice'];?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="shopwwi_phone"><?php echo $lang['shopwwi_phone'];?></label>
        </dt>
        <dd class="opt">
          <input id="shopwwi_phone" name="shopwwi_phone" value="<?php echo $output['list_setting']['shopwwi_phone'];?>" class="input-txt" type="text" />
          <p class="notic"><?php echo $lang['shopwwi_phone_notice'];?></p>
        </dd>
      </dl>
            <dl class="row">
        <dt class="tit">
          <label for="shopwwi_time"><?php echo $lang['shopwwi_time'];?></label>
        </dt>
        <dd class="opt">
          <input id="shopwwi_time" name="shopwwi_time" value="<?php echo $output['list_setting']['shopwwi_time'];?>" class="input-txt" type="text" />
          <p class="notic"><?php echo $lang['shopwwi_time_notice'];?></p>
        </dd>
      </dl>
       <dl class="row">
        <dt class="tit">
          <label for="shopwwi_invite2">二级佣金比</label>
        </dt>
        <dd class="opt">
          <input id="shopwwi_invite2" name="shopwwi_invite2" value="<?php echo $output['list_setting']['shopwwi_invite2'];?>" class="w60" type="text" /><i>%</i>
          <p class="notic">二级佣金=1级佣金*二级佣金比</p>
        </dd>
      </dl>
             <dl class="row">
        <dt class="tit">
          <label for="shopwwi_invite3">三级佣金比</label>
        </dt>
        <dd class="opt">
          <input id="shopwwi_invite3" name="shopwwi_invite3" value="<?php echo $output['list_setting']['shopwwi_invite3'];?>" class="w60" type="text" /><i>%</i>
          <p class="notic">三级佣金=1级佣金*三级佣金比</p>
        </dd>
      </dl>
       
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" onclick="document.form1.submit()"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>