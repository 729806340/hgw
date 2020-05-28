<?php defined('ByShopWWI') or exit('Access Invalid!');

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/9/3
 * Time: 14:02
 */
?>

<style type="text/css">
    .d_inline {
        display: inline;
    }
</style>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回<?php echo $lang['manage'];?>列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>商品管理 - 编辑商品税率信息</h3>
                <h5>修改商品税率</h5>
            </div>
        </div>
    </div>
    <div class="homepage-focus" nctype="editStoreContent">
        <form id="store_form" method="post">
            <input type="hidden" name="form_submit" value="ok" />
            <input type="hidden" name="goods_common_id" value="<?php echo $output['goodsInfo']['goods_commonid'];?>" />
            <div class="ncap-form-default">
                <dl class="row">
                    <dt class="tit">
                        <label>商品ID</label>
                    </dt>
                    <dd class="opt"><?php echo $output['goodsInfo']['goods_commonid'];?><span class="err"></span>
                        <p class="notic"></p>
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit">
                        <label>商品名称</label>
                    </dt>
                    <dd class="opt">
                        <?php echo $output['goodsInfo']['goods_name'];?>
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit">
                        <label for="tax_input"> 进项税率 </label>
                    </dt>
                    <dd class="opt">
                        <input type="number" name="tax_input" id="tax_input" value="<?php echo $output['goodsInfo']['tax_input'];?>" >%
                        <span class="err"></span>
                        <p class="notic"></p>
                    </dd>
                </dl>
                <dl class="row">
                    <dt class="tit">
                        <label for="tax_output"> 销项税率 </label>
                    </dt>
                    <dd class="opt">
                        <input type="number" name="tax_output" id="tax_output" value="<?php echo $output['goodsInfo']['tax_output'];?>" >%
                        <span class="err"></span>
                        <p class="notic"></p>
                    </dd>
                </dl>
                <div class="bot"><a href="JavaScript:void(0);" class="ncap-btn-big ncap-btn-green" id="submitBtn"><?php echo $lang['nc_submit'];?></a></div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>

<script type="text/javascript">
    var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
    $(function(){
        $("#submitBtn").click(function(){
            $("#store_form").submit();
        });

        $("#btn_fail").click(function(){
            $("#joinin_form").submit();
        });


        $('div[nctype="editStoreContent"] > .title').find('li').click(function(){
            $(this).children().addClass('current').end().siblings().children().removeClass('current');
            var _index = $(this).index();
            var _form = $('div[nctype="editStoreContent"]').find('form');
            _form.hide();
            _form.eq(_index).show();
        });
    });
</script>

