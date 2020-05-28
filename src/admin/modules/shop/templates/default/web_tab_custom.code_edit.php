<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<script type="text/javascript">
  var SHOP_SITE_URL = "<?php echo SHOP_SITE_URL; ?>";
  var UPLOAD_SITE_URL = "<?php echo UPLOAD_SITE_URL; ?>";
</script>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title"><a class="back" href="index.php?act=web_config&op=web_tab_custom" title="返回<?php echo '板块区';?>列表"><i class="fa fa-arrow-circle-o-left"></i></a>
      <div class="subject">
        <h3>自定义板块管理 - 设计“<?php echo $output['web_array']['web_name']?>”板块</h3>
        <h5><?php echo $lang['nc_web_index_subhead'];?></h5>
      </div>
    </div>
  </div>
  <!-- 操作说明 -->
  <div class="explanation" id="explanation">
    <div class="title" id="checkZoom"><i class="fa fa-lightbulb-o"></i>
      <h4 title="<?php echo $lang['nc_prompts_title'];?>"><?php echo $lang['nc_prompts'];?></h4>
      <span id="explanationZoom" title="<?php echo $lang['nc_prompts_span'];?>"></span> </div>
    <ul>
      <li><?php echo $lang['web_config_edit_help1'];?></li>
      <li><?php echo $lang['web_config_edit_help2'];?></li>
      <li><?php echo $lang['web_config_edit_help3'];?></li>
    </ul>
  </div>
  <div class="ncap-form-all">
    <dl class="row">
      <dt class="tit">
        <label><?php echo $lang['web_config_edit_html'].$lang['nc_colon'];?></label>
      </dt>
      <dd class="opt">
        <div class="home-templates-board-layout style-<?php echo $output['web_array']['style_name'];?>">

          <div class="left">
            <dl id="left_tit">
            </dl>
          </div>

          <div class="wwi-topbanner">
            <dl>
              <dt>
              <h4>图片组</h4>
              <a href="JavaScript:show_dialog('upload_adv');"><?php echo $lang['nc_edit'];?></a></dt>
      <dd class="adv-pic" style="height:300px;">
        <div id="picture_adv" class="picture">
          <?php if(is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) {
            $adv = current($output['code_adv']['code_info']);
            ?>
            <?php if(is_array($adv) && !empty($adv)) { ?>
              <img src="<?php echo UPLOAD_SITE_URL.'/'.$adv['pic_img'];?>"/>
            <?php } ?>
          <?php } ?>
        </div>
      </dd>
    </dl>
  </div>



</div>
</dd>
</dl>

</div>
<div class="bot"><a href="index.php?act=web_config&op=tab_custom_web_html&web_id=<?php echo $_GET['web_id'];?>" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['web_config_web_html'];?></a> </div>
</div>




