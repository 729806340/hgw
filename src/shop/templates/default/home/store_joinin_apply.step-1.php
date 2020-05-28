<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<!-- 协议 -->
<style>
.ul_btn li {
    width: 43%;
    float: left;
    text-align: center;
    list-style-type: none;
    margin-left:3%;
    margin-top:15px;
}
.ul_btn li a{
    font: normal 22px/20px "microsoft yahei";
    color: #0286D8;
    text-align: center;
    vertical-align: middle;
    display: inline-block;
    height: 20px;
    padding: 8px 0px;
    cursor: pointer;
    margin-left:2%;
    border-radius:3px;
}
 .listyle{
     border:solid 1px #0286D8;
     text-align: left;
     border-radius:6px;
     padding:10px;
     height:150px;

 }
 .listyle p{
     text-align: left;
     font-size:12px;
 }
.joinin-concrete .title{
    padding:10px 0;;
}
</style>
<div id="apply_agreement" class="apply-agreement">
  <div class="title"><h3>商家类型</h3></div>
  <ul class="ul_btn">
      <li class="shoptype" data-id="1">
          <img  src="<?php echo  SHOP_TEMPLATES_URL."/images/storeimg1.png";?>">
          <a>共建</a></li>
      <li class="shoptype" data-id="2" style="padding-left:4%"><img  src="<?php echo  SHOP_TEMPLATES_URL."/images/storeimg2.png";?>"><a>平台</a></li>
      <li  class="shoptype" data-id="1" style="border:solid 1px #0286D8;text-align: left;border-radius:6px;padding:10px;height:150px;">
          <p>1、平台和商家共同运营后台；</p>
          <p>2、商家自建或者第三方物流；</p>
          <p> 3、商家提供售后、退换货服务；</p>
          <p> 4、商家开票给平台，平台开票给顾客；</p>
          <p> 5、商家授权汉购平台在CPS渠道、分销渠道自主推广，按商品供价（结算价）结算；</p>
          <p> 6、对公账户结算货款，确认收货后T+N账期，回款准时。</p>
      </li>
      <li  class="shoptype" data-id="2" style="border:solid 1px #0286D8;text-align: left;border-radius:6px;padding:10px;height:150px;">
          <p>1、自主运营后台；</p>
          <p>2、商家自建或者第三方物流；</p>
          <p>3、商家提供售后、退换货服务；</p>
          <p>4、商家自主开票给顾客；</p>
          <p>5、汉购平台合作CPS渠道推广，按推广销售提佣；</p>
          <p>6、对公账户结算货款，确认收货后T+N账期，回款准时。</p>
      </li>
  </ul>
</div>

<script type="text/javascript">
$(document).ready(function(){
$("li.shoptype").click(function(){
    var type=$(this).attr("data-id");
    window.location.href = "index.php?act=store_joinin&op=step1&storetype="+type;
})
});
</script>