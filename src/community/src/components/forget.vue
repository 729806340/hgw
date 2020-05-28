<template>
    <div class="forget" style="position: relative;">
      <div class="loginBox">
        <img src="../../static/img/loginBG.png" alt="" style="width: 100%;height:100vh;">
        <div class="loginform">
          <div class="title">忘记密码</div>
          <form method="post">
            <input type="password" style="width: 0;height: 0;border: none;position: absolute;">
            <div class="inputBox">
              <img src="../../static/img/zh.png" alt="">
              <input type="text" v-model="name" placeholder="请输入手机号" autocomplete="off" required>
            </div>
            <div class="inputBox" style="display: flex;align-items: center;position: relative;">
              <img src="../../static/img/mm.png" alt="">
              <input type="number" v-model="code" placeholder="输入验证码" autocomplete="off" required class="sendipt">
              <div class="btn sendbtn" @click="senClick" :class="senText=='获取验证码'?'':'nosen'">{{senText}}</div>
            </div>
            <div class="inputBox">
              <img src="../../static/img/mm.png" alt="">
              <input type="password" v-model="password" placeholder="设置密码（6-20位数字或英文组合密码）" autocomplete="off" required>
            </div>
          </form>
          <div class="btn submitbtn" @click="editorPassword">立即修改</div>
          <div class="bottom_box">
            密码想起来了，去
            <span class="register" @click="gologin">登录</span>
          </div>
        </div>
      </div>
      <div id="captcha"></div>
      <div v-show="waitShow" id="wait">正在加载极验...</div>
    </div>
</template>

<script>
    export default {
      name: "forget",
      components:{

      },
      data () {
        return {
          name:'',
          code:'',
          password:'',
          text: '向右滑',
          verifyShow: false,
          senText:'获取验证码',
          times: "60", //60秒倒计时
          waitShow: true,
          captchaObj: {},
          result: {} // 是否已验证极验
        }
      },
      created(){
        this.getInitGtTest()
      },
      methods:{
        gologin(){
          this.$router.push({path:'/login'})
        },
        //提交
        editorPassword(){
          var that = this;
          that.$axios.post(that.$store.state.postURL+'shequ_tuan_member.reset',
            that.$qs.stringify({
              type: 3,
              phone:that.name,
              password: that.password,
              captcha: that.code
            })
          ).then(function (res) {
            // console.log(res);
            if(res.data.code == 200){
              that.$message({
                message: '密码修改成功',
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
                        type: 3,
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
      }
    }
</script>

<style scoped>
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
  }
  .loginBox{
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
  }
  .loginform{
    position: absolute;
    top: 20%;
    width: 420px;
    height: 440px;
    /*background: #55489A;*/
    border-radius: 10px;
    left: 50%;
    margin-left: -210px;
    padding: 0 20px;
    box-sizing: border-box;
  }
  .title{
    text-align: center;
    color: #fff;
    font-size: 22px;
    line-height: 80px;
    letter-spacing: 4px;
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
    position: absolute;
    top: 10px;
    left: 10px;
  }
  .submitbtn{
    width: 100%;
    line-height: 50px;
    text-align: center;
    color: #81691f;
    background: #FFD03F ;
    border-radius: 50px;
    margin-top: 50px;
    font-size: 16px;
    font-weight: bold;
    letter-spacing: 5px;
    margin-bottom: 10px;
  }
  .bottom_box{
    text-align: right;
    color:#B1C8B2;
    font-size: 14px;
  }
  .reset_password{
    font-size: 14px;
    color:#9D95C0;
  }
  .register{
    font-size: 14px;
    color: #fff;
  }
  .register:hover{
    cursor: pointer;
    color: #00b944;
  }
  .inputBox .sendipt{
    width: 100%;
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
  .error_a{
    position: absolute;
    top: 45px;
  }
</style>
