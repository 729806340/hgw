<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=config_tuan_list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>社区团购</h3>
        <h5>社区团购活动设置与管理</h5>
      </div>
    </div>
  </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>
            <li></li>
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
          <input readonly style="background:#E7E7E7 none;" type="text" value="<?php echo $output['config_xianshi_info']['config_tuan_name']?>" name="config_xianshi_name" id="config_xianshi_name" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_title">活动标题</label>
        </dt>
        <dd class="opt">
          <input readonly style="background:#E7E7E7 none;" type="text" value="<?php echo $output['config_xianshi_info']['config_tuan_title']?>" name="config_xianshi_title" id="config_xianshi_title" class="input-txt">
          <span class="err"></span>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">
                <label for="config_xianshi_title"><em>*</em>海报</label>
            </dt>
            <dd class="opt">
                <div style="width: 80px;height: 40px"><img style="width: 80px;height: 40px" src="<?php echo  UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$output['config_xianshi_info']['config_pic']?>"></div>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">
                <label for="config_xianshi_title"><em>*</em>海报二维码</label>
            </dt>
            <dd class="opt">
                <div style="width: 80px;height: 40px"><img style="width: 80px;height: 40px" src="<?php echo  UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$output['config_xianshi_info']['config_pic_er']?>"></div>
            </dd>
        </dl>

      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_explain"><em>*</em>活动描述</label>
        </dt>
        <dd class="opt">
            <?php showEditor('article_content', $output['config_xianshi_info']['config_tuan_description']);?>
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

        <dl class="row">
            <dt class="tit">
                <label for="sc_sort"><em>*</em>发货时间</label>
            </dt>
            <dd class="opt">
                <input readonly style="background:#E7E7E7 none;" id="send_product_date" placeholder="请选择发货时间" name="send_product_date" value="<?php echo $output['config_xianshi_info']['send_product_date']  ? date('Y-m-d H:i', $output['config_xianshi_info']['send_product_date']) : ''?>" type="text" class="s-input-txt" />
                <span class="err"></span>
            </dd>
        </dl>
    </div>
  </form>
</div>
<script>
    //按钮先执行验证再提交表单
    $(function() {
        function insert_editor(file_path) {
            KE.appendHtml('article_content', '<img src="' + file_path + '" alt="' + file_path + '">');
        }
    });

</script>