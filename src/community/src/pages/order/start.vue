<template>
  <div class="startBox">
    <div class="itemBox" id="div1">
      <div class="title">接龙基本设置</div>
      <div class="goodsBox">
        <div class="topBox" style="flex-wrap:wrap;">
          <div class="topBox_item">
            配送方式：
            <el-select v-model="type" placeholder="请选择">
              <el-option label="商家发货" value="1"></el-option>
              <el-option label="买家自提" value="2"></el-option>
            </el-select>
          </div>
          <div  class="topBox_item" v-if="type==2">
            <el-input v-model="pickadd" placeholder="请输入提货点" :disabled="true"></el-input>
          </div>
          <div class="topBox_item csaleTimeBox">
            活动起止时间：
            <el-date-picker
              v-model="timevalue"
              type="datetimerange"
              :picker-options="pickerOptions"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              align="right">
            </el-date-picker>
          </div>
        </div>
        <div class="topBox">
          图文详情：
          <div class="infoitem_right" style="width: 90%;">
            <Editor style="width: 100%;" v-model="editorContent"></Editor>
          </div>
        </div>
      </div>
      <div class="title">选择接龙商品</div>
      <div class="goodsBox">
        <div class="searchBox">
          <div class="search_text">搜索店内商品：</div>
          <!--<div class="search_ipt">-->
            <!--<input type="text" v-model="searchVal" placeholder="请输入商品名称" @keyup.enter="searchClick">-->
            <!--<div class="btn search_ipt_btn" @click="searchClick">搜索</div>-->
          <!--</div>-->
          <div style="width: 280px;margin-right: 20px;">
            <el-input placeholder="请输入商品名称" v-model="input3" class="input-with-select">
              <el-button slot="append" icon="el-icon-search" @click="searchClick"></el-button>
            </el-input>
          </div>
          <div class="search_text_r">不输入名称搜索将显示店内所有普通商品</div>
        </div>

        <div class="itemGoosBox">
          <div class="item_g" :class="item.select?'item_gActive':''" v-for="(item,index) in goodsList" @click="selectclick(index,item)">
            <div class="item_g_img"><img :src="item.goods_image_url" alt=""></div>
            <div class="item_g_text">{{item.goods_name}}</div>
            <img src="../../../static/img/jiaobiao.png" alt="" class="jiaobiao" v-if="item.select">
          </div>
          <div v-if="goodsList.length==0" style="line-height: 60px;margin:0 auto;color: #909399;">暂无数据</div>
        </div>

        <div class="pageBox">
          <el-pagination
            background
            layout="prev, pager, next"
            :page-size=5
            :total=page_total
            @current-change="currentchange"
            :current-page.sync="currentPage"
          ></el-pagination>
        </div>

        <div class="selectBox" v-if="selectList.length>0">
          <div class="sel_leftBox">已选商品：</div>
          <div class="sel_rightBox">
            <div class="sel_itemBox" v-for="(item,index) in selectList">
              <img :src="item.goods_image_url" alt="">
              <div class="sel_itemName">{{item.goods_name}}</div>
              <i class="el-icon-remove" @click="selectremove(index,item)"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Editor from "../../components/editor";
  export default {
    name: "start",
    components: {
      Editor
    },
    props: {
      isEdit: {
        type: Boolean,
        default: false
      }
    },
    inject:["reload"],
    data () {
      return {
        // activeName:'start',
        type:'',//配送方式
        timevalue:'',//时间
        haibao:'../../static/img/addimg.png',
        pickerOptions: {
          shortcuts: [{
            text: '最近一周',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit('pick', [start, end]);
            }
          }, {
            text: '最近一个月',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
              picker.$emit('pick', [start, end]);
            }
          }, {
            text: '最近三个月',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
              picker.$emit('pick', [start, end]);
            }
          }]
        },
        searchVal:'',
        goodsList:[
          {
            goods_id: "329", goods_name: "【农谷鲜】 稻香米 冷泉米 1000g 黑色",
            goods_id: "329",
            goods_image: "1576725503_5dfaebffc3e26.png",
            goods_image_url: "../../static/img/addimg.png",
            goods_name: "【农谷鲜】 稻香米 冷泉米 1000g 黑色",
            store_id: "3",
            select:false
          }
        ],
        selectList:[],//选中数据
        page_total:100 || null,
        currentPage:1,
        input3:'',
        pickadd:"",
        editorContent:'',
      }
    },
    created(){  //载入前
      console.log(222)
    },
    methods:{  //业务
      //搜索
      searchClick(){

      },
      //选中产品
      selectclick(index,item){
        if(this.ifscrollTop){
          const cateItem = document.querySelectorAll('#div1');
          // console.log( cateItem[0])
          $('html, body').animate({
            scrollTop: cateItem[0].offsetTop
          }, 300);
          this.ifscrollTop = false
        }
        this.goodsList[index].select = !this.goodsList[index].select
        if(this.goodsList[index].select){
          this.selectList.push(item)
        }else{
          var id = item.goods_id
          for(var i=0;i<this.selectList.length;i++){
            if(id == this.selectList[i].goods_id){
              this.selectList.splice(i , 1);
            }
          }
        }
        // console.log(item,this.selectList,this.goodsList)
      },
      //删除选中商品
      selectremove(index,item){
        var id = item.goods_id
        for(var i=0;i<this.selectList.length;i++){
          if(id == this.selectList[i].goods_id){
            this.selectList.splice(i , 1);
            break
          }
        }
        for(var i=0;i<this.goodsList.length;i++){
          if(id == this.goodsList[i].goods_id){
            this.goodsList[i].select = false
            break
          }
        }
      },
      //分页
      currentchange(e) {
        console.log(e)
      },
    }
  }
