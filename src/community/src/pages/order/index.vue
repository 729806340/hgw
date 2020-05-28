<template>
  <div class="indexBox">
    <div class="tabsBox">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="团购订单列表" name="list"></el-tab-pane>
        <!--<el-tab-pane label="发起团购" name="start"></el-tab-pane>-->
      </el-tabs>
    </div>
    <List v-if="activeName=='list'"></List>
    <Start v-if="activeName=='start'"></Start>
  </div>
</template>

<script>
  import List from './list'
  import Start from './start'
    export default {
      name: "index",
      components:{
        List,
        Start
      },
      data () {
        return {
          activeName: 'list',
        }
      },
      methods: {  //业务
        handleClick(tab, event) {
          // console.log(tab, event);
          // console.log(tab.name);
          // this.$router.push({
          //   path: tab.name
          // });
          this.activeName = tab.name
        },
      }

    }
</script>

<style scoped>
  .tabsBox{
    padding: 20px;
  }
  .indexBox{
    width: 100%;
  }
</style>
