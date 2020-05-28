<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    .sku-list li img{height: 24px;width: 24px;}
    .sku-list li span{line-height: 24px;vertical-align: top;}
    .sku-list li {margin: 5px 0; }
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=redpacket&op=rptlist" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>平台红包 - 红包详情</h3>
        <h5>平台红包新增与管理</h5>
      </div>
    </div>
  </div>
  
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit">红包名称：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_title'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">领取方式：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_gettype_text'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">有效期：</dt>
        <dd class="opt">
            <?php echo @date('Y-m-d',$output['t_info']['rpacket_t_start_date']);?> 至 
            <?php echo @date('Y-m-d',$output['t_info']['rpacket_t_end_date']);?>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">面额：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_price'];?>&nbsp;&nbsp;<?php echo $lang['currency_zh'];?></dd>
      </dl>
      <?php if($output['t_info']['rpacket_t_gettype_key']=='points'){?>
      <dl class="row" id="points_dl">
        <dt class="tit">兑换所需积分：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_points'];?></dd>
      </dl>
      <?php }?>
      <dl class="row">
        <dt class="tit">可发放总数：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_total'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">每人限领：</dt>
        <dd class="opt"><?php echo ($t=$output['t_info']['rpacket_t_eachlimit']) > 0?$t:'不限';?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">消费限额：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_limit'];?>&nbsp;&nbsp;<?php echo $lang['currency_zh'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">会员级别：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_mgradelimittext'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">红包描述：</dt>
        <dd class="opt">
            <textarea id="rpt_desc" name="rpt_desc" class="w300" readonly ><?php echo $output['t_info']['rpacket_t_desc'];?></textarea>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">红包图片：</dt>
        <dd class="opt">
            <?php if ($output['t_info']['rpacket_t_customimg_url']){?>
              <img onload="javascript:DrawImage(this,220,95);" src="<?php echo $output['t_info']['rpacket_t_customimg_url'];?>"/>
            <?php } ?>
        </dd>
      </dl>


        <!-- 新增商品范围-->

        <dl class="row">
            <dt class="tit">
                <label for="rpt_gettype">红包适用范围</label>
            </dt>
            <dd class="opt">
                <?php
                    // echo $output['t_info']['rpacket_t_range']==1?'仅选定商品适用':($output['t_info']['rpacket_t_range']==2?'除选定商品外适用':'全场通用');
                    if ($output['t_info']['rpacket_t_range']==1) { echo "仅选定商品适用"; }
                    elseif ($output['t_info']['rpacket_t_range']==2) { echo "除选定商品外适用"; }
                    elseif ($output['t_info']['rpacket_t_range']==3) { echo "选定商品分类适用"; }
                    else{ echo "全场通用"; }
                ?>
            </dd>
        </dl>

        <dl class="row"  <?php echo  $output['t_info']['rpacket_t_range']==0?'style="display: none;"':'' ?>>
            <dt class="tit">
            </dt>
            <dd class="opt">
                <?php
                $goodsList = $output['t_info']['goodsList'];
                $goodsClassList = $output['t_info']['goodsClassList'];
                ?>
                    <p>已经选定商品：</p>
                    <ul class="sku-list">
                        <?php
                        if(is_array($goodsList))
                            foreach ($goodsList as $goods){
                                echo '<li><img src="/data/upload/shop/store/goods/'.$goods['store_id'].'/'.$goods['goods_images'].'"> <span>'.$goods['goods_name'].'</span></li>';
                            }
                        if(is_array($goodsClassList))
                            foreach ($goodsClassList as $gc){
                                echo '<li><span>'.$gc['menu'].'</span></li>';
                            }
                        ?>
                    </ul>

            </dd>
        </dl>

      <dl class="row">
        <dt class="tit">最近修改时间：</dt>
        <dd class="opt"><?php echo @date('Y-m-d H:i:s',$output['t_info']['rpacket_t_updatetime']);?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">最近修改人：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_creator_name'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">已领取：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_giveout'];?>  张</dd>
      </dl>
      <dl class="row">
        <dt class="tit">已使用：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_used'];?>  张</dd>
      </dl>
      <dl class="row">
        <dt class="tit">状态：</dt>
        <dd class="opt"><?php echo $output['t_info']['rpacket_t_state_text'];?></dd>
      </dl>
      <dl class="row">
        <dt class="tit">是否推荐：</dt>
        <dd class="opt">
            <?php if($output['t_info']['rpacket_t_recommend'] == '1'){ echo $lang['nc_yes']; }else{ echo $lang['nc_no']; } ?>
        </dd>
      </dl>
      <?php if($output['t_info']['rpacket_t_gettype_key'] == 'free'){ ?>
      <dl class="row">
        <dt class="tit">推广链接：</dt>
        <dd class="opt">
            <input type="text" onclick="oCopy(this)" value="<?php echo SHOP_SITE_URL.DS;?>index.php?act=redpacket&op=getredpacket&tid=<?php echo $output['t_info']['rpacket_t_id'];?>" readonly class='w350'/>
<script>
	function oCopy(obj){
		obj.select();
        if (!!window.ActiveXObject || "ActiveXObject" in window) {
            js=obj.createTextRange();
            js.execCommand("Copy")
            alert("复制成功!");
        }else{
            alert('在“推广链接”文本框上右击鼠标，选择“复制”将推广链接复制到剪切板');
        }
	}
</script>
            <span class="err"></span>
            <p class="notic">可以复制该链接对免费领取的红包进行推广</p>
        </dd>
      </dl>
      <?php }?>
      <?php if($output['t_info']['rpacket_t_gettype_key'] == 'pwd' && $output['t_info']['rpacket_t_isbuild']==0){ ?>
      <dl class="row">
        <dt class="tit">卡密生成状态：</dt>
        <dd class="opt">未生成
            &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" id="build_rp">点击生成红包卡密</a>
        </dd>
      </dl>
      <?php }?>
    </div>
    
    <div id="flexigrid"></div>
    <div class="ncap-form-default">
        <div style="text-align: center;"><a href="index.php?act=redpacket&op=rptlist" class="ncap-btn-big ncap-btn-green" id="submitBtn">返回列表</a></div>
    </div>
