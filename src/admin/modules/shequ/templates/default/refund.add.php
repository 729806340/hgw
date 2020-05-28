<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=order" title="返回品牌列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>新增退款记录</h3>
        
      </div>
    </div>
  </div>
  <form id="refund_form" method="post" action="index.php?act=refund&op=add_refund"  enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>" />
    <input type="hidden" name="order_sn" value="<?php echo $output['order']['order_sn'] ; ?>" />
    <input type="hidden" name="order_state" value="<?php echo $output['order']['order_state'] ; ?>" />
    <input type="hidden" name="upload_img" id="upload_img">
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label>订单号：</label>
        </dt>
        <dd class="opt">
          <?php
          echo $output['order']['order_sn'] ; ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>下单日期</label>
        </dt>
        <dd class="opt">
          <?php echo date("Y-m-d H:i:s", $output['order']['add_time']); ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>订单金额</label>
        </dt>
        <dd class="opt">￥<?php echo $output['order']['order_amount'] ; ?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>已退款金额</label>
        </dt>
        <dd class="opt"><?php echo $output['order']['refund_amount']>0 ? "<font color='red'>￥".$output['order']['refund_amount']."</font>" : "0" ; ?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label>是否整单退款</label>
        </dt>
        <dd class="opt">
          <input type="radio" name="is_refund_all" style="margin-bottom:6px;" value="0" id="is_refund_all_0" onclick="show_tab(0)" <?php if($output['order']['order_state']=='20'){?>disabled="true"<?php }?> >
          <label for="show_type_0">否</label>
          <input type="radio" name="is_refund_all" style="margin-bottom:6px;" value="1" checked="" id="is_refund_all_1" onclick="show_tab(1)">
          <label for="show_type_1">是</label>
          <span class="err">(说明：未发货订单，只能整单退款)</span>
        </dd>
      </dl>
      <!-- <dl class="row">
        <dt class="tit"><em>*</em>退款方式</dt>
        <dd class="opt">
          <div id="gcategory">
            <input type="hidden" value="" name="class_id" class="mls_id">
            <input type="hidden" value="" name="brand_class" class="mls_name">
            <select class="class-select" name="refund_way">
              <option value="0"><?php echo $lang['nc_please_choose'];?></option>
              <?php if(!empty($output['refund_way_list'])){ ?>
              <?php foreach($output['refund_way_list'] as $k => $v){ ?>
              <option value="<?php echo $k;?>"><?php echo $v;?></option>
              <?php } ?>
              <?php } ?>
            </select>
          </div>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">收款人姓名</dt>
        <dd class="opt">
          <input type="text" value="" name="payee_name" id="payee_name" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">收款人账号</dt>
        <dd class="opt">
          <input type="text" value="" name="payee_account" id="payee_account" class="input-txt">
          <span class="err"></span>
          <p class="notic"></p>
        </dd>
      </dl> -->
      <div id="refund_all">
      <dl class="row">
        <dt class="tit"><em>*</em>退款原因</dt>
        <dd class="opt">
          <div id="gcategory">
            <input type="hidden" value="" name="class_id" class="mls_id">
            <input type="hidden" value="" name="brand_class" class="mls_name">
            <select class="class-select" name="reason_id_all" id="reason_id_all">
              <option value="0"><?php echo $lang['nc_please_choose'];?></option>
              <?php if(!empty($output['reason_list'])){ ?>
              <?php foreach($output['reason_list'] as $k => $v){ ?>
              <option value="<?php echo $v['reason_id'];?>"><?php echo $v['reason_info'];?></option>
              <?php } ?>
              <?php } ?>
            </select>
          </div>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><em>*</em>退款金额</dt>
        <dd class="opt">
          <input type="text" value="<?php echo $output['order']['order_amount'] ; ?>" name="refund_amount_all" id="refund_amount_all" class="input-txt">
        </dd>
      </dl>
          <dl class="row">
              <dt class="tit">上传图片
              </dt>
              <dd class="opt">
            <div class="input-file-show"><span class="show">
              <a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>"/>
              <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>>')" onMouseOut="toolTip()"></i></a>
              </span><span class="type-file-box">
            <input class="type-file-file" id="pic" name="pic" type="file" size="30">
            <input type="text" name="textfield" id="textfield1" class="type-file-text" />
            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
            </span></div>
            <p class="notic">1.支持一次性上传多张图片,但最多只能上传5张   &nbsp;&nbsp;&nbsp;&nbsp;2.点击图片可以删除图片</p>
            <ul class="img_show">
            </ul>
            </dd>
          </dl>
      <dl class="row">
        <dt class="tit"><em>*</em>原因备注</dt>
        <dd class="opt">
          <textarea rows="100" cols="50" name="buyer_message_all"></textarea>
          <?php foreach($output['order_goods_list'] as $k => $goods){ ?>
          <input type="hidden" name="product_money[]" value="<?php echo $goods['goods_pay_price']; ?>" />
          <input type="hidden" name="goods_id[]" value="<?php echo $goods['goods_id']; ?>" />
          <?php } ?>
        </dd>
      </dl>
      </div>
      <div id="refund_part" style="display: none;">
      <!-- 商品 -->
      <?php foreach($output['order_goods_list'] as $k => $goods){ ?>
      <dl  class="row">
      <dt class="tit">商品名</dt>
      <dd class="opt">
          <?php echo $k+1; ?>. <?php echo $goods['goods_name']; ?><br>  数量：<?php echo $goods['goods_num']; ?>，单价：<?php echo $goods['goods_price']; ?> , 支付总额：<?php echo $goods['goods_pay_price']; ?>
        	<?php if($goods['extend_refund']['refund_amount']>0 && $goods['extend_refund']['seller_state']!='3'){ ?>，<font color='red'>退款金额：<?php echo $goods['extend_refund']['refund_amount']."</font>";} ?>
        </dd>
      </dl>
  	  <dl  class="row">
  	  	<dt class="tit">退款金额：</dt>
  	  	<dd class="opt">
  	  		<input type="text" value=""  placeholder="<?php echo $goods['goods_pay_price']; ?>" name="refund_amount[]" id="refund_amount_<?php echo $k; ?>" class="input-txt refund_amount">
  	  	</dd>
  	  </dl>
  	  <dl class="row">
  	  	<dt class="tit">退款原因：</dt>
  	  	<dd class="opt">
  	  		<select class="class-select reason_id" name="reason_id[]" id="reason_id_<?php echo $k; ?>">
              <option value="0"><?php echo $lang['nc_please_choose'];?></option>
              <?php if(!empty($output['reason_list'])){ ?>
              <?php foreach($output['reason_list'] as $k => $v){ ?>
              <option value="<?php echo $v['reason_id'];?>"><?php echo $v['reason_info'];?></option>
              <?php } ?>
              <?php } ?>
            </select>
  	  	</dd>
  	  </dl>
          <dl class="row">
              <dt class="tit">上传图片
              </dt>
              <dd class="opt">
                  <div class="input-file-show"><span class="show">
              <a class="nyroModal" rel="gal" href="<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>"/>
              <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo UPLOAD_SITE_URL.'/'.ATTACH_CIRCLE.DS.$output['list_setting']['circle_logo'];?>>')" onMouseOut="toolTip()"></i></a>
              </span><span class="type-file-box">
            <input class="type-file-file" id="pic1" name="pic1" type="file" size="30" multiple="multiple">
            <input type="text" name="textfield" id="textfield1" class="type-file-text" />
            <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
            </span></div>
                  <p class="notic">1.支持一次性上传多张图片,但最多只能上传5张   &nbsp;&nbsp;&nbsp;&nbsp;2.点击图片可以删除图片</p>
                  <ul class="img_show">
                  </ul>
              </dd>
          </dl>
  	    <dl class="row">
        <dt class="tit">原因备注</dt>
        <dd class="opt">
          <textarea rows="100" cols="50" name="buyer_message[]">质量不好</textarea>
        </dd>
      </dl>
      <?php } ?>
      <!-- 商品 -->
      </div>
      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
    </div>
  </form>
