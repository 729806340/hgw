<template>
  <div class="detailBox">
    <div class="header">
      <div class="h_nav_item">
        <div style="width: 100px;">订单编号：</div>
        <el-input type="text" v-model="order_sn" class="searchipt" placeholder="请输入订单编号" />
      </div>
      <div class="h_nav_item">
        <div style="width: 100px;">订单状态：</div>
        <el-select v-model="order_state" placeholder="请选择" class="searchipt">
          <el-option label="全部" value=""></el-option>
          <el-option label="已支付" value="20"></el-option>
          <el-option label="已完成" value="40"></el-option>
          <el-option label="已退款 " value="41"></el-option>
        </el-select>
      </div>
      <div class="h_nav_item">
        <div style="width: 100px;">提货码：</div>
        <el-input type="text" v-model="chain_code" class="searchipt" placeholder="请输入提货码" />
      </div>
      <div class="h_nav_item">
        <div style="width: 110px;">收货人名称：</div>
        <el-input type="text" v-model="link_name" class="searchipt" placeholder="请输入收货人名称" />
      </div>
      <div class="h_nav_item">
        <div style="width: 110px;">收货人电话：</div>
        <el-input type="text" v-model="link_phone" class="searchipt" placeholder="请输入收货人电话" />
      </div>
    </div>
    <div style="margin-bottom: 25px;">
      <el-button type="primary" icon="el-icon-search" @click="searchClick">查询</el-button>
      <el-button type="info" plain @click="resetClick">重置</el-button>
      <el-button type="success" plain @click="exprotClick">导出</el-button>
    </div>
    <div class="topBox">
      <div class="spBox">商品</div>
      <div class="djBox">单价（元）</div>
      <div class="slBox">数量</div>
      <div class="mjBox">买家</div>
      <div class="jeBox">订单金额（元）</div>
      <div class="yjBox">所得佣金</div>
      <div class="czBox">操作</div>
    </div>
    <div class="tableBox" v-for="(item,index) in tableData">
      <div class="tabTop">
        <div>订单号：{{item.order_sn}}</div>
        <div>下单时间：{{item.add_time}}</div>
        <div>配送方式：{{item.shipping_type}}</div>
        <div class="colorL" v-if="item.chain_code!='0'">提货码：{{item.chain_code}}</div>
      </div>
      <div style="display: flex;">
        <div class="goodsLeft">
          <div v-for="items in item.extend_order_goods">
            <div class="goodsFor">
              <div class="gl_itemBox">
                <!--<img :src="items.goods_image" alt="" style="width: 50px;height: 50px;margin-left: 10px;">-->
                <el-image :src="items.goods_image" style="width: 60px;height: 60px;margin-left: 10px;"></el-image>
                <div class="nameBox">
                  <div class="name">{{items.goods_name}}</div>
                  <div class="gg" v-if="item.goods_spec">规格：{{item.goods_spec}}</div>
                </div>
              </div>
              <div class="gl_prc">{{items.goods_price}}</div>
              <div class="gl_num">{{items.goods_num}}</div>
            </div>
          </div>
        </div>
        <div class="goodsR">
          <div class="mjinfoBox">
            <div class="np">
              <div>姓名：{{item.extend_order_shipping.reciver_name}}</div>
              <div>电话：{{item.extend_order_shipping.reciver_phone}}</div>
            </div>
            <div class="add">
              <div style="width: 40px;">地址：</div>
              <div style="flex: 1;">{{item.extend_order_shipping.reciver_address}}</div>
            </div>
          </div>
          <div class="gl_prc">{{item.order_amount}}</div>
          <div class="gl_prc">{{item.shequ_commis}}</div>
          <div class="gl_btnBox">
            <el-button type="primary" plain @click="confirmclick(item,index)" v-if="item.if_receive == 1">确认收货</el-button>
            <el-button type="info" plain disabled v-if="item.if_receive != 1">{{item.state_desc}}</el-button>
          </div>
        </div>
      </div>
    </div>

    <div class="pageBox">
      <el-pagination
        background
        layout="prev, pager, next"
        :page-size=3
        :total=page_total
        @current-change="currentchange"
        :current-page.sync="currentPage"
      ></el-pagination>
    </div>
  </div>
</template>

