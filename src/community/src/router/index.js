import Vue from 'vue'
import Router from 'vue-router'

import Login from '@/components/login'

import Register from '@/components/register'

import Forget from '@/components/forget'

import Home from '@/components/home'

import Survey from '@/pages/survey'

import Attestation from '@/pages/attestation'

import ModifyPassword from '@/pages/modifyPassword'

import Configinfo from '@/pages/config/info'
import Configaddress from '@/pages/config/address'

// import Choicegoods from '@/pages/choice/goods'
// import Choicedistribution from '@/pages/choice/distribution'
import Choiceindex from '@/pages/choice/index'

import Orderindex from '@/pages/order/index'

import Settlementlist from '@/pages/settlement/list'



Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: Login,
    },
    // {
    //   path: '/register',
    //   name: 'Register',
    //   component: Register,
    // },
    // {
    //   path: '/forget',
    //   name: 'Forget',
    //   component: Forget,
    // },
    {
      path: '/',
      name: 'home',
      component: Home,
      children:[
        {
          path: 'survey',
          component: Survey,
        },
        {
          path:'modifyPassword',
          component:ModifyPassword
        },
        {
          path:'attestation',
          component:Attestation
        },
        {
          path:'config/address',
          component:Configaddress
        },
        {
          path:'config/info',
          component:Configinfo
        },
        // {
        //   path:'choice/goods',
        //   component:Choicegoods
        // },
        // {
        //   path:'choice/distribution',
        //   component:Choicedistribution
        // },
        {
          path:'choice/index',
          component:Choiceindex
        },
        {
          path:'order/index',
          component:Orderindex
        },
        {
          path:'settlement/list',
          component:Settlementlist
        },
      ]
    }
  ]
})
