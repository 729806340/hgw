<template>
  <div class="goodsBox">
    <!--<div class="tabsBox">-->
      <!--<el-tabs v-model="activeName">-->
        <!--<el-tab-pane label="所有团购" name="all"></el-tab-pane>-->
        <!--<el-tab-pane label="我发起的团购" name="my"></el-tab-pane>-->
      <!--</el-tabs>-->
    <!--</div>-->
    <div class="topBox">
      <div class="header">
        <div>
          团购名称：
          <el-input type="text" v-model="config_tuan_name" class="searchipt" placeholder="请输入团购名称" />
        </div>
        <div style="margin-left: 25px;">
          团购类型：
          <el-select v-model="type" placeholder="请选择" class="searchipt">
            <el-option label="全部" value=""></el-option>
            <el-option label="物流" value="1"></el-option>
            <el-option label="自提" value="2"></el-option>
          </el-select>
        </div>
        <div style="margin-left: 25px;">
          <el-button type="primary" icon="el-icon-search" @click="searchClick">查询</el-button>
          <el-button type="info" plain @click="resetClick">重置</el-button>
        </div>
      </div>

      <div class="tableBox">
        <div class="itemBox">
          <!--<el-table ref="multipleTable" :data="tableData" tooltip-effect="dark" style="width: 100%"-->
                    <!--@selection-change="handleSelectionChange">-->
            <!--<el-table-column type="selection" width="55"></el-table-column>-->
            <!--<el-table-column prop="config_tuan_id" label="编号" show-overflow-tooltip align="center"></el-table-column>-->
            <!--<el-table-column prop="config_tuan_name" label="团购名称" show-overflow-tooltip align="center"></el-table-column>-->
            <!--<el-table-column prop="config_tuan_title" label="团购标题" show-overflow-tooltip align="center"></el-table-column>-->
            <!--&lt;!&ndash;<el-table-column prop="config_tuan_description" label="团购描述" show-overflow-tooltip align="center"></el-table-column>&ndash;&gt;-->
            <!--<el-table-column prop="config_start_time" label="开始时间" show-overflow-tooltip align="center"></el-table-column>-->
            <!--<el-table-column prop="config_end_time" label="结束时间" show-overflow-tooltip align="center"></el-table-column>-->
            <!--<el-table-column prop="type" label="类型" show-overflow-tooltip align="center">-->
              <!--<template slot-scope="scope">-->
                <!--{{scope.row.type==1?'物流':'自提'}}-->
              <!--</template>-->
            <!--</el-table-column>-->
            <!--<el-table-column prop="send_product_date" label="发货时间" show-overflow-tooltip align="center"></el-table-column>-->
            <!--<el-table-column label="操作" show-overflow-tooltip align="center" width="250">-->
              <!--<template slot-scope="scope">-->
                <!--<el-button plain @click="detailclick(scope.row)">查看团购商品</el-button>-->
                <!--<el-button type="primary" plain @click="startclick(scope.row,scope.$index)">发起团购</el-button>-->
              <!--</template>-->
            <!--</el-table-column>-->
          <!--</el-table>-->

          <div class="tabitemBox" v-for="(item,index) in tableData" >
            <img :src=item.config_pic alt="" class="config_pic">
            <div class="config_name">{{item.config_tuan_name}}</div>
            <div class="config_time">活动时间：{{item.config_start_time}}<span style="color: red;"> / </span>{{item.config_end_time}}</div>
            <div class="config_type">发货方式：{{item.type==1?'物流':'自提'}}</div>
            <div class="config_type">发货时间：{{item.send_product_date}}</div>
            <div class="config_type">获得佣金：{{item.price_scope}}</div>
            <div class="config_btn">
              <el-button plain @click="detailclick(item)">查看团购商品</el-button>
              <el-button type="primary" plain @click="startclick(item,index)">发起团购</el-button>
            </div>
          </div>

        </div>

        <div class="pageBox">
          <el-pagination
            background
            layout="prev, pager, next"
            :page-size=8
            :total=page_total
            @current-change="currentchange"
            :current-page.sync="currentPage"
          ></el-pagination>
        </div>
      </div>
    </div>

    <el-dialog title="收货地址" :visible.sync="dialogFormVisible">
      <el-form :model="ruleForm" ref="ruleForm" :rules="rules">
        <el-form-item label="收货地址" prop="address" :label-width="formLabelWidth">
          <el-select v-model="ruleForm.address" placeholder="请选择收货地址" style="width: 80%;">
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">取 消</el-button>
        <el-button type="primary" @click="submit('ruleForm')">确 定</el-button>
      </div>
    </el-dialog>

    <el-dialog title="团购二维码" :visible.sync="dialogVisible" width="30%">
     <div class="logImgBox">
       <img :src="erweima" alt="">
     </div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog title="团购商品详情" :visible.sync="dialogTableVisible" width="70%">
      <Detail :tableItem="tableItem" v-if="dialogTableVisible"></Detail>
    </el-dialog>
  </div>
</template>

