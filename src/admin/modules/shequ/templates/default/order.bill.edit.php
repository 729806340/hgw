<?php defined('ByShopWWI') or exit('Access Invalid!');?>
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
    .input-file-show a{display: block;position: relative;z-index: 1}
    .input-file-show span{width: 80px; height: 30px;position: absolute;left: 0;top: 0;z-index: 2;cursor: pointer;}
    .input-file{width: 80px;height: 30px;padding: 0;margin: 0;border: none 0;opacity: 0;filter: alpha(opacity=0);cursor: pointer;}
    .upload-image{float:left; width:150px; height:80px; margin-right:5px;}
    .upload-image img{ max-height:80px; max-width:150px;}
</style>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="javascript:history.back(-1)" title="返回列表"><i class="fa fa-arrow-circle-o-left"></i></a>
            <div class="subject">
                <h3>调整订单商品行结算数据</h3>
                <h5><?php echo $lang['order_manage_subhead'];?></h5>
            </div>
        </div>
    </div>
    <div class="ncap-order-style">
        <div class="titile">
            <h3></h3>
        </div>

        <div class="ncap-order-details">
            <div class="tabs-panels">
                <div class="misc-info">
                    <h4>订单信息</h4>
                    <dl>
                        <dt><?php echo $lang['order_number'];?><?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo $output['order_info']['order_sn'];?><?php if ($output['order_info']['order_type'] == 2) echo '[预定]';?><?php if ($output['order_info']['order_type'] == 3) echo '[门店自提]';?></dd>
                        <dt>订单来源<?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo str_replace(array(1,2), array('PC端','移动端'), $output['order_info']['order_from']);?></dd>
                        <dt><?php echo $lang['order_time'];?><?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo date('Y-m-d H:i:s',$output['order_info']['add_time']);?></dd>
                    </dl>
                    <?php if(intval($output['order_info']['payment_time'])){?>
                        <dl>
                            <dt>支付单号<?php echo $lang['nc_colon'];?></dt>
                            <dd><?php echo $output['order_info']['pay_sn'];?></dd>
                            <dt><?php echo $lang['payment'];?><?php echo $lang['nc_colon'];?></dt>
                            <dd><?php echo orderPaymentName($output['order_info']['payment_code']);?></dd>
                            <dt><?php echo $lang['payment_time'];?><?php echo $lang['nc_colon'];?></dt>
                            <dd><?php echo intval(date('His',$output['order_info']['payment_time'])) ? date('Y-m-d H:i:s',$output['order_info']['payment_time']) : date('Y-m-d',$output['order_info']['payment_time']);?></dd>
                        </dl>
                    <?php } else if ($output['order_info']['payment_code'] == 'offline') { ?>
                        <dl>
                            <dt><?php echo $lang['payment'];?><?php echo $lang['nc_colon'];?></dt>
                            <dd><?php echo orderPaymentName($output['order_info']['payment_code']);?></dd>
                        </dl>
                    <?php } ?>
                    <dl>
                        <dt><?php echo $lang['store_name'];?><?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo $output['order_info']['store_name'];?></dd><dt>店主名称<?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo $output['store_info']['seller_name'];?></dd>
                        <dt>联系电话<?php echo $lang['nc_colon'];?></dt>
                        <dd><?php echo $output['store_info']['store_phone'];?></dd>
                    </dl>

                </div>

                <?php if ($output['order_info']['order_type'] == 2) { ?>
                    <div>
                        <h4>预定信息</h4>
                        <table>
                            <tbody>
                            <tr>
                                <td>阶段</td>
                                <td>应付金额</td>
                                <td>支付方式</td>
                                <td>支付交易号</td>
                                <td>支付时间</td>
                                <td>备注</td>
                            </tr>
                            <?php foreach ($output['order_info']['book_list'] as $k => $book_info) { ?>
                                <tr>
                                    <td><?php echo $book_info['book_step'];?></td>
                                    <td><?php echo $book_info['book_amount'].$book_info['book_amount_ext'];?></td>
                                    <td><?php echo $book_info['book_pay_name'];?></td>
                                    <td><?php echo $book_info['book_trade_no'];?></td>
                                    <td>
                                        <?php if (!empty($book_info['book_pay_time'])) { ?>
                                            <?php echo !date('His',$book_info['book_pay_time']) ? date('Y-m-d',$book_info['book_pay_time']) : date('Y-m-d H:i:s',$book_info['book_pay_time']);?>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $book_info['book_state'];?><?php echo $k == 1 ? '（通知手机号'.$book_info['book_buyer_phone'].'）' : null;?></td>
                                    </dd>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

                <div class="goods-info">
                    <h4><?php echo $lang['product_info'];?></h4>
                    <table>
                        <thead>
                        <tr>
                            <th colspan="2">商品</th>
                            <th>单价</th>
                            <th>商品数量</th>
                            <th>支付金额</th>
                            <th>成本金额</th>
                            <th>佣金比例(%)</th>
                            <th>红包金额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        <?php $goods= $output['order_goods_info'];?>
                        <tr data-key="<?php echo $goods['rec_id']; ?>" class="order-goods">
                            <td class="w30">
                                <div class="goods-thumb"><a
                                        href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                        target="_blank"><img alt="<?php echo $lang['product_pic']; ?>"
                                                             src="<?php echo thumb($goods, 60); ?>"/> </a></div>
                            </td>
                            <td style="text-align: left;"><a
                                    href="<?php echo SHOP_SITE_URL; ?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id']; ?>"
                                    target="_blank"><?php echo $goods['goods_name']; ?></a><br/><?php echo $goods['goods_spec']; ?>
                            </td>
                            <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_price']); ?></td>
                            <td class="w60"><?php echo $goods['goods_num']; ?></td>
                            <td class="w80"><?php echo $lang['currency'] . ncPriceFormat($goods['goods_pay_price']); ?></td>
                            <?php if($output['order_info']['manage_type']=='platform'){?>
                                <td class="w80">
                                    <?php echo $lang['currency'] . ncPriceFormat(($goods['goods_pay_price']+$goods['rpt_bill'])*$goods['commis_rate']/100); ?>
                                </td>
                            <?php }else{ ?>
                                <td class="w60">
                                    <?php echo $lang['currency'] . ncPriceFormat($goods['goods_cost']); ?>
                                </td>
                            <?php } ?>
                            <td class="w80">
                                <?php echo $goods['commis_rate'] == 200 ? '' : $goods['commis_rate']; ?>
                            </td>
                            <td class="w60">
                                <?php echo $lang['currency'] . ncPriceFormat($goods['rpt_bill']); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="ncap-form-default">
            <?php echo $output['form']; ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo ADMIN_RESOURCE_URL;?>/js/jquery.nyroModal.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/fileupload/jquery.fileupload.js" charset="utf-8"></script>
<script type="text/javascript">
    $(function() {
        $('.nyroModal').nyroModal();



        $('a[nc_type="submit_btn"]').click(function(){
            var data = {
                opinion:1,
            };
            $('input[nc_type="text"]').each(function(){
                data[$(this).data('type')]=$(this).val();
            });
            $.ajax({
                dataType:'json',
                url:window.location.href,
                type:"post",
                data:data,
                success:function(data){
                    if(data.state ==true){
                        alert('审核提交成功！');
                        location.href='index.php?act=order';
                    }else{
                        alert(data.msg);
                    }
                }
            });
        })
        var upaction = '';
        //上传凭证
        $('input[nc_type="upload_sign"]').each(function(){
            upaction = $(this).data('upload')?$(this).data('upload'):'upload';
            $(this).fileupload({
                dataType: 'json',
                url: 'index.php?act=workflow&op='+upaction,
                formData: '',
                done: function (e,data) {
                    if(data.result.state== "true"){
                        var upload_dir = "<?php echo SHOP_SITE_URL.DS.DIR_UPLOAD;?>";
                        var type  = $(this).data('type');
                        var file_path = upload_dir+"/"+data.result.file_path;
                        var img = "<img src=\""+file_path+"\">";
                        $("#show-"+type+"-image").html(img);
                        $("#"+type).val(file_path);
                    }
                },
            });
        });
    });
</script>