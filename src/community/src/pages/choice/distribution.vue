<template>
  <div class="listBox">
    <div class="topBox">
      <div class="header">
        <div>
          团购名称：
          <el-input type="text" v-model="sn" class="searchipt" placeholder="请输入团购名称" />
        </div>
        <div style="margin-left: 25px;">
          订单状态：
          <el-select v-model="state" placeholder="请选择" class="searchipt">
            <el-option label="全部" value=""></el-option>
            <el-option label="进行中" value="1"></el-option>
            <el-option label="未开始" value="2"></el-option>
            <el-option label="已结束 " value="3"></el-option>
          </el-select>
        </div>
        <div style="margin-left: 25px;">
          <el-button type="primary" icon="el-icon-search" @click="searchClick">查询</el-button>
          <el-button type="info" plain @click="resetClick">重置</el-button>
        </div>
      </div>

      <div class="tableBox">
        <div class="itemBox">
          <el-table ref="multipleTable" :data="tableData" tooltip-effect="dark" style="width: 100%"
                    @selection-change="handleSelectionChange">
            <el-table-column label="团购海报" prop="config_pic" show-overflow-tooltip align="center" width="200">
              <template scope="scope">
                <div class="tabimgBox">
                  <el-image
                    style="height: 120px;display: flex;align-items: center;justify-content: center; margin: 0 auto;"
                    :src=scope.row.config_pic
                    :preview-src-list="scope.row.srcList_pic" class="elimg_img">
                  </el-image>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="团购名称" prop="sn" align="center" width="200">
              <template scope="scope">
                <div class="tab-name">{{scope.row.name}}</div>
              </template>
            </el-table-column>
            <el-table-column label="活动时间" prop="sn" align="center" width="100">
              <template scope="scope">
                <div class="tab-time">{{scope.row.start_time}}</div>
                <div class="tab-time">至</div>
                <div class="tab-time">{{scope.row.end_time}}</div>
              </template>
            </el-table-column>
            <el-table-column label="成团人员数量（人）" prop="join_num" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="交易总金额（元）" prop="trans_amount" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="所得佣金金额（元）" prop="commis_amount" show-overflow-tooltip align="center"></el-table-column>
            <el-table-column label="订单状态" prop="type" show-overflow-tooltip align="center">
              <template scope="scope">
                <div class="tl">{{scope.row.type}}</div>
                <div class="hoverPointer hoverBlue" @click="detailclick(scope.row)">查看详情</div>
              </template>
            </el-table-column>
            <el-table-column label="团购二维码" prop="qr_code" show-overflow-tooltip align="center">
              <template scope="scope">
                <div class="tabimgBox">
                  <el-image
                    style="width: 60px; height: 60px;display: flex;align-items: center;justify-content: center; margin: 0 auto;"
                    :src="scope.row.qr_code"
                    :preview-src-list="scope.row.srcList_code"class="elimg_img">
                  </el-image>
                </div>
              </template>
            </el-table-column>
            <el-table-column label="操作" show-overflow-tooltip align="center" width="140">
              <template slot-scope="scope">
                <!--<el-button :type="!scope.row.disabled?'primary':'info'" plain @click="confirmclick(scope.row,scope.$index)" :disabled="scope.row.disabled">-->
                <!--{{!scope.row.disabled?'批量确认收货':'已收货'}}-->
                <!--</el-button>-->
                <div><el-button type="primary" plain @click="confirmclick(scope.row,scope.$index)" size="small" v-if="scope.row.plqr == '1'">批量确认收货</el-button></div>
                <!--<div><el-button type="info" plain disabled size="small">已收货</el-button></div>-->
                <div><el-button type="danger" plain @click="delclick(scope.row,scope.$index)" size="small" style="margin-top: 5px;" v-if="scope.row.is_del != '1'">删除团购</el-button></div>
              </template>
            </el-table-column>
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

    <el-dialog title="订单明细" :visible.sync="dialogTableVisible" width="90%" top="50px">
      <Detail :tableItem="tableItem" :type=type v-if="dialogTableVisible"></Detail>
    </el-dialog>
  </div>
</template>

<script>
  import Detail from '../../components/order_detail'
  export default {
    name: "list",
    inject:["reload"],
    components:{
      Detail
    },
    data () {
      return {
        // activeName: 'list',
        sn:'',
        state:'',
        tableData: [],
        multipleSelection: [],
        page_total:null,
        currentPage:1,
        dialogTableVisible:false,
        tableItem:'',
        type:'',
      }
    },
    created(){  //载入前
      this.listPost(1,this.sn,this.state)   //列表数据
    },
    methods:{  //业务
      //删除团购
      delclick(item,index){
        // console.log(item,index)
        var that = this
        this.$confirm("是否确认删除团购?", "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        }).then(() => {
          // that.tableData[index].disabled = true
          // console.log(item.id)
          that.$post('shequ_tuan.del_tuan',{
            tuan_id:item.id
          }).then(res => {
            if(res.data.code == 200){
              that.$message({
                message: '删除成功~',
                type: 'success'
              });
              that.listPost(that.currentPage,this.sn,this.state)   //列表数据
            }else{
              that.$message.error(res.data.datas.error);
            }
          })
        });
      },
      //确认收货
      confirmclick(item,index){
        // console.log(item,index)
        var that = this
        this.$confirm("是否批量确认收货?", "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        }).then(() => {
          // that.tableData[index].disabled = true
          // console.log(item.id)
          that.$post('shequ_tuan.queren_tuan',{
            tuan_id:item.id
          }).then(res => {
            if(res.data.code == 200){
              that.$message({
                message: '批量确认成功~',
                type: 'success'
              });
            }else{
              that.$message.error(res.data.datas.error);
            }
          })
        });
      },
      //查看详情
      detailclick(item){
        // console.log(item)
        // this.$router.push({path:'/home/channel/apply/detail', query: { id: item.id }})
        this.dialogTableVisible = true
        this.tableItem = item.id
        this.type = item.config_type
      },
      //选中
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      //分页
      currentchange(e) {
        // console.log(e,this.currentPage)
        this.listPost(e,this.sn,this.state)   //列表数据
      },
      //重置
      resetClick(){
        this.state = ''
        this.sn = ''
        this.listPost(1,this.sn,this.state)   //列表数据
      },
      //查询
      searchClick(){
        this.listPost(1,this.sn,this.state)//列表数据
      },
      //列表数据
      listPost(cur_page,sn,state){
        var that = this
        that.$post('shequ_tuan.tuan_list',{
          page: 5,
          curpage:cur_page,
          name:sn,
          type:state
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 5
            that.tableData = res.data.datas
            for(var i=0;i<that.tableData.length;i++){
              that.tableData[i].srcList_pic = [that.tableData[i].config_pic]
              that.tableData[i].srcList_code = [that.tableData[i].qr_code]
            }
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
  .hoverPointer{
    display: inline-block;
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
  .tl{
    color: #00b944;
  }
  .tab-name{
    /*font-weight: 600;*/
    /*font-size: 14px;*/
    /*overflow: hidden;*/
    /*text-overflow:ellipsis;*/
    /*white-space: nowrap;*/
  }
  .tab-time{
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
</style>
