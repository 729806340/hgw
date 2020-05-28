<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=goods" title="返回商品列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>商品管理 - 查看商品详情</h3>
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
        <th class="w150">商品广告词：</th>
        <td colspan="20"><?php echo $output['commonInfo']['goods_jingle'];?></td>
      </tr>
      <tr>
        <th>所在店铺：</th>
        <td><a href="index.php?act=store&op=store_joinin_detail&member_id=<?php echo $output['store_info']['member_id'];?>"><?php echo $output['commonInfo']['store_name'];?></a></td>
        <th>商品分类：</th>
        <td><?php echo $output['commonInfo']['gc_name'];?></td>
        <th>品牌：</th>
        <td><?php echo $output['commonInfo']['brand_name'];?></td>
      </tr>
      <tr>
        <th>上架状态：</th>
        <td><?php echo $output['commonInfo']['goods_state']=='1'?'正常':(
          $output['commonInfo']['goods_state']=='10'?'违规':'下架'
          );?></td>
        <th>锁定状态：</th>
        <td><?php echo $output['commonInfo']['goods_lock']=='1'?'已锁':'未锁定';?></td>
        <th>审核状态：</th>
        <td><?php echo $output['commonInfo']['goods_verify']=='1'?'通过':(
          $output['commonInfo']['goods_verify']=='10'?'审核中':'未通过'
          );?></td>
      </tr>
      <tr>
        <th>商品价格：</th>
        <td><?php echo ncPriceFormat($output['commonInfo']['goods_price']);?></td>
        <th>市场价：</th>
        <td colspan="20"><?php echo ncPriceFormat($output['commonInfo']['goods_marketprice']);?></td>
      </tr>
      <tr>
        <th>进项税：</th>
        <td><?php echo $output['commonInfo']['tax_input'];?></td>
        <th>销项税：</th>
        <td colspan="20"><?php echo $output['commonInfo']['tax_output'];?></td>
      </tr>
      <tr>
        <th class="w150">商品图片：</th>
        <td colspan="20"><?php if(!empty($output['commonInfo']['goods_image'])){?><a target="_blank" href="<?php echo cthumb($output['commonInfo']['goods_image'],360);?>"><img src="<?php echo cthumb($output['commonInfo']['goods_image'],360);?>" alt=""></a><?php }else{echo '暂未上传商品图片';}?></td>
      </tr>
      <tr>
        <th class="w150">商品资质证明：</th>
        <td colspan="20"><?php if(!empty($output['commonInfo']['certification'])){

                $certification = $output['commonInfo']['certification'];
                $certifications = explode(',', $certification);
                foreach ($certifications as $k => $v) {
                    //if (preg_match("/(20[123][0-9].[a-zA-Z0-9_].(jpg|png|jpeg|gif|bmp))/i",$v,$match)){
                    if (preg_match("/(20[123][0-9]\/[a-zA-Z0-9_]+\.(jpg|png|jpeg|gif|bmp))/i",$v,$match)){
                        $v = $match[1];
                    }
                    echo '<a target="_blank" style="margin-right: 5px;" href="' . UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $output['commonInfo']['store_id'] . '/' . $v . '" ><img nctype="certification" data-name="' . $v . '" src="' . UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $output['commonInfo']['store_id'] . '/' . $v . '" /></a>';
                }

                ?>
            <!--<a target="_blank" href="<?php echo $output['commonInfo']['certification'];?>"><img src="<?php echo $output['commonInfo']['certification'];?>" alt=""></a>-->
            <?php }else{echo '暂未上传资质文件';}?></td>
      </tr>

    </tbody>
  </table>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">SKU信息</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($output['goods_list'] as $goodsInfo){ ?>
      <tr>
        <th class="w150">商品名称：</th>
        <td colspan="20"><?php echo $goodsInfo['goods_name'];?></td>
      </tr>
      <tr>
        <th class="w150">商品售价：</th>
        <td ><?php echo ncPriceFormat($goodsInfo['goods_price']);?></td>
        <th class="w150">促销价：</th>
        <td ><?php echo ncPriceFormat($goodsInfo['goods_promotion_price']);?></td>
        <th class="w150">市场价：</th>
        <td ><?php echo ncPriceFormat($goodsInfo['goods_marketprice']);?></td>
      </tr>
      <tr>
        <th>商品成本：</th>
        <td><?php echo ncPriceFormat($goodsInfo['goods_cost']);?></td>
        <th>进项税：</th>
        <td><?php echo $goodsInfo['tax_input'];?></td>
        <th>销项税：</th>
        <td><?php echo $goodsInfo['tax_output'];?></td>
      </tr>
      <tr>
          <th>上架状态：</th>
          <td><?php echo $goodsInfo['goods_state']=='1'?'正常':(
              $goodsInfo['goods_state']=='10'?'违规':'下架'
              );?></td>
          <th>审核状态：</th>
          <td><?php echo $goodsInfo['goods_verify']=='1'?'通过':(
              $goodsInfo['goods_verify']=='10'?'审核中':'未通过'
              );?></td>
          <th>库存数量：</th>
          <td><?php echo $goodsInfo['goods_storage'];?></td>
      </tr>

    <?php }?>
    </tbody>
  </table>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">商家信息（<?php echo $output['store_info']['store_name']?>）<a href="index.php?act=store&op=store_joinin_detail&member_id=<?php echo $output['store_info']['member_id'];?>">【查看注册信息】</a></th>
      </tr>
    </thead>
    <tbody>
    <tr>
        <th>店主账号：</th>
        <td><?php echo $output['store_info']['member_name'];?></td>
        <th>店铺类型名称：</th>
        <td><?php switch ($output['store_info']['manage_type']){
                case 'platform':echo '平台商家';break;
                case 'co_construct':echo '共建商家';break;
                case 'b2b':echo '集采商家';break;
                default: echo '未设定';break;
            };?></td>
        <th>开店时间：</th>
        <td><?php echo date('Y-m-d',$output['store_info']['store_time']);?></td>
    </tr>
    <tr>
        <th>联系电话：</th>
        <td><?php echo $output['store_info']['store_phone'];?></td>
        <th>QQ：</th>
        <td><?php echo $output['store_info']['store_qq'];?></td>
        <th>公司名称：</th>
        <td><?php echo $output['store_info']['store_company_name'];?></td>
    </tr>

    </tbody>
  </table>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">商家资质认证信息</th>
      </tr>
    </thead>
    <tbody>
    <?php if(empty($output['store_certifications'])){
        echo '<tr><td>暂无认证信息</td></tr>';
    }else foreach ($output['store_certifications'] as $certification){ ?>
      <tr>
        <th>认证名称：</th>
        <td><?php echo ($certification['name']);?></td>
        <th>认证描述：</th>
        <td><?php echo $certification['description'];?></td>
        <th>认证图片：</th>
        <td><?php if(!empty($certification['content'])){?><a target="_blank" href="<?php echo $certification['content'];?>"><img src="<?php echo $certification['content'];?>" alt=""></a><?php }?></td>
      </tr>

    <?php }?>
    </tbody>
  </table>

  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">商品详情</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
    <?php echo ($output['commonInfo']['goods_body']);?>


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