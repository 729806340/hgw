/*******************************************************************************
*
* @author lijianshe 305347185@qq.com
* @site http://www.itlife365.com/
* @licence http://www.kindsoft.net/license.php
*******************************************************************************/
KindEditor.plugin('insertcontent', function(K) {
        var editor = this, name = 'insertcontent';
		var new_html = $("#new_html").html();
		new_html=new_html.replace(/\r\n/g,"")  
        new_html=new_html.replace(/\n/g,"");
		new_html = K.unescape( new_html );
        // 点击图标时执行
        editor.clickToolbar(name, function() {
          //editor.insertHtml('这里插入要自动插入的内容哦 by itlife365.com 分享,不能换行哦，不然为无法插入哦');
         //editor.insertHtml('<div style="background-color: #000;"><a href="http://jiandanjie.com/gotourl/527278012027.html"><img src="uploadfile/image/jiandanjie.com-readmore.gif" width="30" height="19" title="点击查看宝贝详情" alt="点击查看宝贝详情" />点击这里购买——背带阔腿裤两件套asdfasdf</a></div>');
         editor.insertHtml(new_html);
       
    });
});