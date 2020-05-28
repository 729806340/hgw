<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=cms_special_widgetpl&op=index" title="返回专题列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>专题商品模板 -  新增模板</h3>
        <h5>专题商品模板新增与编辑</h5>
      </div>
    </div>
  </div>
  <form id="add_form" method="post" enctype="multipart/form-data" action="index.php?act=cms_special_widgetpl&op=widgetpl_save">
    <input name="id" type="hidden" value="<?php if(!empty($output['detail'])) echo $output['detail']['id'];?>" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="special_title"><em>*</em>模板名称</label>
        </dt>
        <dd class="opt">
          <input id="tpl_name" name="tpl_name" class="input-txt" type="text" value="<?php if(!empty($output['detail'])) echo $output['detail']['name'];?>"/>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
       <dl class="row">
        <dt class="tit">
          <label for="tpl_content"><em>*</em>模板内容</label>
        </dt>
        <dd class="opt">
          <textarea cols="50" rows="50" style="min-width:600px;min-height:500px;" name="tpl_content"><?php echo $output['detail']['content'];?></textarea>
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
		<dl class="row">
        <dt class="tit">
          &nbsp;
        </dt>
        <dd class="opt">
          <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-blue" id="btn_submit">保存</a> </div>
        </dd>
      </dl>
    </div>
    
    
  </form>

</div>

<script>
$(document).ready(function(){
	$("#btn_submit").click(function(){
		$("form").submit();
	  }); 
});
</script>