<script>
  import Detail from '../../components/detail'
  export default {
    name: "goods",
    inject:["reload"],
    components:{
      Detail
    },
    data () {
      var address = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请选择收货地址'));
        } else{
          callback();
        }
      };
      return {
        activeName:"all",
        type:'',
        config_tuan_name: "",
        price_from:'',
        price_to:'',
        tableData: [
          // {
          //   config_tuan_id: '564684',//编号
          //   config_tuan_name: '劳动节惠民团购',//团购名称
          //   config_tuan_title: '大家快来买',//团购标题
          //   config_tuan_description: '特价劲爆促销商品',//描述
          //   config_start_time: '2020-03-21 12:00:00',//开始时间
          //   config_end_time: '2020-03-22 12:00:00',//结束时间
          //   type: 1,//类型
          //   send_product_date: '2020-03-23 12:00:00',//发货时间
          // }
        ],
        multipleSelection: [],
        page_total:null,
        currentPage:1,
        srcList:[],
        dialogTableVisible:false,
        dialogFormVisible: false,
        dialogVisible:false,
        formLabelWidth: '120px',
        ruleForm:{
          address:'',
        },
        options: [
          // {
          //   value: '选项1',
          //   label: '黄金糕'
          // }
        ],
        rules: {
          address: [{required: true, validator: address, trigger: 'change'},],
        },
        tableItem:'',
        config_tuan_id:'',
        erweima:'',
      }
    },
    created(){  //载入前
      this.listPost(1,this.config_tuan_name,this.type)//列表数据
      this.addressPost() //取货点
    },
    methods:{  //业务
      //选中
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      imgshow(url){
        this.srcList = [url]
      },
      //查看详情
      detailclick(item){
        console.log(item)
        this.dialogTableVisible = true
        this.tableItem = item.config_tuan_id
      },
      //发起团购
      startclick(item,index){
        console.log(item,index)
        var that = this
        this.config_tuan_id = item.config_tuan_id
        if(item.type == 2){
          this.dialogFormVisible = true
        }else if(item.type == 1){
          this.$confirm('确定发起团购?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            that.joinpost(0)
          }).catch(() => {

          });
        }
      },
      //提交发起团购
      submit(formName) {
        var that = this
        this.$refs[formName].validate((valid) => {
          if (valid) {
            //验证成功
            // console.log(that.ruleForm.address)
            that.dialogFormVisible = false
            that.joinpost(that.ruleForm.address)
          } else {
            //验证失败
            return false;
          }
        });
      },
      //发起团购
      joinpost(address_id){
        var that = this
        that.$post('shequ_tuan_join.indext',{
          address_id:address_id,
          tuan_config_id:that.config_tuan_id
        }).then(res => {
          if(res.data.code == 200){
            that.erweima = res.data.datas.wx_code
            setTimeout(function () {
              that.dialogVisible = true
            },300)
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //重置
      resetClick(){
        this.type = ''
        this.config_tuan_name = ''
        this.listPost(1,this.config_tuan_name,this.type)//列表数据
      },
      //查询
      searchClick(){
        this.listPost(1,this.config_tuan_name,this.type)//列表数据
      },
      //分页
      currentchange(e) {
        this.listPost(e,this.config_tuan_name,this.type)//列表数据
      },
      //列表数据
      listPost(cur_page,config_tuan_name,type){
        var that = this
        that.$post('shequ_tuan.tuangou_list',{
          page: 8,
          curpage:cur_page,
          config_tuan_name:config_tuan_name,
          type:type
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 8
            that.tableData = res.data.datas
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //获取取货点
      addressPost(){
        var that = this
        that.$post('shequ_tuan_join.get_address_list',{

        }).then(res => {
          if(res.data.code == 200){
            that.options = res.data.datas
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      }
    }
  }
</script>

<style scoped>
  .tabsBox{
    padding: 20px;
  }
  .goodsBox{
    width: 100%;
  }
  .topBox {
    display: flex;
    flex-direction: column;
    /*padding: 20px;*/
    box-sizing: border-box;
  }

  .topBox .header {
    width: 100%;
    display: flex;
    align-items: center;
    padding: 20px;
    padding-top: 0;
  }
  .searchipt{
    width: 150px;
    margin-left: 5px;
  }
  .searchipt_select{
    width: 110px;
  }
  .filterbtn {
    color: #00b944 !important;
    background: #fff !important;
    border: 1px solid #00b944 !important;
    border-radius: 4px;
    padding: 0 15px;
    height: 35px;
  }

  .filterbtn:hover {
    color: #00b944 !important;
    cursor: pointer;
  }
  .searchBtn {
    background: #00b944 !important;
    border-color: #00b944 !important;
    height: 35px;
    margin-right: 15px;
    padding: 0 15px;
  }
  .itemBox{
    /*margin-top: 45px;*/
    display: flex;
    flex-wrap:wrap;
    /*justify-content:space-around;*/
  }
  .tabimgBox{
    display: flex;
    align-items: center;
    width: 50px;
    height: 50px;
    border: 1px solid #f5f5f5;
    margin: 0 auto;
  }
  .tableimg {
    max-width: 100%;
    max-height: 100%;
    display: block;
    margin: 0 auto;
  }
  .tableBox{
    padding: 20px;
    box-sizing: border-box;
    border-top: 10px solid #f0f5fc;
  }
  .hoverPointer:hover{
    cursor: pointer;
    text-decoration: underline;
  }
  .hoverRed:hover{
    color: red;
  }
  .hoverBlue:hover{
    color: blue;
  }
  .logImgBox{

  }
  .logImgBox img{
    margin: 0 auto;
    display: block;
    max-width: 80%;
  }
  .tabitemBox{
    flex: 0 0 calc(25% - 10px);
    box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
    margin: 10px 5px;
    border-radius: 5px;
    overflow: hidden;
  }
  .config_pic{
    width: 100%;
  }
  .config_name{
    font-size: 14px;
    padding: 2px 10px;
    text-overflow: -o-ellipsis-lastline;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    height: 44px;
    color: #333;
    font-weight: 600;
  }
  .config_time{
    color: #666;
    padding: 0 10px;
    line-height: 22px;
  }
  .config_type{
    color: #666;
    padding: 0 10px;
    line-height: 22px;
  }
  .config_btn{
    padding: 15px 10px;
    text-align: center;
  }

</style>
