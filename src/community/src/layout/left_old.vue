<template>
  <div class="leftBox">
    <img src="../../static/img/1.png" alt="" class="topImg">
    <ul class="navlist">
      <router-link v-for="(item,index) in navlist" tag="li" :to="{path:'/home/'+item.path }" class="nav_item" :data-path="item.path" :data-active="item.active" @click.native="nav($event)"><img :src="item.img" alt="">{{item.name}}</router-link>

      <li class="nav_item" @click="configclick"><img src="" alt="">基本信息<i class="el-icon-arrow-down" :class="show_config?'itransform':''"></i></li>
      <el-collapse-transition>
        <ul v-show="show_config">
          <li class="subnav_item" :class="configTab==0?'serviceA':''" @click="configGo($event)" datapath="/home/config/attestation" dataindex="0" :configTab="configTab">企业认证</li>
          <li class="subnav_item" :class="configTab==1?'serviceA':''" @click="configGo($event)" datapath="/home/config/invoice" dataindex="1" :configTab="configTab">发票管理</li>
        </ul>
      </el-collapse-transition>

      <li class="nav_item" @click="goodsclick"><img src="" alt="">商品中心<i class="el-icon-arrow-down" :class="show_goods?'itransform':''"></i></li>
      <el-collapse-transition>
        <ul v-show="show_goods">
          <li class="subnav_item" :class="goodsTab==0?'serviceA':''" @click="goodsGo($event)" datapath="/home/goods/list" dataindex="0">商品列表</li>
          <li class="subnav_item" :class="goodsTab==1?'serviceA':''" @click="goodsGo($event)" datapath="/home/goods/brand" dataindex="1">品牌管理</li>
          <li class="subnav_item" :class="goodsTab==2?'serviceA':''" @click="goodsGo($event)" datapath="/home/goods/ladderprice" dataindex="2">阶梯价管理</li>
        </ul>
      </el-collapse-transition>
    </ul>
  </div>
</template>

<script>
    export default {
      name: "left",
      data() {
        return {
          show_config: false,
          configTab:2,

          show_goods: false,
          goodsTab:3,
        };
      },
      created(){
        var path = this.$route.path
        var pathArray = path.split('/');
        this.active = pathArray[2]
        //基本信息
        if(pathArray[2] != 'config'){this.configTab = 2}
        if(pathArray[2] == 'config'&&pathArray[3] == 'attestation'){this.configTab = 0;this.show_config = true}
        if(pathArray[2] == 'config'&&pathArray[3] == 'invoice'){this.configTab = 1;this.show_config = true}

        //商品
        if(pathArray[2] != 'goods'){this.goodsTab = 3}
        if(pathArray[2] == 'goods'&&pathArray[3] == 'list'){this.goodsTab = 0;this.show_goods = true}
        if(pathArray[2] == 'goods'&&pathArray[3] == 'added'){this.goodsTab = 0;this.show_goods = true}
        if(pathArray[2] == 'goods'&&pathArray[3] == 'brand'){this.goodsTab = 1;this.show_goods = true}
        if(pathArray[2] == 'goods'&&pathArray[3] == 'ladderprice'){this.goodsTab = 2;this.show_goods = true}
      },
      watch: {
        $route(to,from){
          var path = this.$route.path
          var pathArray = path.split('/');
          this.active = pathArray[2]
          //基本信息
          if(pathArray[2] != 'config'){this.configTab = 2}
          if(pathArray[2] == 'config'&&pathArray[3] == 'attestation'){this.configTab = 0;this.show_config = true}
          if(pathArray[2] == 'config'&&pathArray[3] == 'invoice'){this.configTab = 1;this.show_config = true}

          //商品
          if(pathArray[2] != 'goods'){this.goodsTab = 3}
          if(pathArray[2] == 'goods'&&pathArray[3] == 'list'){this.goodsTab = 0;this.show_goods = true}
          if(pathArray[2] == 'goods'&&pathArray[3] == 'added'){this.goodsTab = 0;this.show_goods = true}
          if(pathArray[2] == 'goods'&&pathArray[3] == 'brand'){this.goodsTab = 1;this.show_goods = true}
          if(pathArray[2] == 'goods'&&pathArray[3] == 'ladderprice'){this.goodsTab = 2;this.show_goods = true}
        }
      },
      methods: {
        //配置管理
        configclick(){
          this.show_config = !this.show_config
          this.show_goods = false
        },
        configGo(e){
          var path = e.target.getAttribute('datapath')
          var index = e.target.getAttribute('dataindex')
          this.$router.push({path: path})
          this.configTab = index
        },

        //商品中心
        goodsclick(){
          this.show_config = false
          this.show_goods = !this.show_goods
        },
        goodsGo(e){
          var path = e.target.getAttribute('datapath')
          var index = e.target.getAttribute('dataindex')
          this.$router.push({path: path})
          this.goodsTab = index
        },
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
    width: 15px;
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
    font-size: 13px;
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
