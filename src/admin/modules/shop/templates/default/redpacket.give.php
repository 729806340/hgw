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
</style>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=redpacket&op=rptlist" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台红包 - 发红包</h3>
        <h5>发红包，请谨慎使用，有日志记录，滥用违规使用必究！！！</h5>
      </div>
    </div>
  </div>
  <form action="/admin/modules/shop/?act=redpacket&op=give" method="post">
  <p>会员id/或用户名：
  	<textarea name="members_id" rows="5" cols="80"></textarea>
  <p>红包tid: <input type="text" name="tid" /></p>
  <select name="use_member_id">
	  <option value ="1">会员id</option>
	  <option value ="0">会员名</option>
	</select>
</br>
  <input type="submit" value="提交" />
</form>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script> 