</div>
<script>
$(function(){
    var flexUrl = 'index.php?act=redpacket&op=rplist_xml&tid=<?php echo $output['t_info']['rpacket_t_id'];?>&gtype=<?php echo $output['t_info']['rpacket_t_gettype_key'];?>';

    $("#flexigrid").flexigrid({
        url: flexUrl,
        colModel: [
            {display: '红包编码', name: 'rpacket_code', width: 250, sortable: false, align: 'center'},
            <?php if ($output['t_info']['rpacket_t_gettype_key'] == 'pwd'){ ?>
            {display: '卡密', name: 'rpacket_pwd', width: 250, sortable: false, align: 'center'},
            <?php } ?>
            {display: '使用状态', name: 'rpacket_statetext', width: 150, sortable: false, align: 'center'},
            {display: '所属会员', name: 'rpacket_owner_name', width: 150, sortable: false, align: 'left'},
            {display: '领取时间', name: 'rpacket_active_datetext', width: 200, sortable: false, align: 'center'}
        ],
        buttons : [
            {display: '<i class="fa fa-file-excel-o"></i>导出数据', name : 'csv', bclass : 'csv', title : '将选定行数据导出excel文件,如果不选中行，将导出列表所有数据', onpress : fg_operate }
        ],
        sortname: "rpacket_id",
        sortorder: "desc",
        title: '红包列表'
    });

    $("#build_rp").click(function(){
    	ajaxget("<?php echo urlAdminShop('redpacket','rpbulidpwd',array('tid'=>$output['t_info']['rpacket_t_id']));?>");
    });
});
function fg_operate(name, grid) {
	if (name == 'csv') {
    	var itemlist = new Array();
        if($('.trSelected',grid).length>0){
            $('.trSelected',grid).each(function(){
            	itemlist.push($(this).attr('data-id'));
            });
        }
        fg_csv(itemlist);
    }
}
function fg_csv(ids) {
    id = ids.join(',');
    window.location.href = 'index.php?act=redpacket&op=export_step1&tid=<?php echo $output['t_info']['rpacket_t_id'];?>&rid=' + id;
}
</script>