</div>
<style>
    .img_show li img{
        width:15%;
        float:left;
        border:solid 1px #cccccc;
        margin-right:2%;
        margin-bottom:2%;
        margin-top:5px;
    }
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/ajaxfileupload/ajaxfileupload.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.js"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.Jcrop/jquery.Jcrop.min.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>


<script>
    var busy = false;
//裁剪图片后返回接收函数
function call_back(picname){
  $('#brand_pic').val(picname);
  $('#view_img').attr('src','<?php echo UPLOAD_SITE_URL.'/'.ATTACH_BRAND;?>/'+picname);
}

$('#pic').change(uploadChange);
$('#pic1').change(uploadChange1);

function uploadChange(){
    var filepatd=$(this).val();
    var extStart=filepatd.lastIndexOf(".");
    var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();
    if($("ul.img_show>li").length==5){
        alert("最多只能上传5张图片");
        return false;
    }
    if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
        alert("file type error");
        $(this).attr('value','');
        return false;
    }
    if ($(this).val() == '') return false;
    ajaxFileUpload();
}

function uploadChange1(){
    var filepatd=$(this).val();
    var extStart=filepatd.lastIndexOf(".");
    var ext=filepatd.substring(extStart,filepatd.lengtd).toUpperCase();
    if($("ul.img_show>li").length==5){
        alert("最多只能上传5张图片");
        return false;
    }
    if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
        alert("file type error");
        $(this).attr('value','');
        return false;
    }
    if ($(this).val() == '') return false;
    ajaxFileUpload1();
}

