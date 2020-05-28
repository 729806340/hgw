<template>
  <div class="detailBox">
    <div class="tableBox">
      <div class="itemBox">
        <el-table :data="tableData" tooltip-effect="dark" style="width: 100%">
          <el-table-column label="商品图片" prop="goods_image" show-overflow-tooltip align="center">
            <template scope="scope">
              <div class="tabimgBox">
                <el-image
                  style="width: 60px; height: 60px;display: flex;align-items: center;justify-content: center; margin: 0 auto;"
                  :src="scope.row.goods_image"
                  :preview-src-list="scope.row.srcList" class="elimg_img">
                </el-image>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="商品名称" prop="goods_name" show-overflow-tooltip align="center"></el-table-column>
          <el-table-column label="分类" prop="gc_name" show-overflow-tooltip align="center"></el-table-column>
          <el-table-column label="商品价格（元）" prop="goods_price" show-overflow-tooltip align="center"></el-table-column>
          <el-table-column label="所得佣金（元）" prop="commis" show-overflow-tooltip align="center"></el-table-column>
        </el-table>
      </div>
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
</template>

<script>
  export default {
    name: "detail",
    inject:["reload"],
    components:{

    },
    props: ['tableItem'],//父组件传的数据
    data () {
      return {
        tableData: [],
        page_total:null,
        currentPage:1,
      }
    },
    created(){  //载入前
      // console.log(this.tableItem)
      this.listPost(1)
    },
    methods:{  //业务
      //分页
      currentchange(e){
        this.listPost(e)
      },
      //列表数据
      listPost(cur_page){
        var that = this
        that.$post('shequ_tuan.tuangou_info',{
          page: 5,
          curpage:cur_page,
          config_tuan_id:that.tableItem
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 5
            that.tableData = res.data.datas
            for(var i=0;i<that.tableData.length;i++){
              that.tableData[i].srcList = [that.tableData[i].goods_image]
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
  .detailBox{
    width: 100%;
  }
  .topBox{
    display: flex;
    align-items: center;
    justify-content: space-around;
    background: #F6F8F7;
    padding: 15px;
  }
  .topBox div{
    display: flex;
    align-items: center;
  }
  .tableBox{
    margin-top: 20px;
  }
  .tit{
    font-size: 14px;
    margin-bottom: 8px;
  }
  .itemBox{
    /*max-height: 530px;*/
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
</style>
