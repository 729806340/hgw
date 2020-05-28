<template>
  <div class="listBox">
    <div class="tabsBox">
      <el-tabs v-model="activeName">
        <el-tab-pane label="结算列表" name="list"></el-tab-pane>
      </el-tabs>
    </div>
    <div class="topBox">
      <!--<div class="header">-->
        <!--&lt;!&ndash;<div>&ndash;&gt;-->
          <!--&lt;!&ndash;结算编号：&ndash;&gt;-->
          <!--&lt;!&ndash;<el-input type="text" v-model="sn" class="searchipt" placeholder="请输入订单编号" />&ndash;&gt;-->
        <!--&lt;!&ndash;</div>&ndash;&gt;-->
        <!--<div>-->
          <!--订单状态：-->
          <!--<el-select v-model="state" placeholder="请选择" class="searchipt">-->
            <!--<el-option label="待审核" value="1"></el-option>-->
            <!--<el-option label="团购中" value="2"></el-option>-->
            <!--<el-option label="待发货" value="3"></el-option>-->
            <!--<el-option label="已完成" value="4"></el-option>-->
          <!--</el-select>-->
        <!--</div>-->
        <!--<div style="margin-left: 25px;">-->
          <!--<el-button type="primary" icon="el-icon-search">查询</el-button>-->
          <!--<el-button type="info" plain @click="resetClick">重置</el-button>-->
        <!--</div>-->
      <!--</div>-->

      <div class="tableBox">
        <div class="itemBox">
          <el-table ref="multipleTable" :data="tableData" tooltip-effect="dark" style="width: 100%"
                    @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55"></el-table-column>
            <el-table-column label="结算编号" prop="ob_no" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="出帐日期" prop="ob_create_date" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="账单起始日期" show-overflow-tooltip align="center">
              <template scope="scope">
                <div class="tab-time">{{scope.row.ob_start_date}}</div>
                <div class="tab-time">至</div>
                <div class="tab-time">{{scope.row.ob_end_date}}</div>
              </template>
            </el-table-column>
            <el-table-column label="销售金额" prop="ob_order_totals" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="退款金额" prop="ob_order_return_totals" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="所得佣金" prop="ob_result_totals" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="结算状态" prop="ob_state" show-overflow-tooltip align="center"></el-table-column>
            <!--<el-table-column label="操作" show-overflow-tooltip align="center">-->
              <!--<template slot-scope="scope">-->
                <!--<div class="hoverPointer hoverBlue" @click="cancelclick(scope.row,scope.$index)">查看结算详情</div>-->
                <!--<div class="hoverPointer hoverBlue" @click="detailclick(scope.row)">查看订单明细</div>-->
              <!--</template>-->
            <!--</el-table-column>-->
          </el-table>
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
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: "list",
    inject:["reload"],
    components:{

    },
    data () {
      return {
        activeName:'list',
        sn:'',
        state:'',
        tableData: [

        ],
        multipleSelection: [],
        page_total:null,
        currentPage:1,
        srcList:[],
      }
    },
    created(){  //载入前
      this.listPost(1)//列表数据
    },
    methods:{  //业务
      imgshow(url){
        this.srcList = [url]
      },
      //取消分销
      cancelclick(item,index){
        // console.log(item,index)
        // var that = this
        // this.$confirm("是否确认收货?", "提示", {
        //   confirmButtonText: "确定",
        //   cancelButtonText: "取消",
        //   type: "warning"
        // }).then(() => {
        //
        // });
      },
      //查看详情
      detailclick(item){
        // console.log(item)
        // this.$router.push({path:'/home/channel/apply/detail', query: { id: item.id }})
      },
      //选中
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      //分页
      currentchange(e) {
        console.log(e)
      },
      //重置
      resetClick(){
        this.state = ''
        this.sn = ''
      },
      //列表数据
      listPost(cur_page){
        var that = this
        that.$post('shequ_tuan_bill.index',{
          page: 5,
          curpage:cur_page
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 5
            that.tableData = res.data.datas.bill_list
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
    }
  }
</script>

<style scoped>
  .listBox{
    width: 100%;
  }
  .tableBox{
    padding: 20px;
    box-sizing: border-box;
    border-top: 10px solid #f0f5fc;
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
  .tabsBox{
    padding: 20px;
  }
  .tab-time{
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
</style>
