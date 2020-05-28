import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
  state:{
    //存储数据
    postURL:'', // 线上
    // postURL:'http://test.hangowa.com/communityApi/?method=', //测试
    access_token:localStorage.getItem('t_access_token'),
    userName:localStorage.getItem('t_userName'),
    member_id:localStorage.getItem('t_member_id'),
    t_tuanzhang:localStorage.getItem('t_tuanzhang'),
  },
  mutations:{
    //数据处理方法

    //获取时分秒
    getTime(time) {
      var hour = parseInt(time / 3600);
      if (hour < 10) {
        hour = '0' + hour;
      }
      var fen = parseInt((time - hour * 3600) / 60);
      if (fen < 10) {
        fen = '0' + fen;
      }
      var second = time - hour * 3600 - fen * 60;
      if (second < 10) {
        second = '0' + second;
      }
      var aa = [];
      aa.push(hour);
      aa.push(fen);
      aa.push(second);
      // console.log(aa);
      return aa;
    },
    //制保留2位小数，如：2，会在2后面补上00.即2.00
    toDecimal2(x) {
      var f = parseFloat(x);
      if (isNaN(f)) {
        return false;
      }
      var f = Math.round(x*100)/100;
      var s = f.toString();
      var rs = s.indexOf('.');
      if (rs < 0) {
        rs = s.length;
        s += '.';
      }
      while (s.length <= rs + 2) {
        s += '0';
      }
      return s;
    },
  },
  getters:{
    //数据包装
  }
})
