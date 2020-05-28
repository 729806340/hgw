<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .rpt-range li{padding: 5px 5px 0;border: 1px solid gray;margin: 5px 0;}
    .rpt-range li.selected{background: lightgrey;}
    .rpt-range li img,.sku-list li img{height: 24px;width: 24px;}
    .rpt-range li span,.sku-list li span{line-height: 24px;vertical-align: top;}
    #goods-select-box{width: 45%;float: right;}
    #goods-selected-list{width: 45%;float: left;}
    #list-head{font-size: 16px;margin: 10px 0 0;}
    .sku-list li {margin: 5px 0; }
    .ui-datepicker {
        width: 19em;
        padding: .2em .2em 0;
        display: none;
    }
</style>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=redpacket&op=rptlist" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台红包 - 新建红包模板</h3>
        <h5>平台红包新增与管理</h5>
      </div>
    </div>
  </div>
  <form id="rpt_form" method="post" name="rpt_form" action="index.php?act=redpacket&op=rptadd" enctype="multipart/form-data">
    <input type="hidden" name="form_submit" value="ok" />
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">
          <label for="rpt_title"><em>*</em>红包名称</label>
        </dt>
        <dd class="opt">
          <?php if($output['t_info']['ableedit']==false){?>
            <?php echo $output['t_info']['rpacket_t_title'];?>
          <?php }else{ ?>
            <input type="text" value="<?php echo $output['t_info']['rpacket_t_title'];?>" name="rpt_title" id="rpt_title" class="input-txt" <?php echo $output['t_info']['ableedit']==false?'readonly':'';?>>
            <span class="err"></span>
            <p class="notic">模版名称不能为空且不能大于50个字符</p>
          <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="rpt_gettype"><em>*</em>领取方式</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_gettype_text'];?>
                <input type="hidden" value="<?php echo $output['t_info']['rpacket_t_gettype'];?>" name="rpt_gettype" id="rpt_gettype"/>
            <?php }else{ ?>
                <select name="rpt_gettype" id="rpt_gettype"  <?php echo $output['t_info']['ableedit']==false?'readonly':'';?>>
                    <option value=""><?php echo $lang['nc_please_choose'];?></option>
                    <?php if(!empty($output['gettype_arr']) && is_array($output['gettype_arr'])){ ?>
                    <?php foreach($output['gettype_arr'] as $k => $v){ ?>
                    <option value="<?php echo $k;?>" <?php echo $output['t_info']['rpacket_t_gettype_key'] == $k?'selected':'';?>><?php echo $v['name'];?></option>
                    <?php } ?>
                    <?php } ?>
                </select>
                <span class="err"></span>
                <p class="notic">“积分兑换”时会员可以在积分中心用积分进行兑换；“卡密兑换”时会员需要在“我的商城——我的红包”中输入卡密获得红包；“免费领取”时会员可以点击红包的推广广告领取红包。</p>
            <?php } ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="rpt_sdate"><em>*</em>有效期</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo @date('Y-m-d',$output['t_info']['rpacket_t_start_date']);?> 至 <?php echo @date('Y-m-d',$output['t_info']['rpacket_t_end_date']);?>
            <?php }else{?>
                <input type="text" id="rpt_sdate" name="rpt_sdate" data-dp="1" class="s-input-txt" value="<?php echo @date('Y-m-d H:i',$output['t_info']['rpacket_t_start_date']);?>"/> 至
                <input type="text" id="rpt_edate" name="rpt_edate" data-dp="1" class="s-input-txt" value="<?php echo @date('Y-m-d H:i',$output['t_info']['rpacket_t_end_date']);?>"/>
                <span class="err"></span>
                <p class="notic">会员领取红包后，将在该有效期内使用红包</p>
            <?php } ?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="rpt_price"><em>*</em>面额</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_price'];?>&nbsp;&nbsp;<?php echo $lang['currency_zh'];?>
            <?php }else{?>
                <input type="text" name="rpt_price" id="rpt_price" value="<?php echo ncPriceFormat($output['t_info']['rpacket_t_price'], 0);?>" />&nbsp;&nbsp;<?php echo $lang['currency_zh'];?>
                <span class="err"></span>
                <p class="notic">面额应为大于1的整数</p>
            <?php }?>
        </dd>
      </dl>
      <dl class="row" id="points_dl">
        <dt class="tit">
          <label for="rpt_points"><em>*</em>兑换所需积分</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_points'];?>
            <?php }else{?>
                <input type="text" name="rpt_points" id="rpt_points" value="<?php echo $output['t_info']['rpacket_t_points'];?>" />
                <span class="err"></span>
                <p class="notic">兑换所需积分应为大于1的整数</p>
            <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="rpt_total"><em>*</em>可发放总数</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_total'];?>
            <?php }else{?>
                <input type="text" id="rpt_total" name="rpt_total" value="<?php echo $output['t_info']['rpacket_t_total'];?>"/>
                <span class="err"></span>
                <p class="notic">如果红包领取方式为卡密兑换，则发放总数应为1~10000之间的整数</p>
            <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
            <label for="rpt_eachlimit"><em>*</em>每人限领</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_eachlimit']==0?'不限':$output['t_info']['rpacket_t_eachlimit'];?>
            <?php }else{?>
                <select name="rpt_eachlimit" id="rpt_eachlimit">
                    <option value="">不限</option>
                    <?php for($i=1;$i<6;$i++){ ?>
                    <option value="<?php echo $i;?>" <?php echo $output['t_info']['rpacket_t_eachlimit']==$i?'selected':'';?>><?php echo $i;?></option>
                    <?php } ?>
                </select>
                <span class="err"></span>
                <p class="notic"></p>
           <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label for="rpt_orderlimit"><em>*</em>消费限额</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_limit'];?>&nbsp;&nbsp;<?php echo $lang['currency_zh'];?>
            <?php }else{?>
                <input type="text" name="rpt_orderlimit" id="rpt_orderlimit" value="<?php echo $output['t_info']['rpacket_t_limit'];?>" />&nbsp;&nbsp;<?php echo $lang['currency_zh'];?>
                <span class="err"></span>
                <p class="notic">如果消费限额设置为0，则表示不限制使用红包的消费限额</p>
            <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
            <label for="rpt_mgradelimit"><em>*</em>会员级别</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php echo $output['t_info']['rpacket_t_mgradelimittext'];?>
            <?php }else{?>
                <select name="rpt_mgradelimit" id="rpt_mgradelimit" <?php echo $output['t_info']['ableedit']==false?'readonly':'';?>>
                    <?php if(!empty($output['member_grade']) && is_array($output['member_grade'])){ ?>
                    <?php foreach($output['member_grade'] as $k => $v){ ?>
                    <option value="<?php echo $v['level'];?>" <?php echo $output['t_info']['rpacket_t_mgradelimit']==$v['level']?'selected':'';?>><?php echo $v['level_name'];?></option>
                    <?php } ?>
                    <?php } ?>
                </select>
                <span class="err"></span>
                <p class="notic">当会员兑换红包时，需要达到该级别或者以上级别后才能兑换领取</p>
            <?php }?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
            <label for="rpt_desc"><em>*</em>红包描述</label>
        </dt>
        <dd class="opt">
            <textarea id="rpt_desc" name="rpt_desc" class="w300" <?php echo $output['t_info']['ableedit']==false?'readonly':'';?>><?php echo $output['t_info']['rpacket_t_desc'];?></textarea>
            <span class="err"></span>
            <p class="notic">模版描述不能为空且小于200个字符</p>
        </dd>
      </dl>
      <dl class="row">
          <dt class="tit">
              <label for="rpt_show">红包是否在小程序商品详情显示</label>
          </dt>
          <dd class="opt">
              <input name="rpacket_t_show_goods_detail" type="radio" <?php if($output['t_info']['rpacket_t_show_goods_detail'] == 1) {?>  checked="checked" <?php } ?> value="1" />显示
              <input name="rpacket_t_show_goods_detail" type="radio" <?php if($output['t_info']['rpacket_t_show_goods_detail'] != 1) {?>  checked="checked" <?php } ?> value="0" />不显示
          </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
            <label>红包图片</label>
        </dt>
        <dd class="opt">
            <?php if($output['t_info']['ableedit']==false){?>
                <?php if ($output['t_info']['rpacket_t_customimg_url']){?>
                  <img onload="javascript:DrawImage(this,220,95);" src="<?php echo $output['t_info']['rpacket_t_customimg_url'];?>"/>
                <?php } ?>
            <?php }else{?>
              <div class="input-file-show">
                <span class="show">
                    <a class="nyroModal" rel="gal" href="<?php echo $output['t_info']['rpacket_t_customimg_url'];?>">
                        <i class="fa fa-picture-o" onMouseOver="toolTip('<img src=<?php echo $output['t_info']['rpacket_t_customimg_url'];?>>')" onMouseOut="toolTip()"/></i>
                    </a>
                </span>
                <span class="type-file-box">
                    <input type="text" name="textfield" id="textfield1" class="type-file-text" <?php echo $output['t_info']['ableedit']==false?'readonly':'';?>/>
                    <input type="button" name="button" id="button1" value="选择上传..." class="type-file-button" />
                    <input class="type-file-file" id="rpt_img" name="rpt_img" type="file" size="30" hidefocus="true" title="点击前方预览图可查看大图，点击按钮选择文件并提交表单后上传生效">
                </span>
              </div>
           <?php }?>
        </dd>
      </dl>

        <!-- 新增商品选择 -->

        <dl class="row">
            <dt class="tit">
                <label for="rpt_gettype"><em>*</em>红包适用范围</label>
            </dt>
            <dd class="opt">
                <select name="rpacket_t_goods_type" id="rpacket_t_goods_type" disabled="true">
                    <option value="0" <?php echo  $output['t_info']['rpacket_t_goods_type']==0?'selected':'' ?>>全部商品类型</option>
                    <option value="1" <?php echo  $output['t_info']['rpacket_t_goods_type']==1?'selected':'' ?>>共建商品</option>
                    <option value="2" <?php echo  $output['t_info']['rpacket_t_goods_type']==2?'selected':'' ?>>平台商品</option>
                </select>
                <select name="rpt_range" id="rpt-range">
                    <option value="0" <?php echo  $output['t_info']['rpacket_t_range']==0?'selected':'' ?>>全场通用</option>
                    <option value="1" <?php echo  $output['t_info']['rpacket_t_range']==1?'selected':'' ?>>仅选定商品适用</option>
                    <option value="2" <?php echo  $output['t_info']['rpacket_t_range']==2?'selected':'' ?>>除选定商品外适用</option>
                </select>
                <span class="err"></span>
                <p class="notic">留空默认为全场通用</p>

            </dd>
        </dl>

        <dl class="row" id="range-select-row"  <?php echo  $output['t_info']['rpacket_t_range']==0?'style="display: none;"':'' ?>>
            <dt class="tit">
            </dt>
            <dd class="opt">
                <?php
                $goodsList = $output['t_info']['goodsList'];
                if($output['t_info']['ableedit']==false){?>
                    <p>已经选定商品：</p>
                    <ul class="sku-list">
                            <?php
                            if(is_array($goodsList))
                                foreach ($goodsList as $goods){
                                    echo '<li><img src="/data/upload/shop/store/goods/'.$goods['store_id'].'/'.$goods['goods_images'].'"> <span>'.$goods['goods_name'].'</span></li>';
                                }
                            ?>
                    </ul>
                    <?php }else{?>

                <div class="rpt-range">
                    <div>
                        <input type="hidden" name="rpt_skus" id="rpt-skus" value="<?php echo $output['t_info']['rpacket_t_skus']; ?>"/>
                    </div>
                    <div id="goods-selected-list">
                        <div id="list-head">
                            <span>已选商品</span>
                            <a href="JavaScript:cleanSku();" class="ncap-btn ncap-btn-red" id="select-goods-reset">清空已选商品</a>
                        </div>
                        <ul id="list-body">
                            <?php
                            if(is_array($goodsList))
                                foreach ($goodsList as $goods){
                                echo '<li id="selected-sku-'.$goods['goods_id'].'" data-key="'.$goods['goods_id'].'"><img src="/data/upload/shop/store/goods/'.$goods['store_id'].'/'.$goods['goods_images'].'"> <span>'.$goods['goods_name'].'</span></li>';
                                }
                            ?>
                        </ul>
                    </div>
                    <div id="goods-select-box">
                        <div style="margin: 10px 0 0;">
                            <input type="text" placeholder="搜索商品名称" value="" name="goods_name" id="goods_name" maxlength="20" class="input-txt">
                            <a id="goods-search" href="JavaScript:void(0);" class="ncap-btn mr5">搜索</a>
                        </div>
                        <div id="goods-search-result">请搜索商品...</div>
                    </div>
                </div>
                <?php }?>

            </dd>
        </dl>


      <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js" charset="utf-8"></script>

