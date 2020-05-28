<template>
  <div class="messageBox">
    <p class="title">修改密码</p>
    <div class="width300">
      <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm">
        <el-form-item label="手机号：" prop="name">
          <div style="display: flex;height: 40px;align-items: center;">
            <div class="phone">{{ ruleForm.name }}</div>
            <!--<div class="btn sendbtn" @click="senClick" :class="senText=='获取验证码'?'':'nosen'">{{senText}}</div>-->
          </div>
        </el-form-item>
        <!--<el-form-item label="验证码：" prop="code">-->
          <!--<el-input type="code" v-model="ruleForm.code"></el-input>-->
        <!--</el-form-item>-->
        <el-form-item label="旧密码：" prop="oldPassword">
          <el-input type="oldPassword" v-model="ruleForm.oldPassword"></el-input>
        </el-form-item>
        <el-form-item label="新密码：" prop="newPassword">
          <el-input type="newPassword" v-model="ruleForm.newPassword"></el-input>
        </el-form-item>
        <el-form-item label="确认密码：" prop="confirmPassword">
          <el-input type="confirmPassword" v-model="ruleForm.confirmPassword"></el-input>
        </el-form-item>

        <el-button type="success" class="pushbtn" @click="submit('ruleForm')">保存</el-button>
      </el-form>
    </div>

    <!--<div id="captcha"></div>-->
    <!--<div v-show="waitShow" id="wait">正在加载极验...</div>-->
  </div>
</template>

<script>
import ElForm from "element-ui/packages/form/src/form";

export default {
  components: {ElForm},
  name: "modifyPassword",
  data() {
    var code = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入验证码'));
      } else {
        callback();
      }
    };
    var oldPassword = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入旧密码'));
      } else if (value.length < 6 || value.length > 20) {
        callback(new Error('长度在 6 到 20 个字符!'));
      } else {
        callback();
      }
    };
    var newPassword = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入新密码'));
      } else if (value.length < 6 || value.length > 20) {
        callback(new Error('长度在 6 到 20 个字符!'));
      } else {
        callback();
      }
    };
    var confirmPassword = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请再次输入密码'));
      } else if (value !== this.ruleForm.newPassword) {
        callback(new Error('两次输入密码不一致!'));
      } else {
        callback();
      }
    };
    return {
      ruleForm: {
        name: "18888888888",
        code: "",
        oldPassword:'',
        newPassword: "",
        confirmPassword: "",
      },
      rules: {
        code: [{required: true, validator: code, trigger: 'blur'},],
        oldPassword: [{ required: true, validator: oldPassword, trigger: 'blur' }],
        newPassword: [{ required: true, validator: newPassword, trigger: 'blur' }],
        confirmPassword: [{ required:true,validator: confirmPassword, trigger: 'blur' }]
      },
      text: "向右滑",
      verifyShow: false,
      senText: "获取验证码",
      times: "60", //60秒倒计时
      waitShow: true,
      captchaObj: {},
      result: {} // 是否已验证极验
    };
  },
  created() {
    this.getInitGtTest();
  },

  methods: {
    submit(formName) {
      var that = this
      this.$refs[formName].validate((valid) => {
        if (valid) {
          //验证成功
          that.$post('shequ_tuan_member.change_password',{
            password:that.ruleForm.oldPassword,
            new_password:that.ruleForm.newPassword,
          }).then(res => {
            // console.log(res);
            if(res.data.code == 200){
              that.$message({
                message: '修改成功',
                type: 'success'
              });
              localStorage.removeItem('t_access_token')
              localStorage.removeItem('t_member_id')
              localStorage.removeItem('t_userName')
              localStorage.removeItem('t_tuanzhang')
              setTimeout(function () {
                that.$router.push({path:'/login'})
              },500)
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
    //获取验证码
    senClick() {
      if (this.senText != "获取验证码") {
        return;
      }
      this.captchaObj.verify(); //显示验证码
    },
    //60秒倒计时
    countdown: function() {
      var that = this;
      if (that.times == 0) {
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
      var that = this;
      that.$axios
        .get(
          "https://www.geetest.com/demo/gt/register-slide?t=" +
            new Date().getTime()
        )
        .then(res => {
          if (res.status === 200) {
            const data = res.data;
            window.initGeetest(
              {
                gt: data.gt,
                challenge: data.challenge,
                offline: !data.success, // 表示用户后台检测极验服务器是否宕机
                new_captcha: data.new_captcha, // 用于宕机时表示是新验证码的宕机

                product: "bind", // 产品形式，包括：float，popup
                width: "100%",
                https: true
              },
              captchaObj => {
                // 箭头函数 若使用function 使用this报错
                this.captchaObj = captchaObj;
                captchaObj.appendTo("#captcha");
                captchaObj.onReady(() => {
                  this.waitShow = false; // 隐藏等待提示
                });
                captchaObj.onSuccess(() => {
                  /**
                   * 将极验结果赋值给result 便于在点击登录按钮时做判断 是否已经完成极验
                   */
                  that.result = captchaObj.getValidate();
                  console.log("验证成功");
                  // console.log(that.result);
                  that.$axios
                    .post(
                      that.$store.state.url + "access/send_sms",
                      that.$qs.stringify({
                        phone: that.name,
                        type: 2,
                        geetest_challenge: that.result.geetest_challenge,
                        geetest_validate: that.result.geetest_validate,
                        geetest_seccode: that.result.geetest_seccode
                      })
                    )
                    .then(function(res) {
                      // console.log(res);
                      if (res.data.code == 200) {
                        that.countdown();
                        that.$message({
                          message: "短信发送成功",
                          type: "success"
                        });
                      } else {
                        that.$message.error(res.data.datas.error);
                      }
                    })
                    .catch(function(error) {
                      // console.log(error);
                    });

                });
                captchaObj.onError(() => {
                  this.$Message.error("出错啦, 请稍后重试!");
                });
              }
            );
          }
        })
        .catch(err => {
          // console.log(err);
        });
    }
  }
};
</script>

<style scoped>
.messageBox {
  width: 100%;
  height: 100%;
  background: #fff;
  padding: 25px;
  box-sizing: border-box;
}
.title {
  font-size: 28px;
  margin-top: 30px;
  margin-left: 20px;
  margin-bottom: 50px;
}

.infoitem {
  display: flex;
  margin-bottom: 20px;
  margin-left: 20px;
}
.infoitem_left {
  height: 40px;
  line-height: 40px;
  font-size: 14px;
  width: 100px;
  padding-right: 12px;
}
.infoitem_right {
  display: flex;
  align-items: center;
}
.redx {
  color: red;
  margin-right: 5px;
}
.firmname {
  height: 40px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 4px 15px;
  box-sizing: border-box;
  font-size: 14px;
  width: 380px;
}
.pushbtn {
  background: #00b944;
  border-color: #00b944;
  width: 100px;
  height: 40px;
  margin-left: 140px;
}
.borderColor {
  border-color: red;
}
.error {
  color: red;
  margin-left: 5px;
}
.phone {
  height: 30px;
  width: 100px;
  line-height: 30px;
  font-size: 12px;
  color: #333;
}
.sendbtn {
  height: 30px;
  width: 100px;
  border: 1px solid #ccc;
  line-height: 30px;
  text-align: center;
  font-size: 12px;
  color: #333;
  letter-spacing: 1px;
  margin-left: 20px;
  border-radius: 4px;
}
.error_a {
  position: absolute;
  top: 45px;
}
  .width300{
    width: 400px;
  }
</style>
