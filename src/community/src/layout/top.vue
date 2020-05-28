<template>
  <div class="topBox">
    <div class="leftBox">
      <div class="title"><img src="../../static/img/LOGO.png" alt="">社区团购平台</div>
    </div>
    <div class="rightBox">
      <ul class="right_ul">
        <!--<li class="right_itemBox"><img src="../assets/img/dh.png" alt=""></li>-->
        <li class="right_itemBox right_itemBox_xx">
          <el-badge class="item">
            <img src="" alt="">
          </el-badge>

        </li>

        <li class="right_itemBox right_itemBox_h" style="margin-right: 12px;">
          <img :src=avatar alt="" class="name" style="width: 30px;height: 30px;border-radius: 50%;overflow: hidden;border: 1px solid #eee;">{{userName}}<i class="el-icon-arrow-down"></i>
          <el-collapse-transition>
            <ul class="right_itemBox_li">
              <li class="subnav_item" @click="goindex"><img src="../../static/img/back.png" alt="">返回首页</li>
              <!--<li class="subnav_item" @click="changePasswordHandler"><img src="../../static/img/xiugai.png" alt="">密码修改</li>-->
              <li class="subnav_item" @click="outclick" style="border-bottom: none;"><img src="../../static/img/out.png" alt="">退出登录</li>
            </ul>
          </el-collapse-transition>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
    export default {
        name: "top",
      data() {
        return {
          userName:'',
          avatar:'../../static/img/1.png',
        };
      },
      created(){  //载入前
        // this.userName = localStorage.getItem('t_userName').substr(0, 3) + '****' + localStorage.getItem('t_userName').substring(7, 11)
        this.userName = localStorage.getItem('t_userName')
        if(localStorage.getItem('t_avatar')){
          this.avatar = localStorage.getItem('t_avatar')
        }
      },
      methods: {
        //返回首页
        goindex(){
          this.$router.push({path:'/survey'})
        },
        //修改密码
        changePasswordHandler() {
          this.$router.push({path:'/modifyPassword'})
          // window.location.href = 'http://www.hangowa.com/member/index.php?act=member_security&op=auth&type=modify_pwd'
        },
        //退出登录
        outclick(){
          localStorage.removeItem('t_access_token')
          localStorage.removeItem('t_member_id')
          localStorage.removeItem('t_userName')
          localStorage.removeItem('t_tuanzhang')
          localStorage.removeItem('t_avatar')
          this.$router.push({path:'/login'})
        },
      }
    }
</script>

<style scoped>
  .topBox{
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 70px;
    padding: 0 30px;
    background: #fff;
    position: fixed;
    top: 0;
    width: 100%;
    padding-left: 210px;
    left: 0;
    box-sizing: border-box;
    z-index: 8;
    /*border-bottom: 10px solid #F2F2F2;*/
    box-shadow: 0px -3px 20px #999;
  }
  .leftBox{
    display: flex;
    align-items: center;
    height: 65px;
  }
  .title{
    font-size: 18px;
    font-weight: bold;
    display: flex;
    align-items: center;
    color: #333;
  }
  .title img{
    padding-right: 20px;
    border-right: 1px solid #ddd;
    margin-right: 20px;
    margin-left: 20px;
  }
  .title img:hover{
    cursor: pointer;
  }
  .btnBox{
    height: 45px;
    display: flex;
    align-items: center;
    margin-left: 15px;
    background: #FEEDD9;
    border-radius: 2px;
  }
  .btnBox span{
    font-size: 12px;
    color: #666;
    margin: 0 10px;
  }
  .btnBox button{
    padding: 5px 10px;
    margin-right: 15px;
  }
  .right_ul{
    display: flex;
    align-items: center;
    height: 70px;
  }
  .right_itemBox{
    padding: 0 22px;
    height: 100%;
    align-items: center;
    display: flex;
    font-size: 14px;
    position: relative;
  }
  .right_itemBox img{
    /*width: 25px;*/
  }
  .rightBox{
    height: 65px;
    display: flex;
    align-items: center;
  }
  .right_itemBox .name{
    margin-right: 5px;
  }
  .right_itemBox i{
    font-size: 13px;
    -webkit-transition: .3s ease all;
    transition: .3s ease all;
    margin-left: 5px;
  }
  .itransform{
    transform:rotate(180deg);
  }
  .right_itemBox_li{
    position: absolute;
    width: 150px;
    background: #fff;
    top: 70px;
    right: 0px;
    border: 1px solid #DCDFE6;
    display: none;
    z-index: 999;
  }
  .right_itemBox_li li{
    text-align: center;
    line-height: 50px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #eee;
  }
  .right_itemBox_li li img{
    /*width: 18px;*/
    margin-right: 10px;
  }
  .right_itemBox_li li:hover{
    background-color: #f7fdf9;
    cursor: pointer;
  }
  .right_itemBox_h:hover .right_itemBox_li{
    display: block;
  }
  .xxNoneBox{
    width: 360px;
    position: absolute;
    border: 1px solid #DCDFE6;
    background: #fff;
    right: -110px;
    top: 70px;
    display: none;
    z-index: 999;
  }
  .xxNoneBox li{
    display: flex;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding: 14px 8px;
  }
  .xxNoneBox li:hover{
    cursor: pointer;
    background: #f7fdf9;
  }
  .xxNone_icon{
    margin-right: 10px;
  }
  .xxNone_text{
    width: 240px;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .xxNone_time{
    flex: 1;
    text-align: right;
    color: #999;
  }
  .right_itemBox_xx:hover .xxNoneBox{
    display: block;
  }
</style>
