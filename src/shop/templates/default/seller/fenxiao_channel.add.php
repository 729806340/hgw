<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<style>
    #gcategory select {margin-left:4px}
</style>
<div class="ncsc-form-default">
    <form method="post" action="index.php?act=fenxiao_channel&op=index&action=add" target="_parent" name="store_certification_form" id="store_certification_form" enctype="multipart/form-data">
        <input type="hidden" name="form_submit" value="ok" />
        <dl>
            <dt><i class="required">*</i>渠道名称：</dt>
            <dd>
                <input name="member_cn_code" id="member_cn_code" type="text" class="text w210" value="<?php echo $output['member_fenxiao']['member_cn_code'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
                <span></span>
                <p class="hint">如拼多多、人人店、有赞。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>渠道拼音：</dt>
            <dd>
                <input name="member_en_code" id="member_en_code" type="text" class="text w210" value="<?php echo $output['member_fenxiao']['member_en_code'];?>" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
                <span></span>
                <p class="hint">如pinduoduo、renrendian、youzan。</p>
            </dd>
        </dl>
        <dl>
            <dt><i class="required">*</i>是否贴标：</dt>
            <dd>
                <input name="is_sign" type="radio" class="text" value="1" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">是&nbsp;
                <input name="is_sign" checked type="radio" class="text" value="0" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">否
                <span></span>
                <p class="hint"></p>
            </dd>
        </dl>
<!--        <dl>-->
<!--            <dt><i class="required">*</i>结算模式：</dt>-->
<!--            <dd>-->
<!--                <input name="billing_mode" checked type="radio" class="text" value="1" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">自己结算&nbsp;-->
<!--                <input name="billing_mode" type="radio" class="text" value="2" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">汉购网结算-->
<!--                <span></span>-->
<!--                <p class="hint"></p>-->
<!--            </dd>-->
<!--        </dl>-->
        <dl>
            <dt><i class="required">*</i>密码：</dt>
            <dd>
                <input name="password" id="password" type="password" class="text w210" value="" style="background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
                <span></span>
                <p class="hint">6-20位字符，可由英文、数字及标点符号组成。</p>
            </dd>
        </dl>
        <div class="bottom">
            <label class="submit-border"><input type="button" id="btn_add_certification" class="submit" value="<?php echo $lang['nc_submit'];?>" /></label>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        //页面输入内容验证
        $('#btn_add_certification').on('click', function() {
            var url = 'index.php?act=fenxiao_channel&op=index&action=add';
            var member_cn_code = $('#member_cn_code').val();
            var member_en_code = $('#member_en_code').val();
            var is_sign = $('input[name="is_sign"]:checked').val();
            var billing_mode = $('input[name="billing_mode"]:checked').val();
            var password = $('#password').val();

            showDialog('确认要添加吗？', 'confirm', '', function(){
                $.post(url, {member_cn_code:member_cn_code,member_en_code:member_en_code,is_sign:is_sign,billing_mode:billing_mode,password:password}, function (data) {
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