<!-- 标题图片 -->
<div id="upload_tit_dialog" style="display:none;">
  <div class="s-tips"><i class="fa fa-lightbulb-o"></i><?php echo $lang['web_config_prompt_tit'];?></div>
  <form id="upload_tit_form" name="upload_tit_form" enctype="multipart/form-data" method="post" action="index.php?act=web_config&op=upload_pic" target="upload_pic">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="web_id" value="<?php echo $output['code_tit']['web_id'];?>">
    <input type="hidden" name="code_id" value="<?php echo $output['code_tit']['code_id'];?>">
    <input type="hidden" name="tit[pic]" value="<?php echo $output['code_tit']['code_info']['pic'];?>">
    <input type="hidden" name="tit[url]" value="">
    <div class="ncap-form-default">
      <dl class="row">
        <dt class="tit"><?php echo $lang['web_config_upload_type'].$lang['nc_colon'];?></dt>
        <dd class="opt">
          <label title="<?php echo $lang['web_config_upload_pic'];?>">
            <input type="radio" name="tit[type]" value="pic" onclick="upload_type('tit');" <?php if($output['code_tit']['code_info']['type'] != 'txt'){ ?>checked="checked"<?php } ?>>
            <span><?php echo $lang['web_config_upload_pic'];?></span></label>
          <label title="<?php echo '文字类型';?>">
            <input type="radio" name="tit[type]" value="txt" onclick="upload_type('tit');" <?php if($output['code_tit']['code_info']['type'] == 'txt'){ ?>checked="checked"<?php } ?>>
            <span>文字部分</span></label>
          <p class="notic"></p>
        </dd>
      </dl>
      <dl id="upload_tit_type_pic" class="row" <?php if($output['code_tit']['code_info']['type'] == 'txt'){ ?>style="display:none;"<?php } ?>>
        <dt class="tit"><?php echo $lang['web_config_upload_tit'].$lang['nc_colon'];?></dt>
        <dd class="opt">
          <div class="input-file-show"> <span class="type-file-box">
            <input type='text' name='textfield' id='textfield1' class='type-file-text' />
            <input type='button' name='button' id='button1' value='选择上传...' class='type-file-button' />
            <input name="pic" id="pic" type="file" class="type-file-file" size="30">
            </span></div>
          <p class="notic"><?php echo $lang['web_config_upload_tit_tips'];?></p>
        </dd>
      </dl>
      <div id="upload_tit_type_txt" <?php if($output['code_tit']['code_info']['type'] != 'txt'){ ?>style="display:none;"<?php } ?>>
        <dl class="row">
          <dt class="tit"><?php echo '楼层编号';?></dt>
          <dd class="opt">
            <input class="input-txt" type="text" name="tit[floor]" id="tit_floor" value="<?php echo $output['code_tit']['code_info']['floor'];?>">
            <p class="notic"><?php echo '如1F、2F、3F。';?></p>
          </dd>
        </dl>
        <dl class="row">
          <dt class="tit"><?php echo '版块标题';?></dt>
          <dd class="opt">
            <input class="input-txt" type="text" name="tit[title]" id="tit_title" value="<?php echo $output['code_tit']['code_info']['title'];?>">
            <p class="notic"><?php echo '如鞋包配饰、男女服装、运动户外。';?></p>
          </dd>
        </dl>
      </div>
      <div class="bot"><a href="JavaScript:void(0);" onclick="$('#upload_tit_form').submit();" class="ncap-btn-big ncap-btn-green"><?php echo $lang['nc_submit'];?></a></div>
    </div>
  </form>
</div>

