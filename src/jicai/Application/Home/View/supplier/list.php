<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分销商管理</title>
<link href="__PUBLIC__/css/reset.css" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/css/layout.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="wrap-DRP">
  
  <div class="drp-main clear">
    
    <div class="right-content fl">
      <div class="con-inside">
        <div class="location fs12">您的当前位置：<a href="#">系统首页</a> > <a href="#">订单管理</a></div>
        <div class="operation clear">
          <form method="get" action="#">
            <div class="search fl"><span class="fl"></span><i class="fl"></i>
              <input class="w100 fl" type="text" placeholder="输入关键字查询" />
            </div>
            <div class="date fl">
              <input type="text" class="w100 mh_date" placeholder="请选择起始日期" readonly="true" />
            </div>
            <div class="date fl">
              <input type="text" class="w100 mh_date" placeholder="请选择结束日期" readonly="true" />
            </div>
            <div class="query-btn fl">
              <button>查询</button>
            </div>
            <div class="reset-btn fl">
              <button>重置</button>
            </div>
          </form>
          <div class="import-order fl">
            <button class="io-btn">导入订单</button>
            <div class="import-wrap p-dialog">
              <div class="dialog">
                <div class="d-con">
                    <div class="i-inside">
                        <form method="get" action="#">
                          <select><option>商品类目</option><option>类目1</option><option>类目2</option><option>类目3</option></select>
                          <select><option>商品名字</option><option>商品1</option><option>商品2</option><option>商品3</option></select>
                          <div class="file-upload clear"><input class="fl" type="file" /><button class="fl">上传</button></div>
                        </form>
                    </div>
                    <p class="close-btn">×</p>
                </div>
              </div>
            </div>
          </div>
          <div class="export-order fl">
            <button class="eo-btn">导出订单</button>
            <div class="export-wrap p-dialog">
              <div class="dialog">
                <div class="d-con">
                  <div class="i-inside">
                  	<form method="get" action="#">
                    	<label><span>订单状态：</span><select><option>订单列表</option><option>退款订单</option><option>异常订单</option><option>已发订单</option><option>交易结算</option></select></label>
                        <div class="s-date-con clear">
                            <div class="date fl">
                              <input type="text" class="w100 mh_date" placeholder="请选择起始日期" readonly="true" />
                            </div>
                            <div class="date fl">
                              <input type="text" class="w100 mh_date" placeholder="请选择结束日期" readonly="true" />
                            </div>
                            <div class="file-export"><button>导出</button></div>
                        </div>
                    </form>
                  </div>	
                  <p class="close-btn">×</p>
                </div>
              </div>
            </div>
          </div>
          <div class="download fl"><a href="#">下载模板</a></div>
        </div>
        <!-- 另写... -->
        <div class="">
          <div class="orderball">
            <ul>
              <li class="titl bolda"><a href="#">订单列表</a></li>
              <li class="titl"><a href="#">退款订单</a></li>
              <li class="titl"><a href="#">异常订单 </a></li>
              <li class="titl"><a href="#">已发订单</a></li>
              <li class="titl borderri"><a href="#">交易结算</a></li>
            </ul>
          </div>
          <div class="teblor"></div>
          <table class="titlea">
            <tr>
              <th class="leftbor"><input type="checkbox" name="car" /></th>
              <th>序号</th>
              <th>商品名称</th>
              <th>商品数量</th>
              <th>订单编号</th>
              <th>收货人</th>
              <th>手机</th>
              <th>省</th>
              <th>市</th>
              <th>区</th>
              <th>街道</th>
              <th>付款时间</th>
              <th>物流单号</th>
              <th>物流公司</th>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr class="">
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
            <tr>
              <td class="leftbor"><input type="checkbox" name="car" /></td>
              <td>01</td>
              <td>特论述牛奶500ML</td>
              <td>1</td>
              <td>20151104191747064894326</td>
              <td>晓明</td>
              <td>136368668830</td>
              <td>湖北</td>
              <td>武汉</td>
              <td>江汉区</td>
              <td>北湖西路花园道302号</td>
              <td>2015-11-11</td>
              <td>102541268</td>
              <td>中通快递</td>
            </tr>
          </table>
          <div class="jiange clear">
            <ul>
              <li class="column no">每页显示<!--<span class="shezhi">5</span>-->
                <select class="shezhi">
                  <option>5</option>
                  <option>10</option>
                  <option>15</option>
                  <option>20</option>
                </select>
                条</li>
              <li class="column no">共<span>120</span>条数据</li>
              <li class="column no rline">当前<span>1</span>/<span>20</span>页</li>
              <!--<li class="column"><a class="shuaxin" href="#"><em>刷新列表</em></a></li>-->
            </ul>
            <ul class="rightfl">
              <li class="column"><a href="#">首页</a></li>
              <li class="column"><a href="#">上一页</a></li>
              <li class="column"><a href="#">1</a></li>
              <li class="column"><a href="#">2</a></li>
              <li class="column"><a href="#">3</a></li>
              <li class="column no">...</li>
              <li class="column"><a href="#">31</a></li>
              <li class="column"><a href="#">32</a></li>
              <li class="column"><a href="#">33</a></li>
              <li class="column"><a href="#">下一页</a></li>
              <li class="column"><a href="#">末页</a></li>
              <li class="column rline no">跳转至
                <input type="text" name="lastname" />
                页</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<script type="text/javascript" src="__PUBLIC__/js/jquery.min.js"></script>

<!--日期选择-->
<script type="text/javascript" src="__PUBLIC__/js/manhuaDate.1.0.js"></script>
<script type="text/javascript">
$(function (){
	$("input.mh_date").manhuaDate({					       
		Event : "click",//可选				       
		Left : 0,//弹出时间停靠的左边位置
		Top : -16,//弹出时间停靠的顶部边位置
		fuhao : "-",//日期连接符默认为-
		isTime : false,//是否开启时间值默认为false
		beginY : 2000,//年份的开始默认为1949
		endY :2015//年份的结束默认为2049
	});
	
});
</script>

<!--订单导入、导出弹窗-->
<script type="text/javascript">
$(function(){
	
	/*导入*/
	$(".io-btn").click(function(){
		$(".import-wrap").stop().fadeIn(500);
		$(".close-btn").click(function(){
			$(".import-wrap").fadeOut();
		})
		$("body").css({overflow:"hidden"});
	})
	
	/*导出*/
	$(".eo-btn").click(function(){
		$(".export-wrap").stop().fadeIn(500);
		$(".close-btn").click(function(){
			$(".export-wrap").fadeOut();
		})
		$("body").css({overflow:"hidden"});
	})
})
</script>

<!-- 隔行加背景色 -->
<script>
$(function(){
	
	$(".titlea tr:even").addClass("even-bg");
	$(".titlea tr").hover(function(){
		$(this).addClass("hover-bg").siblings().removeClass("hover-bg");
	},function(){
		$(this).removeClass("hover-bg");
		})
})
</script>

