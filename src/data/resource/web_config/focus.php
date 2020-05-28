<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<ul id="fullScreenSlides" class="full-screen-slides">
	<?php if (is_array($output['code_screen_list']['code_info']) && !empty($output['code_screen_list']['code_info'])) { ?>
	<?php foreach ($output['code_screen_list']['code_info'] as $key => $val) { ?>
	<?php if (is_array($val) && $val['ap_id'] > 0) { ?>
    <li ap_id="<?php echo $val['ap_id'];?>" color="<?php echo $val['color'];?>" style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top">
        <a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a>
    </li>
	<?php }else { ?>
    <li style="background: <?php echo $val['color'];?> url('<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>') no-repeat center top">
        <a href="<?php echo $val['pic_url'];?>" target="_blank" title="<?php echo $val['pic_name'];?>">&nbsp;</a>
    </li>
	<?php } } } ?>
</ul>

<!--<ul class="full-screen-slides-pagination"><li class=""><a href="javascript:void(0)">1</a></li><li class=""><a href="javascript:void(0)">2</a></li><li class="current"><a href="javascript:void(0)">3</a></li><li class=""><a href="javascript:void(0)">4</a></li><li class=""><a href="javascript:void(0)">5</a></li></ul>-->


<a href="javascript:void(0);" class="angle-btn prev-btn"><i></i></a>
<a href="javascript:void(0);" class="angle-btn next-btn"><i></i></a>

		<script type="text/javascript">
			update_screen_focus();

		</script>

<script>
$(function(){
    window.onload=function(){
        
    }
    var aImg = $('#fullScreenSlides li');		//图像集合
	var iSize = aImg.size();		//图像个数
    for (var h = '<ul class="full-screen-slides-pagination">', k = 0; k < iSize; k++) 
    h += '<li><a href="javascript:void(0)">' + (k + 1) + "</a></li>";
    $(".full-screen-slides").after(h + "</ul>");
    
    //$(".full-screen-slides-pagination li:gt(0)").siblings().removeClass("current");
    aImg.first().show().siblings().hide();
//    console.log(index);
    //aImg.last.hide();
    
    
    var aPage = $('.full-screen-slides-pagination li');		//分页按钮
	
	var index = 0;		//切换索引
	var t;
    var d = aPage.size();
    //console.log(d);
    
    
    
    $(".prev-btn").click(function(){
        index--;
        
        if(index < 0){
            index=iSize-1
        }
        //console.log(index);
        change(index)
    })
    
    $(".next-btn").click(function(){
        index++;
        
        if(index > iSize-1){
			index=0
		}
        //console.log(index);
		change(index)
    })
    
    
    

    
    //分页按钮点击
	aPage.click(function(){
		index = $(this).index();
		change(index)
	});
    
    
     
    
    aPage.first().addClass('current');
    
    function change(index){
    
        aPage.removeClass('current');
        
		aPage.eq(index).addClass('current');
		//aImg.stop();
		//隐藏除了当前元素，所以图像
		aImg.eq(index).siblings().fadeOut(500);
		//显示当前图像
		aImg.eq(index).fadeIn(400);
	}
    
    
    function autoshow() {
		index=index+1;
		if(index<=iSize-1){
		   change(index);
		}else{
			index=0;
			change(index);
		}
			
	}
	int=setInterval(autoshow,3000);
	function clearInt() {
		$('.prev-btn,.next-btn,.full-screen-slides-pagination li').mouseover(function() {
			clearInterval(int);
		})
	
	}
	function setInt() {
		$('.prev-btn,.next-btn,.full-screen-slides-pagination li').mouseout(function() {
			int=setInterval(autoshow,3000);
		})
	}
	clearInt();
	setInt();
    
    

    
    $(".angle-btn").hide();
    $(".prev-btn,.next-btn,.full-screen-slides li").hover(function(){
        $(".angle-btn").stop().show();
    },function(){
        $(".angle-btn").stop().hide();
    })
    
    console.log(index);

  
    
    
})
</script>