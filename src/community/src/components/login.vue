<template>
  <div class="first">
    <div class="loginBox">
      <img src="../../static/img/loginBG2.png" alt="" style="width: 100%;height: 100vh;">
      <div class="loginform">
        <!--<div class="title_b">您好，欢迎登录</div>-->
        <!--<div class="tab_title"><div class="divA">登录</div><div @click="goregister">注册</div></div>-->
        <!--<div style="display: flex;align-items: center;">-->
          <!--<div style="flex: 1;">-->
            <!--<form method="post">-->
              <!--<div class="inputBox">-->
                <!--<img src="../../static/img/zh.png" alt="">-->
                <!--<input type="text" v-model="name" placeholder="请输入手机号" autocomplete="off" required>-->
                <!--&lt;!&ndash;<el-input type="text" v-model="name" placeholder="请输入汉购网账号" autocomplete="off"></el-input>&ndash;&gt;-->
              <!--</div>-->
              <!--<div class="inputBox">-->
                <!--<img src="../../static/img/mm.png" alt="">-->
                <!--<input type="password" v-model="password" placeholder="6-20位数字或英文组合密码" autocomplete="off" required>-->
                <!--&lt;!&ndash;<el-input type="password" v-model="password" placeholder="请输入汉购网密码" autocomplete="off" show-password></el-input>&ndash;&gt;-->
              <!--</div>-->
            <!--</form>-->
            <!--<div class="btn submitbtn" @click="submit">登录</div>-->
            <!--<div style="display: flex;justify-content: space-between;">-->
              <!--<span class="reset_password" @click="goforget">忘记密码？</span>-->
            <!--</div>-->
          <!--</div>-->
          <!--<div style="margin-left: 20px;">-->
            <!--<img :src=wx_login_image alt="" class="wx_login_image">-->
            <!--<div class="smlogin">扫码登录</div>-->
          <!--</div>-->
        <!--</div>-->
        <img src="../../static/img/z3.png" alt="" class="z3">
        <div class="z4">
          <div class="t1"><div class="t1_1"></div>团长登录</div>
          <div class="t2">微信扫一扫，一键成为汉购网社区团长</div>
          <img :src=wx_login_image alt="" class="z4_img">
          <div class="t3">{{text}}</div>
          <div class="t4" @click="erweimaclick">立即登录</div>
        </div>
      </div>
    </div>

  </div>