<script>
//按钮先执行验证再提交表单
$(function(){
	$("#submitBtn").click(function(){
        if($("#rpt_form").valid()){
        	var choose_gettype = $("#rpt_gettype").val();
        	if(choose_gettype == 'pwd'){
            	var template_total = parseInt($("#rpt_total").val());
            	if(template_total > 10000){
            		$("#rpt_total").addClass('error');
            		$("#rpt_total").parent('dd').children('span.err').append('<label for="rpt_total" class="error"><i class="fa fa-exclamation-circle"></i>领取方式为卡密兑换的红包，发放总数不能超过10000张</label>');
            		return false;
                }
            }
            $("#rpt_form").submit();
    	}
	});

	// 模拟默认用户图片上传input type='file'样式
    $("#rpt_img").change(function(){
    	   $("#textfield1").val($("#rpt_img").val());
    });
    // 上传图片类型
	$('input[class="type-file-file"]').change(function(){
		var filepath=$(this).val();
		var extStart=filepath.lastIndexOf(".");
		var ext=filepath.substring(extStart,filepath.length).toUpperCase();
		if(ext!=".PNG"&&ext!=".GIF"&&ext!=".JPG"&&ext!=".JPEG"){
			alert("图片限于png,gif,jpeg,jpg格式");
			$(this).attr('value','');
			return false;
		}
	});
    // 点击查看图片
	$('.nyroModal').nyroModal();
});

