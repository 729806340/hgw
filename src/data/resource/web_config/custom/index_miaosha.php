       
<div class="title clearfix">
    <h3 class="fl">秒杀专区</h3>
    <div class="explain fl">
        <span class="time-text"></span>
        <div class="countdown">
            <span class="hour box"></span>
            <span class="dosh">:</span>
            <span class="minute box"></span>
            <span class="dosh">:</span>
            <span class="seconds box"></span>
        </div>
    </div>
</div>



<div class="seckill-con">
    <div class="picScroll-left">
        <div class="hd">
            <a class="next"></a>
            <a class="prev"></a>
        </div>
        <div class="bd">
            <ul class="picList">
                <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
                <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
                <?php if (is_array($val) && !empty($val)) { ?>
                <li>
                    <div class="goods-pic">
                        <a href="<?php echo $val['pic_url'];?>">
                            <img src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>" alt="<?php echo $val['pic_name'];?>" title="<?php echo $val['pic_name'];?>" />
                        </a>
                    </div>
                    <div class="goods-info">
                        <a class="goods-name" href="<?php echo $val['pic_url'];?>" title="<?php echo $val['pic_name'];?>"><?php echo $val['pic_name'];?></a>
                        <div class="goods-price">
                            <span class="current-price">&yen;<?php echo $val['pic_sname'];?></span>
                            <span class="original-price">&yen;<?php echo $val['pic_simg'];?></span>
                        </div>
                    </div>
                </li>
                <?php }?>
                <?php }?>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<script>
    $(function(){
        
        setInterval(function(){
            
            starttime ='2018-08-28 09:00:00';
            endtime = '2018-08-28 12:00:00';
            starttime = starttime.replace(new RegExp("-","gm"),"/");
            endtime = endtime.replace(new RegExp("-","gm"),"/");


            var starttimeHaoMiao = (new Date(starttime)).getTime(); //得到毫秒数
            var endtimeHaoMiao = (new Date(endtime)).getTime();
            var nowtime = (new Date()).getTime();
            var settime = $('.countdown').text();



            console.log('开始时间',starttimeHaoMiao);
            console.log('结束时间',endtimeHaoMiao);
            console.log('当前时间',nowtime);
            console.log('设定时间',settime);
            
            if (nowtime < starttimeHaoMiao) {
                $('.time-text').text("距开场还剩");
                var settime = starttimeHaoMiao - nowtime;
                var new_settime = new Date(settime);

                $(".hour").text(new_settime.getHours());
                $(".minute").text(new_settime.getMinutes());
                $(".seconds").text(new_settime.getSeconds());
               
                console.log('旧的时间格式',settime);
                console.log('新的时间格式',new_settime);
            }
            if (nowtime >= starttimeHaoMiao && nowtime <= endtimeHaoMiao) {
                $('.time-text').text("距结束还剩");
                var settime = endtimeHaoMiao - nowtime;
                var new_settime = new Date(settime);
                $(".hour").text(new_settime.getHours());
                $(".minute").text(new_settime.getMinutes());
                $(".seconds").text(new_settime.getSeconds());
            }
            if (nowtime > endtimeHaoMiao) {
                $('.time-text').text("已结束");
                settime = 0;
                $(".hour").text('00');
                $(".minute").text('00');
                $(".seconds").text('00');
            }
        },1000)
        
        
        
        
        
    })
</script>
