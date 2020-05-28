// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import axios from 'axios'
import Vuex from 'vuex'
import store from './store/index.js'
import qs from 'qs'
import VueQuillEditor from 'vue-quill-editor'  //富文本
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

import config_ from './config'


//动态更改页面title
router.beforeEach((to, from, next) => {
  /* 路由发生变化修改页面title */
  if (to.meta.title) {
    document.title = to.meta.title
  }
  next()
})



Vue.config.productionTip = false

store.state.postURL = config_.url
Vue.use(ElementUI)
Vue.prototype.$axios = axios
Vue.use(Vuex)
Vue.prototype.$qs = qs;
Vue.use(VueQuillEditor)

Vue.prototype.$post = function (api,data) {
  data.member_id = localStorage.getItem('t_member_id');
  data.access_token = localStorage.getItem('t_access_token');
  data = qs.stringify(data);
  var ox = axios.post(this.$store.state.postURL + api,data)
  return ox;
}

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
})
