<template>
  <div class="leftBox">
    <img :src=avatar alt="" class="topImg">
    <ul class="navlist">
      <router-link tag="li" :to="{path:'/survey' }" class="nav_item" :class="active=='survey'?'serviceA':''" data-path="survey" data-active="survey"><img src="../../static/navicon/1.png" alt="">概况总览</router-link>
      <div v-for="(item,index) in nav">
        <li class="nav_item" @click="navclick($event)" :index="index"><img :src="item.icon" alt="">{{item.name}}<i class="el-icon-arrow-down" :class="item.show?'itransform':''"></i></li>
        <el-collapse-transition>
          <ul v-show="item.show">
            <li class="subnav_item" v-for="items in item.children" :class="item.tab==items.dataindex?'serviceA':''" @click="pathclick($event)" :datapath="items.datapath" :dataindex="items.dataindex" :configTab="item.tab" :index="index">{{items.name}}</li>
          </ul>
        </el-collapse-transition>
      </div>
    </ul>
  </div>
</template>

<script>
  export default {
    name: "left",
    data() {
      return {
        active:'survey',
        avatar:'../../static/img/1.png',
        nav:[
          {
            show:false,
            tab:2,
            name:'基本信息',
            path:'config',
            icon:'../../static/navicon/config.png',
            children:[
              {
                datapath:"/config/info",
                dataindex:0,
                name:'认证信息',
                path:'info'
              },
              {
                datapath:"/config/address",
                dataindex:1,
                name:'提货点',
                path:'address'
              }
            ]
          },
          {
            show:false,
            tab:2,
            name:'社区团购',
            path:'choice',
            icon:'../../static/navicon/3.png',
            children:[
              {
                datapath:"/choice/index",
                dataindex:0,
                name:'团购列表',
                path:'index'
              },
              // {
              //   datapath:"/choice/goods",
              //   dataindex:0,
              //   name:'团购列表',
              //   path:'goods'
              // },
              // {
              //   datapath:"/choice/distribution",
              //   dataindex:1,
              //   name:'我发起的团购',
              //   path:'distribution'
              // },
            ]
          },
          // {
          //   show:false,
          //   tab:1,
          //   name:'订单管理',
          //   path:'order',
          //   icon:'../../static/navicon/11.png',
          //   children:[
          //     {
          //       datapath:"/order/index",
          //       dataindex:0,
          //       name:'团购订单',
          //       path:'index'
          //     }
          //   ]
          // },
          {
            show:false,
            tab:1,
            name:'结算管理',
            path:'settlement',
            icon:'../../static/navicon/13.png',
            children:[
              {
                datapath:"/settlement/list",
                dataindex:0,
                name:'结算列表',
                path:'list'
              }
            ]
          }
        ]
      };
    },
    created(){
      this.navshow(this)
      if(localStorage.getItem('t_avatar')){
        this.avatar = localStorage.getItem('t_avatar')
      }
    },
    watch: {
      $route(to,from){
        this.navshow(this)
      }
    },
    methods: {
      navclick(e){
        var that = this
        var index = e.target.getAttribute('index')
        for(var i = 0;i<that.nav.length;i++){
          if(i!=index){
            that.nav[i].show = false
          }else{
            that.nav[index].show = !that.nav[index].show
          }
        }
      },
      pathclick(e){
        var that = this
        if(!localStorage.getItem('t_tuanzhang')){
          that.$message.error('您还未申请为团长~');
          return
        }
        var path_arr = that.$route.path
        var pathArray = path_arr.split('/');

        var path = e.target.getAttribute('datapath')
        var index = e.target.getAttribute('dataindex')
        var navindex = e.target.getAttribute('index')
        if(that.nav[navindex].path == pathArray[1] && that.nav[navindex].children[index].path == pathArray[2]){
          return
        }
        that.active = ''
        that.$router.push({path: path})
        that.nav[navindex].tab = index
      },
      navshow(e){
        var that = e
        var path = that.$route.path
        var pathArray = path.split('/');
        if(!localStorage.getItem('t_access_token')){
          that.$router.push({path:'/login'})
          return
        }
        if(localStorage.getItem('t_access_token') && pathArray[1] == ''){
          that.$router.push({path:'/survey'})
        }
        that.active = pathArray[1]
        if(that.active == 'survey'){
          for(var i=0;i<that.nav.length;i++){
            that.nav[i].tab = that.nav[i].children.length
            that.nav[i].show = false
          }
          return
        }
        for(var i=0;i<that.nav.length;i++){
          if(pathArray[1] != that.nav[i].path){
            that.nav[i].tab = that.nav[i].children.length
            that.nav[i].show = false
          }else{
            that.nav[i].show = true
            for(var y=0;y<that.nav[i].children.length;y++){
              if(pathArray[2] == that.nav[i].children[y].path){
                that.nav[i].tab = that.nav[i].children[y].dataindex
              }
            }
          }
        }
      }
    }
  }
</script>

<style scoped>
  .leftBox{
    width: 200px;
    background: #041324;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 9;
    overflow-y: scroll;
  }
  .leftBox::-webkit-scrollbar {
    width: 0px;
  }
  .topImg{
    width: 60px;
    height: 60px;
    display: block;
    margin: 30px auto;
    border-radius: 50%;
    overflow: hidden;
  }
  .nav_item{
    display: flex;
    align-items: center;
    color: #f9f9f9;
    font-size: 14px;
    letter-spacing: 1px;
    height: 55px;
    position: relative;
    line-height: 55px;
  }
  .nav_item img{
    /*width: 15px;*/
    margin-left: 35px;
    margin-right: 10px;
  }
  .nav_item i{
    font-size: 12px;
    position: absolute;
    right: 25px;
    top: 22px;
    transition: .3s ease all;
    font-weight: bold;
  }
  .nav_item:hover{
    cursor: pointer;
  }
  .activeNo:hover{
    background: rgba(255,255,255,0.08);
  }
  .subnav_item{
    color: #f9f9f9;
    font-size: 12px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    background: #020c17;
    transition: all .3s ease;
  }
  .subnav_item:hover{
    cursor: pointer;
  }
  .activeClass{
    background: #F2F2F2;
    color: #283043;
    border-left: 5px solid #03B941;
  }
  .itransform{
    transform:rotate(180deg);
  }
  .serviceA{
    background: #00b944;
  }
  .navlist{
    padding-bottom: 30px;
  }
</style>