<script>
  export default {
    name: "order_detail",
    inject:["reload"],
    components:{

    },
    props: ['tableItem'],//父组件传的数据
    data () {
      return {
        srcList:[],
        tableData: [],
        page_total:null,
        currentPage:1,
        order_state:'',
        order_sn:'',
        chain_code:'',
        link_name:'',
        link_phone:'',
        new_page:1,
      }
    },
    created(){  //载入前
      // console.log(this.tableItem)
      this.listPost(1,this.order_state,this.order_sn,this.chain_code,this.link_name,this.link_phone)   //列表数据
    },
    methods:{  //业务
      imgshow(img){
        this.srcList = [img]
      },
      currentchange(e){
        this.new_page = e
        this.listPost(e,this.order_state,this.order_sn,this.chain_code,this.link_name,this.link_phone)   //列表数据
      },
      //确认收货
      confirmclick(item,index){
        // console.log(item.order_id)
        var that = this
        this.$confirm("是否确认收货?", "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        }).then(() => {
          // that.tableData[index].disabled = true
          that.$post('shequ_tuan.queren_one',{
            order_id:item.order_id
          }).then(res => {
            if(res.data.code == 200){
              that.$message({
                message: '收货成功~',
                type: 'success'
              });
              that.listPost(that.new_page,that.order_state,that.order_sn,that.chain_code,that.link_name,that.link_phone)   //列表数据
            }else{
              that.$message.error(res.data.datas.error);
            }
          })
        });
      },
      //列表数据
      listPost(cur_page,order_state,order_sn,chain_code,link_name,link_phone){
        var that = this
        that.$post('shequ_tuan_order.index',{
          page: 2,
          curpage:cur_page,
          // tuan_id:that.tableItem,
          order_state:order_state,
          order_sn:order_sn,
          chain_code:chain_code,
          link_name:link_name,
          link_phone:link_phone,
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 3
            that.tableData = res.data.datas.order_list
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //重置
      resetClick(){
        this.order_state = ''
        this.order_sn = ''
        this.chain_code = ''
        this.link_name = ''
        this.link_phone = ''
        this.listPost(1,this.order_state,this.order_sn,this.chain_code,this.link_name,this.link_phone)   //列表数据
      },
      //查询
      searchClick(){
        this.listPost(1,this.order_state,this.order_sn,this.chain_code,this.link_name,this.link_phone)   //列表数据
      },
      //导出
      exprotClick(cur_page,order_state,order_sn,chain_code,link_name,link_phone){
        var that = this
        window.location.href= this.$store.state.postURL+"shequ_tuan_order.exprot_csv&access_token=" + localStorage.getItem('t_access_token') +
          '&member_id='+ localStorage.getItem('t_member_id') +
          '&tuan_id=' + that.tableItem +
          '&order_state=' + that.order_state +
          '&order_sn=' + that.order_sn +
          '&chain_code=' + that.chain_code +
          '&link_name=' + that.link_name +
          '&link_phone=' + that.link_phone
      }
    }
  }
</script>

<style scoped>
  .detailBox{
    width: 100%;
    padding: 20px;
  }
  .topBox{
    display: flex;
    align-items: center;
    /*justify-content: space-around;*/
    background: #F5F8FB;
    padding: 10px;
  }
  /*.topBox div{*/
  /*display: flex;*/
  /*align-items: center;*/
  /*}*/
  .tableBox{
    margin-top: 20px;
    border: 1px solid #f2f2f2;
    border-radius: 2px;
  }
  .tit{
    font-size: 14px;
    margin-bottom: 8px;
  }
  .itemBox{
    max-height: 300px;
    overflow-y: auto;
  }
  .infinite-list{
    width: 100%;
    max-height: 200px;
    padding: 0;
    margin: 0;
    list-style: none;
  }
  .infinite-list li{
    display: flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    background: #fff;
    margin: 5px;
    color: #333;
  }
  .spBox{
    width: 30%;
    text-align: center;
  }
  .djBox{
    width: 10%;
    text-align: center;
  }
  .slBox{
    width: 10%;
    text-align: center;
  }
  .mjBox{
    width: 20%;
    text-align: center;
  }
  .jeBox{
    width: 10%;
    text-align: center;
  }
  .yjBox{
    width: 10%;
    text-align: center;
  }
  .czBox{
    width: 10%;
    text-align: center;
  }
  .tabTop{
    display: flex;
    padding: 5px 8px;
    background: #F0F5FC;
    border-bottom: 1px solid #f2f2f2;
  }
  .tabTop div{
    margin-right: 30px;
  }
  .colorL{
    color: #00b944;
  }
  .goodsLeft{
    width: 50%;
    /*display: flex;*/
    /*align-items: center;*/
    border-right: 1px solid #f2f2f2;
    padding-bottom: 10px;
  }
  .goodsR{
    width: 50%;
    display: flex;
    padding: 10px 0;
  }
  .gl_itemBox{
    display: flex;
    width: 60%;
  }
  .nameBox{
    flex: 1;
    padding: 0 10px;
  }
  .nameBox .name{
    height: 32px;
    text-overflow: -o-ellipsis-lastline;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    margin-top: 3px;
  }
  .nameBox .gg{
    color: #999;
    margin-top: 5px;
  }
  .gl_prc{
    width: 20%;
    text-align: center;
    color: red;
  }
  .gl_num{
    width: 20%;
    text-align: center;
  }
  .goodsFor{
    width: 100%;
    display: flex;
    align-items: center;
    margin-top: 10px;
  }
  .mjinfoBox{
    width: 40%;
    padding: 0px 10px;
  }
  .np{
    display: flex;
    width: 100%;
    justify-content: space-around;
    margin-bottom: 5px;
  }
  .add{
    display: flex;
    justify-content: center;
  }
  .gl_btnBox{
    width: 20%;
    text-align: center;
  }
  .header {
    width: 100%;
    display: flex;
    align-items: center;
    /*padding-bottom: 20px;*/
    flex-wrap: wrap;
  }
  .h_nav_item{
    margin-right: 25px;
    display: flex;
    align-items: center;
    width: 240px;
    padding-bottom: 10px;
  }
</style>
