
    
    
$(function(){
	var a=getCookie("key");var e='<div class="nctouch-footer-wrap posr">'+'<div class="nav-text">';if(a){e+='<a href="'+WapSiteUrl+'/tmpl/member/member.html">我的商城</a>'+'<a id="logoutbtn" href="javascript:void(0);">注销</a>'+'<a href="'+WapSiteUrl+'/tmpl/member/member_feedback.html">反馈</a>'}else{e+='<a href="'+WapSiteUrl+'/tmpl/member/login.html">登录</a>'+'<a href="'+WapSiteUrl+'/tmpl/member/register.html">注册</a>'+'<a href="'+WapSiteUrl+'/tmpl/member/login.html">反馈</a>'}e+='<a href="javascript:void(0);" class="gotop">返回顶部</a>'+"</div>"+'<div class="nav-pic">'+'<a href="'+SiteUrl+'/index.php?act=mb_app" class="app"><span><i></i></span><p>客户端</p></a>'+'<a href="javascript:void(0);" class="touch"><span><i></i></span><p>触屏版</p></a>'+'<a href="'+SiteUrl+'" class="pc"><span><i></i></span><p>电脑版</p></a>'+"</div>"+'<div class="copyright">'+'Copyright&nbsp;&copy;&nbsp;2014-2016 汉购网<a href="javascript:void(0);">hangowa.com</a>版权所有'+"</div></div>";
	var n = '<div id="footnav" class="footnav clearfix"><ul>'
		+'<li><a href="'+WapSiteUrl+'"><i class="home"></i><p>首页</p></a></li>'
		+'<li><a href="'+WapSiteUrl+'/special.html?special_id=1"><i class="cart"></i><p>发现</p></a></li>'
		+'<li><a href="'+WapSiteUrl+'/tmpl/product_first_categroy.html"><i class="categroy"></i><p>类目</p></a></li>'
		/*+'<li><a href="'+WapSiteUrl+'/tmpl/search.html"><i class="search"></i><p>搜索</p></a></li>'*/
		+'<li><a href="'+WapSiteUrl+'/tmpl/member/member.html"><i class="member"></i><p>我的商城</p></a></li></ul>'
		+'</div>';
$("#footer").html(e+n);var a=getCookie("key");$("#logoutbtn").click(function(){var a=getCookie("username");var e=getCookie("key");var i="wap";$.ajax({type:"get",url:ApiUrl+"/index.php?act=logout",data:{username:a,key:e,client:i},success:function(a){if(a){delCookie("username");delCookie("key");location.href=WapSiteUrl}}})})

	//小能客服js文件引入
    loadJs('http://dl.ntalker.com/js/b2b/ntkfstat.js?siteid=hf_1000');

	//百度统计代码
	var _hmt = _hmt || [];
	var hm = document.createElement("script");
	hm.src = "//hm.baidu.com/hm.js?6d62f1ab694b4458abbfea20b89761f5";
	var s = document.getElementsByTagName("script")[0];
	s.parentNode.insertBefore(hm, s);
});












var name = getCookie('UNAME')? getCookie('UNAME'):'';
var uid = getCookie('S[MEMBER]') ? getCookie('S[MEMBER]'):'';

//小能客服通用参数配置
NTKF_PARAM = {
	siteid: 'hf_1000',		//企业ID，必填，取值参见文档开始表
	sellerid:"",
	settingid: 'hf_1000_1508484886546',		//缺省客服配置ID，必填，取值参见文档开始表
	uid:uid,		//用户ID,未登录可以为空
	uname:name,		//用户名，未登录可以为空
	ntalkerparam:{}
} ;
   