<div class="eject_con">
  <div id="warning" class="alert alert-error"></div>
      <dl>
          <dt>商品名称<?php echo $lang['nc_colon'];?></dt>
          <dd><?php echo $output['goodsdata']['goods_name'];?></dd>
      </dl>
      <dl>
          <dt>&nbsp;&nbsp;当前售价<?php echo $lang['nc_colon'];?></dt>
          <dd><?php echo $output['goodsdata']['goods_price'];?>元</dd>
      </dl>
      <dl>
          <dt>当前成本价<?php echo $lang['nc_colon'];?></dt>
          <dd><?php echo $output['goodsdata']['goods_cost'];?>元</dd>
      </dl>
    <dl>
      <dt><i class="required">*</i>新的成本价<?php echo $lang['nc_colon'];?></dt>
    <dd>
    <input type="text" class="text" name="goods_cost" value="<?php echo $output['goodsdata']['goods_cost'];?>" id="goods_cost" />
        <span style="color:red" id="rate">利率:
            <?php $num=($output['goodsdata']['goods_price']-$output['goodsdata']['goods_cost'])/$output['goodsdata']['goods_price']*100;
              echo number_format($num, 2);
            ?>%</span>
    <p style="color:red">*当毛利小于5%需走系统后台审批流程。</p>
</dd>
</dl>
<div class="bottom">
    <label class="submit-border"><input class="submit" value="<?php echo $lang['nc_submit'];?>"/></label>
</div>
</div>
<script>
    $("input[name='goods_cost']").blur(function(){
           var goods_cost=parseFloat($(this).val());
           var goods_price=parseFloat(<?php echo $output['goodsdata']['goods_price']?>);
           var rate=(goods_price-goods_cost)/goods_cost*100;
           $(this).next().html("利率："+Math.floor(rate*100)/100+"%");
           $(this).next().attr("data-rate",Math.floor(rate*100)/100);

    })

    $(".submit").click(function(){
        var goods_cost=$("input[name='goods_cost']").val();
        var reg=/([1-9]+[0-9]*|0)(\\.[\\d]+)?/;
        var goods_id="<?php echo $output['goodsdata']['goods_id']?>";
        var goods_old_cost="<?php echo $output['goodsdata']['goods_cost']?>";
        if(!reg.test(goods_cost)){
            showSucc("此输入框只能输入数字");
            return false;
        }
        var goods_price=parseFloat(<?php echo $output['goodsdata']['goods_price']?>);
        var rate=Math.floor((goods_price-goods_cost)/goods_cost*100)/100*100;
        if(rate<0){
            showSucc("成本价不能大于销售价");
            return false;
        }

        if(rate<5){
            if(confirm("当前输入成本价利率小于5%，要走审批流程，你确定要输入此成本价吗？")){
                is_ajax(goods_id,goods_old_cost,goods_cost,rate);
            }
        }else{
            is_ajax(goods_id,goods_old_cost,goods_cost,rate);
        }
    })

    function is_ajax(goods_id,goods_old_cost,goods_cost,rate){
        $.ajax({
            dataType:'json',
            url:"index.php?act=store_goods_online&op=update_goodscost",
            type:"post",
            data:{
                isajax:1,
                goods_id:goods_id,
                goods_cost:goods_cost,
                goods_old_cost:goods_old_cost,
                rate:rate
            },
            success:function(data){
                if(data.state ==true){
                    showSucc(data.msg);
                    DialogManager.close('my_goods_cost_update');
                }else{
                    alert(data.msg);
                }
            }
        });
    }
</script>


