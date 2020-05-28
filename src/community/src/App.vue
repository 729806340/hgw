<template>
  <div id="app">
    <router-view v-if="isRouterAlive"></router-view>
  </div>
</template>

<script>
export default {
  name: 'App',
  provide () {
    return {
      reload: this.reload
    }
  },
  data () {
    return {
      isRouterAlive: true
    }
  },
  created(){  //载入前

  },
  methods: {
    //页面刷新
    reload () {
      this.isRouterAlive = false
      this.$nextTick(function () {
        this.isRouterAlive = true
      })
    }
  },
  mounted(){
    // 关闭浏览器窗口的时候清空浏览器缓存在localStorage的数据
    // window.onbeforeunload = function (e) {
    //   var storage = window.localStorage;
    //   storage.clear()
    // }
  }
}
</script>

<style>
  *{
    margin: 0;
    padding: 0;
    font-size: 12px;
    font-family: '苹方';
    box-sizing: border-box;
  }
  ul{
    list-style: none;
  }
  body{
    background: #F2F2F2;
  }
  button:hover,.btn:hover{
    cursor: pointer !important;
    opacity: 0.9 !important;
  }
  .el-table__header th, .el-table__header tr {
    background-color: #f3f3f3 !important;
  }
  .pageBox {
    margin: 50px 0;
    text-align: center;
  }
  .amap-marker-label {
    padding: 12px 18px !important;
    /* box-shadow: 1px 1px 10px #999; */
    border: 1px solid #eee !important;
    border-radius: 4px;
  }
  .amap-marker-label::after{
    content: "";
    width: 25px !important;
    height: 16px !important;
    background: url("../static/img/mapicon.png") !important;
    position: absolute;
    top: 38px;
    left: 48px;
  }
  .elimg_img img {
    width: auto !important;
    height: auto !important;
    max-width: 100% !important;
    max-height: 100% !important;
  }
  .el-image-viewer__wrapper .el-icon-circle-close {
    font-size: 40px !important;
    color: #fff !important;
  }
  .csaleTimeBox .el-range-editor.el-input__inner {
    width: 400px !important;
  }
  .page-editor {
    width: 100% !important;
  }
  .el-image__inner:hover{
    cursor: pointer;
  }
  .config_btn button{
    width: 45%;
    padding: 0 !important;
    line-height: 38px !important;
  }
</style>
