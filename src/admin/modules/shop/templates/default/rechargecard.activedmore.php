<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="<?php echo urlAdminShop('rechargecard', 'index'); ?>" title="返回平台充值卡列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台充值卡 - 批量激活</h3>
        <h5>商城充值卡设置生成及用户充值使用明细</h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li>只能上传txt文件。文件格式为每行一条“卡号，密码”，例如：</li>
	  <li>3182725322400,8888</li>
      <li>3182724822400,8888</li>
      <li>3182715921900,2222</li>
    </ul>
  </div>
  <form method="post" enctype="multipart/form-data" name="form_add" id="form_add">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
<!--     <dl class="row">-->
<!--        <dt class="tit">-->
<!--          <label><em>*</em>销售方式</label>-->
<!--        </dt>-->
<!--        <dd class="opt">-->
<!--          <select name="isflag">-->
<!--             --><?php //foreach ($output['isflag'] as $k=>$v){?>
<!--                <option value="--><?php //echo $k?><!--" >--><?php //echo $v?><!--</option>-->
<!--             --><?php //}?>
<!--          </select>-->
<!--        </dd>-->
<!--      </dl>-->

<!--        <dl class="row">-->
<!--            <dt class="tit">-->
<!--                <label><em>*</em>领卡人</label>-->
<!--            </dt>-->
<!--            <dd class="opt">-->
<!--                <select name="receiver">-->
<!--                    --><?php //foreach ($output['receiver_list'] as $k=>$v){?>
<!--                        <option value="--><?php //echo $v['sn']?><!--" >--><?php //echo $v['receiver']?><!--</option>-->
<!--                    --><?php //}?>
<!--                </select>-->
<!--            </dd>-->
<!--        </dl>-->

      <dl class="row">
        <dd class="opt tabswitch-target">
          <div class="input-file-show"><span class="type-file-box">
            <input class="type-file-file" id="_textfile" name="_textfile" type="file" size="30" hidefocus="true" onchange="$('#textfile').val(this.value);"  title="点击按钮选择文件并提交表单后上传生效">
            <input type="text" name="textfile" id="textfile" class="type-file-text" />
            <input type="button" name="button" id="button" value="选择上传..." class="type-file-button" />
            </span></div>
        </dd>
      </dl>
      <div class="bot"><a href="javascript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script>
$("#submitBtn").click(function(){
    $("#form_add").submit();
});
</script>
