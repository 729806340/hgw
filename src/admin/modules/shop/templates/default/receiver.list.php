<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div style="height:400px;overflow:scroll;">
    <form method="post" name="receiver_add" id="receiver_add" action="<?php echo urlAdminShop('receiver', 'receiver_list');?>">
        <input type="hidden" name="form_submit" value="ok" />
        <input type="hidden" value="<?php echo $output['common_info']['goods_commonid'];?>" name="commonid">
        <div class="ncap-form-default">
            <?php foreach($output['receiver_list'] as $k=>$v){?>
            <dl class="row">
                <dt class="tit"><?php echo $v['sn']?></dt>
                <dd class="opt"><?php echo $v['receiver']?></dd>
                <dd class="radio">
                    <label>是否可用<input type=checkbox name="receiver_sn[]" value="<?php echo $v['sn']?>" <?php if($v['status'] == 1){ echo 'checked="checked"';}?>></label>
                </dd>
            </dl>
            <?php }?>
            <div class="bot"><a href="javascript:void(0);" class="ncap-btn-big ncap-btn-green" nctype="btn_submit"><?php echo $lang['nc_submit'];?></a></div>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('a[nctype="btn_submit"]').click(function(){
            ajaxpost('receiver_add', '', '', 'onerror');
        });


    });
</script>