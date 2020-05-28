<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=promotion_xianshi&op=config_xianshi_list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台限时活动</h3>
        <h5>平台限时活动设置与管理</h5>
      </div>
    </div>
  </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>
            <li>平台可以在此处添加平台活动</li>
            <li>新增的平台活动可以提供给商家进行活动绑定</li>
        </ul>
    </div>
  <form id="store_class_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_name"><em>*</em>活动名称</label>
        </dt>
        <dd class="opt">
          <input readonly style="background:#E7E7E7 none;" type="text" value="<?php echo $output['config_xianshi_info']['config_xianshi_name']?>" name="config_xianshi_name" id="config_xianshi_name" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_title">活动标题</label>
        </dt>
        <dd class="opt">
          <input readonly style="background:#E7E7E7 none;" type="text" value="<?php echo $output['config_xianshi_info']['config_xianshi_title']?>" name="config_xianshi_title" id="config_xianshi_title" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_explain">活动描述</label>
        </dt>
        <dd class="opt">
          <input readonly style="background:#E7E7E7 none;" type="text" value="<?php echo $output['config_xianshi_info']['config_xianshi_explain']?>" name="config_xianshi_explain" id="config_xianshi_explain" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>开始时间</label>
        </dt>
        <dd class="opt">
            <input readonly style="background:#E7E7E7 none;" id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="<?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_start_time'])?>" type="text" class="s-input-txt" />
          <span class="err"></span>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>结束时间</label>
        </dt>
        <dd class="opt">
            <input readonly style="background:#E7E7E7 none;" id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="<?php echo date('Y-m-d H:i', $output['config_xianshi_info']['config_end_time'])?>" type="text" class="s-input-txt" />
          <span class="err"></span>
        </dd>
      </dl>
    </div>
  </form>
</div>