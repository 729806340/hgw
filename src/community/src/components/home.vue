<template>
  <div class="home">
    <Left></Left>
    <div class="rightBox">
      <Top></Top>
      <router-view/>
    </div>
  </div>
</template>

<script>
  import Left from '../layout/left'
  import Top from '../layout/top'
    export default {
      name: "home",
      components:{
        Left,
        Top
      },
      data () {
        return {

        }
      },
      created(){  //载入前
        // if(localStorage.getItem('access_token')){
        //   this.$store.state.access_token = localStorage.getItem('access_token')
        //   this.$store.state.userName = localStorage.getItem('userName')
        // }else{
        //   this.$router.push({path:'/login'})
        // }
      },
      methods:{  //业务

      }
    }
</script>

<style scoped>
  .home{
    display: flex;
  }
  .rightBox{
    /*flex: 1;*/
    width: 100%;
    display: flex;
    /*flex-direction: column;*/
    /*padding: 80px 10px 10px 210px;*/
    box-sizing: border-box;
    height: 100vh;
    background: #fff;
    border-top: 80px #f0f5fc solid;
    border-right: 10px #f0f5fc solid;
    border-bottom: 10px #f0f5fc solid;
    border-left: 210px #f0f5fc solid;
    overflow-y: auto;
  }
  /*修改滚动条样式*/
  .rightBox::-webkit-scrollbar{
    width:5px;
    height:10px;
    /**/
  }
  .rightBox::-webkit-scrollbar-track{
    background: rgb(239, 239, 239);
    border-radius:2px;
  }
  .rightBox::-webkit-scrollbar-thumb{
    background: #bfbfbf;
    border-radius:10px;
  }
  .rightBox::-webkit-scrollbar-thumb:hover{
    background: #333;
  }
  .rightBox::-webkit-scrollbar-corner{
    background: #179a16;
  }
</style>
