<template>
  <div class="infoBox">
    <!--<div class="tabsBox">-->
      <!--<el-tabs v-model="activeName">-->
        <!--<el-tab-pane label="我的资料" name="info"></el-tab-pane>-->
      <!--</el-tabs>-->
    <!--</div>-->
    <Attestation text="修改"></Attestation>
  </div>
</template>

<script>
  import Attestation from '../attestation'
  export default {
    name: "info",
    inject:["reload"],
    components:{
      Attestation
    },
    data () {
      return {
        activeName:'info',
      }
    },
    created(){  //载入前

    },
    methods:{  //业务

    }
  }
</script>

<style scoped>
.infoBox{
  width: 100%;
}
.tabsBox{
  padding: 20px;
}
</style>
