// JavaScript Document


//导航菜单
function navList(id) {
    var $obj = $(".left-sidebar ul"), $item = $("#J_nav_" + id);
    $obj.find(".nav01").click(function () {
        var $div = $(this).siblings("dl");
        if ($(this).parent().hasClass("selected")) {
            $div.slideUp(600);
            $(this).parent().removeClass("selected");
        }
        if ($div.is(":hidden")) {
            $(".left-sidebar ul li").find("dl").slideUp(600);
            $(".left-sidebar ul li").removeClass("selected");
            $(this).parent().addClass("selected");
            $div.slideDown(600);

        } else {
            $div.slideUp(600);
        }
    });
}
