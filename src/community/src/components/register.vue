<template>
    <div class="forget">
      <div class="loginBox">
        <img src="../../static/img/loginBG.png" alt="" style="width: 100%;height: 100vh;">
        <div class="loginform">
          <div class="title_b">您好，欢迎登录</div>
          <div class="tab_title"><div @click="gologin">登录</div><div class="divA">注册</div></div>
          <form method="post">
            <!--<input type="password" style="width: 0;height: 0;border: none;position: absolute;">-->
            <div class="inputBox">
              <img src="../../static/img/zh.png" alt="">
              <input type="text" v-model="name" placeholder="请输入手机号" autocomplete="off" required>
            </div>
            <div class="inputBox">
              <img src="../../static/img/mm.png" alt="">
              <input type="password" v-model="password" placeholder="设置密码（6-20位数字或英文组合密码）" autocomplete="off" required>
            </div>
            <div class="inputBox">
              <img src="../../static/img/mm.png" alt="">
              <input type="password" v-model="password_2" placeholder="确认密码（两次密码必须一致）" autocomplete="off" required>
            </div>
            <div class="inputBox" style="display: flex;align-items: center;position: relative;">
              <img src="../../static/img/yzm.png" alt="">
              <input type="number" v-model="code" placeholder="输入验证码" autocomplete="off" required class="sendipt">
              <div class="btn sendbtn" @click="senClick" :class="senText=='获取验证码'?'':'nosen'">{{senText}}</div>
            </div>
          </form>
          <div class="btn submitbtn" @click="registersubmit">立即注册</div>
        </div>
      </div>

      <div id="captcha"></div>
      <div v-show="waitShow" id="wait">正在加载极验...</div>
    </div>
</template>

<script>
    export default {
      name: "register",
      components:{

      },
      data () {
        return {
          name:'',
          code:'',
          password:'',
          password_2:'',
          text: '向右滑',
          verifyShow: false,
          senText:'获取验证码',
          times: "60", //60秒倒计时
          checked:true,
          waitShow: true,
          captchaObj: {},
          result: {}, // 是否已验证极验
          noresult: false,
          userActive:0,//用户类型
          ifusertype:true,
        }
      },
      created(){
        this.getInitGtTest()
      },
      mounted: function() {

      },
      methods:{
        gologin(){
          this.$router.push({path:'/login'})
        },
        //提交
        registersubmit(){
          var that = this;
          that.$axios.post(that.$store.state.postURL+'shequ_tuan_member.regist',
            that.$qs.stringify({
              type: 1,
              phone:that.name,
              password: that.password,
              captcha: that.code,
            })
          ).then(function (res) {
            // console.log(res);
            if(res.data.code == 200){
              that.$message({
                message: '注册成功',
                type: 'success'
              });
              setTimeout(function () {
                that.$router.push({path:'/login'})
              },500)
            }else{
              that.$message.error(res.data.datas.error);
            }
          }).catch(function (error) {
            // console.log(error);
          });
        },
        //获取验证码
        senClick(){
          // let verifyList = ['name'];
          // if(!this.$vuerify.check(verifyList)){
          //   return;
          // }
          if(this.senText != '获取验证码'){
            return
          }
          // if(!this.noresult){
          //   this.$message.error('手机号已经注册');
          //   return
          // }
          this.captchaObj.verify(); //显示验证码
        },
        //60秒倒计时
        countdown: function() {
          var that = this;
          if(that.times == 0) {
            that.senText = "获取验证码";
            that.times = 60;
            return false;
          } else {
            that.senText = that.times + "s";
            that.times--;
          }
          setTimeout(function() {
            that.countdown();
          }, 1000);
        },
        //极验证初始化
        getInitGtTest() {
          var that = this
          that.$axios.get(that.$store.state.postURL+"shequ_tuan_member.geetest")
            .then(res => {
              if (res.status === 200) {
                const data = res.data
                window.initGeetest({
                  gt: data.gt,
                  challenge: data.challenge,
                  offline: !data.success, // 表示用户后台检测极验服务器是否宕机
                  new_captcha: data.new_captcha, // 用于宕机时表示是新验证码的宕机

                  product: "bind", // 产品形式，包括：float，popup
                  width: "100%",
                  https: true
                }, captchaObj => { // 箭头函数 若使用function 使用this报错
                  this.captchaObj = captchaObj
                  captchaObj.appendTo("#captcha");
                  captchaObj.onReady(() => {
                    this.waitShow = false // 隐藏等待提示
                  });
                  captchaObj.onSuccess(() => {
                    /**
                     * 将极验结果赋值给result 便于在点击登录按钮时做判断 是否已经完成极验
                     */
                    that.result = captchaObj.getValidate();
                    // console.log('验证成功')
                    // console.log(that.result)
                    that.$axios.post(that.$store.state.postURL+'shequ_tuan_member.sendSms',
                      that.$qs.stringify({
                        phone:that.name,
                        type: 1,
                        geetest_challenge: that.result.geetest_challenge,
                        geetest_validate: that.result.geetest_validate,
                        geetest_seccode: that.result.geetest_seccode
                      })
                    ).then(function (res) {
                      // console.log(res);
                      if(res.data.code == 200){
                        that.countdown()
                        that.$message({
                          message: '短信发送成功',
                          type: 'success'
                        });
                      }else{
                        that.$message.error(res.data.datas.error);
                      }
                    }).catch(function (error) {
                      // console.log(error);
                    });
                  });
                  captchaObj.onError(() => {
                    this.$Message.error("出错啦, 请稍后重试!");
                  })
                })
              }
            })
            .catch(err => {
              // console.log(err)
            })
        },
        //电话号码是否注册
        ifphone(){
          var that = this
          that.$axios.post(that.$store.state.postURL+'access/check_phone',
            that.$qs.stringify({
              phone:that.name,
            })
          ).then(function (res) {
            // console.log(res);
            if(res.data.code == 200){
              that.noresult = true
            }else{
              that.$message.error(res.data.data.error);
              that.noresult = false
            }
          }).catch(function (error) {
            // console.log(error);
          });
        },
        //选择用户类型
        userAclick(e){
          this.userActive = e
        },
        //确认选择
        userselect(){
          if(this.userActive == 0){
            this.$message.error('请选择类型')
            return
          }
          this.ifusertype = false
        }
      }
    }