<!-- 切换广告图片 -->
<div id="upload_adv_dialog" class="upload_adv_dialog" style="display:none;">
  <div class="s-tips"><i class="fa fa-lightbulb-o"></i><?php echo '小提示：单击图片选中修改，拖动可以排序，最少保留1个，最多可加5个，保存后生效。';?></div>
  <form id="upload_adv_form" name="upload_adv_form" enctype="multipart/form-data" method="post" action="index.php?act=web_config&op=slide_adv" target="upload_pic">
    <input type="hidden" name="web_id" value="<?php echo $output['code_adv']['web_id'];?>">
    <input type="hidden" name="code_id" value="<?php echo $output['code_adv']['code_id'];?>">
    <div class="ncap-form-all">
      <dl class="row">
        <dt class="tit"><?php echo '已上传图片';?></dt>
        <dd class="opt">
          <ul class="adv dialog-adv-s1">
            <?php if (is_array($output['code_adv']['code_info']) && !empty($output['code_adv']['code_info'])) { ?>
              <?php foreach ($output['code_adv']['code_info'] as $key => $val) { ?>
                <?php if (is_array($val) && !empty($val)) { ?>
                  <li slide_adv_id="<?php echo $val['pic_id'];?>">
                    <div class="adv-pic"><span class="ac-ico" onclick="del_slide_adv(<?php echo $val['pic_id'];?>);"></span><img onclick="select_slide_adv(<?php echo $val['pic_id'];?>);" title="<?php echo $val['pic_name'];?>" src="<?php echo UPLOAD_SITE_URL.'/'.$val['pic_img'];?>"/></div>
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_id]" value="<?php echo $val['pic_id'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_name]" value="<?php echo $val['pic_name'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_url]" value="<?php echo $val['pic_url'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_surl]" value="<?php echo $val['pic_surl'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_sname]" value="<?php echo $val['pic_sname'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_simg]" value="<?php echo $val['pic_simg'];?>" type="hidden">
                    <input name="adv[<?php echo $val['pic_id'];?>][pic_img]" value="<?php echo $val['pic_img'];?>" type="hidden">

                    <textarea style="display:none;" name="adv[<?php echo $val['pic_id'];?>][extra_fields]"><?php echo $val['extra_fields'];?></textarea>
                  </li>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </ul>
          <a class="ncap-btn" href="JavaScript:add_slide_adv();"><i class="fa fa-plus"></i><?php echo '新增图片';?>&nbsp;(最多5个)</a></dd>
      </dl>
    </div>
    <div id="upload_slide_adv" class="ncap-form-default" style="display:none;">
      <dl class="row">
        <dt class="tit"><?php echo '文字标题';?></dt>
        <dd class="opt">
          <input type="hidden" name="slide_id" value="">
          <input class="input-txt" type="text" name="slide_pic[pic_name]" value="">
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo $lang['web_config_upload_url'];?></label>
        </dt>
        <dd class="opt">
          <input name="slide_pic[pic_url]" value="" class="input-txt" type="text">
          <p class="notic"><?php echo $lang['web_config_adv_url_tips'];?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo $lang['web_config_upload_adv_pic'].$lang['nc_colon'];?></dt>
        <dd class="opt">
          <div class="input-file-show"><span class="type-file-box">
            <input type='text' name='textfield' id='textfield1' class='type-file-text' />
            <input type='button' name='button' id='button1' value='选择上传...' class='type-file-button' />
            <input name="pic" id="pic" type="file" class="type-file-file" size="30">
            </span></div>
          <p class="notic"><?php echo $lang['web_config_upload_pic_tips'];?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit"><?php echo '文字标题2';?></dt>
        <dd class="opt">
          <input class="input-txt" type="text" name="slide_pic[pic_sname]" value="">
          <p class="notic"></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo '图片链接地址，非上传图';?></label>
        </dt>
        <dd class="opt">
          <input name="slide_pic[pic_simg]" value="" class="input-txt" type="text">
          <p class="notic"><?php echo $lang['web_config_adv_url_tips'];?></p>
        </dd>
      </dl>
      <dl class="row">
        <dt class="tit">
          <label><?php echo '跳转连接2';?></label>
        </dt>
        <dd class="opt">
          <input name="slide_pic[pic_surl]" value="" class="input-txt" type="text">
          <p class="notic"><?php echo $lang['web_config_adv_url_tips'];?></p>
        </dd>
      </dl>

      <dl class="row">
        <dt class="tit">
          <label>扩展字段（多个字段以换行分隔）</label>
        </dt>
        <dd class="opt">
          <textarea name="slide_pic[extra_fields]" class="input-txt" ></textarea>
          <p class="notic"><?php echo $lang['web_config_adv_url_tips'];?></p>
        </dd>
      </dl>
      <div class="bot"><a href="JavaScript:void(0);" onclick="$('#upload_adv_form').submit();" class="ncap-btn-big ncap-btn-green"><?php echo $lang['web_config_save'];?></a></div>
    </div>
  </form>
</div>



<!-- 商品推荐模块 -->
<div id="recommend_list_dialog" style="display:none;">
  <div class="s-tips"><i></i>商品组</div>
  <form id="recommend_list_form">
    <input type="hidden" name="web_id" value="<?php echo $output['code_recommend_list']['web_id'];?>">
    <input type="hidden" name="code_id" value="<?php echo $output['code_recommend_list']['code_id'];?>">
    <div id="recommend_input_list" style="display:none;"><!-- 推荐拖动排序 --></div>
    <?php if (is_array($output['code_recommend_list']['code_info']) && !empty($output['code_recommend_list']['code_info'])) { ?>
      <?php foreach ($output['code_recommend_list']['code_info'] as $key => $val) { ?>
        <div class="ncap-form-default" select_recommend_id="<?php echo $key;?>">
          <dl class="row">
            <dt class="tit"> <?php echo $lang['web_config_recommend_title'];?></dt>
            <dd class="opt">
              <input name="recommend_list[<?php echo $key;?>][recommend][name]" value="<?php echo $val['recommend']['name'];?>" type="text" class="input-txt">
              <p class="notic"><?php echo $lang['web_config_recommend_tips'];?></p>
            </dd>
          </dl>
        </div>
        <div class="ncap-form-all" select_recommend_id="<?php echo $key;?>">
          <dl class="row">
            <dt class="tit"><?php echo $lang['web_config_recommend_goods'];?></dt>
            <dd class="opt">
              <ul class="dialog-goodslist-s1 goods-list">
                <?php if(!empty($val['goods_list']) && is_array($val['goods_list'])) { ?>
                  <?php foreach($val['goods_list'] as $k => $v) { ?>
                    <li id="select_recommend_<?php echo $key;?>_goods_<?php echo $k;?>">
                      <div ondblclick="del_recommend_goods(<?php echo $v['goods_id'];?>);" class="goods-pic"> <span class="ac-ico" onclick="del_recommend_goods(<?php echo $v['goods_id'];?>);"></span> <span class="thumb size-72x72"><i></i><img select_goods_id="<?php echo $v['goods_id'];?>" title="<?php echo $v['goods_name'];?>" goods_name="<?php echo $v['goods_name'];?>" src="<?php echo strpos($v['goods_pic'],'http')===0 ? $v['goods_pic']:UPLOAD_SITE_URL."/".$v['goods_pic'];?>" onload="javascript:DrawImage(this,72,72);" /></span></div>
                      <div class="goods-name"><a href="<?php echo SHOP_SITE_URL."/index.php?act=goods&goods_id=".$v['goods_id'];?>" target="_blank"><?php echo $v['goods_name'];?></a></div>
                      <input name="recommend_list[<?php echo $key;?>][goods_list][<?php echo $v['goods_id'];?>][goods_id]" value="<?php echo $v['goods_id'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][goods_list][<?php echo $v['goods_id'];?>][market_price]" value="<?php echo $v['market_price'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][goods_list][<?php echo $v['goods_id'];?>][goods_name]" value="<?php echo $v['goods_name'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][goods_list][<?php echo $v['goods_id'];?>][goods_price]" value="<?php echo $v['goods_price'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][goods_list][<?php echo $v['goods_id'];?>][goods_pic]" value="<?php echo $v['goods_pic'];?>" type="hidden">
                    </li>
                  <?php } ?>
                <?php } elseif (!empty($val['pic_list']) && is_array($val['pic_list'])) { ?>
                  <?php foreach($val['pic_list'] as $k => $v) { ?>
                    <li id="select_recommend_<?php echo $key;?>_pic_<?php echo $k;?>" style="display:none;">
                      <input name="recommend_list[<?php echo $key;?>][pic_list][<?php echo $v['pic_id'];?>][pic_id]" value="<?php echo $v['pic_id'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][pic_list][<?php echo $v['pic_id'];?>][pic_name]" value="<?php echo $v['pic_name'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][pic_list][<?php echo $v['pic_id'];?>][pic_url]" value="<?php echo $v['pic_url'];?>" type="hidden">
                      <input name="recommend_list[<?php echo $key;?>][pic_list][<?php echo $v['pic_id'];?>][pic_img]" value="<?php echo $v['pic_img'];?>" type="hidden">
                    </li>
                  <?php } ?>
                <?php } ?>
              </ul>
            </dd>
          </dl>
        </div>
      <?php } ?>
    <?php } ?>

    <div id="add_recommend_list" style="display:none;"></div>
    <div class="ncap-form-all">
      <dl class="row">
        <dt class="tit"><?php echo $lang['web_config_recommend_add_goods'];?></dt>
        <dd class="opt">
          <div class="search-bar">
            <label id="recommend_gcategory">商品分类
              <input type="hidden" id="cate_id" name="cate_id" value="0" class="mls_id" />
              <input type="hidden" id="cate_name" name="cate_name" value="" class="mls_names" />
              <select>
                <option value="0"><?php echo $lang['nc_please_choose'];?></option>
                <?php if(!empty($output['goods_class']) && is_array($output['goods_class'])) { ?>
                  <?php foreach($output['goods_class'] as $k => $v) { ?>
                    <option value="<?php echo $v['gc_id'];?>"><?php echo $v['gc_name'];?></option>
                  <?php } ?>
                <?php } ?>
              </select>
            </label>
            <input type="text" value="" name="recommend_goods_name" id="recommend_goods_name" placeholder="输入商品名称或SKU编号" class="txt w150">
            <a href="JavaScript:void(0);" onclick="get_recommend_goods();" class="ncap-btn"><?php echo $lang['nc_query'];?></a></div>
          <div id="show_recommend_goods_list" class="show-recommend-goods-list"></div>
        </dd>
      </dl>
    </div>
    <div class="bot"><a href="JavaScript:void(0);" onclick="update_recommend();" class="ncap-btn-big ncap-btn-green"><span><?php echo $lang['web_config_save'];?></span></a></div>
  </form>
</div>

<!-- 品牌模块 -->


<iframe style="display:none;" src="" name="upload_pic"></iframe>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.css" rel="stylesheet" type="text/css">
<script src="<?php echo RESOURCE_SITE_URL;?>/js/colorpicker/evol.colorpicker.min.js"></script>
<script src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js"></script>
<script src="<?php echo RESOURCE_SITE_URL;?>/js/waypoints.js"></script>
<script src="<?php echo ADMIN_RESOURCE_URL?>/js/web_index.js"></script>
<script src="<?php echo ADMIN_RESOURCE_URL?>/js/web_focus.js"></script>