<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script src="<?php echo B2B_RESOURCE_SITE_URL.'/js/search_goods.js';?>"></script>
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/wwi-main.css" rel="stylesheet" type="text/css">
<link href="<?php echo B2B_TEMPLATES_URL;?>/css/layout.css" rel="stylesheet" type="text/css">
<style type="text/css">
body { _behavior: url(<?php echo B2B_TEMPLATES_URL;
?>/css/csshover.htc);
}
</style>
<?php
if(count($output['nextclass'])>0){
?>
<div class="b2b-classify w1200">
  <div class="classify-con">
    <?php foreach($output['nextclass'] as $next){ ?>
    <a href="<?php echo urlB2B('search','index',array('bc_id'=>$next['bc_id']))?>"><?php echo $next['bc_name']; ?></a>
    <?php } ?>
  </div>
  <div class="unfold-btn"></div>
</div>
<?php } ?>

<div class="b2b-sort w1200">
  <ul class="fl">
    <li class="selected"><a href='<?php echo urlB2B('search', 'index', array($output['gettype'] => $_GET[$output['gettype']]));?>'>综合</a></li>
    <li class="price"
        style="background:<?php if(isset($_GET['order'])){echo $_GET['order']=="1" ? "url(".B2B_TEMPLATES_URL."/images/b2b_icon/sort-icon0.png) no-repeat 45px center":"url(".B2B_TEMPLATES_URL."/images/b2b_icon/sort-icon01.png) no-repeat 45px center";}else{ echo "url(".B2B_TEMPLATES_URL."/images/b2b_icon/sort-icon01.png) no-repeat 45px center";} ?>"
        data-rank="<?php if(isset($_GET['order'])){echo $_GET['order']=="1" ? "0":"1";}else{ echo "1";} ?>"><a href='#'>价格</a></li>
    <!--li class="area"><a href='#'>所在地区</a></li-->
  </ul>
  <div class="price-interval fl">
    <input  id="lowerprice" placeholder="&yen;最低价"  value="<?php echo  isset($_GET['lowerprice']) ? $_GET['lowerprice']:'&yen;最低价' ; ?>" type="text" />—<input  id="highprice" placeholder="&yen;最高价"  value="<?php echo  isset($_GET['highprice']) ? $_GET['highprice']:'&yen;最高价' ; ?>" type="text" />
    <input id="searchprice"type="button"  style="background-color:#14a83b;color:#fff;display:<?php echo  isset($_GET['lowerprice']) || isset($_GET['highprice']) ? 'display':'none'; ?>" value="确定">
  </div>
  <div class="classify-hint fr"><span>"批发农产品"</span>的热销产品</div>
</div>

<?php
if(count($output['goodsinfo'])>0){?>
<div class="b2b-classify-list w1200">
  <ul class="b2bclearfix">
    <?php foreach($output['goodsinfo'] as $goods){ ?>
    <li class="b2bclearfix">
      <div class="goods-pic fl">
        <a href='#'>
          <div><img src='<?php echo $goods['img']?>' /></div>
        </a>
      </div>
      <div class="goods-info fl">
        <div class="goods-name"><a href="#"><?php echo $goods['goods_name']?></a></div>
        <div class="goods-model b2bclearfix">
          <div class="model">
            <p class="goods-price">&yen;<?php echo $goods['min_price']; ?>~<?php echo $goods['max_price']; ?></p>
            <!--p class="goods-specifications">3-19袋</p-->
          </div>
          <!--div class="model">
            <p class="goods-price">&yen;14.9</p>
            <p class="goods-specifications">20-99袋</p>
          </div-->
          <!--div class="model">
            <p class="goods-price">&yen;13.9</p>
            <p class="goods-specifications">&ge;100袋</p>
          </div-->
        </div>
        <div class="b2bclearfix">
          <!--p class="shop-name fl">鲜天下专营店</p-->
          <a class="view-details fr" href="<?php echo  urlB2B('goods','index',array('goods_commonid'=>$goods['goods_commonid']))?>">查看详情&gt;</a>
        </div>
      </div>
      <div class="tab">新品</div>
    </li>
    <?php } ?>
  </ul>
</div>
<?php }else{
  echo "<div id=\"no_results\" class=\"no-results\"><i></i>没有找到符合条件的商品</div>";
} ?>
<!-- 分页 -->
<div class="b2b-pagination">
  <?php echo $output['show_page']; ?>
</div>
<script>
  $(function(){
    $("#lowerprice").change(function(){
      $("#searchprice").show();
    })
    $("#highprice").change(function(){
      $("#searchprice").show();
    })

    $("div.b2b-sort li.price").click(function(){
      var rank=$(this).attr("data-rank");
      window.location.href="?act=search&op=index&<?php echo $output['gettype']."=".$_GET[$output['gettype']];?>&order="+rank;
    })

    $("#searchprice").click(function(){
      var lowerprice=$("#lowerprice").val();
      var highprice=$("#highprice").val();
      if(lowerprice=="" || highprice==""){
        alert("请填写价格");
        return false;
      }
      if(isNaN(lowerprice) || isNaN(highprice)){
        alert("请输入数字");
        return false;
      }
      window.location.href="?act=search&op=index&<?php echo $output['gettype']."=".$_GET[$output['gettype']];?>&lowerprice="+lowerprice+"&highprice="+highprice;
    })
  })
</script>

