<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<style>
    .ncm-goods-gift {
        text-align: left;
    }

    .ncm-goods-gift ul {
        display: inline-block;
        font-size: 0;
        vertical-align: middle;
    }

    .ncm-goods-gift li {
        display: inline-block;
        letter-spacing: normal;
        margin-right: 4px;
        vertical-align: top;
        word-spacing: normal;
    }

    .ncm-goods-gift li a {
        background-color: #fff;
        display: table-cell;
        height: 30px;
        line-height: 0;
        overflow: hidden;
        text-align: center;
        vertical-align: middle;
        width: 30px;
    }

    .ncm-goods-gift li a img {
        max-height: 30px;
        max-width: 30px;
    }

    input.editable, input[type="text"], input[type="number"], input[type="password"] {
        width: 48px;
    }
</style>
<div class="page" style="padding: 10px;">
    <div class="ncap-order-style">

        <form id="punish_form" method="post" action="index.php?act=store&op=add_punish"  enctype="multipart/form-data">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="store_id" id="store_id" value="<?php echo $output['store_array']['store_id'] ; ?>" />
            <div class="ncap-form-default">
                <dl class="row">
                    <dt class="tit"><em>*</em>相关渠道</dt>
                    <dd class="opt">
                        <select name="channel_id" id="channel_id" title="">
                            <option value="0">请选择渠道</option>
                            <?php
                            foreach ($output['member_fenxiao'] as $member){
                                echo "<option value='{$member['member_id']}'>{$member['member_cn_code']}</option>";
                            }
                            ?>
                        </select>
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit"><em>*</em>罚款金额</dt>
                    <dd class="opt">
                        <input title="" type="text" value="" name="cost_price" id="cost_price" class="input-txt">
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit"><em>*</em>罚款理由</dt>
                    <dd class="opt">
                        <textarea title="" rows="100" cols="50" name="cost_remark" id="cost_remark"></textarea>
                    </dd>
                </dl>
                <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a><p class="notic" style="color:red;"></p></div>
            </div>
        </form>

    </div>

</div>
<script type="text/javascript">
    $(function () {
        $('#submitBtn').click(function (e) {
            var channel_id = $('#channel_id').val();
            var store_id = $('#store_id').val();
            var cost_price = $('#cost_price').val();
            var cost_remark = $('#cost_remark').val();
            if(cost_price===''||cost_remark ==='') return alert('罚款金额或者备注不能为空');
            $.ajax({url: 'index.php?act=store&op=add_punish',
                type: 'POST',
                dataType: 'json',
                data: {channel_id:channel_id,store_id:store_id,cost_price:cost_price,cost_remark:cost_remark,form_submit:'ok'}}).done(function (res) {
                console.log(res);
                if(!res.state){
                    return alert(res.msg)
                }
                alert('罚款成功');
                console.log(res);
                $('.dialog_close_button').click();
            }).fail(function (xhr,error) {
                alert('请求失败');
                console.log(xhr);
            });
            //$('#punish_form').submit();
        });
    });
</script>