</template>
<script>
  export default {
    name: 'login',
    components:{

    },
    data () {
      return {
        name:'',
        password:'',
        checked: true,
        dialogTableVisible:false,
        wx_login_code:'',
        wx_login_image:'../../static/img/z4.png',
        wx_login_image_2:'',
        show:false,
        interval:'',
        text:'您还未登录，请点击登录',
      }
    },
    created(){  //载入前
      var that = this
      // if(localStorage.getItem('userPassword')){
      //   this.name = localStorage.getItem('userName')
      //   this.password = localStorage.getItem('userPassword')
      // }else{
      //   this.name = ''
      //   this.password = ''
      // }
      this.codePost()
      var interval = setInterval(function () {
        that.wx_loginPost()
        console.log(111)
      },3000)
      that.interval = interval
    },
    methods:{  //方法
      /*提交进行判断的函数 */
      submit:function(){
        var that = this;
        that.$axios.post(that.$store.state.postURL+'shequ_tuan_member.tuan_login',
          that.$qs.stringify({
            user_name:that.name,
            password:that.password
          })
        ).then(function (res) {
          // console.log(res);
          if(res.data.code == 200){
            localStorage.setItem("t_access_token",res.data.access_token);
            localStorage.setItem("t_member_id",res.data.member_id);
            localStorage.setItem("t_userName",that.name);
            that.$message({
              message: '登录成功',
              type: 'success'
            });
            clearInterval(that.interval);
            setTimeout(function() {
              that.$router.push({path:'/survey'})
            }, 300);
          }else{
            that.$message.error(res.data.datas.error);
          }
        }).catch(function (error) {
          // console.log(error);
        });
      },

      goregister(){
        this.$router.push({path:'/register'})
      },

      goforget:function(){
        this.$router.push({path:'/forget'})
      },
      erweimaclick(){
        this.wx_login_image = this.wx_login_image_2
        this.text = '微信扫一扫，立即登录'
      },
      codePost(){
        var that = this;
        that.$axios.post(that.$store.state.postURL+'shequ_connect_wx.index',
          that.$qs.stringify({

          })
        ).then(function (res) {
          // console.log(res);
          if(res.data.code == 200){
            that.wx_login_code = res.data.datas.wx_login_code
            that.wx_login_image_2 = res.data.datas.wx_login_image
            that.show = true
          }else{
            that.$message.error(res.data.datas.error);
          }
        }).catch(function (error) {
          // console.log(error);
        });
      },

      wx_loginPost(){
        var that = this;
        that.$axios.post(that.$store.state.postURL+'shequ_connect_wx.wx_login',
          that.$qs.stringify({
            wx_code:that.wx_login_code
          })
        ).then(function (res) {
          // console.log(res);
          if(res.data.code == 200){
            localStorage.setItem("t_access_token",res.data.access_token);
            localStorage.setItem("t_member_id",res.data.member_id);
            localStorage.setItem("t_userName",res.data.userName);
            localStorage.setItem("t_avatar",res.data.avatar);
            that.$message({
              message: '登录成功',
              type: 'success'
            });
            clearInterval(that.interval);
            setTimeout(function() {
              that.$router.push({path:'/survey'})
            }, 300);
          }else{

          }
        }).catch(function (error) {
          // console.log(error);
        });
      },

    }
  }
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
  .loginBox{
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
  }
  .loginform{
    position: absolute;
    top: 20%;
    width: 880px;
    height: 456px;
    /*background: #55489A;*/
    border-radius: 10px;
    left: 50%;
    margin-left: -440px;
    padding: 0 20px;
    box-sizing: border-box;
    display: flex;
  }
  .inputBox{
    width: 100%;
    height: 45px;
    position: relative;
    margin-top: 20px;
  }
  .inputBox input{
    border: none;
    background: none;
    width: 100%;
    height: 45px;
    box-sizing: border-box;
    border-bottom: 1px solid #B1C8B2;
    padding: 0 20px 0 45px;
    font-size: 14px;
    color: #fff;
  }
  .inputBox input:focus{
    border-color: #fff !important;
  }
  .inputBox input:-webkit-autofill{
    -webkit-box-shadow: 0 0 0px 1000px #00b944 inset !important;
    -webkit-text-fill-color: #fff !important;
  }
  .inputBox input::-webkit-input-placeholder{
    color:#B1C8B2;
  }
  .inputBox img{
    /*width: 25px;*/
    position: absolute;
    top: 10px;
    left: 10px;
  }
  .submitbtn{
    width: 100%;
    line-height: 50px;
    text-align: center;
    color: #81691f;
    background: #FFD03F;
    border-radius: 50px;
    margin-top: 50px;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 5px;
    margin-bottom: 10px;
  }
  .bottom_box{
    text-align: right;
    color:#fff;
    font-size: 16px;
  }
  .reset_password{
    font-size: 14px;
    color:#fff;
  }
  .register{
    font-size: 16px;
    margin-left: 5px;
  }
  .reset_password:hover,.register:hover{
    cursor: pointer;
    color: #00b944;
  }
  .error{
    color: red;
  }
  .inputBox .borderColor{
    border-color: red;
  }
  .title_b{
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    letter-spacing: 8px;
    /*margin-bottom: 50px;*/
  }
  .tab_title{
    width: 170px;
    height: 30px;
    margin: 0 auto;
    border: 1px solid #fff;
    border-radius: 30px;
    margin-top: 20px;
    margin-bottom: 60px;
    text-align: center;
    line-height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .tab_title div{
    width: 85px;
    text-align: center;
    font-size: 14px;
    color: #fff;
  }
  .tab_title .divA{
    background: #fff;
    color: #00b944;
    border-radius: 15px;
    width: 110px;
    font-weight: bold;
    border: 1px solid #fff;
    margin-right: -1px;
  }
  .tab_title div:hover{
    cursor: pointer;
  }
  .wx_login_image{
    width: 200px;
    height: 200px;
    border-radius: 5px;
  }
  .smlogin{
    color: #fff;
    text-align: center;
    font-size: 12px;
  }
  .z3{
    width: 420px;
    height: 456px;
  }
  .z4{
    width: 420px;
    height: 456px;
    background: #fff;
  }
  .t1{
    display: flex;
    align-items: center;
    padding-top: 40px;
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 1px;
    padding-left: 20px;
  }
  .t1_1{
    width: 5px;
    height: 21px;
    background: #00b944;
    margin-right: 10px;
  }
  .t2{
    padding-left: 35px;
    padding-top: 6px;
    color: #666;
  }
  .z4_img{
    width: 180px;
    height: 180px;
    display: block;
    margin: 30px auto;
  }
  .t3{
    text-align: center;
    color: #999;
  }
  .t4{
    width: 240px;
    text-align: center;
    height: 35px;
    line-height: 35px;
    background: #00b944;
    color: #fff;
    font-size: 14px;
    border-radius: 50px;
    margin: 30px auto;
  }
  .t4:hover{
    cursor: pointer;
    opacity: 0.9;
  }
</style>
