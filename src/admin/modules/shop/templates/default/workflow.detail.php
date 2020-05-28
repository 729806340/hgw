<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style type="text/css">
.d_inline {
	display: inline;
}
.input-file-show{
	vertical-align: top;display: inline-block;width: 80px;height: 30px;margin: 5px 5px 0 0;
}
.input-file-show a{display: block;position: relative;z-index: 1}
.input-file-show span{width: 80px; height: 30px;position: absolute;left: 0;top: 0;z-index: 2;cursor: pointer;}
.input-file{width: 80px;height: 30px;padding: 0;margin: 0;border: none 0;opacity: 0;filter: alpha(opacity=0);cursor: pointer;}
.upload-image{float:left; width:150px; height:80px; margin-right:5px;}
.upload-image img{ max-height:80px; max-width:150px;}
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3><?php echo $output['workflow']['title'];?>详情</h3>
      </div>
    </div>
  </div>
  <div class="ncap-form-default">
    <div class="title">
      <h3>审批起始详情</h3>
    </div>
    <dl class="row">
        <dt class="tit">审批编号</dt>
        <dd class="opt">
        <?php echo $output['workflow']['id'] ?>
 		</dd>
      </dl>
      <dl class="row">
        <dt class="tit">申请时间</dt>
        <dd class="opt"><?php echo date('Y-m-d H:i' , $output['workflow']['created_at']) ?> </dd>
      </dl>
    <dl class="row">
      <dt class="tit">发起人类型</dt>
      <dd class="opt">
          <?php echo $output['workflow']['role']?>
        </dd>
    </dl>
    <dl class="row">
      <dt class="tit">发起人</dt>
      <dd class="opt"><?php echo $output['workflow']['user']?></dd>
    </dl>
     <dl class="row">
      <dt class="tit">审核状态</dt>
      <dd class="opt"><?php echo $output['workflow']['status']?>
      </dd>
    </dl>
     <dl class="row">
      <dt class="tit">当前节点</dt>
      <dd class="opt"><?php echo $output['workflow']['stage']?>
      </dd>
    </dl>

    <div class="title">
      <h3>修改内容</h3>
    </div>
    <?php
    echo $output['view'];
    $attributes = $output['attributes'];
    ?>
    <?php if ($output['workflow']['reference']){?>
    <dl class="row">
      <dt class="tit">引用链接</dt>
      <dd class="opt"><a href="<?php echo $output['workflow']['reference']?>" target="_blank">点击前往</a>
      </dd>
    </dl>
    <?php }?>
    <div class="title">
      <h3>审核日志</h3>
    </div>
    <?php foreach($output['workflow']['log'] as $k => $v){?>
    <dl class="row">
      <dt class="tit">审核节点：<?php echo $v['stage']?></dt>
      <dd class="opt">审核人：<?php echo $v['user'];?>&nbsp;&nbsp;
      审核结果： <?php if($v['opinion']=='1'){ echo '同意';} else{ echo '不同意' ;}?>&nbsp;&nbsp;
      审核时间：<?php echo date('Y-m-d H:i' , $v['created_at']);?>
      </dd>
    </dl>
    
    <?php if(count($v['attachment']) > 0){?>
    <?php foreach($v['attachment'] as $key=>$item){?>
    <dl class="row">
      <dt class="tit"><?php echo $attributes[$key]['label'];?></dt>
      <dd class="opt">
        <?php if(!empty($item)){?>
        <a href="<?php echo $item;?>" target="_blank"><img style="max-height:70px; max-width:150px;" src="<?php echo $item;?>"></a>
        <?php }?>
      </dd>
    </dl>
    <?php }?>
    <?php }?>
    <dl class="row">
      <dt class="tit">处理备注</dt>
      <dd class="opt"><?php echo $v['message']?> </dd>
    </dl>
    <?php }?>
    <?php if($output['gname']==$output['workflow']['stage']){
        echo $output['form'];
    ?>
    <!--<div class="title">
      <h3>审批审核</h3>
    </div>
    <dl class="row">
      <dt class="tit">是否同意</dt>
      <dd class="opt"><input type="radio" name="opinion" value="1" checked="checked"> 同意 &nbsp; <input type="radio" name="opinion" value="0"> 不同意</dd>
    </dl>
    <dl class="row">
      <dt class="tit">审核意见</dt>
      <dd class="opt"><textarea id="message" name="message"></textarea></dd>
    </dl>
    <div class="bot">
	<a href="JavaScript:;" nc_type="submit_btn" class="ncap-btn-big ncap-btn-green">确认提交</a>
	</div>
   </dl>
  </div>-->
  <?php }?>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
$(function(){
    $('.nyroModal').nyroModal();

    
		
	$('a[nc_type="submit_btn"]').click(function(){
		var op = handleAction?handleAction:'reduce_workflow';
	    var opinion = $('input[name="opinion"]:checked').val();
		var message = $("#message").val();
		if(message.length <2 || message.length> 150){
			alert('审核意见的长度在2-150个字符之间');
			return false;
		}
		var data = {
				opinion:opinion,
				message:message,
				id:<?php echo $output['workflow']['id']?>
		};
	    $('input[nc_type="text"]').each(function(){
			data[$(this).data('type')]=$(this).val();
		});
	    $('select[nc_type="select"]').each(function(){
			data[$(this).data('type')]=$(this).val();
		});
		$.ajax({
			    dataType:'json',
				url:"index.php?act=workflow&op="+op,
				type:"post",
				data:data,
            	success:function(data){
               	   if(data.state ==true){
                   	   alert('审核提交成功！');
                   	   location.href='index.php?act=workflow';
                   }else{
                       alert(data.msg);
                   }
            	}
          });
	})
    var upaction = '';
	//上传凭证
	$('input[nc_type="upload_sign"]').each(function(){
		 upaction = $(this).data('upload')?$(this).data('upload'):'upload';
		 $(this).fileupload({
			dataType: 'json',
			url: 'index.php?act=workflow&op='+upaction,
			formData: '',
			done: function (e,data) {
				if(data.result.state== "true"){
					var upload_dir = "<?php echo SHOP_SITE_URL.DS.DIR_UPLOAD;?>";
					var type  = $(this).data('type');
					var file_path = upload_dir+"/"+data.result.file_path;
					var img = "<img src=\""+file_path+"\">";
					$("#show-"+type+"-image").html(img);
					$("#"+type).val(file_path);
				}
			},
   		});
	});
});
</script>