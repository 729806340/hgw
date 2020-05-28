<template>
    <div class="indexBox">
      <div class="tabsBox">
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="所有团购" name="all"></el-tab-pane>
          <el-tab-pane label="我发起的团购" name="my"></el-tab-pane>
        </el-tabs>
      </div>
      <Goods v-if="activeName=='all'"></Goods>
      <Distribution v-if="activeName=='my'"></Distribution>
    </div>
</template>

<script>
  import Distribution from './distribution'
  import Goods from './goods'
    export default {
      name: "index",
      components:{
        Distribution,
        Goods
      },
      data(){
        return {
          activeName:"all",
        }
      },
      methods: {  //业务
        handleClick(tab, event) {
          this.activeName = tab.name
        },
      }
    }
</script>

<style scoped>
.indexBox{
  width: 100%;
}
.tabsBox{
  padding: 20px;
}
</style>