function ajaxFileUpload1()
{
    $.ajaxFileUpload
    ({
            url:'index.php?act=refund&op=pic_upload&form_submit=ok&uploadpath=shop/refund',
            secureuri:false,
            fileElementId:'pic1',
            dataType: 'json',
            success: function (data, status)
            {
                if (data.status == 1){
                    if(data.pic_info==""){
                        alert("上传图片失败，请刷新页面重新上传");
                        $('#pic').bind('change',uploadChange);
                        return false;
                    }
                    $("ul.img_show").append("<li onclick='delimg(this)'><img data-url='"+data.pic_info+"' src='"+data.url+"'></li>");
                }else{
                    alert(data.msg);
                }
                $('#pic1').bind('change',uploadChange1);
            },
            error: function (data, status, e)
            {
                alert('上传失败');
                $('#pic1').bind('change',uploadChange1);
            }
        }
    )
}

function ajaxFileUpload()
{
    $.ajaxFileUpload
    ({
            url:'index.php?act=refund&op=pic_upload&form_submit=ok&uploadpath=shop/refund',
            secureuri:false,
            fileElementId:'pic',
            dataType: 'json',
            success: function (data, status)
            {
                if (data.status == 1){
                    if(data.pic_info==""){
                        alert("上传图片失败，请刷新页面重新上传");
                        $('#pic').bind('change',uploadChange);
                        return false;
                    }
                   $("ul.img_show").append("<li onclick='delimg(this)'><img data-url='"+data.pic_info+"' src='"+data.url+"'></li>");
                }else{
                    alert(data.msg);
                }
                $('#pic').bind('change',uploadChange);
            },
            error: function (data, status, e)
            {
                alert('上传失败');
                $('#pic').bind('change',uploadChange);
            }
        }
    )
}

function delimg(obj){
    var file_name=$(obj).find("img").attr("src");
    if(confirm("你确定要删除图片吗？")) {
        $("img[src='"+file_name+"']").parent().remove();
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?act=refund&op=delimg",
            data: {"file_name": file_name},
            success: function (data) {
                if (data.state=="1") {
                    alert(data.msg);
                }
            }
        });
    }
}


$(function(){
    //自动加载滚动条
    $('#class_div').perfectScrollbar();
    $('#brand_div').perfectScrollbar();
    // 点击查看图片
    $('.nyroModal').nyroModal();
    //图片上传预览功能
    var userAgent = navigator.userAgent;//用于判断浏览器类型

  $("#submitBtn").click(function(){
      if(busy) return alert('正在提交，请勿重复提交');
      busy = true;
	  var is_refund_all=$('input:radio[name="is_refund_all"]:checked').val();
	  if( is_refund_all == 0 ) {
		  var total_amount = 0 ;
		  $('.refund_amount').each(function (index,domEle){
				if(  $(domEle).val() > 0 && $("#reason_id_"+index).val() == 0 ) {
					$("#reason_id_"+index).css('border', '1px solid red');
                    busy = false;
                    return false;
				}
				total_amount += $(domEle).val();
		  });
		  
			if( total_amount == 0 ) {
				$(".notic").html("至少选择一个商品退款");
                busy = false;
                return false;
			}
	  } else {
			if( $('#reason_id_all').val() == 0 ) {
				$('#reason_id_all').css('border', '1px solid red');
                busy = false;
                return false;
			}
			if( $('#refund_amount_all').val() == 0 ) {
				$('#refund_amount_all').css('border', '1px solid red');
                busy = false;
                return false;
			}
	  }

      var arr=new Array();
      $("ul.img_show>li").each(function(){
          var img_src=$(this).children("img").attr("data-url");
          arr.push(img_src);
      });

      for(var i=0;i<arr.length;i++) {
          for(var j=i+1;j<arr.length;j++) {
              if(arr[i]===arr[j]) {
                  arr.splice(j,1);
                  j--;
              }
          }
      }

      if(arr.length>0){
          $("#upload_img").val(arr.join(","));
      }
     $("#refund_form").submit();
  });


  jQuery.validator.addMethod("initial", function(value, element) {
    return /^[A-Za-z0-9]$/i.test(value);
  }, "");
  $("#brand_form").validate({
    errorPlacement: function(error, element){
      var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
            brand_name : {
                required : true,
                remote   : {
                    url :'index.php?act=brand&op=ajax&branch=check_brand_name',
                    type:'get',
                    data:{
                        brand_name : function(){
                            return $('#brand_name').val();
                            },
                            id  : ''
                    }
                }
            },
            brand_initial : {
                initial  : true
            },
            brand_sort : {
                number   : true
            }
        },
        messages : {
            brand_name : {
                required : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['brand_add_name_null'];?>',
                remote   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['brand_add_name_exists'];?>'
            },
            brand_initial : {
                initial : '<i class="fa fa-exclamation-circle"></i>请填写正确首字母'
            },
            brand_sort  : {
                number   : '<i class="fa fa-exclamation-circle"></i><?php echo $lang['brand_add_sort_int'];?>'
            }
        }
  }); 
});

gcategoryInit('gcategory');

function  show_tab(status)
{
	if( status == 1 ){
		$("#refund_all").show();
		$("#refund_part").hide();
	} else {
		$("#refund_all").hide();
		$("#refund_part").show();
	}
}
</script> 
