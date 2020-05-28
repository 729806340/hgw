<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="<?php echo urlAdminShop('rechargecard', 'index'); ?>" title="返回平台充值卡列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台充值卡 - 激活</h3>
        <h5>商城充值卡设置生成及用户充值使用明细</h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <form method="post" enctype="multipart/form-data" action="index.php?act=rechargecard&op=active_card" name="form_add" id="form_add">
    <input type="hidden" name="id" value="<?php echo $output['card']['id'] ?>" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label>卡号</label>
        </dt>
        <dd class="opt">
            <?php echo $output['card']['sn']; ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>卡号密码</label>
        </dt>
        <dd class="opt">
            <input class="txt" style="width:80px;" type="text" name="pwd" value="<?php echo $output['card']['pwd'];?>">
             <span class="err"></span>
          <p class="notic">请输入充值卡密码,长度不少于4个字符</p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>面额(元)</label>
        </dt>
        <dd class="opt">
           <?php echo $output['card']['denomination'] ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>批次标识</label>
        </dt>
        <dd class="opt">
           <?php echo $output['card']['batchflag'] ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>生成时间</label>
        </dt>
        <dd class="opt">
           <?php echo date('Y-m-d H:i',$output['card']['tscreated']) ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>创建人</label>
        </dt>
        <dd class="opt">
           <?php echo $output['card']['admin_name'] ?>
        </dd>
      </dl>
        <dl class="row">
            <dt class="tit">
                <label>领卡人</label>
            </dt>
            <dd class="opt">
                <select name="receiver">
                    <option value="0">请选择</option>
                    <?php foreach ($output['receiver_list'] as $k=>$v){?>
                        <option value="<?php echo $v['sn']?>" <?php if($output['card']['receiver']==$v['sn']){?>selected<?php }?>><?php echo $v['receiver']?></option>
                    <?php }?>
                </select>
            </dd>
        </dl>

      
      <dl class="row">
        <dt class="tit">
          <label>状态</label>
        </dt>
        <dd class="opt">
         <input type="radio" name="disabled"
         <?php if($output['card']['disabled']==0){?> checked="checked"<?php }?> 
          value="0">&nbsp;未激活  &nbsp;<input type="radio" name="disabled"
          <?php if($output['card']['disabled']==1){?> checked="checked"<?php }?>
           value="1">&nbsp;已激活
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>备注</label>
        </dt>
        <dd class="opt">
           <textarea name="memo" class="tarea" rows="6" ><?php echo $output['card']['memo']?></textarea>
           <span class="err"></span>
        </dd>
      </dl>
      <div class="bot"><a href="javascript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn">确认提交</a></div>
    </div>
  </form>
</div>
<script type="text/javascript">
$(function(){
$('.tabswitch').click(function() {
    var i = parseInt(this.value);
    $('.tabswitch-target').hide().eq(i).show();
});

$("#submitBtn").click(function(){
    $("#form_add").submit();
});

$("#form_add").validate({
    rules : {
        pwd:{
            required:true,
            minlength:4,
        },
   		memo:{
   			maxlength:150,
        }    
    },
    messages : {
        pwd:{
            required: '<i class="fa fa-exclamation-circle"></i>请输入充值卡密码',
            min : '<i class="fa fa-exclamation-circle"></i>密码长度不可以小于4个字符',
        },
        memo:{
             max:'<i class="fa fa-exclamation-circle"></i>备注信息不超过150个字符',
        }
    }
});
});
</script> 
