<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    #gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
    <form method="post" action="index.php?act=fenxiao_channel&op=index&action=edit" target="_parent" name="store_certification_form" id="store_certification_form" enctype="multipart/form-data">
        <input type="hidden" name="form_submit" value="ok" />
        <input type="hidden" name="member_fenxiao_id" id="member_fenxiao_id" value="<?php echo $output['member_fenxiao']['id']; ?>" />
        <dl>
            <dt><i class="required">*</i>渠道名称：</dt>
            <dd>
                <input name="member_cn_code" id="member_cn_code" type="text" class="text w210" readonly value="<?php echo $output['member_fenxiao']['member_cn_code'];?>" style="background:#E7E7E7 none;">
                <span></span>
                <p class="hint">如拼多多、人人店、有赞。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>渠道拼音：</dt>
            <dd>
                <input name="member_en_code" id="member_en_code" type="text" class="text w210" readonly value="<?php echo $output['member_fenxiao']['member_en_code'];?>" style="background:#E7E7E7 none;">
                <span></span>
                <p class="hint">如pinduoduo、renrendian、youzan。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>是否贴标：</dt>
            <dd>
                <input name="is_sign" <?php if ($output['member_fenxiao']['is_sign'] == 1) echo 'checked';?> type="radio" class="text" value="1" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">是&nbsp;
                <input name="is_sign" <?php if ($output['member_fenxiao']['is_sign'] == 0) echo 'checked';?> type="radio" class="text" value="0" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">否
                <span></span>
                <p class="hint"></p>
            </dd>
        </dl>
<!--        <dl>-->
<!--            <dt><i class="required">*</i>结算模式：</dt>-->
<!--            <dd>-->
<!--                <input name="billing_mode" --><?php //if ($output['member_fenxiao']['billing_mode'] == 1) echo 'checked';?><!-- type="radio" class="text" value="1" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">自己结算&nbsp;-->
<!--                <input name="billing_mode" --><?php //if ($output['member_fenxiao']['billing_mode'] == 2) echo 'checked';?><!-- type="radio" class="text" value="2" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">汉购网结算-->
<!--                <span></span>-->
<!--                <p class="hint"></p>-->
<!--            </dd>-->
<!--        </dl>-->
        <div class="bottom">
            <label class="submit-border"><input type="button" id="btn_add_certification" class="submit" value="<?php echo $lang['nc_submit'];?>" /></label>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        //页面输入内容验证
        $('#btn_add_certification').on('click', function() {
            var url = 'index.php?act=fenxiao_channel&op=index&action=edit';
            var member_fenxiao_id = $('#member_fenxiao_id').val();
            var is_sign = $('input[name="is_sign"]:checked').val();
            var billing_mode = $('input[name="billing_mode"]:checked').val();

            showDialog('确认要编辑吗？', 'confirm', '', function(){
                $.post(url, {member_fenxiao_id:member_fenxiao_id,is_sign:is_sign,billing_mode:billing_mode}, function (data) {
                    alert(data.msg);
                    location.href = data.url;
                }, 'json');
            });
        });

        $('input[nc_type="logo"]').change(function(){
            var src = getFullPath($(this)[0]);
            $('img[nc_type="logo1"]').attr('src', src);
        });
    });
</script>
