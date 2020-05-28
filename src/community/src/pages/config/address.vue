<template>
  <div class="setBox">
    <div class="tabsBox">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="提货点管理" name="address"></el-tab-pane>
      </el-tabs>
    </div>

    <div class="btnBox">
      <!--<el-button type="success" class="pushbtn" @click="addclick">+ 新增收货点地点</el-button>-->
      <el-button type="primary" @click="addclick">+ 新增提货点</el-button>
      <el-button
        @click="multipleSelectionHandler()"
        :disabled="multipleSelection.length < 1"
        type="danger">
        删除
      </el-button>
    </div>

    <div class="itemBox">
      <el-table
        ref="multipleTable"
        :data="tableData"
        tooltip-effect="dark"
        style="width: 100%"
        @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55" align="center"></el-table-column>
        <el-table-column label="编号" width="120" align="center">
          <template slot-scope="scope">{{ scope.row.id }}</template>
        </el-table-column>
        <el-table-column prop="pin" label="所属区域" show-overflow-tooltip align="center"></el-table-column>
        <el-table-column prop="address" label="详细地址" show-overflow-tooltip align="center">
          <template slot-scope="scope">
            <div class="addBottom" style="color: #666;font-weight: bold;">{{scope.row.address}}</div>
            <div class="addBottom">{{scope.row.building}}</div>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="联系人" show-overflow-tooltip align="center"></el-table-column>
        <el-table-column prop="phone" label="联系方式" show-overflow-tooltip align="center"></el-table-column>
        <el-table-column label="操作" width="180" align="center">
          <template slot-scope="scope">
            <img
              src="../../../static/img/bj.png"
              alt
              class="czimg"
              @click="handleEdit(scope.$index, scope.row)"
            />
            |
            <img
              src="../../../static/img/sc.png"
              alt
              class="czimg"
              @click="handleDelete(scope.$index, scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>

      <div class="pageBox">
        <el-pagination
          background
          layout="prev, pager, next"
          :total="page_total"
          @current-change="currentchange"
          :current-page.sync="currentPage"
        ></el-pagination>
      </div>
    </div>


    <transition name="el-fade-in-linear">
      <div class="zz" v-show="tcshow">
        <div class="addTcBox">
          <div class="tctitleBox">{{titletext}}收货点<i class="el-icon-close" @click="tc_close"></i></div>

          <div class="tc_itemBox">
            <div class="tc_itemL">所属区域：</div>
            <div class="tc_itemR configAdd">
              <el-cascader
                v-model="region"
                :options="options"
                ref="cascaderAddr"
                @change="handleChange" ></el-cascader>
            </div>
          </div>

          <div class="tc_itemBox">
            <div class="tc_itemL">详细地址：</div>
            <div class="tc_itemR">
              <el-input placeholder="详细地址" v-model="searchText" class="input-with-select" @input="searchinput">
              </el-input>
              <div class="poiBox" v-if="poiList.pois">
                <div class="poi_item" @click="searchclick(item)" v-for="(item,index) in poiList.pois">{{item.name}}</div>
              </div>
            </div>
          </div>

          <div class="tc_itemBox">
            <div class="tc_itemL"></div>
            <div class="tc_itemR">
              <div class="mapBox" id="container-map"></div>
            </div>
          </div>

          <div class="tc_itemBox">
            <div class="tc_itemL">门牌号：</div>
            <div class="tc_itemR">
              <el-input placeholder="例如：2栋2204" v-model="housenumber" class="input-with-select"></el-input>
            </div>
          </div>

          <div class="tc_itemBox" style="display: flex;">
            <div style="display: flex;align-items: center;margin-right: 30px;">
              <div class="tc_itemL">联系人：</div>
              <div class="tc_itemR" style="width: 202px;">
                <el-input placeholder="请输入联系人姓名" v-model="username" class="input-with-select"></el-input>
              </div>
            </div>
            <div style="display: flex;align-items: center;">
              <div class="tc_itemL">联系人电话：</div>
              <div class="tc_itemR" style="width: 202px;">
                <el-input placeholder="请输入联系人电话" v-model="phone" class="input-with-select"></el-input>
              </div>
            </div>
          </div>

          <div style="margin-top: 15px;text-align: right;">
            <el-button type="primary" @click="submitClick">立即{{titletext}}</el-button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
  export default {
    name: "address",
    inject:["reload"],
    data(){
      return{
        activeName: 'address',
        tableData: [
          // {},
        ],//列表数据
        multipleSelection: [],//列表选择内容
        region: [],//区域
        options: [],//区域数据
        tcshow: false,//弹窗显示隐藏
        searchText:'',//搜索内容
        housenumber:'',//门牌号
        username:'',//联系人
        phone:'',//联系人电话
        location:'',//搜索地址的经纬度
        map:null,
        marker:'',//标记
        /* 拖拽对象 */
        positionPickerObj: {},
        /* 当前城市编码 */
        citycode: '420100',
        cur_page:1,//分页
        page_total:null,
        currentPage:1,
        editList:[],//编辑数据
        titletext:'添加',
        address_id:'',
        poiList:'',
        ismarker:false,
        map_label:'',
        tuanzhang_id:'',
      }
    },
    mounted (){
      this.AMapInit()//地图初始
    },
    created(){
      this.addPost(this.currentPage) //地址列表
      this.regionPost()//地区码
    },
    methods:{
      //tabs
      handleClick(tab, event) {
        // console.log(tab, event);
        // console.log(tab.name);
        this.reload()//刷新页面
      },
      handleSelectionChange(val) {
        this.multipleSelection = val;
      },
      //编辑
      handleEdit(index, row) {
        console.log(index,row)
        this.titletext = '修改'
        this.tcshow = true
        var that = this
        that.$post('shequ_tuan.tuan_address_edit',{
          id: row.id,//id
        }).then(res => {
          if(res.data.code == 200){
            //region searchText  housenumber username phone location
            var code = []
            code.push(res.data.datas.area_id)
            code.push(res.data.datas.street_id)
            code.push(res.data.datas.community_id)
            that.region = code
            that.searchText = res.data.datas.address
            that.housenumber = res.data.datas.building
            that.username = res.data.datas.name
            that.phone = res.data.datas.phone
            that.address_id = res.data.datas.id
            that.tuanzhang_id = row.tuanzhang_id
            that.map_label = []
            that.map_label.push(row.area)
            that.map_label.push(row.street)
            that.map_label.push(row.community)
            AMap.plugin('AMap.PlaceSearch', function(){
              var placeSearch = new AMap.PlaceSearch({
                // city 指定搜索所在城市，支持传入格式有：城市名、citycode和adcode
                city: '武汉市',
                citylimit: true
              })
              placeSearch.search(that.searchText, function (status, result) {
                // 查询成功时，result即对应匹配的POI信息
                console.log(result)
                that.map.remove(that.marker);
                that.location = result.poiList.pois[0].location
                that.marker = new AMap.Marker({
                  position: result.poiList.pois[0].location, // （e.position）-&ndash;&gt;定位点的点坐标, position ---> marker的定位点坐标，也就是marker最终显示在那个点上，
                  icon:'', // marker的图标，可以自定义，不写默认使用高德自带的
                  map: that.map,  // map ---> 要显示该marker的地图对象
                })
                var markerPosition = [result.poiList.pois[0].location.lng, result.poiList.pois[0].location.lat];
                that.map.panTo(markerPosition);
                that.marker.setLabel({
                  offset: new AMap.Pixel(-48, -50),
                  content: result.poiList.pois[0].address + result.poiList.pois[0].name
                });
                that.searchText = result.poiList.pois[0].name
                that.ismarker = true
              })
            })
          }else{
            that.$message.error(res.data.datas.error);
          }
        })

      },
      //删除
      handleDelete(index, row) {
        var that = this
        this.$confirm("是否要进行删除操作?", "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        }).then(() => {
          // console.log(index,row)
          that.$post('shequ_tuan.tuan_address_del',{
            ids:row.id,//id
          }).then(res => {
            if(res.data.code == 200){
              that.$message({
                message: '删除成功~',
                type: 'success'
              });
              that.tableData.splice(index,1)
            }else{
              that.$message.error(res.data.datas.error);
            }
          })
        });
      },
      //批量删除
      multipleSelectionHandler() {
        var that = this
        if(this.multipleSelection == null || this.multipleSelection.length < 1){
          this.$message({
            message: '请选择要操作的商品',
            type: 'warning',
            duration: 1000
          });
          return;
        }
        this.$confirm('是否要进行该批量删除操作?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          var id_all = []
          for(var i=0;i<that.multipleSelection.length;i++){
            id_all.push(that.multipleSelection[i].id)
          }
          id_all = id_all.join(",")
          that.$post('shequ_tuan.tuan_address_del',{
            ids:id_all,//id
          }).then(res => {
            if(res.data.code == 200){
              that.$message({
                message: '删除成功~',
                type: 'success'
              });
              // that.tableData = []
              for(var i=0;i<that.multipleSelection.length;i++){
                for(var y =0;y<that.tableData.length;y++){
                  if(that.multipleSelection[i].id == that.tableData[y].id){
                    that.tableData.splice(y,1)
                  }
                }
              }
            }else{
              that.$message.error(res.data.datas.error);
            }
          })
        });
      },
      //分页
      currentchange(e) {
        console.log(e)
        this.currentPage = e
        this.addPost(this.currentPage)
      },
      //立即添加
      submitClick(){
        if(!this.ismarker){
          this.$message.error('您还没有具体定位地点~');
          return;
        }
        if(!this.username){
          this.$message.error('您还没有填写联系人~');
          return;
        }
        if(!this.phone){
          this.$message.error('您还没有填联系电话~');
          return;
        }else if(!( /^(1[3584]\d{9})$/.test(this.phone))){
          this.$message.error('电话格式不正确~');
          return;
        }
        var that = this
        that.$post('shequ_tuan.tuan_address_add',{
          tuanzhang_id:that.tuanzhang_id,
          area:that.map_label[0],
          area_id:that.region[0],//地址码
          street:that.map_label[1],
          street_id:that.region[1],//地址码
          community:that.map_label[2],
          community_id:that.region[2],//地址码
          address:that.searchText,//详细地址
          longitude:that.location.lng,//经度
          latitude:that.location.lat,//维度
          name:that.username,//联系人
          phone: that.phone,//联系电话
          building: that.housenumber,//门牌号
          id: that.address_id
        }).then(res => {
          if(res.data.code == 200){
            that.$message({
              message: that.titletext + '成功~',
              type: 'success'
            });
            that.reload()//刷新页面
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //打开弹窗
      addclick(){
        this.titletext = '添加'
        this.tcshow = true
        if(this.region != ''){
          this.region = ''
          this.searchText = ''
          this.housenumber = ''
          this.username = ''
          this.phone = ''
          this.address_id = ''
          this.poiList = ''
          this.map.remove(this.marker);
        }
      },
      //关闭弹窗
      tc_close(){
        this.tcshow = false
      },
      //选择区域
      handleChange(e,form,thsAreaCode) {
        //获取label值
        var   thsAreaCode = this.$refs['cascaderAddr'].getCheckedNodes()
        this.map_label = thsAreaCode[0].pathLabels
        // console.log(this.map_label );
        this.searchText = ''
        var that = this
        AMap.plugin('AMap.PlaceSearch', function(){
          var placeSearch = new AMap.PlaceSearch({
            // city 指定搜索所在城市，支持传入格式有：城市名、citycode和adcode
            city: '武汉市',
            citylimit: true
          })
          placeSearch.search(that.map_label[0], function (status, result) {
            // 查询成功时，result即对应匹配的POI信息
            // console.log(result.poiList.pois[0].location)
            that.poiList = ''
            that.map.remove(that.marker);
            var markerPosition = [result.poiList.pois[0].location.lng, result.poiList.pois[0].location.lat];
            that.map.panTo(markerPosition);
          })
        })
      },
      //地图初始
      AMapInit: function () {
        this.map = new AMap.Map('container-map', {
          resizeEnable: true,
          zoom: 14,
        })
      },
      //改变值搜索
      searchinput(){
        if(!this.region[2]){
          this.$message.error('请选择所属区域~');
          return
        }
        if(!this.searchText){
          this.poiList = ''
          this.map.remove(this.marker);
          this.ismarker = false
          this.$message.error('请填写需要搜索的详细地址~');
          return
        }
        var that = this
        AMap.plugin('AMap.PlaceSearch', function(){
          var placeSearch = new AMap.PlaceSearch({
            // city 指定搜索所在城市，支持传入格式有：城市名、citycode和adcode
            city: '武汉市',
            citylimit: true
          })
          placeSearch.search(that.searchText, function (status, result) {
            // 查询成功时，result即对应匹配的POI信息
            console.log(result)
            if(!result.poiList){
              that.$message.error('搜索不到对应地址');
              that.poiList = ''
              that.map.remove(that.marker);
              that.ismarker = false
              return
            }
            that.poiList = result.poiList
          })
        })
      },
      //点击搜索
      searchclick(item) {
        // console.log(item)
        var that = this
        that.poiList = ''
        that.map.remove(that.marker);
        that.location = item.location
        that.marker = new AMap.Marker({
          position: item.location, // （e.position）-&ndash;&gt;定位点的点坐标, position ---> marker的定位点坐标，也就是marker最终显示在那个点上，
          icon:'', // marker的图标，可以自定义，不写默认使用高德自带的
          map: that.map,  // map ---> 要显示该marker的地图对象
        })
        var markerPosition = [item.location.lng, item.location.lat];
        that.map.panTo(markerPosition);
        that.marker.setLabel({
          offset: new AMap.Pixel(-48, -50),
          content: item.address + item.name
        });
        that.searchText = item.name
        that.ismarker = true
      },
      //地址列表
      addPost(cur_page){
        var that = this
        that.$post('shequ_tuan.tuan_address_list',{
          page: 5,
          curpage:cur_page,
        }).then(res => {
          if(res.data.code == 200){
            that.page_total = res.data.page_total * 5
            that.tableData = res.data.datas
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //地区码
      regionPost(){
        var that = this
        that.$post('area.get_area_list',{

        }).then(res => {
          if(res.data.code == 200){
            that.options = res.data.datas
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
    }
  }
</script>

<style scoped>
  .setBox{
    padding: 20px 30px;
    background: #fff;
    width: 100%;
  }
  .btnBox{
    margin-top: 25px;
    display: flex;
    align-items: center;
  }
  .pushbtn {
    background: #00b944 !important;
    border-color: #00b944 !important;
    height: 40px;
    margin-right: 15px;
    font-size: 12px !important;
  }
  .btnBox .el-button--primary {
    /*background: #fff !important;*/
    /*border-color: #dcdfe6 !important;*/
    /*color: #999 !important;*/
    /*height: 40px;*/
    /*!* border-left: none; *!*/
    /*border-radius: 4px;*/
    /*padding: 0;*/
    /*width: 50px;*/
  }
  .itemBox{
    margin-top: 20px;
  }
  .czimg {
    width: 15px;
    vertical-align: middle;
    margin: 0 5px;
    margin-bottom: 3px;
  }
  .czimg:hover {
    cursor: pointer;
  }
  .pageBox {
    margin: 50px 0;
    text-align: center;
  }
  .addTop{
    font-size: 14px;
    font-weight: 600;
  }
  .addBottom{
    /*color: #999;*/
    /*font-size: 12px;*/
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .zz{
    width: 100vw;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.6);
    z-index: 999;
  }
  .addTcBox{
    width: 900px;
    height: 630px;
    border-radius: 5px;
    background: #fff;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -340px;
    margin-left: -420px;
    padding: 30px;
  }
  .tctitleBox{
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 1px;
    display: flex;
    justify-content: space-between;
  }
  .tctitleBox i{
    font-size: 22px;
    color: #666;
  }
  .tctitleBox i{
    cursor: pointer;
  }
  .tcbtn{
    width: 120px;
    line-height: 40px;
    text-align: center;
    color: #fff;
    background: #00b944;
    border-radius: 4px;
    margin: 0 auto;
    margin-top: 23px;
    float: right;
  }
  .tc_itemBox{
    display: flex;
    align-items: center;
    margin-top: 25px;
  }
  .tc_itemL{
    font-size: 14px;
    margin-right: 15px;
    width: 90px;
    text-align: right;
  }
  .tc_itemR{
    width: 540px;
    position: relative;
  }
  .configAdd .el-cascader{
    width: 270px !important;
  }
  .error{
    color: red;
    margin-left: 5px;
    position: absolute;
    top: 40px;
    left: 0px;
  }
  .mapBox{
    width: 540px;
    height: 210px;
  }
  .poiBox{
    width: 100%;
    border: 1px solid #f5f5f5;
    position: absolute;
    top: 40px;
    left: 0;
    background: #fff;
    z-index: 9;
    max-height: 190px;
    overflow-y: scroll;
  }
  .poiBox::-webkit-scrollbar {
    width: 0px;
  }
  .poi_item{
    padding: 0 20px;
    line-height: 40px;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .poi_item:hover{
    background: #f7fdf9;
  }
</style>
