

<div id="body" style="background:#be2a2a; position:relative;">
	<div class="cms-content">
			<?php loop_include_widgets($output); ?>
        <div class="right-lift">
            <div>
                <a href="javascript:void(0);" title="1">口碑推荐</a>
                <a href="javascript:void(0);" title="2">爆款直降</a>
                <a href="javascript:void(0);" title="3">买一送一</a>
                <a href="javascript:void(0);" title="4">疯狂九块九</a>
                <a href="javascript:void(0);" title="5">地道粮油杂货</a>
                <a href="javascript:void(0);" title="6">休闲零食必备</a>
            </div>
        </div>
	</div>
</div>





<style>
/*    .adv_list{ margin-bottom: 300px;}*/
    .right-lift{ width: 150px; height: 510px; position: fixed; left: 50%; margin-left: 600px;  top: 200px; background: url(/data/resource/pc_special/js/right-lift-bg.png) no-repeat; z-index: 99}
    .right-lift div{ margin: 110px auto 0 auto; width: 120px;}
    .right-lift div a{ width: 120px; height: 32px; line-height: 32px; text-align: center; background: #d90505; margin-bottom: 10px; display: block; font-size: 16px; font-weight: bold; border:solid 2px #000; }
    .right-lift div a:hover{ color: #fff; border-color: #fff;}
</style>




<script>
    $(function() {
        $(".adv_list").each(function(index, element) {
            var add_claas = "floor" + index;
            console.log(add_claas);
            $(element).addClass(add_claas);

        })



        $(".right-lift div a").click(function(event) {
            var index = this.title;
            var f_class = '.' + 'floor' + index;
            console.log(f_class);
            $("html,body").animate({
                scrollTop: $(f_class).offset().top
            }, 500);
        });

        $(function() {
            $(window).scroll(function() {
                t = $(document).scrollTop();
                if (t > 500) {
                    $('.right-lift').show();
                } else {
                    $('.right-lift').hide();
                }

            })



        });


    })
</script>
