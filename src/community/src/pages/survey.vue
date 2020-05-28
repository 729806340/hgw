<template>
    <div class="surveyBox">
      <div class="infoBox">
        <div class="infoBox_row">
          <img :src=avatar alt="" class="info_logo">
          <div class="info_nBox" v-if="dataList.length == 0">
            <div>您还不是团长，请先申请为团长</div>
            <div class="btn info_n_btn" @click="goAttestation">立即申请</div>
          </div>
          <div class="info_YBox" v-if="dataList.length != 0">
            <div class="info_YName">
              {{dataList.name}}
              <div class="info_YIcon">{{dataList.type_name}}</div>
            </div>

            <div class="info_YInfo">
              <div class="phone">
                <img src="../../static/img/phone.png" alt="" class="icon">
                手机：{{dataList.phone}}
              </div>
            </div>
          </div>
        </div>
        <div class="infoBox_row_2">
          <div class="t_numBox_1">
            <div class="prc">{{dataList.length == 0?'0':dataList.un_bill_amount}}</div>
            <div class="text">待发佣金（元）</div>
          </div>
          <div class="shu"></div>
          <div class="t_numBox_1">
            <div class="prc" style="color: #ff853b;">{{dataList.length == 0?'0':dataList.unpay_bill_amount}}</div>
            <div class="text">可提现金额（元）</div>
          </div>
        </div>
      </div>

      <div class="dataBox_new">
        <div class="today"><div></div>今日数据统计</div>
        <div class="all_tongji">
          <div :class='tab == 1?"all_tongji_a":""' @click="tabClick(1)">今日统计</div>
          <div :class='tab == 2?"all_tongji_a":""' @click="tabClick(2)">累计统计</div>
        </div>
        <div class="dataBox">
        <div class="data_itemBox bg_1" v-if="tab == 1">
          <div class="name">今日参团人数（人）</div>
          <div class="num">{{dataList.length == 0?'0':dataList.today_join_num}}</div>
        </div>
        <div class="data_itemBox bg_2" v-if="tab == 1">
          <div class="name">今日佣金（元）</div>
          <div class="num">{{dataList.length == 0?'0':dataList.today_commis}}</div>
        </div>
        <div class="data_itemBox bg_3" v-if="tab == 1">
          <div class="name">今日订单数</div>
          <div class="num">{{dataList.length == 0?'0':dataList.today_order}}</div>
        </div>
        <div class="data_itemBox bg_4" v-if="tab == 1">
          <div class="name">今日销售额（元）</div>
          <div class="num">{{dataList.length == 0?'0':dataList.today_amount}}</div>
        </div>

          <div class="data_itemBox bg_1" v-if="tab == 2">
            <div class="name">累计参团人数（人）</div>
            <div class="num">{{dataList.length == 0?'0':dataList.total_join_num}}</div>
          </div>
          <div class="data_itemBox bg_2" v-if="tab == 2">
            <div class="name">累计佣金（元）</div>
            <div class="num">{{dataList.length == 0?'0':dataList.total_commis}}</div>
          </div>
          <div class="data_itemBox bg_3" v-if="tab == 2">
            <div class="name">累计订单数</div>
            <div class="num">{{dataList.length == 0?'0':dataList.total_order}}</div>
          </div>
          <div class="data_itemBox bg_4" v-if="tab == 2">
            <div class="name">累计销售额（元）</div>
            <div class="num">{{dataList.length == 0?'0':dataList.total_amount}}</div>
          </div>
      </div>
      </div>
    </div>
</template>

<script>
    export default {
        name: "survey",
        data() {
          return {
            dataList:'',
            avatar:'../static/img/info_tou.png',
            tab:1,
          };
        },
      created(){  //载入前
        this.postdata()
        if(localStorage.getItem('t_avatar')){
          this.avatar = localStorage.getItem('t_avatar')
        }
      },
        methods: {
          goAttestation(){
            this.$router.push({
              path: '/attestation'
            })
          },
          //团长数据
          postdata(){
            var that = this
            that.$post('shequ_tuan.app_info',{

            }).then(res => {
              // console.log(res);
              if(res.data.code == 200){
                that.dataList = res.data.datas
                if(that.dataList.length != 0){
                  localStorage.setItem("t_tuanzhang",'1');
                }else{
                  localStorage.removeItem('t_tuanzhang')
                }
              }else{
                that.$message.error(res.data.datas.error);
              }
            })
          },
          tabClick(e){
            this.tab = e
          }
        }
    }
