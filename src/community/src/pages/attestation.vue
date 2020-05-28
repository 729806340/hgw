<template>
  <div class="attBox">
    <div class="tabsBox">
      <el-tabs v-model="activeName">
        <el-tab-pane label="认证信息" name="info"></el-tab-pane>
      </el-tabs>
    </div>
    <div class="editBox">
      <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="110px" class="demo-ruleForm">
        <el-form-item label="真实姓名：" prop="name">
          <el-input v-model="ruleForm.name"></el-input>
        </el-form-item>

        <el-form-item label="团长身份：" prop="state">
          <el-select v-model="ruleForm.state" placeholder="请选择">
            <el-option label="社区工作人员" value="1"></el-option>
            <el-option label="个体商户" value="2"></el-option>
            <el-option label="自由职业者" value="3"></el-option>
            <!--<el-option label="公司员工" value="4"></el-option>-->
          </el-select>
        </el-form-item>

        <el-form-item label="商户类别：" prop="type" v-if="ruleForm.state == 2">
          <el-select v-model="ruleForm.type" placeholder="请选择">
            <el-option label="餐饮" value="1"></el-option>
            <el-option label="超市便利店" value="2"></el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="店铺名称：" prop="shop_name" v-if="ruleForm.state == 2">
          <el-input v-model="ruleForm.shop_name"></el-input>
        </el-form-item>

        <!--<el-form-item label="战队名称：" prop="team" v-if="ruleForm.state == 4">-->
          <!--<el-input v-model="ruleForm.team"></el-input>-->
        <!--</el-form-item>-->

        <el-form-item label="手机号码：" prop="phone">
          <el-input v-model="ruleForm.phone"></el-input>
        </el-form-item>

        <!--<el-form-item label="个人头像：" prop="logo">-->
          <!--<div class="imgBox">-->
            <!--<label for="uploadlogo"><img :src="ruleForm.logo" alt=""></label>-->
            <!--<input type="file" name="image" accept="image/png,image/jpeg" id="uploadlogo" style="display: none;" @change="logoFile($event)" />-->
          <!--</div>-->
        <!--</el-form-item>-->

        <el-form-item label="身份证号：" prop="sn">
          <el-input v-model="ruleForm.sn"></el-input>
        </el-form-item>

        <el-form-item label="身份证正面：" prop="sn_zheng">
          <div class="imgBox">
            <label for="uploadsfz1"><img :src="ruleForm.sn_zheng" alt=""></label>
            <input type="file" name="image" accept="image/png,image/jpeg" id="uploadsfz1" style="display: none;" @change="sfz1File($event)" />
          </div>
        </el-form-item>

        <el-form-item label="身份证反面：" prop="sn_fan">
          <div class="imgBox">
            <label for="uploadsfz2"><img :src="ruleForm.sn_fan" alt=""></label>
            <input type="file" name="image" accept="image/png,image/jpeg" id="uploadsfz2" style="display: none;" @change="sfz2File($event)" />
          </div>
        </el-form-item>

        <!--<el-form-item label="所属区域：" prop="region">-->
          <!--<el-cascader-->
            <!--v-model="ruleForm.region"-->
            <!--:options="options"-->
            <!--@change="handleChange"></el-cascader>-->
        <!--</el-form-item>-->

        <!--<el-form-item label="详细地址：" prop="address">-->
          <!--<div>{{ruleForm.address}}</div>-->
          <!--<el-button @click="tc_show">获取详细地址</el-button>-->
        <!--</el-form-item>-->

        <el-form-item label="开户行：" prop="bank">
          <el-input v-model="ruleForm.bank"></el-input>
        </el-form-item>

        <el-form-item label="开户人：" prop="bank_ren">
          <el-input v-model="ruleForm.bank_ren"></el-input>
        </el-form-item>

        <el-form-item label="银行卡号：" prop="number">
          <el-input v-model="ruleForm.number"></el-input>
        </el-form-item>

        <div class="btnBox">
          <el-button type="primary" @click="submit('ruleForm')">提交{{t}}</el-button>
        </div>
      </el-form>
    </div>

    <transition name="el-fade-in-linear">
      <div class="zz" v-show="tcshow">
        <div class="addTcBox">
          <div class="tctitleBox">获取详细地址<i class="el-icon-close" @click="tc_close"></i></div>

          <div class="tc_itemBox">
            <div class="tc_itemL">详细地址：</div>
            <div class="tc_itemR">
              <el-input placeholder="详细地址" v-model="address" class="input-with-select" @input="searchinput">
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

          <div class="btnBox" style="margin-top: 30px;">
            <el-button type="primary" @click="mapClick">确定</el-button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
  export default {
    name: "attestation",
    inject:["reload"],
    props: ['text'],//父组件传的数据
    components:{

    },
    data () {
      var name = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入真实姓名'));
        } else {
          callback();
        }
      };
      var state = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请选择团长身份'));
        } else {
          callback();
        }
      };
      var type = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请选择个体户类别'));
        } else {
          callback();
        }
      };
      var shop_name = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入店铺名称'));
        } else {
          callback();
        }
      };
      var phone = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入手机号码'));
        } else if(!( /^(1[3584]\d{9})$/.test(value))){
          callback(new Error('手机号码不正确'));
        } else{
          callback();
        }
      };
      // var logo = (rule, value, callback) => {
      //   if (value == '../../static/img/addimg.png') {
      //     callback(new Error('请上传个人头像'));
      //   } else{
      //     callback();
      //   }
      // };
      var sn = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入身份证号'));
        } else if(value.length != 18){
          callback(new Error('身份证号不正确'));
        }else{
          callback();
        }
      };
      var sn_zheng = (rule, value, callback) => {
        if (value == '../../static/img/addimg.png') {
          callback(new Error('请上传身份证正面'));
        } else{
          callback();
        }
      };
      var sn_fan = (rule, value, callback) => {
        if (value == '../../static/img/addimg.png') {
          callback(new Error('请上传身份证反面'));
        } else{
          callback();
        }
      };
      var bank = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入银行名称'));
        } else{
          callback();
        }
      };
      var bank_ren = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入开户人名称'));
        } else{
          callback();
        }
      };
      var number = (rule, value, callback) => {
        if (value === '') {
          callback(new Error('请输入银行卡号'));
        } else{
          callback();
        }
      };
      return {
        ruleForm: {
          name:'',//真实姓名
          state:'',//团长身份
          type:'',//个体户类别
          shop_name:'',//店铺名称
          phone:'',//手机号码
          // logo:'../../static/img/addimg.png',//个人logo
          sn:'',//身份证
          sn_zheng:'../../static/img/addimg.png',//身份证正面
          sn_fan:'../../static/img/addimg.png',//身份证反面
          bank:'',//银行
          bank_ren:'',//开户人
          number:'',//卡号
        },
        rules: {
          name: [{required: true, validator: name, trigger: 'blur'},],
          state: [{required: true, validator: state, trigger: 'change'},],
          type: [{required: true, validator: type, trigger: 'change'},],
          shop_name: [{required: true, validator: shop_name, trigger: 'blur'},],
          phone: [{required: true, validator: phone, trigger: 'blur'},],
          // logo: [{required: true, validator: logo, trigger: 'change'},],
          sn: [{required: true, validator: sn, trigger: 'blur'},],
          sn_zheng: [{required: true, validator: sn_zheng, trigger: 'change'},],
          sn_fan: [{required: true, validator: sn_fan, trigger: 'change'},],
          bank: [{required: true, validator: bank, trigger: 'blur'},],
          bank_ren: [{required: true, validator: bank_ren, trigger: 'blur'},],
          number: [{required: true, validator: number, trigger: 'blur'},],
        },
        activeName:'info',
        options: [],
        tcshow:false,
        poiList:'',
        map:null,
        marker:'',//标记
        address:'',
        lo_la:'',
        // logo:'',
        sn_zheng:'',
        sn_fan:"",
        t:'',
        id:'',
      }
    },
    mounted (){
      // this.AMapInit()//地图初始
    },
    created(){  //载入前
      // this.postRegion() //获取区域数据
      this.t = this.text || '申请'
      if(this.text){
        this.postdata()  //团长数据
      }
    },
    methods:{  //业务
      //团长数据
      postdata(){
        var that = this
        that.$post('shequ_tuan.app_info',{

        }).then(res => {
          // console.log(res);
          if(res.data.code == 200){
            that.ruleForm.name = res.data.datas.name
            that.ruleForm.state = res.data.datas.type
            that.ruleForm.type = res.data.datas.category
            that.ruleForm.shop_name = res.data.datas.store_name
            that.ruleForm.phone = res.data.datas.phone
            that.ruleForm.sn = res.data.datas.sn
            that.ruleForm.bank = res.data.datas.bank_name
            that.ruleForm.number = res.data.datas.bank_sn
            that.ruleForm.bank_ren = res.data.datas.bank_ren
            that.id = res.data.datas.id

            // that.ruleForm.logo = res.data.datas.avatar_http
            that.ruleForm.sn_zheng = res.data.datas.sn_image1_http
            that.ruleForm.sn_fan = res.data.datas.sn_image2_http

            // that.logo = res.data.datas.avatar
            that.sn_zheng = res.data.datas.sn_image1
            that.sn_fan = res.data.datas.sn_image2
          }else{
            that.$message.error(res.data.datas.error);
          }
        })
      },
      //提交
      submit(formName) {
        var that = this
        this.$refs[formName].validate((valid) => {
          if (valid) {
            //验证成功
            var data = {
              id: that.id,
              name:that.ruleForm.name,//真实姓名
              type:that.ruleForm.state,//团长身份
              category:that.ruleForm.type,//个体户类别
              store_name:that.ruleForm.shop_name,//店铺名称
              phone:that.ruleForm.phone,//手机号码
              // avatar:that.logo,//个人logo
              sn:that.ruleForm.sn,//身份证
              sn_image1:that.sn_zheng,//身份证正面
              sn_image2:that.sn_fan,//身份证反面
              bank_name:that.ruleForm.bank,//银行
              bank_sn:that.ruleForm.number,//卡号
              bank_ren:that.ruleForm.bank_ren,//户名
            }
            that.$post('shequ_tuan.app_approve',data).then(res => {
              if(res.data.code == 200){
                that.$message({
                  message: that.t + '成功~',
                  type: 'success'
                });
                localStorage.setItem("t_tuanzhang",'1');
                this.$router.push({
                  path: '/survey'
                })
              }else{
                that.$message.error(res.data.datas.error);
              }
            })
          } else {
            //验证失败
            return false;
          }
        });
      },
      //打开弹窗
      tc_show(){
        this.tcshow = true
      },
      //关闭弹窗
      tc_close(){
        this.tcshow = false
      },
      //个人头像
      // logoFile(e){
      //   // console.log(e.target.files);
      //   let file=e.target.files[0];
      //   if(!file){
      //     return
      //   }
      //   if(file.size>5120000){
      //     this.$message.error('图片已超过5M，请更换...');
      //     return
      //   }
      //   let url='';
      //   var reader = new FileReader();
      //   reader.readAsDataURL(file);
      //   let that=this;
      //   reader.onload = function (e) {
      //     url=this.result.substring(this.result.indexOf(',')+1);
      //     // that.$refs['imgimg'].setAttribute('src','data:image/png;base64,'+url);
      //     // that.ruleForm.logo = 'data:image/png;base64,'+url
      //   }
      //   let formData = new FormData();
      //   formData.append('file', file)
      //   that.$axios.post(that.$store.state.postURL+'shequ_tuan.new_upload_pic',
      //     formData
      //   ).then(function (res) {
      //     if(res.data.code == 200){
      //       that.ruleForm.logo = res.data.datas.http_pic
      //       that.logo = res.data.datas.pic
      //     }else{
      //       that.$message.error(res.data.datas.error);
      //     }
      //   }).catch(function (error) {
      //     // console.log(error);
      //   });
      // },
      //身份证正面
      sfz1File(e){
        // console.log(e.target.files);
        let file=e.target.files[0];
        if(!file){
          return
        }
        if(file.size>5120000){
          this.$message.error('图片已超过5M，请更换...');
          return
        }
        let url='';
        var reader = new FileReader();
        reader.readAsDataURL(file);
        let that=this;
        reader.onload = function (e) {
          url=this.result.substring(this.result.indexOf(',')+1);
          // that.$refs['imgimg'].setAttribute('src','data:image/png;base64,'+url);
          // that.ruleForm.sn_zheng = 'data:image/png;base64,'+url
        }
        let formData = new FormData();
        formData.append('file', file)
        that.$axios.post(that.$store.state.postURL+'shequ_tuan.new_upload_pic',
          formData
        ).then(function (res) {
          if(res.data.code == 200){
            that.ruleForm.sn_zheng = res.data.datas.http_pic
            that.sn_zheng = res.data.datas.pic
          }else{
            that.$message.error(res.data.datas.error);
          }
        }).catch(function (error) {
          // console.log(error);
        });
      },
      //身份证反面
      sfz2File(e){
        // console.log(e.target.files);
        let file=e.target.files[0];
        if(!file){
          return
        }
        if(file.size>5120000){
          this.$message.error('图片已超过5M，请更换...');
          return
        }
        let url='';
        var reader = new FileReader();
        reader.readAsDataURL(file);
        let that=this;
        reader.onload = function (e) {
          url=this.result.substring(this.result.indexOf(',')+1);
          // that.$refs['imgimg'].setAttribute('src','data:image/png;base64,'+url);
          that.ruleForm.sn_fan = 'data:image/png;base64,'+url
        }
        let formData = new FormData();
        formData.append('file', file)
        that.$axios.post(that.$store.state.postURL+'shequ_tuan.new_upload_pic',
          formData
        ).then(function (res) {
          // console.log(res);
          if(res.data.code == 200){
            that.ruleForm.sn_fan = res.data.datas.http_pic
            that.sn_fan = res.data.datas.pic
          }else{
            that.$message.error(res.data.datas.error);
          }
        }).catch(function (error) {
          // console.log(error);
        });
      },
      //改变值搜索
      searchinput(){
        if(!this.address){
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
          placeSearch.search(that.address, function (status, result) {
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
          position: item.location, // （e.position）--->定位点的点坐标, position ---> marker的定位点坐标，也就是marker最终显示在那个点上，
          icon:'', // marker的图标，可以自定义，不写默认使用高德自带的
          map: that.map,  // map ---> 要显示该marker的地图对象
        })
        var markerPosition = [item.location.lng, item.location.lat];
        that.map.panTo(markerPosition);
        that.marker.setLabel({
          offset: new AMap.Pixel(-48, -50),
          content: item.address + item.name
        });
        that.address = item.name
        that.ismarker = true
        that.lo_la = markerPosition
      },
      //地图初始
      AMapInit: function () {
        var that = this
        this.map = new AMap.Map('container-map', {
          resizeEnable: true,
          zoom: 14
        })
      },
      //地址确定
      mapClick(){
        this.ruleForm.address = this.address
        this.ruleForm.lo_la = this.lo_la
        this.tcshow = false
      },
      //获取区域数据
      postRegion(){
        var that = this
        that.$post('area.get_area_list',{

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
  .attBox{
    width: 100%;
    /*padding: 20px;*/
    box-sizing: border-box;
  }
  .editBox{
    width: 400px;
    margin: 0 auto;
    margin-top: 20px;
  }
  .tabsBox{
    padding: 20px;
  }
  .btnBox{
    text-align: center;
    padding-bottom: 20px;
  }
  .imgBox{
    display: flex;
    align-items: center;
    width: 80px;
    height: 80px;
    border: 1px solid #f5f5f5;
    border-radius: 4px;
  }
  .imgBox img:hover{
    cursor: pointer;
  }
  .imgBox img {
    max-width: 100%;
    max-height: 100%;
    display: block;
    margin: 0 auto;
  }
  .imgBox label{
    margin: 0 auto;
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
    width: 840px;
    height: 570px;
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
  .mapBox{
    width: 540px;
    height: 300px;
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
