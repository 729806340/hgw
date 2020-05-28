<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=goods" title="返回商品列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>商品管理 - 查看商品运费模板详情</h3>
        <h5><?php echo $output['commonInfo']['goods_name'];?></h5>
      </div>
    </div>
  </div>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">商品基本信息</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th class="w150">商品名称：</th>
        <td colspan="20"><?php echo $output['commonInfo']['goods_name'];?></td>
      </tr>
      <tr>
        <th>所在店铺：</th>
        <td><?php echo $output['commonInfo']['store_name'];?></td>
      </tr>
    </tbody>
  </table>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
      <?php if($output['transportInfo']){?>
    <thead>
      <tr>
        <th colspan="20">运费模板:<?php echo $output['commonInfo']['transport_title']?></th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($output['transportInfo'] as $transport){ ?>
      <tr>
        <th class="w150">销售区域：</th>
        <td colspan="10"><?php echo $transport['area_name'];?></td>
        <th class="w150">运费：</th>
        <td colspan="10"><?php echo ncPriceFormat($transport['sprice']);?></td>
      </tr>
        <?php } ?>
    </tbody>
      <?php } else{ ?>
          <thead>
          <tr>
              <th colspan="20">此商品未使用运费模板</th>
          </tr>
          </thead>
      <?php } ?>
  </table>


</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('a[nctype="nyroModal"]').nyroModal();

        $('#btn_fail').on('click', function() {
            if($('#joinin_message').val() == '') {
                $('#validation_message').text('请输入审核意见');
                $('#validation_message').show();
                return false;
            } else {
                $('#validation_message').hide();
            }
            if(confirm('确认拒绝申请？')) {
                $('#verify_type').val('fail');
                $('#form_store_verify').submit();
            }
        });
        $('#btn_pass').on('click', function() {
        	manage_type = $("#manage_type").val();
            if(manage_type=='unselect'){
            	$('#validation_message').text('请设置商家类型');
                $('#validation_message').show();
                return false;
            }
            var valid = true;
            $('[nctype="commis_rate"]').each(function(commis_rate) {
                rate = $(this).val();
                if(rate == '') {
                    valid = false;
                    return false;
                }

                var rate = Number($(this).val());
                if(isNaN(rate) || rate < 0 || rate >= 100) {
                    valid = false;
                    return false;
                }
            });
            
            if(valid) {
                $('#validation_message').hide();
                //if(confirm('确认通过申请？')) {
                    $('#verify_type').val('pass');
                    $('#form_store_verify').submit();
                //}
            } else {
                $('#validation_message').text('请正确填写分佣比例');
                $('#validation_message').show();
            }
        });
    });
</script>