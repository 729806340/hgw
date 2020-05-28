<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<div class="tabmenu">
    <?php include template('layout/submenu');?>
</div>
<div class="ncsc-form-default">
    <form id="add_form" action="index.php?act=store_promotion_xianshi&op=xianshi_save" method="post">
        <input type="hidden" value="<?php echo $output['config_xianshi_info']['config_xianshi_id'];?>" name="config_xianshi_id">
        <dl>
            <dt><i class="required">*</i><?php echo $lang['xianshi_name'];?><?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="xianshi_name" name="xianshi_name" readonly style="background:#E7E7E7 none;" type="text"  maxlength="25" class="text w400" value="<?php echo $output['config_xianshi_info']['config_xianshi_name'];?>"/>
                <span></span>
                <p class="hint"><?php echo $lang['xianshi_name_explain'];?></p>
            </dd>
        </dl>
        <dl>
            <dt>活动标题<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="xianshi_title" name="xianshi_title" readonly style="background:#E7E7E7 none;" type="text"  maxlength="10" class="text w200" value="<?php echo $output['config_xianshi_info']['config_xianshi_title'];?>"/>
                <span></span>
                <p class="hint"><?php echo $lang['xianshi_title_explain'];?></p>
            </dd>
        </dl>
        <dl>
            <dt>活动描述<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="xianshi_explain" name="xianshi_explain" readonly style="background:#E7E7E7 none;" type="text"  maxlength="30" class="text w400" value="<?php echo $output['config_xianshi_info']['config_xianshi_explain'];?>"/>
                <span></span>
                <p class="hint"><?php echo $lang['xianshi_explain_explain'];?></p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i><?php echo $lang['start_time'];?><?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="start_time" name="start_time" readonly style="background:#E7E7E7 none;" value="<?php echo date('Y-m-d H:i',$output['config_xianshi_info']['config_start_time']) ?>" type="text" class="text w130" />
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i><?php echo $lang['end_time'];?><?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="end_time" name="end_time" readonly style="background:#E7E7E7 none;" value="<?php echo date('Y-m-d H:i',$output['config_xianshi_info']['config_end_time']) ?>" type="text" class="text w130"/>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>购买下限<?php echo $lang['nc_colon'];?></dt>
            <dd>
                <input id="lower_limit" name="lower_limit" type="text" class="text w130" value=""/><span></span>
                <p class="hint">参加活动的最低购买数量，默认为1</p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input id="submit_button" type="submit" class="submit" value="<?php echo $lang['nc_submit'];?>"></label>
        </div>
    </form>
</div>