</script>

<style scoped>
  .surveyBox{
    /*display: flex;*/
    width: 100%;
    background: #ffffff;
    box-sizing: border-box;
    /*padding: 20px;*/
  }
  .infoBox{
    display: flex;
  }
  .infoBox_row{
    display: flex;
    align-items: center;
    padding: 20px;
    width: 40%;
    min-width: 500px;
  }
  .infoBox_row_2{
    flex: 1;
    border-left: 10px solid #f0f5fc;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    padding: 20px;
  }
  .info_logo{
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
  }
  .info_nBox{
    margin-left: 35px;
    display: flex;
    align-items: center;
    background: #FFEDD9;
    padding: 10px 15px;
    border-radius: 4px;
  }
  .info_n_btn{
    padding: 5px 15px;
    background: #FB6161;
    border-radius: 4px;
    margin-left: 10px;
    color: #fff;
  }
  .info_YBox{
    margin-left: 35px;
  }
  .info_YName{
    font-size: 30px;
    display: flex;
    align-items: center;
  }
  .info_YIcon{
    display: inline-block;
    padding: 1px 8px;
    border: 1px solid #2BC664;
    border-radius: 20px;
    margin: 8px 0;
    background: #EBFFF2;
    color: #2BC664;
    margin-left: 20px;
  }
  .info_YInfo{
    display: flex;
    margin-top: 12px;
  }
  .info_YInfo div{
    display: flex;
    align-items: center;
    font-size: 14px;
  }
  .info_YInfo .add{
    margin-left: 30px;
  }
  .info_YInfo .icon{
    margin-right: 8px;
  }
  .dataBox_new{
    padding: 30px 0;
    border-top: 10px solid #f0f5fc;
  }
  .today{
    padding-bottom: 50px;
    padding-left: 20px;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
  }
  .today div{
    width: 5px;
    height: 20px;
    background: #00b944;
    margin-right: 10px;
  }
  .dataBox{
    display: flex;
    align-items: center;
    justify-content: space-around;
  }
  .data_itemBox{
    width: 22%;
    background: #f2f2f2;
    padding: 15px;
    box-sizing: border-box;
    border-radius: 6px;
  }
  .data_itemBox .name{
    font-size: 16px;
    color: #fff;
    margin-top: 10px;
    font-weight: 600;
  }
  .data_itemBox .num{
    font-size: 28px;
    color: #fff;
    margin: 20px 0 15px 0;
    font-weight: 600;
  }
  .bg_1{
    background: linear-gradient(to top,#FC6273,#FD913E);
  }
  .bg_2{
    background: linear-gradient(to top,#2EA6D5,#5FE5B9);
  }
  .bg_3{
    background: linear-gradient(to top,#8C37FF,#3C94FF);
  }
  .bg_4{
    background: linear-gradient(to top,#DD2FBA,#ED1078);
  }
  .all_tongji{
    display: flex;
    align-items: center;
    justify-content: center;
    width: 280px;
    height: 35px;
    background: #fff;
    border: 1px solid #041324;
    border-radius: 35px;
    overflow: hidden;
    margin-left: 20px;
    margin-bottom: 40px;
  }
  .all_tongji div{
    width: 50%;
    height: 35px;
    background: #fff;
    font-size: 14px;
    color: #041324;
    line-height: 35px;
    text-align: center;
  }
  .all_tongji div:hover{
    cursor: pointer;
  }
  .all_tongji .all_tongji_a{
    color: #fff;
    background: #041324;
    font-weight: 600;
  }
  .infoBox_row{

  }
  .t_numBox_1{

  }
  .t_numBox_1 .prc{
    color: #ff444e;
    font-size: 40px;
    font-weight: 600;
    text-align: center;
  }
  .t_numBox_1 .text{
    color: #333;
    font-size: 16px;
    text-align: center;
  }
  .shu{
    width: 1px;
    height: 45px;
    background: #999;
    margin: 0 10%;
  }
</style>
