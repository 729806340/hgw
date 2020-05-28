<div class="chat"><a href="javascript:void(0);" id="chat_show_user"><span class="icon"></span><i id="new_msg" class="new_msg" style="display:none;"></i><span class="tit">在线联系</span></a></div>


<script>
    //返回顶部
    $(function() {
        $("#chat_show_user").click(function(){
            /*会员登录了之后没有会员值，只有强制刷新网页的时候才有会员值*/
            var arr={ uid:'',uname:''};
            NTKF.im_updatePageInfo(arr);
            NTKF.im_openInPageChat('hf_1000_1545011587137');
        })
    })

</script>

<script type="text/javascript">
    var NTKF_PARAM = {
        "siteid":"hf_1000" /*网站siteid*/,
        "settingid":"hf_1000_1545011587137" /*代码ID*/,
        "uid":"209592" /*会员ID*/,
        "uname":""/*会员名*/,
        "userlevel": "0"/*会员等级*/,
        "erpparam": 'hango:order:3154'
    }
</script>
<script type="text/javascript" src="http://dl.ntalker.com/js/b2b/ntkfstat.js?siteid=hf_1000" charset="utf-8"></script>
