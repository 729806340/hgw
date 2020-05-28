<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=config_tuan_list" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>社区团购活动</h3>
        <h5>社区团购活动设置与管理</h5>
      </div>
    </div>
  </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>
            <li>平台可以在此处添加社区团购</li>
            <li>新增的社区团购可以提供用户选用</li>
        </ul>
    </div>
  <form id="store_class_form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_name"><em>*</em>活动名称</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="config_xianshi_name" id="config_xianshi_name" class="input-txt">
          <span class="err"></span>
          <p class="notic">活动名称将显示在社区团购活动列表中，方便用户管理使用，最多可输入25个字符。</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_title">活动标题</label>
        </dt>
        <dd class="opt">
          <input type="text" value="" name="config_xianshi_title" id="config_xianshi_title" class="input-txt">
          <span class="err"></span>
          <p class="notic">请使用例如“新品打折”、“月末折扣”类短语表现，最多可输入10个字符； 非必填选项。</p>
        </dd>
      </dl>

        <dl class="row">
            <dt class="tit">
                <label for="site_logo"><em>*</em>海报</label>
            </dt>
            <dd class="opt">
                <div class="input-file-show">
                    <span class="show"></span>
                    <span class="type-file-box">
                        <input type="text" name="textfield2" id="textfield2" class="type-file-text" />
                        <input type="button" name="button2" id="button2" value="选择上传..." class="type-file-button" />
                        <input class="type-file-file" id="member_logo" name="member_logo" type="file" size="30" hidefocus="true"  title="点击按钮选择文件并提交表单后上传生效">
                    </span>
                </div>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">
                <label for="site_logo"><em>*</em>海报二维码</label>
            </dt>
            <dd class="opt">
                <div class="input-file-show">
                    <span class="show"></span>
                    <span class="type-file-box">
                        <input type="text" name="textfield3" id="textfield3" class="type-file-text" />
                        <input type="button" name="button3" id="button3" value="选择上传..." class="type-file-button" />
                        <input class="type-file-file" id="member_logo_er"  name="member_logo_er" type="file" size="30" hidefocus="true"  title="点击按钮选择文件并提交表单后上传生效">
                    </span>
                </div>
            </dd>
        </dl>

      <dl class="row">
        <dt class="tit">
          <label for="config_xianshi_explain"><em>*</em>活动描述</label>
        </dt>
        <dd class="opt">
            <?php showAdminEditor('article_content');?>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>开始时间</label>
        </dt>
        <dd class="opt">
            <input readonly id="query_start_date" placeholder="请选择起始时间" name=query_start_date value="" type="text" class="s-input-txt" />
          <span class="err"></span>
          <p class="notic">开始时间不能为空</p>
        </dd>
      </dl>
        <dl class="row">
        <dt class="tit">
          <label for="sc_sort"><em>*</em>结束时间</label>
        </dt>
        <dd class="opt">
            <input readonly id="query_end_date" placeholder="请选择结束时间" name="query_end_date" value="" type="text" class="s-input-txt" />
          <span class="err"></span>
          <p class="notic">结束时间不能为空</p>
        </dd>
      </dl>

        <dl class="row">
            <dt class="tit">
                <label for="sc_sort"><em>*</em>发货时间</label>
            </dt>
            <dd class="opt">
                <input readonly id="send_product_date" placeholder="发货时间" name="send_product_date" value="" type="text" class="s-input-txt" />
                <span class="err"></span>
                <p class="notic">发货时间不能为空</p>
            </dd>
        </dl>

        <dl class="row">
            <dt class="tit">
                <label for="sc_sort"><em>*</em>类型</label>
            </dt>
            <dd class="opt">
                <select name="type">
                    <option value="0">请选择类型</option>
                    <option value="1">物流</option>
                    <option value="2">自提</option>
                </select>
            </dd>
        </dl>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script>
//按钮先执行验证再提交表单
$(function() {

    $("#member_logo").change(function(){
        $("#textfield2").val($(this).val());
    });

    $("#member_logo_er").change(function(){
        $("#textfield3").val($(this).val());
    });

    // 上传图片类型
    $('input[class="type-file-file"]').change(function(){
        var filepath=$(this).val();
        var extStart=filepath.lastIndexOf(".");
        var ext=filepath.substring(extStart,filepath.length).toUpperCase();
        if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
            alert("<?php echo $lang['default_img_wrong'];?>");
            $(this).attr('value','');
            return false;
        }
    });

    $('#query_start_date').datetimepicker({
        controlType: 'select'
    });

    $('#query_end_date').datetimepicker({
        controlType: 'select'
    });
    $('#send_product_date').datetimepicker({
        controlType: 'select'
    });
    $("#submitBtn").click(function() {
        if ($('#config_xianshi_name').val() == '') {
            alert('请输入活动名称！');return false;
        }
        $("#store_class_form").submit();
    });
    function insert_editor(file_path) {
        KE.appendHtml('article_content', '<img src="' + file_path + '" alt="' + file_path + '">');
    }
});

</script>