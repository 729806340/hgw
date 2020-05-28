<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=tuan_config&op=tuanzhang_list&config_tuan_id=<?php echo $output['tuanzhang_info']['config_id']?>" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>团购</h3>
                <h5>团购设置与管理</h5>
            </div>
        </div>
    </div>
    <div class="explanation" id="explanation">
        <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
            <h4 title="提示相关设置操作时应注意的要点">操作提示</h4>
            <span id="explanationZoom" title="收起提示"></span> </div>
        <ul>
            <li>团长司机分配</li>
        </ul>
    </div>
    <form id="store_class_form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_submit" value="ok" />
        <div class="ncap-form-default">
            <dl class="row">
                <dt class="tit">
                    <label for="config_xianshi_name"><em>*</em>团长名称</label>
                </dt>
                <dd class="opt">
                    <input type="text" disabled="disabled" value="<?php echo $output['tuanzhang_info']['tz_name']?>" name="config_xianshi_name" id="config_xianshi_name" class="input-txt">
                </dd>
            </dl>
            <dl class="row">
                <dt class="tit">
                    <label for="config_xianshi_phone"><em>*</em>团长电话</label>
                </dt>
                <dd class="opt">
                    <input type="text" disabled="disabled" value="<?php echo $output['tuanzhang_info']['tz_phone']?>" name="config_xianshi_phone" id="config_xianshi_phone" class="input-txt">
                </dd>
            </dl>

            <dl class="row">
                <dt class="tit">
                    <label for="config_xianshi_address">收货地址</label>
                </dt>
                <dd class="opt">
                    <input type="text" disabled="disabled" value="<?php echo $output['tuanzhang_info']['address']?>" name="config_xianshi_address" id="config_xianshi_address" class="input-txt">
                </dd>
            </dl>

            <dl class="row">
                <dt class="tit">
                    <label for="config_xianshi_driver">选择司机</label>
                </dt>
                <dd class="opt">
                    <select style = "width:300px;" name="config_xianshi_driver" id="config_xianshi_driver" >
                        <option value ="0">请选择司机</option>
                     <?php
                      foreach ($output['driver_list'] as $val){
                        echo  "<option value =\"{$val['driver_id']}\">{$val['driver_name']}---{$val['driver_phone']}---{$val['driver_car_number']}</option>";
                      }
                     ?>
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
                alert('团长名称不能为空！');return false;
            }
            if ($('#config_xianshi_phone').val() == '') {
                alert('团长电话不能为空！');return false;
            }
            if ($('#config_xianshi_address').val() == '') {
                alert('团长地址不能为空！');return false;
            }
            if ($('#config_xianshi_driver').val() == 0) {
                alert('请选择配送司机！');return false;
            }
            $("#store_class_form").submit();
        });
        function insert_editor(file_path) {
            KE.appendHtml('article_content', '<img src="' + file_path + '" alt="' + file_path + '">');
        }
    });

</script>