function getGoods(name,page) {
    console.log('搜索商品');
    var goodsSearch = $('#goods-search');
    goodsSearch.data('page',page);
    $("#rpacket_t_goods_type").attr("disabled","disabled");
    var goods_type = $("#rpacket_t_goods_type").val();
    var url = 'index.php?act=redpacket&op=get_goods_list&goods_name='+name+'&goods_type='+goods_type+'&curpage='+page;
    $.get(url,function (data) {
        goodsSearch.data('total',data.total);
        renderResult(data.items);
    },'json');
}
function renderResult(items) {
    // TODO 渲染结果页面
    var resElem = $('#goods-search-result');
    if(typeof items != 'object'||items.length <=0){
        resElem.text('没有找到对应结果');
        return false;
    }
    var content = '<ul>';
    var sku = getSelectedSku();
    for(var i=0;i<items.length;i++){
        var item = items[i];
        var isSelected = $.inArray(item.goods_id,sku)>-1;
        var className = isSelected?'selected':'';
        var itemHtml='';
        itemHtml = '<li class="'+className+'" id="search-sku-'+item.goods_id+'" data-key="'+item.goods_id+'"><img src="/data/upload/shop/store/goods/'+item.store_id+'/'+item.goods_image+'"> <span>'+item.goods_name+'</span></li>';
        content += itemHtml;
    }
    content += '</ul>';
    content += '<div>' +
        '<a href="javascript:prevSearch();" class="ncap-btn mr5">上一页</a>' +
        '<a href="javascript:nextSearch();" class="ncap-btn mr5">下一页</a>' +
        '</div>';
    resElem.html(content);

}
function prevSearch() {
    var goodsSearch = $('#goods-search');
    var curPage = goodsSearch.data('page');
    if(curPage <=1) {
        alert('当前为第一页');
        return;
    }
    var totalPage = goodsSearch.data('total');
    getGoods($('#goods_name').val(),parseInt(curPage)-1);
}
function nextSearch() {
    var goodsSearch = $('#goods-search');
    var curPage = goodsSearch.data('page');
    var totalPage = goodsSearch.data('total');
    if(curPage >= totalPage) {
        alert('当前为第一页');
        return;
    }
    getGoods($('#goods_name').val(),parseInt(curPage)+1);
}
function getSelectedSku() {
    var goods = $('#rpt-skus').val();
    if(goods == '') return [];
    return goods.split(',');
}
function addSku(sku,obj) {
    var selectedSku = getSelectedSku();
    var index = $.inArray(sku,selectedSku);
    if(index>-1) return;
    selectedSku.push(sku);
    $('#rpt-skus').val(selectedSku.join(','));
    var content = '<li id="selected-sku-'+obj.data('key')+'" data-key="'+obj.data('key')+'">'+obj.html()+'</li>';
    $('#list-body').append(content);
    $('#search-sku-'+sku).addClass('selected');

}
function removeSku(sku) {
    var selectedSku = getSelectedSku();
    var index = $.inArray(sku,selectedSku);
    console.log(selectedSku);
    if(index==-1) return;
    selectedSku.splice(index,1);
    $('#rpt-skus').val(selectedSku.join(','));
    $('#selected-sku-'+sku).remove();
    $('#search-sku-'+sku).removeClass('selected');

}
function cleanSku() {
    $('#rpt-skus').val('');
    $('#list-body').empty();
    $('#goods-search-result li').removeClass('selected');
}
$(document).ready(function(){
	//绑定时间控件
	$('[data-dp]').datetimepicker({
        controlType: 'select'
	});
	//判断显示内容
	<?php if($output['t_info']['rpacket_t_gettype_key'] == 'points'){?>
	$("#points_dl").show();
	<?php }else{?>
	$("#points_dl").hide();
	<?php }?>
	$("#rpt_gettype").change(function(){
		$("#points_dl").hide();
		var gtype = $("#rpt_gettype").val();
		if(gtype == 'points'){
			$("#points_dl").show();
		}
	});
	
	jQuery.validator.addMethod("checkvaliddate", function(value, element) {
		var sdate = $("#rpt_sdate").val();
		var edate = $("#rpt_edate").val();
		if(!sdate){
			return false;
		}else if(!edate){
			return false;
		}
		var sdate = new Date(Date.parse(sdate.replace(/-/g, "/")));
        var edate = new Date(Date.parse(edate.replace(/-/g, "/")));
        return sdate < edate;        
	}, "开始时间不能大于结束时间");
	jQuery.validator.addMethod("checkpoints", function(value, element) {
		var gtype = $("#rpt_gettype").val();
		var rpt_points = $("#rpt_points").val();
		if(gtype == 'points'){
			if(!rpt_points){
				return false;
			}
			//声明正则表达式验证为正整数
			var re = /^([+]?)(\d+)$/;
			if (!re.test(rpt_points)){
				return false;
			}
			if(rpt_points < 1){
				return false;
			}
		}
		return true;
	}, "开始时间不能大于结束时间");
	
	$('#rpt_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
        rules : {
        	rpt_title : {
        		required : true,
                rangelength : [1,50]
            },
            rpt_gettype : {
            	required : true
            },
            rpt_sdate : {
            	required : true,
            	checkvaliddate :true
            },
            rpt_edate : {
            	required : true,
            	checkvaliddate :true
            },
            rpt_price : {
            	required : true,
            	digits : true,
                min: 1
            },
            rpt_points : {
            	checkpoints : true
            },
            rpt_total : {
            	required : true,
            	digits : true,
                min: 1
            },
            rpt_orderlimit : {
            	required : true,
                number : true
            },
            rpt_desc : {
            	required : true,
            	rangelength:[1,200]
            }
        },
        messages : {
        	rpt_title : {
                required : '<i class="fa fa-exclamation-circle"></i>模版名称不能为空且小于50个字符',
                rangelength : '<i class="fa fa-exclamation-circle"></i>模版名称不能为空且小于50个字符'
            },
            rpt_gettype : {
                required : '<i class="fa fa-exclamation-circle"></i>请选择领取方式'
            },
            rpt_sdate : {
            	required : '<i class="fa fa-exclamation-circle"></i>请选择有效期'
            },
            rpt_edate : {
            	required : '<i class="fa fa-exclamation-circle"></i>请选择有效期'
            },
            rpt_price : {
                required : '<i class="fa fa-exclamation-circle"></i>面额不能为空且为大于1的整数',
                digits : '<i class="fa fa-exclamation-circle"></i>面额不能为空且为大于1的整数',
                min: '<i class="fa fa-exclamation-circle"></i>面额不能为空且为大于1的整数'
            },
            rpt_points : {
            	checkpoints : '<i class="fa fa-exclamation-circle"></i>兑换所需积分不能为空且为大于1的整数'
            },
            rpt_total  : {
            	required : '<i class="fa fa-exclamation-circle"></i>可发放数量不能为空且为大于1的整数',
                digits : '<i class="fa fa-exclamation-circle"></i>可发放数量不能为空且为大于1的整数',
                min: '<i class="fa fa-exclamation-circle"></i>可发放数量不能为空且为大于1的整数'
            },
            rpt_orderlimit : {
            	required : '<i class="fa fa-exclamation-circle"></i>模版使用消费限额不能为空且必须是数字',
                number : '<i class="fa fa-exclamation-circle"></i>模版使用消费限额不能为空且必须是数字'
            },
            rpt_desc : {
            	required : '<i class="fa fa-exclamation-circle"></i>模版描述不能为空且小于200个字符',
            	rangelength:'<i class="fa fa-exclamation-circle"></i>模版描述不能为空且小于200个字符'
            }
        },
        groups : {
            phone:'rpt_sdate rpt_edate'
        }
    });

    // 限定商品选择功能
    $('#select-goods').click(function (e) {
        // 显示商品选择栏目
        var extend=$(this).data('extend');
        var box = $('#goods-select-box');
        if(extend){
            box.fadeOut();
            $(this).data('extend',false);
            $(this).text('打开商品选择');
        }else{
            box.fadeIn();
            $(this).data('extend',true);
            $(this).text('关闭商品选择');
        }
    });
    $('#rpt-range').change(function (e) {
        var $this = $(this);
        console.log($this.val());
        if($this.val()>0){
            $('#range-select-row').fadeIn();
        }else {
            $('#range-select-row').fadeOut();
        }
    });

    $('#goods-search').click(function (e) {
        $(this).data('page',1);
        getGoods($('#goods_name').val(),1);
        return true;
    });

    $('#goods-select-box').on('click','li',function (e) {
        var $this = $(this);
        var sku = $this.data('key')+'';
        // TODO 添加删除
        var selectedSku = getSelectedSku();
        if($.inArray(sku,selectedSku)>-1){
            removeSku(sku);
        }else{
            addSku(sku,$this);
        }
    });
    $('#list-body').on('click','li',function (e) {
        var $this = $(this);
        var sku = $this.data('key')+'';
        removeSku(sku);
    });
});
</script>