</script>

<style scoped>
  .loginBox{
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
  }

  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
  }
  input[type="number"]{
    -moz-appearance: textfield;
  }
  .loginform{
    position: absolute;
    top: 20%;
    width: 420px;
    height: 490px;
    /*background: #55489A;*/
    border-radius: 10px;
    left: 50%;
    margin-left: -210px;
    padding: 0 20px;
    box-sizing: border-box;
  }
  .title{
    text-align: center;
    color: #D0CCE7;
    font-size: 22px;
    line-height: 80px;
  }
  .inputBox{
    width: 100%;
    height: 45px;
    position: relative;
    margin-top: 18px;
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
    margin-top: 20px;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 5px;
  }
  .bottom_box{
    text-align: right;
    color:#9D95C0;
    font-size: 14px;
  }
  .reset_password{
    font-size: 14px;
    color:#9D95C0;
  }
  .register{
    font-size: 14px;
    color: #FFD03F;
  }
  .register:hover{
    cursor: pointer;
    color: #FFD03F;
  }
  .inputBox .sendipt{
    width: 100%;
    position: relative;
  }
  .sendbtn{
    height: 30px;
    width: 90px;
    line-height: 30px;
    text-align: center;
    font-size: 12px;
    background: #fff;
    color: #00b944;
    border: 1px solid #fff;
    border-radius: 25px;
    letter-spacing: 1px;
    position: absolute;
    right: 10px;
  }
  .error{
    color: red;
  }
  .inputBox .borderColor{
    border-color: red;
  }
  .zzBox{
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.6);
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
  }
  .nosen{
    background: none;
    border: 1px solid #fff;
    color: #fff;
  }
  .a_xy{
    color: #FFD03F;
  }
  .a_xy:hover{
    cursor: pointer;
    text-decoration: underline;
  }
  .error_a{
    position: absolute;
    top: 45px;
  }
  .title_b{
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    letter-spacing: 8px;
  }
  .tab_title{
    width: 170px;
    height: 30px;
    margin: 0 auto;
    border: 1px solid #fff;
    border-radius: 30px;
    margin-top: 20px;
    margin-bottom: 35px;
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
  .usertypeBox{
    width: 800px;
    height: 600px;
    background: #fff;
    border-radius: 4px;
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: -400px;
    margin-top: -350px;
  }
  .userTitle{
    font-size: 25px;
    font-weight: bold;
    text-align: center;
    line-height: 160px;
    color: #333;
    letter-spacing: 2px;
  }
  .userIconBox{
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
  }
  .userIconitemBox{
    text-align: center;
    margin: 0 50px;
    box-shadow: 0px 0px 26px #e7e6e6;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #fff;
  }
  .userIconitemBox:hover{
    cursor: pointer;
  }
  .userIconitemBoxActive{
    border-color: #00b944;
    position: relative;
    overflow: hidden;
  }
  .userIconitemBoxActive:before{
    content:"";
    /*background: url("../assets/img/jiaobiao.png");*/
    width: 40px;
    height: 40px;
    position: absolute;
    bottom: 0;
    right: 0;
    background-size: 100% 100%;
  }
  .userIconite_text{
    font-size: 18px;
    color: #333;
    margin-top: 15px;
  }
  .userIconite_text span{
    font-size: 12px;
  }
  .usertypeBtn{
    width: 480px;
    height: 50px;
    text-align: center;
    line-height: 50px;
    color: #fff;
    font-size: 16px;
    letter-spacing: 2px;
    background: #00b944;
    border-radius: 60px;
    margin: 0 auto;
    margin-top: 60px;
  }
  .user_z{
    text-align: center;
    margin-top: 30px;
    font-size: 14px;
    color: #333;
  }
  .user_z span{
    color: red;
    margin-right: 5px;
    font-size: 14px;
  }
</style>
