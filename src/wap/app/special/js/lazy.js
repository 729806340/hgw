function fade() {
    
    $(document).find("img[rel='lazy']").each(function () {


        var $scroTop = $(this).offset();
        if ($scroTop.top <= $(window).scrollTop() + $(window).height()) {
            $(this).hide();
            $(this).attr("src", $(this).attr("shopwwi-url"));
            $(this).removeAttr("rel");
            $(this).removeAttr("name");
            $(this).fadeIn(500);
        }
    });
}

/*if ($(document).find("img[rel='lazy']").length > 0) {
    $(window).scroll(function () {
        fade();
    });
};
fade();*/