</script>

<style scoped>
  .startBox{
    width: 100%;
    padding: 20px;
    padding-top: 0;
  }
  .topBox{
    display: flex;
    padding: 15px;
  }
  .thd{
    font-size: 14px;
    margin-top: 15px;
    display: flex;
    align-items: center;
  }
  .detailBox{
    padding: 20px 30px;
    background: #fff;
    padding-bottom: 70px;
  }
  .stepsBox{
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 0;
  }
  .stepsBox div{
    font-size: 14px;
    letter-spacing: 1px;
  }
  .stepActive{
    color: #00b944;
  }
  .btnBoxbox{
    position: fixed;
    bottom: 0;
    width: 100%;
    left: 0;
    padding-left: 140px;
    padding-right: 10px;
    box-sizing: border-box;
  }
  .btnBox{
    padding: 20px 0;
    background: #fff;
    box-shadow: 0px -3px 8px #f2f2f2;
    width: 100%;
    box-sizing: border-box;
  }
  .btn{
    width: 200px;
    border-radius: 4px;
    text-align: center;
    line-height: 40px;
    background: #00b944;
    color: #fff;
    font-size: 14px;
    margin: 0 15px;
  }
  .itemBox{
    padding: 20px;
    background: #F6F8F7;
    /*margin-top: 10px;*/
  }
  .title{
    font-size: 15px;
    font-weight: bold;
  }
  .radioBox{
    margin: 15px 0;
    margin-bottom: 0;
  }
  .goodsBox{
    margin: 15px 0;
    background: #fff;
  }
  .searchBox{
    display: flex;
    align-items: center;
    padding: 15px 0;
  }
  .search_text{
    margin: 0 15px;
    font-size: 14px;
  }
  .search_ipt{
    display: flex;
    align-items: center;
    margin-right: 20px;
  }
  .search_ipt input{
    width: 200px;
    height: 40px;
    border: 1px solid #DCDFE6;
    box-sizing: border-box;
    padding: 2px 10px;
    border-radius: 4px 0 0 4px;
    border-right: none;
  }
  .search_text_r{

  }
  .itemGoosBox{
    width: 100%;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    border: 1px solid #DCDFE6;
  }
  .item_g{
    width: 20%;
    text-align: center;
    box-sizing: border-box;
    padding: 20px;
    border: 1px solid #DCDFE6;
    position: relative;
  }
  .item_g_img{
    width: 50%;
    height: 50%;
    margin: 0 auto;
    padding-top: 50%;
    position: relative;
    overflow: hidden;
  }
  .item_g_img img{
    display: block;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }
  .item_g_text{
    font-size: 14px;
    line-height: 30px;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
    width: 100%;
  }
  .item_g:hover{
    cursor: pointer;
  }
  .item_gActive{
    border-color: #409EFF;
  }
  .jiaobiao{
    position: absolute;
    right: 0;
    bottom: 0;
    width: 40px;
  }
  .pageBox{
    text-align: center;
    padding: 20px 0;
  }
  .selectBox{
    border-top: 1px solid #DCDFE6;
    padding: 20px;
    display: flex;
  }
  .sel_leftBox{
    width: 100px;
    font-size: 14px;
  }
  .sel_rightBox{
    flex: 1;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
  }
  .sel_itemBox{
    width: 240px;
    height: 50px;
    padding: 5px;
    background: #F6F8F7;
    position: relative;
    display: flex;
    align-items: center;
    margin-right: 25px;
    margin-bottom: 15px;
  }
  .sel_itemBox img{
    width: 50px;
    height: 50px;
    margin-right: 10px;
    margin-left: 10px;
  }
  .sel_itemName{
    flex: 1;
    font-size: 14px;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .sel_itemBox i{
    color: red;
    font-size: 22px;
    position: absolute;
    right: -10px;
    top: -10px;
  }
  .sel_itemBox i:hover{
    cursor: pointer;
    opacity: 0.6;
  }
  .radioBox .el-textarea{
    height: 100px;
  }
  .btnBoxbox{
    position: fixed;
    bottom: 0;
    width: 100%;
    left: 0;
    padding-left: 210px;
    padding-right: 10px;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
  }
  .btnBox{
    padding: 10px 0;
    background: #fff;
    box-shadow: 0px -3px 8px #f2f2f2;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
  }
  .btn{
    width: 150px;
    text-align: center;
    line-height: 40px;
    font-size: 14px;
    color: #fff;
    background: #00b944;
    border-radius: 4px;
    margin: 0 15px;
  }
  .submitOKbox{
    text-align: center;
  }
  .submitOKbox img{
    margin: 30px 0;
  }
  .submitOKtext{
    font-size: 14px;
  }
  .submitOKbtn{
    margin: 30px auto;
    width: 180px;
  }
  .search_ipt_btn{
    width: 50px;
    height: 40px;
    background: #409EFF;
    border-radius: 0 4px 4px 0;
    text-align: center;
    line-height: 40px;
    color: #fff;
    font-size: 14px;
    margin: 0;
  }
  .brandListBox{
    margin: 15px 0;
  }
  .topBox_item{
    margin: 0 15px 15px 0;
  }
</style>
