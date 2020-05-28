<?php defined('ByShopWWI') or exit('Access Invalid!');?>

<div class="wrap">
  <div class="tabmenu">
    <?php include template('layout/submenu');?>
  </div>
  <form id="rp_list_form" method="get">
    <table class="ncm-search-table">
      <input type="hidden" id='act' name='act' value='member_redpacket' />
      <input type="hidden" id='op' name='op' value='rp_list' />
      <tr>
        <td>&nbsp;</td>
        <td class="w100 tr">
          <select name="rp_state_select">
                <option value="" <?php if (!$_GET['rp_state_select']){ echo 'selected=true'; } ?>>红包状态</option>
                <?php if (!empty($output['redpacketstate_arr'])){?>
                <?php foreach ($output['redpacketstate_arr'] as $k=>$v){?>
                <option value="<?php echo $k;?>" <?php if ($_GET['rp_state_select'] == $k){echo 'selected';}?>><?php echo $v['name'];?></option>
                <?php }?>
                <?php }?>
          </select>
        </td>
        <td class="w70 tc">
            <label class="submit-border">
                <input type="submit" class="submit" value="<?php echo $lang['nc_search'];?>" />
            </label>
        </td>
      </tr>
    </table>
  </form>
  <table class="ncm-default-table">
    <thead>
      <tr>
        <th class="w10"></th>
        <th class="w70"></th>
        <th class="tl">红包编码</th>
        <th class="w100">适用范围</th>
        <th class="w80">面额（元）</th>
        <th class="w300">有效期</th>
        <th class="w100">状态</th>
        <th class="w70"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php  if (count($output['list'])>0) { ?>
      <?php foreach($output['list'] as $val) { ?>
      <tr class="bd-line">
        <td></td>
        <td><div class="ncm-goods-thumb"><a href="javascript:void(0);"><img src="<?php echo $val['rpacket_customimg_url'];?>" onMouseOver="toolTip('<img src=<?php echo $val['rpacket_customimg_url'];?>>')" onMouseOut="toolTip()" /></a></div></td>
        <td class="tl">
            <dl class="goods-name">
                <dt><?php echo $val['rpacket_code'];?></dt>
                <dd>（使用条件：订单满<?php echo $val['rpacket_limit'].$lang['currency_zh'];?>）</dd>
            </dl>
        </td>
        <td class="goods-name">
          <?php 
              if ($val['rpacket_range']==1) 
              { 
                  echo '<a href="/index.php?act=redpacket&op=sku&tid='.$val['rpacket_t_id'].'">仅选定商品适用</a>'; 
              } 
              elseif ($val['rpacket_range']==2) 
              { 
                  echo '<a href="/index.php?act=redpacket&op=sku&tid='.$val['rpacket_t_id'].'">除选定商品外适用</a>'; 
              } 
              elseif ($val['rpacket_range']==3) 
              { 
                  echo '<a href="/index.php?act=redpacket&op=sku&tid='.$val['rpacket_t_id'].'">选定商品分类适用</a>'; 
              } 
              else
              { 
                  echo "全场通用"; 
              }
              // echo  $val['rpacket_range']==0?'全场通用':
              // '<a href="/index.php?act=redpacket&op=sku&tid='.$val['rpacket_t_id'].'">'.($val['rpacket_range']==1?'仅选定商品适用':'除选定商品外适用').
              // '</a>';
          ?>
        </td>
        <td class="goods-price"><?php echo $val['rpacket_price'];?></td>
        <td class="goods-time"><?php echo date("Y-m-d H:i:s",$val['rpacket_start_date']).'~'.date("Y-m-d H:i:s",$val['rpacket_end_date']);?></td>
        <td><?php echo $val['rpacket_state_text'];?></td>
        <td class="<?php echo $val['rpacket_state_key'] == 'unused' ? 'ncm-table-handle' : null?>">
            <?php if ($val['rpacket_state_key'] == 'unused'){?>
                <span><a href="<?php echo urlShop('search', 'index');?>" class="btn-mint" target="_blank"><i class="icon-shopping-cart"></i><p>使用</p></a></span>
            <?php } elseif ($val['rpacket_state_key'] == 'used'){?>
                <span><a target="_blank" href="<?php echo urlShop('member_order','index',array('pay_sn'=>$val['rpacket_order_id']));?>"><p>查看订单</p></a></span>
            <?php } ?>
        </td>
      </tr>
      <?php }?>
      <?php } else { ?>
      <tr>
        <td colspan="20" class="norecord"><div class="warning-option"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></div></td>
      </tr>
      <?php } ?>
    </tbody>
    <?php  if (count($output['list'])>0) { ?>
    <tfoot>
      <tr>
        <td colspan="20"><div class="pagination"><?php echo $output['show_page'];?></div></td>
      </tr>
    </tfoot>
    <?php } ?>
  </table>
</div>